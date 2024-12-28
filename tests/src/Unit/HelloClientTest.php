<?php

declare(strict_types=1);

namespace Drupal\Tests\hellocoop\Unit;

use Drupal\hellocoop\HelloClient;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\file\FileRepositoryInterface;
use Drupal\Core\Entity\EntityTypeRepositoryInterface;
use Drupal\Core\Session\SessionManagerInterface;
use Drupal\user\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use GuzzleHttp\Client;

class HelloClientTest extends TestCase {

  /**
   * The file repository mock.
   *
   * @var \Drupal\file\FileRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $fileRepository;

  /**
   * The entity type manager mock.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $entityTypeManager;

  /**
   * The entity type repository mock.
   *
   * @var \Drupal\Core\Entity\EntityTypeRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $entityTypeRepository;

  /**
   * The session manager mock.
   *
   * @var \Drupal\Core\Session\SessionManagerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $sessionManager;

  /**
   * The HTTP client mock.
   *
   * @var \GuzzleHttp\Client|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $httpClient;

  /**
   * The container.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerBuilder
   */
  protected $container;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    // Create mocks for the required services.
    $this->fileRepository = $this->createMock(FileRepositoryInterface::class);
    $this->entityTypeManager = $this->createMock(EntityTypeManagerInterface::class);
    $this->entityTypeRepository = $this->createMock(EntityTypeRepositoryInterface::class);
    $this->sessionManager = $this->createMock(SessionManagerInterface::class);

    // Create the mock HTTP client and its response.
    $this->httpClient = $this->createMock(Client::class);

    $mockResponse = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
    $mockStream = $this->createMock(\Psr\Http\Message\StreamInterface::class);
    // Define the behavior of the StreamInterface.
    $mockStream->method('__toString')
      ->willReturn('mocked image data');

    // Define the behavior of the ResponseInterface.
    $mockResponse->method('getBody')
      ->willReturn($mockStream);

    // Define the behavior of the `http_client` service.
    $this->httpClient->method('get')
      ->willReturn($mockResponse);

    // Create the container and set the services.
    $this->container = new ContainerBuilder();
    $this->container->set('file.repository', $this->fileRepository);
    $this->container->set('entity_type.manager', $this->entityTypeManager);
    $this->container->set('entity_type.repository', $this->entityTypeRepository);
    $this->container->set('session_manager', $this->sessionManager);
    $this->container->set('http_client', $this->httpClient);

    // Manually set the container in Drupal.
    \Drupal::setContainer($this->container);
  }

  /**
   * Tests the loginUpdate method.
   */
  public function testLoginUpdate(): void {
    $payload = [
      'email' => 'test@example.com',
      'name' => 'Test User',
      'picture' => 'http://example.com/image.jpg',
    ];

    $user = $this->createMock(User::class);
    $user->expects($this->once())->method('save');

    $this->entityTypeManager->expects($this->once())
      ->method('getStorage')
      ->with('user')
      ->willReturn($this->createMock(\Drupal\Core\Entity\EntityStorageInterface::class));

    $client = new HelloClient($this->entityTypeManager, $this->fileRepository);
    $client->loginUpdate($payload);

    // Assertions can be extended based on more complex scenarios.
  }

  /**
   * Tests the logOut method.
   */
  public function testLogOut(): void {
    $this->sessionManager->expects($this->once())->method('destroy');

    $client = new HelloClient($this->entityTypeManager, $this->fileRepository);
    $client->logOut();

    // Add assertions for expected behaviors or outcomes.
  }
}
