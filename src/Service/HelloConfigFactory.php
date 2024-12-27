<?php

/**
 * @file
 * Contains \Drupal\hellocoop\Service\HelloConfigFactory.
 *
 * This file provides a factory service class for creating configured
 * HelloConfig objects. It handles dependency injection and implements
 * login and logout callback functionalities used by the HelloCoop module.
 */

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
 * This class creates instances of the HelloConfig class, configures them with
 * API routes, app ID, and secret values, and injects dependencies for callback
 * functions used for login and logout functionalities.
 */
class HelloConfigFactory {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * The file repository service.
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
   *   Used for saving user profile pictures.
   */
  public function __construct(
    EntityTypeManagerInterface $entityTypeManager,
    FileRepositoryInterface $fileRepository
  ) {
    $this->entityTypeManager = $entityTypeManager;
    $this->fileRepository = $fileRepository;
  }

  /**
   * Login synchronous callback function.
   *
   * @param array $helloWalletResponse
   *   The response data from the Hello Wallet service.
   *
   * @return array
   *   The modified or original response data.
   */
  public function loginCallback(array $helloWalletResponse = []): array {
    if (empty($helloWalletResponse['payload'])) {
      return $helloWalletResponse;
    }

    $payload = $helloWalletResponse['payload'];

    // Load user by email.
    $users = $this->entityTypeManager->getStorage('user')
      ->loadByProperties(['mail' => $payload['email']]);

    $user = $users ? reset($users) : NULL;

    // Create a new user if one doesn't exist.
    if (!$user) {
      $user = User::create([
        'name' => $payload['name'],
        'mail' => $payload['email'],
        'status' => 1, // Active user.
      ]);
      $user->enforceIsNew();
    }
    else {
      // Update the user's name.
      $user->set('name', $payload['name']);
    }

    // Save the profile picture if exist in the payload.
    if (!empty($payload['picture'])) {
      $externalImage = $this->saveExternalImageAsFile($payload['picture']);
      if ($externalImage && $externalImage->getFileUri()) {
        $file = File::create([
          'uri' => $externalImage->getFileUri(),
        ]);
        $file->save();
        if ($file->id()) {
          $user->set('user_picture', $file->id());
        } else {
          throw new \LogicException('Failed to save file entity or retrieve its ID.');
        }
      } else {
        throw new \LogicException('Failed to save external image as file.');
      }
    }

    // Save the user and finalize login.
    $user->save();
    user_login_finalize($user);

    return $helloWalletResponse;
  }

  /**
   * Logout synchronous callback function.
   *
   * @return void
   */
  public function logoutCallback(): void {
    user_logout();
  }

  /**
   * Creates a configured HelloConfig instance.
   *
   * @return \HelloCoop\Config\HelloConfig
   *   A configured HelloConfig object.
   */
  public function createConfig() {
    $config = \Drupal::config('hellocoop.settings');

    return new HelloConfig(
      $config->get('api_route'),
      $config->get('api_route') . '?op=auth',
      $config->get('api_route') . '?op=login',
      $config->get('api_route') . '?op=logout',
      FALSE,
      $config->get('app_id'),
      \Drupal::request()->getSchemeAndHttpHost() . $config->get('api_route'),
      \Drupal::request()->getHost(),
      $config->get('secret'),
      [$this, 'loginCallback'],
      [$this, 'logoutCallback']
    );
  }

  /**
   * Saves an external image as a file.
   *
   * @param string $url
   *   The external image URL.
   *
   * @return \Drupal\file\FileInterface|null
   *   The saved file entity or NULL on failure.
   */
  private function saveExternalImageAsFile(string $url): ?FileInterface {
    try {
      $data = (string) \Drupal::httpClient()->get($url)->getBody();
      return $this->fileRepository->writeData(
        $data,
        'public://user_pictures/' . uniqid('profile_', TRUE) . '.jpg',
        FileSystemInterface::EXISTS_REPLACE
      );
    } catch (\Exception $e) {
      \Drupal::logger('hellocoop')->error('Failed to save external image: @message', ['@message' => $e->getMessage()]);
      return NULL;
    }
  }

}
