<?php

namespace Drupal\hellocoop\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\hellocoop\HelloRequest\HelloRequestInterface;
use Drupal\hellocoop\HelloResponse\HelloResponseInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class HelloCoopController extends ControllerBase {

  /**
   * The HelloRequest service.
   *
   * @var HelloRequestInterface
   */
  protected $helloRequest;

  /**
   * The HelloResponse service.
   *
   * @var HelloResponseInterface
   */
  protected $helloResponse;

  /**
   * Constructs a HelloCoopController object.
   *
   * @param HelloRequestInterface $helloRequest
   *   The HelloRequest service.
   * @param HelloResponseInterface $helloResponse
   *   The HelloResponse service.
   */
  public function __construct(HelloRequestInterface $helloRequest, HelloResponseInterface $helloResponse) {
    $this->helloRequest = $helloRequest;
    $this->helloResponse = $helloResponse;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('hellocoop.hello_request'),
      $container->get('hellocoop.hello_response')
    );
  }

  /**
   * Handles API requests.
   */
  public function handle() {
    // Example of using HelloRequest to fetch a query parameter.
    $param = $this->helloRequest->fetch('example_param', 'default_value');

    // Example of using HelloResponse to create a response.
    $response = $this->helloResponse->json([
      'message' => 'Hello from the API!',
      'param' => $param,
    ]);

    return new JsonResponse(json_decode($response, true));
  }

}
