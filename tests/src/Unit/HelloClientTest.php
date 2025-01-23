<?php

declare(strict_types=1);

namespace Drupal\Tests\HelloLogin\Unit;

use Drupal\hello_login\HelloClient;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\file\FileRepositoryInterface;
use Drupal\externalauth\ExternalAuthInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\user\Entity\User;
use Drupal\file\Entity\File;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\file\FileInterface;


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
   * Mock for the HTTP client service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected ModuleHandlerInterface $moduleHandlerMock;

  /**
   * Mock for the user storage service.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $userStorageMock;

  /**
   * Mock for the user storage service.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $fileStorageMock;

  /**
   * Sets up the test environment by creating mock services and setting up the container.
   */
  protected function setUp(): void {
    $this->fileRepositoryMock = $this->createMock(FileRepositoryInterface::class);
    $this->entityTypeManagerMock = $this->createMock(EntityTypeManagerInterface::class);
    $this->userStorageMock = $this->createMock(EntityStorageInterface::class);
    $this->fileStorageMock = $this->createMock(EntityStorageInterface::class);

    $this->entityTypeManagerMock->expects($this->exactly(2))
    ->method('getStorage')
    ->willReturnMap([
        ['user', $this->userStorageMock],
        ['file', $this->fileStorageMock],
    ]);

    $this->externalAuthMock = $this->createMock(ExternalAuthInterface::class);
    $this->moduleHandlerMock = $this->createMock(ModuleHandlerInterface::class);

    $this->httpClientMock = $this->createMock(Client::class);
    $mockResponse = $this->createMock(ResponseInterface::class);
    $mockStream = $this->createMock(StreamInterface::class);

    // Mock HTTP client behavior for profile picture.
    $mockStream->method('__toString')->willReturn('mocked image data');
    $mockResponse->method('getBody')->willReturn($mockStream);
    $this->httpClientMock->method('get')->willReturn($mockResponse);

    $this->container = new ContainerBuilder();
    $this->container->set('file.repository', $this->fileRepositoryMock);
    $this->container->set('entity_type.manager', $this->entityTypeManagerMock);
    $this->container->set('http_client', $this->httpClientMock);
    $this->container->set('module_handler', $this->moduleHandlerMock);

    \Drupal::setContainer($this->container);
  }

  /**
   * Tests the loginUpdate method of the HelloClient class.
   */
  public function testLoginUpdate(): void {
    // Prepare mock payload.
    $payload = [
      'sub' => 'sub_vvCgtpv35lDgQpHtxmpvmnxK_2nZ',
      'email' => 'test@example.com',
      'name' => 'Test User',
      'picture' => 'http://example.com/image.jpg',
    ];

    // Mock existing user loading.
    $userMock = $this->createMock(User::class);
    $userMock->method('id')->willReturn(1);
    $this->externalAuthMock->method('load')
      ->with($payload['sub'], 'hello_login')
      ->willReturn(false);

    // Mock user creation.
    $this->externalAuthMock->method('register')
      ->with($payload['sub'], 'hello_login', [
        'name' => $payload['name'],
        'mail' => $payload['email'],
        'init' => $payload['email'],
        'status' => 1,
      ])
      ->willReturn($userMock);

    // Mock user field updates.
    $userMock->expects($this->exactly(3))
    ->method('set')
    ->willReturnCallback(function ($key, $value) use ($payload) {
        static $callCount = 0;
        $callCount++;

        switch ($callCount) {
            case 1:
                $this->assertEquals('name', $key);
                $this->assertEquals($payload['name'], $value);
                break;
            case 2:
                $this->assertEquals('mail', $key);
                $this->assertEquals($payload['email'], $value);
                break;
            case 3:
                $this->assertEquals('user_picture', $key);
                $this->assertEquals(123, $value);
                break;
            default:
                $this->fail('Unexpected call to set method.');
        }
    });

    $userMock->expects($this->once())->method('save');

    $fileMock = $this->createMock(FileInterface::class);
    $fileMock->method('getFileUri')->willReturn('public://user_pictures/mock_profile.jpg');

    // Mock saving the user profile picture.
    $this->fileRepositoryMock->method('writeData')->willReturn($fileMock);

    $this->fileStorageMock->method('create')
    ->willReturnCallback(function ($values) {
        $fileMock = $this->createMock(File::class);
        $fileMock->method('save')->willReturn(true);
        $fileMock->method('id')->willReturn(123); // Mock file ID
        return $fileMock;
    });

    $this->userStorageMock->method('loadByProperties')
      ->with(['mail' => $payload['email']])
      ->willReturn([]);

    // Mock module handler for login hook.
    $this->moduleHandlerMock->expects($this->once())
      ->method('invokeAll')
      ->with('hello_login_user_login', ['user' => $userMock]);

    // Test the client.
    $client = new HelloClient(
      $this->entityTypeManagerMock,
      $this->fileRepositoryMock,
      $this->externalAuthMock
    );

    $client->loginUpdate($payload);
  }
}
