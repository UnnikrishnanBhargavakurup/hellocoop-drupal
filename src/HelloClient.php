<?php

/**
 * @file
 * Contains \Drupal\hellocoop\HelloClient.
 *
 * Provides functionality to manage user login, creation, updates, and logout
 * within the HelloCoop module. It handles user data processing, profile picture
 * management, and invokes relevant events for custom extensions.
 */

namespace Drupal\hellocoop;

use Drupal\file\FileInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\file\FileRepositoryInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\file\Entity\File;
use Drupal\user\Entity\User;
use Drupal\externalauth\ExternalAuthInterface;

/**
 * Provides functionality for managing HelloCoop user login and logout.
 */
class HelloClient {

  /**
   * The User entity storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected EntityStorageInterface $userStorage;

  /**
   * The file repository service.
   *
   * @var \Drupal\file\FileRepositoryInterface
   */
  protected FileRepositoryInterface $fileRepository;

  /**
   * The external auth.
   *
   * @var \Drupal\externalauth\ExternalAuthInterface
   */
  protected ExternalAuthInterface $externalAuth;

  /**
   * Constructs a HelloClient object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager service.
   * @param \Drupal\file\FileRepositoryInterface $fileRepository
   *   The file repository service for saving user profile pictures.
   */
  public function __construct(
    EntityTypeManagerInterface $entityTypeManager,
    FileRepositoryInterface $fileRepository,
    ExternalAuthInterface $externalAuth
  ) {
    $this->userStorage = $entityTypeManager->getStorage('user');
    $this->fileRepository = $fileRepository;
    $this->externalAuth = $externalAuth;
  }

  /**
   * Logs in or updates a user based on the provided payload.
   *
   * @param array $payload
   *   An associative array containing user information.
   */
  public function loginUpdate(array $payload): void {
    $user = $this->loadUser($payload);

    if(!$user) {
      $user = $this->createUser($payload);
    }

    $this->updateUserFields($user, $payload);
    $user->save();
    \Drupal::moduleHandler()->invokeAll('hellocoop_user_login', ['user' => $user]);
    $this->externalAuth->userLoginFinalize($user, $payload['sub'], 'hellocoop');
  }

  /**
   * Logs out the current user.
   */
  public function logOut(): void {
    \Drupal::moduleHandler()->invokeAll('hellocoop_user_logout', []);
    user_logout();
  }

  /**
   * Loads an existing user or creates a new one.
   *
   * @param array $payload
   *   The user data payload.
   *
   * @return \Drupal\user\Entity\User
   *   The loaded or created user entity.
   */
  private function loadUser(array $payload): User {
    /** @var \Drupal\user\UserInterface|bool $account */
    return $this->externalAuth->load($payload['sub'], 'hellocoop');
  }

    /**
   * Loads an existing user or creates a new one.
   *
   * @param array $payload
   *   The user data payload.
   *
   * @return \Drupal\user\Entity\User
   *   The loaded or created user entity.
   */
  private function createUser(array $payload): User {
    $userData = [
      'name' => $payload['name'],
      'mail' => $payload['email'],
      'init' => $payload['email'],
      'status' => 1, // Active user.
    ];
    return $this->externalAuth->register($payload['sub'], 'hellocoop', $userData);
  }

  /**
   * Updates user fields based on the provided payload.
   *
   * @param \Drupal\user\Entity\User $user
   *   The user entity to update.
   * @param array $payload
   *   The user data payload.
   */
  private function updateUserFields(User $user, array $payload): void {
    
    if (isset($payload['name']) && !empty($payload['name'])) {
      $user->set('name', $payload['name']);
    }

    $accountByMail = $this->userStorage->loadByProperties(['mail' => $payload['email']]);
    $accountByMail = $accountByMail ? reset($accountByMail) : NULL;

    if (empty($accountByMail) || ($accountByMail->id() == $user->id())) {
      $user->set('mail', $payload['email']);
    }

    if(isset($payload['picture'])) {
      $this->updateUserPicture($user, $payload['picture']);
    }

    // Additional fields can be added here with proper validations.
    // Example:
    // if (isset($payload['phone_number']) && $this->isValidPhoneNumber($payload['phone_number'])) {
    //   $user->set('field_phone_number', $payload['phone_number']);
    // }
  }

  /**
   * Validates phone number format.
   *
   * @param string $phoneNumber
   *   The phone number to validate.
   *
   * @return bool
   *   TRUE if the phone number is valid, FALSE otherwise.
   */
  private function isValidPhoneNumber(string $phoneNumber): bool {
    // Basic validation example (can be replaced with more robust logic).
    return preg_match('/^\+?[0-9]{10,15}$/', $phoneNumber) === 1;
  }

  /**
   * Updates the user's profile picture.
   *
   * @param \Drupal\user\Entity\User $user
   *   The user entity to update.
   * @param string $pictureUrl
   *   The URL of the profile picture.
   *
   * @throws \LogicException
   *   If the profile picture cannot be saved.
   */
  private function updateUserPicture(User $user, string $pictureUrl): void {
    $externalImage = $this->saveUserImageAsFile($pictureUrl);
    if ($externalImage && $externalImage->getFileUri()) {
      $file = File::create([
        'uri' => $externalImage->getFileUri(),
      ]);
      $file->save();
      if ($file->id()) {
        $user->set('user_picture', $file->id());
      }
      else {
        throw new \LogicException('Failed to save file entity or retrieve its ID.');
      }
    }
    else {
      throw new \LogicException('Failed to save external image as file.');
    }
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
  private function saveUserImageAsFile(string $url): ?FileInterface {
    try {
      $data = (string) \Drupal::httpClient()->get($url)->getBody();
      return $this->fileRepository->writeData(
        $data,
        'public://user_pictures/' . uniqid('profile_', TRUE) . '.jpg',
        FileSystemInterface::EXISTS_REPLACE
      );
    }
    catch (\Exception $e) {
      \Drupal::logger('hellocoop')->error('Failed to save external image: @message', ['@message' => $e->getMessage()]);
      return NULL;
    }
  }
}
