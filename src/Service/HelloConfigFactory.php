<?php

namespace Drupal\hellocoop\Service;

use HelloCoop\Config\HelloConfig;
use Drupal\externalauth\ExternalAuthInterface;

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
   * Constructs a HelloConfigFactory object.
   *
   *   The first dependency for setting up callback functions.
   *
   * @param \Drupal\externalauth\ExternalAuthInterface $externalAuth
   *   The second dependency for setting up callback functions.
   */
  public function __construct(ExternalAuthInterface $externalAuth) {
    $this->externalAuth = $externalAuth;
  }

  /**
   * Login synchronous callback function.
   *
   * @return mixed
   *   The result of the login callback logic.
   */
  public function loginCallback() {
    // Perform login logic using $this->someDependency1
    // return $this->externalAuth->loginRegister();
  }

  /**
   * Logout synchronous callback function.
   *
   * @return mixed
   *   The result of the logout callback logic.
   */
  public function logoutCallback() {
    // Perform logout logic using $this->someDependency2.
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
   * @param string $apiRoute
   *   The API route to be used in the configuration.
   * @param string $appId
   *   The application ID to be used in the configuration.
   * @param string $secret
   *   The secret key to be used in the configuration.
   *
   * @return \HelloCoop\Config\HelloConfig
   *   The configured HelloConfig object with injected callbacks.
   */
  public function createConfig($apiRoute, $appId, $secret) {
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

}
