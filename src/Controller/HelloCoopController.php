<?php

namespace Drupal\hellocoop\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use HelloCoop\HelloRequest\HelloRequestInterface;
use HelloCoop\HelloResponse\HelloResponseInterface;
use HelloCoop\Renderers\PageRendererInterface;
use HelloCoop\Config\ConfigInterface;
use HelloCoop\HelloClient;

/**
 * For handling callback requests from HelloCoop.
 */
class HelloCoopController extends ControllerBase {

  /**
   * The HelloRequest service.
   *
   * @var \HelloCoop\HelloRequest\HelloRequestInterface
   */
  protected HelloRequestInterface $helloRequest;

  /**
   * The HelloResponse service.
   *
   * @var \HelloCoop\HelloResponse\HelloResponseInterface
   */
  protected HelloResponseInterface $helloResponse;

  /**
   * The PageRenderer service.
   *
   * @var \HelloCoop\Renderers\PageRendererInterface
   */
  protected PageRendererInterface $pageRenderer;

  /**
   * The HelloConfig service.
   *
   * @var \HelloCoop\Config\ConfigInterface
   */
  protected ConfigInterface $helloConfig;

  /**
   * The HelloClient service.
   *
   * @var \HelloCoop\HelloClient
   */
  protected HelloClient $helloClient;

  /**
   * Constructs a HelloCoopController object.
   *
   * @param \HelloCoop\HelloRequest\HelloRequestInterface $helloRequest
   *   The HelloRequest service.
   * @param \HelloCoop\HelloResponse\HelloResponseInterface $helloResponse
   *   The HelloResponse service.
   * @param \HelloCoop\Renderers\PageRendererInterface $pageRenderer
   *   The PageRenderer service.
   * @param \HelloCoop\Config\ConfigInterface $helloConfig
   *   The HelloConfig service.
   * @param \HelloCoop\HelloClient $helloClient
   *   The HelloClient service.
   */
  public function __construct(
    HelloRequestInterface $helloRequest,
    HelloResponseInterface $helloResponse,
    PageRendererInterface $pageRenderer,
    ConfigInterface $helloConfig,
    HelloClient $helloClient,
  ) {
    $this->helloRequest = $helloRequest;
    $this->helloResponse = $helloResponse;
    $this->pageRenderer = $pageRenderer;
    $this->helloConfig = $helloConfig;
    $this->helloClient = $helloClient;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {

    // Use the HelloConfigFactory service.
    $configFactory = $container->get('hellocoop.config_factory');
    $helloConfig = $configFactory->createConfig();

    return new static(
      $container->get('hellocoop.hello_request'),
      $container->get('hellocoop.hello_response'),
      $container->get('hellocoop.page_renderer'),
      $helloConfig,
      $container->get('hellocoop.hello_client')
    );
  }

  /**
   * Handles Hello routes.
   */
  public function handle() {
    return $this->helloClient->route();
  }

}
