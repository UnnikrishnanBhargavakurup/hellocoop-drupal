<?php

namespace Drupal\hello_login\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use HelloCoop\HelloClient;

/**
 * For handling callback requests from HelloCoop.
 */
class HelloLoginController extends ControllerBase {

  /**
   * The HelloClient service.
   *
   * @var \HelloCoop\HelloClient
   */
  protected HelloClient $helloClient;

  /**
   * Constructs a HelloCoopController object.
   *
   * @param \HelloCoop\HelloClient $helloClient
   *   The HelloClient service.
   */
  public function __construct(HelloClient $helloClient) {
    $this->helloClient = $helloClient;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('hello_login.client_service')
    );
  }

  /**
   * Handles Hello routes.
   */
  public function handle() {
    return $this->helloClient->route();
  }

}
