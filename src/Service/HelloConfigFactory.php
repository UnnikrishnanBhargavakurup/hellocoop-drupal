<?php

namespace Drupal\hellocoop\Service;

use HelloCoop\Config\HelloConfig;
use Drupal\externalauth\ExternalAuthInterface;
use Drupal\file\Entity\File;
use Drupal\Core\File\FileSystemInterface;

/**
 * Factory class for creating HelloConfig objects.
 *
 * This class is responsible for creating instances of the HelloConfig class,
 * configuring them with API route, app ID, and secret values, and injecting
 * dependencies for callback functions. These callbacks are used by the
 * HelloConfig instance to perform additional logic.
 *
 * The factory class ensures that dependencies are injected correctly and
 * provides a clean separation between configuration creation and business logic.
 */
class HelloConfigFactory {

  /**
   * The second dependency for setting up callbacks.
   *
   * @var \Drupal\externalauth\ExternalAuthInterface
   */
  protected ExternalAuthInterface $externalAuth;

  /**
   * The file system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected FileSystemInterface $fileSystem;

  /**
   * Constructs a HelloConfigFactory object.
   *
   * Initializes the dependencies required for setting up callback functions.
   *
   * @param \Drupal\externalauth\ExternalAuthInterface $externalAuth
   *   Used for registering or logging in the user from Hello Wallet.
   * @param \Drupal\file\Entity\FileSystemInterface $fileSystem
   *   Used for saving the user's profile picture to Drupal.
   */
  public function __construct(ExternalAuthInterface $externalAuth, FileSystemInterface $fileSystem) {
    $this->externalAuth = $externalAuth;
    $this->fileSystem = $fileSystem;
  }


  /**
   * Login synchronous callback function.
   *
   * @return mixed
   *   The result of the login callback logic.
   */
  public function loginCallback(array $helloWalletRespose = []) {
    if (isset($helloWalletRespose['payload']) === FALSE) {
      return $helloWalletRespose;
    }

    $payload = $helloWalletRespose['payload'];
    $fileId = $this->saveExternalImageAsFile($payload['picture']);

    $accountData = [];
    $accountData['name'] = $payload['name'];
    $accountData['email'] = $payload['email'];
    $accountData['user_picture'] = [
      'target_id' => $fileId,
    ];
    // Perform login logic using externalAuth.
    $this->externalAuth->loginRegister(
      $payload['email'],
      'hellocoop',
      $accountData
    );

    return $helloWalletRespose;
  }

  /**
   * Logout synchronous callback function.
   *
   * @return mixed
   *   The result of the logout callback logic.
   */
  public function logoutCallback() {
    // Perform logout logic here.
    return user_logout();
  }

  /**
   * Creates a configured HelloConfig instance.
   *
   * This method creates a new HelloConfig object, sets up the required
   * API route, app ID, and secret key, and injects callback functions using
   * the provided dependencies.
   *
   * The callbacks are functions that are executed when specific actions are
   * triggered by the HelloConfig instance. The dependencies provide the logic
   * for these actions.
   *
   * @return \HelloCoop\Config\HelloConfig
   *   The configured HelloConfig object with injected callbacks.
   */
  public function createConfig() {
    $config = \Drupal::config('hellocoop.settings');
    $apiRoute = $config->get('api_route');
    $appId = $config->get('app_id');
    $secret = $config->get('secret');

    $config = new HelloConfig(
      $apiRoute,
      $apiRoute . '?op=auth',
      $apiRoute . '?op=login',
      $apiRoute . '?op=logout',
      FALSE,
      $appId,
      \Drupal::request()->getSchemeAndHttpHost() . $apiRoute,
      \Drupal::request()->getHost(),
      $secret,
    // Pass the login callback.
      [$this, 'loginCallback'],
    // Pass the logout callback.
      [$this, 'logoutCallback']
    );

    return $config;
  }

  /**
   * Private function to download an image from an external URL and return the file ID.
   *
   * @param string $image_url
   *   The external URL of the image.
   *
   * @return int
   *   The file ID of the saved image.
   */
  private function saveExternalImageAsFile(string $image_url) {
    // Fetch the image data from the URL.
    $image_data = file_get_contents($image_url);

    // Check if the image was fetched successfully.
    if ($image_data === FALSE) {
      throw new \Exception('Unable to fetch image from URL: ' . $image_url);
    }

    // Generate a unique file name based on the image URL.
    $file_name = basename($image_url);
    // Directory for saving images.
    $file_directory = 'public://user_pictures/';

    // Ensure the directory exists.
    $this->fileSystem->prepareDirectory($file_directory, FileSystemInterface::CREATE_DIRECTORY);
    // Create the file path.
    $file_path = $file_directory . $file_name;
    // Save the image data to the file system.
    file_put_contents($file_path, $image_data);
    // Create the file entity.
    $file = File::create([
      'uri' => $file_path,
    // Mark the file as permanent.
      'status' => 1,
    ]);

    // Save the file entity.
    $file->save();

    // Return the file ID.
    return $file->id();
  }

}
