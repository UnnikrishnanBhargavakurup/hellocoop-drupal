<?php

namespace Drupal\hello_login\HelloRequest;

use HelloCoop\HelloRequest\HelloRequestInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * For fetching cookies and HTTP request params.
 *
 * @phpcsSuppress Drupal.Commenting.DocComment.MissingShort
 */
class HelloRequest implements HelloRequestInterface {
  /**
   * @var \Symfony\Component\HttpFoundation\Request|null
   */
  protected $currentRequest;

  /**
   * Constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack service.
   */
  public function __construct(RequestStack $requestStack) {
    $this->currentRequest = $requestStack->getCurrentRequest();
  }

  /**
   * {@inheritdoc}
   */
  public function has(string $key): bool {
    return $this->currentRequest->query->has($key) || $this->currentRequest->request->has($key);
  }


  /**
   * {@inheritdoc}
   */
  public function fetch(string $key, $default = NULL): ?string {
    return $this->currentRequest->get($key, $default);
  }

  /**
   * {@inheritdoc}
   */
  public function fetchMultiple(array $keys): array {
    $values = [];
    foreach ($keys as $key) {
      $values[$key] = $this->currentRequest->get($key, NULL);
    }
    return $values;
  }

  /**
   * {@inheritdoc}
   */
  public function fetchHeader(string $key, $default = NULL): ?string {
    return $this->currentRequest->headers->get($key, $default);
  }

  /**
   * {@inheritdoc}
   */
  public function getCookie(string $name): ?string {
    return $this->currentRequest->cookies->get($name, NULL);
  }

  /**
   * {@inheritdoc}
   */
  public function getRequestUri(): string {
    return $this->currentRequest->getRequestUri();
  }

  /**
   * {@inheritdoc}
   */
  public function getMethod(): string {
    return $this->currentRequest->getMethod();
  }

}
