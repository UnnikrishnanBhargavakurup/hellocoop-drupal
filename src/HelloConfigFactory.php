<?php

/**
 * @file
 * Contains \Drupal\hellocoop\HelloConfigFactory.
 *
 * Provides a factory service class for creating configured HelloConfig objects.
 * It handles dependency injection and implements login and logout callback
 * functionalities used by the HelloCoop module.
 */

namespace Drupal\hellocoop;

use HelloCoop\Config\HelloConfig;
use Drupal\hellocoop\HelloClient;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\Config;

/**
 * Factory class for creating HelloConfig objects.
 *
 * This class creates instances of the HelloConfig class, configures them with
 * API routes, app ID, and secret values, and injects dependencies for callback
 * functions used for login and logout functionalities.
 */
class HelloConfigFactory {

  /**
   * The HelloClient service.
   *
   * @var \Drupal\hellocoop\HelloClient
   */
  protected HelloClient $helloClient;

  /**
   * The HelloCoop configuration.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected Config $config;

  /**
   * Constructs a HelloConfigFactory object.
   *
   * @param \Drupal\hellocoop\HelloClient $helloClient
   *   The HelloClient service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The configuration factory service.
   */
  public function __construct(HelloClient $helloClient, ConfigFactoryInterface $configFactory) {
    $this->helloClient = $helloClient;
    $this->config = $configFactory->get('hellocoop.settings');
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
    if (empty($helloWalletResponse['payload']) || empty($helloWalletResponse['payload']['email'])) {
      return $helloWalletResponse;
    }

    $this->helloClient->loginUpdate($helloWalletResponse['payload']);
    return $helloWalletResponse;
  }

  /**
   * Logout synchronous callback function.
   */
  public function logoutCallback(): void {
    $this->helloClient->logOut();
  }

  /**
   * Creates a configured HelloConfig instance.
   *
   * @return \HelloCoop\Config\HelloConfig
   *   A configured HelloConfig object.
   */
  public function createConfig(): HelloConfig {
    return new HelloConfig(
      $this->config->get('api_route'),
      $this->config->get('api_route') . '?op=auth',
      $this->config->get('api_route') . '?op=login',
      $this->config->get('api_route') . '?op=logout',
      FALSE,
      $this->config->get('app_id'),
      \Drupal::request()->getSchemeAndHttpHost() . $this->config->get('api_route'),
      \Drupal::request()->getHost(),
      $this->config->get('secret'),
      [$this, 'loginCallback'],
      [$this, 'logoutCallback']
    );
  }

}
