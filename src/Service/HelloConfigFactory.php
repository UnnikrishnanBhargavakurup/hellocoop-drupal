<?php

namespace Drupal\hellocoop\Service;

use HelloCoop\Config\HelloConfig;
use Drupal\file\Entity\File;
use Drupal\file\FileRepositoryInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\file\FileInterface;
use Drupal\user\Entity\User;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Factory class for creating HelloConfig objects.
 *
 * This class is responsible for creating instances of the HelloConfig class,
 * configuring them with API route, app ID, and secret values, and injecting
 * dependencies for callback functions. These callbacks are used by the
 * HelloConfig instance to perform login and logout functionalities.
 */
class HelloConfigFactory {


  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * The file system service.
   *
   * @var \Drupal\file\FileRepositoryInterface
   */
  protected FileRepositoryInterface $fileRepository;

  /**
   * Constructs a HelloConfigFactory object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager service.
   * @param \Drupal\file\FileRepositoryInterface $fileRepository
   *   Used for saving the user's profile picture to Drupal.
   */
  public function __construct(
    EntityTypeManagerInterface $entityTypeManager,
    FileRepositoryInterface $fileRepository,
  ) {
    $this->entityTypeManager = $entityTypeManager;
    $this->fileRepository = $fileRepository;
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

    $user = $this->entityTypeManager
      ->getStorage('user')
      ->loadByProperties(['mail' => $payload['email']]);

    $user = $user ? reset($user) : NULL;

    if (!$user) {
      // User doesn't exist, create a new one.
      $user = User::create([
        'name' => $payload['name'],
        'mail' => $payload['email'],
      // Active user.
        'status' => 1,
      ]);
      $user->enforceIsNew();
    }
    else {
      // User exists, update their name.
      $user->set('name', $payload['name']);
    }

    if (!empty($payload['picture'])) {
      $file = File::create([
        'uri' => $this->saveExternalImageAsFile($payload['picture'])->getFileUri(),
      ]);
      $file->save();
      $user->set('user_picture', $file->id());
    }

    $user->save();

    user_login_finalize($user);

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
   * for login and logout functionalities
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
   * @param string $url
   *   The external URL of the image.
   *
   * @return \Drupal\file\Entity\FileInterface
   *   Saved file
   */
  private function saveExternalImageAsFile(string $url):FileInterface {
    $data = (string) \Drupal::httpClient()->get($url)->getBody();
    return $this->fileRepository->writeData($data, 'public://user_pictures/', FileSystemInterface::EXISTS_REPLACE);
  }

}
