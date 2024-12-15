<?php

namespace Drupal\hellocoop\Renderers;

use Drupal\Core\Render\RendererInterface;
use HelloCoop\Renderers\PageRendererInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Implements the PageRendererInterface for rendering different types of pages.
 */
class PageRenderer implements PageRendererInterface {

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Constructs a PageRenderer object.
   *
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   */
  public function __construct(RendererInterface $renderer) {
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public function renderErrorPage(string $error, string $errorDescription, string $errorURI, string $targetURI = '/'): string {
    $content = [
      '#theme' => 'error_page',
      '#error' => $error,
      '#error_description' => $errorDescription,
      '#error_uri' => $errorURI,
      '#target_uri' => $targetURI,
    ];

    return $this->renderer->renderRoot($content);
  }

  /**
   * {@inheritdoc}
   */
  public function renderSameSitePage(): string {
    $content = [
      '#theme' => 'same_site_page',
      '#title' => t('Same-Site Page'),
      '#content' => t('This is a placeholder for a same-site page.'),
    ];

    return $this->renderer->renderRoot($content);
  }

  // phpcs:disable
  /**
   * {@inheritdoc}
   */
  public function renderRedirectURIBounce(): string {
    $content = [
      '#theme' => 'redirect_uri_bounce_page',
      '#title' => t('Redirecting...'),
      '#content' => t('You are being redirected to the target URI.'),
    ];

    return $this->renderer->renderRoot($content);
  }
  // phpcs:enable

  /**
   * {@inheritdoc}
   */
  public function renderWildcardConsole(string $uri, string $targetURI, string $appName, string $redirectURI): string {
    $content = [
      '#theme' => 'wildcard_console_page',
      '#uri' => $uri,
      '#target_uri' => $targetURI,
      '#app_name' => $appName,
      '#redirect_uri' => $redirectURI,
    ];

    return $this->renderer->renderRoot($content);
  }

  /**
   * Factory method for instantiating the renderer service.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The service container.
   *
   * @return \Drupal\hellocoop\Renderers\PageRenderer
   *   The PageRenderer instance.
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('renderer'));
  }

}
