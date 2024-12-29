<?php

declare(strict_types=1);

namespace Drupal\Tests\hellocoop\Unit;

use Drupal\hellocoop\HelloClient;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\file\FileRepositoryInterface;
use Drupal\externalauth\ExternalAuthInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\user\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use GuzzleHttp\Client;

/**
 * Unit tests for the HelloClient class.
 */
class HelloClientTest extends TestCase {

  /**
   * Mock for the file repository service.
   *
   * @var \Drupal\file\FileRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $fileRepositoryMock;

  /**
   * Mock for the entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $entityTypeManagerMock;

  /**
   * Mock for the external authentication service.
   *
   * @var \Drupal\externalauth\ExternalAuthInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $externalAuthMock;

  /**
   * The container used to set mocked services.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerBuilder
   */
  protected $container;

  /**
   * Mock for the HTTP client service.
   *
   * @var \GuzzleHttp\Client|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $httpClientMock;

  /**
   * Sets up the test environment by creating mock services and setting up the container.
   */
  protected function setUp(): void {
    // Create mock for the file repository service.
    $this->fileRepositoryMock = $this->createMock(FileRepositoryInterface::class);

    // Create mock for the entity type manager service.
    $this->entityTypeManagerMock = $this->createMock(EntityTypeManagerInterface::class);

    // Create mock for the external authentication service.
    $this->externalAuthMock = $this->createMock(ExternalAuthInterface::class);

    // Create mock for the HTTP client.
    $this->httpClientMock = $this->createMock(Client::class);
    $mockResponse = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
    $mockStream = $this->createMock(\Psr\Http\Message\StreamInterface::class);

    // Define behavior for the mock HTTP response.
    $mockStream->method('__toString')->willReturn('mocked image data');
    $mockResponse->method('getBody')->willReturn($mockStream);
    $this->httpClientMock->method('get')->willReturn($mockResponse);

    // Set up the container and add services.
    $this->container = new ContainerBuilder();
    $this->container->set('file.repository', $this->fileRepositoryMock);
    $this->container->set('entity_type.manager', $this->entityTypeManagerMock);
    $this->container->set('http_client', $this->httpClientMock);

    // Set the container in the global Drupal container.
    \Drupal::setContainer($this->container);
  }

  /**
   * Tests the loginUpdate method of the HelloClient class.
   */
  public function testLoginUpdate(): void {
    // Prepare mock payload.
    $payload = [
      'email' => 'test@example.com',
      'name' => 'Test User',
      'picture' => 'http://example.com/image.jpg',
    ];

    // Mock user storage.
    $userStorageMock = $this->createMock(EntityStorageInterface::class);
    $this->entityTypeManagerMock->expects($this->once())
      ->method('getStorage')
      ->with('user')
      ->willReturn($userStorageMock);

    // Mock user entity.
    $userMock = $this->createMock(User::class);
    $userMock->expects($this->once())->method('save');

    // Mock externalAuth to load user.
    $this->externalAuthMock->expects($this->once())
      ->method('load')
      ->with('test@example.com', 'hellocoop')
      ->willReturn($userMock);

    // Instantiate the HelloClient.
    $client = new HelloClient($this->entityTypeManagerMock, $this->fileRepositoryMock, $this->externalAuthMock);

    // Test loginUpdate method.
    $client->loginUpdate($payload);

    // Assertions can be extended for additional scenarios.
  }

  /**
   * Tests the logOut method of the HelloClient class.
   */
  public function testLogOut(): void {
    // Mock module handler to simulate invoking hooks.
    $moduleHandlerMock = $this->createMock(\Drupal\Core\Extension\ModuleHandlerInterface::class);
    $moduleHandlerMock->expects($this->once())
      ->method('invokeAll')
      ->with('hellocoop_user_logout', []);

    // Set the mocked module handler in the container.
    $this->container->set('module_handler', $moduleHandlerMock);

    // Mock the user logout function.
    $this->expectNotToPerformAssertions();

    // Instantiate the HelloClient and test the method.
    $client = new HelloClient($this->entityTypeManagerMock, $this->fileRepositoryMock, $this->externalAuthMock);
    $client->logOut();
  }
}
