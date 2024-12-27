<?php

/**
 * @file
 * Unit tests for the HelloConfigFactory service in the HelloCoop module.
 */

namespace Drupal\Tests\hellocoop\Unit\Service;

use Drupal\hellocoop\Service\HelloConfigFactory;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityTypeRepositoryInterface;
use Drupal\Core\Session\SessionManagerInterface;
use Drupal\file\FileRepositoryInterface;
use Drupal\file\FileInterface;
use Drupal\user\UserInterface;
use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;
use Drupal\Core\DependencyInjection\ContainerBuilder;

/**
 * @coversDefaultClass \Drupal\hellocoop\Service\HelloConfigFactory
 * @group hellocoop
 */
class HelloConfigFactoryTest extends TestCase {

  /**
   * The mocked file repository service.
   *
   * @var \Drupal\file\FileRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $fileRepository;

  /**
   * The mocked entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $entityTypeManager;

  /**
   * The mocked entity type repository service.
   *
   * @var \Drupal\Core\Entity\EntityTypeRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $entityTypeRepository;

  /**
   * The mocked session manager service.
   *
   * @var \Drupal\Core\Session\SessionManagerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $sessionManager;

  /**
   * The mocked HTTP client service.
   *
   * @var \GuzzleHttp\ClientInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $httpClient;

  /**
   * The HelloConfigFactory service under test.
   *
   * @var \Drupal\hellocoop\Service\HelloConfigFactory
   */
  protected $factory;

  /**
   * The container builder.
   *
   * @var \Drupal\Core\DependencyInjection\ContainerBuilder
   */
  protected $container;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

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

    // Instantiate the service with the mocked dependencies.
    $this->factory = new HelloConfigFactory(
      $this->entityTypeManager,
      $this->fileRepository
    );
  }

  /**
   * Tests the loginCallback method.
   *
   * @covers ::loginCallback
   */
  public function testLoginCallback(): void {
    $userEntity = $this->createMock(UserInterface::class);
    $fileEntity = $this->createMock(FileInterface::class);

    $payload = [
      'email' => 'test@example.com',
      'name' => 'Test User',
      'picture' => 'http://example.com/image.jpg',
    ];

    $response = ['payload' => $payload];

    $entityStorage = $this->createMock('Drupal\Core\Entity\EntityStorageInterface');

    // Here we load user and file for profile pic.
    $this->entityTypeManager->expects($this->exactly(2))
      ->method('getStorage')
      ->willReturn($entityStorage);

    $entityStorage->method('create')
      ->willReturn($userEntity);

    // Setting up exact expectations for 'set' method.
    $matcher = $this->exactly(2);
    $userEntity->expects($matcher)
      ->method('set')
      ->willReturnCallback(function (string $key, string $value) use ($matcher, $payload, $fileEntity) {
        switch ($matcher->numberOfInvocations()) {
          case 1:
            $this->assertEquals('name', $key);
            $this->assertEquals($payload['name'], $value);
            break;

          case 2:
            $this->assertEquals('user_picture', $key);
            $this->assertEquals($fileEntity->id(), $value);
            break;
        }
      });

    $userEntity->expects($this->once())
      ->method('save');

    $this->fileRepository->method('writeData')
      ->willReturn($fileEntity);

    $fileEntity->method('id')
      ->willReturn(1);

    // Call the method being tested.
    $result = $this->factory->loginCallback($response);

    $this->assertEquals($response, $result);
  }

  /**
   * Tests the logoutCallback method.
   *
   * @covers ::logoutCallback
   */
  public function testLogoutCallback(): void {
    // Mocking session destroy method.
    $this->sessionManager->expects($this->once())
      ->method('destroy');

    // Call the method being tested.
    $this->factory->logoutCallback();
  }

}
