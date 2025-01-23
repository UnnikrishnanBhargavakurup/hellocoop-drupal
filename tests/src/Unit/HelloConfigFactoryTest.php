<?php

declare(strict_types=1);

namespace Drupal\Tests\HelloLogin\Unit;

use Drupal\Core\Config\Config;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\HelloLogin\HelloClient;
use Drupal\HelloLogin\HelloConfigFactory;
use HelloCoop\Config\HelloConfig;
use PHPUnit\Framework\TestCase;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Request;

/**
 * @coversDefaultClass \Drupal\HelloLogin\HelloConfigFactory
 *
 * Unit test for the HelloConfigFactory class.
 */
class HelloConfigFactoryTest extends TestCase {

  /**
   * The mock HelloClient service.
   *
   * @var \Drupal\HelloLogin\HelloClient|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $helloClient;

  /**
   * The mock ConfigFactory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $configFactory;

  /**
   * The HelloConfigFactory instance under test.
   *
   * @var \Drupal\HelloLogin\HelloConfigFactory
   */
  protected $helloConfigFactory;

    /**
   * The mock request_stack service.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $requestStack;

  /**
   * The mock Request object.
   *
   * @var \Symfony\Component\HttpFoundation\Request|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $request;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Create mocks for dependencies.
    $this->helloClient = $this->createMock(HelloClient::class);
    $this->configFactory = $this->createMock(ConfigFactoryInterface::class);

    // Mock the Config object.
    $config = $this->createMock(Config::class);
    $config->method('get')
      ->willReturnCallback(function ($key) {
        $settings = [
          'api_route' => 'https://api.example.com',
          'app_id' => 'app123',
          'secret' => 'secret456',
        ];
        return $settings[$key] ?? NULL;
      });

    // Configure the configFactory mock to return the Config mock.
    $this->configFactory->method('get')
      ->with('hello_login.settings')
      ->willReturn($config);

    // Mock the Request object.
    $this->request = $this->createMock(Request::class);
    $this->request->method('getSchemeAndHttpHost')
      ->willReturn('https://example.com');
    $this->request->method('getHost')
      ->willReturn('example.com');

    // Mock the request_stack service.
    $this->requestStack = $this->createMock(RequestStack::class);
    $this->requestStack->method('getCurrentRequest')
      ->willReturn($this->request);

    // Set up a mock container
    $container = new ContainerBuilder();
    $container->set('request_stack', $this->requestStack);
    $container->set('config.factory', $this->configFactory); // Replace with actual mock or service

    // Set the container globally
    \Drupal::setContainer($container);

    // Create the HelloConfigFactory instance.
    $this->helloConfigFactory = new HelloConfigFactory(
      $this->helloClient,
      $this->configFactory
    );
  }

  /**
   * Tests the loginCallback method.
   *
   * @covers ::loginCallback
   */
  public function testLoginCallback(): void {
    $payload = ['email' => 'test@example.com'];
    $response = ['payload' => $payload];

    $this->helloClient->expects($this->once())
      ->method('loginUpdate')
      ->with($payload);

    $result = $this->helloConfigFactory->loginCallback($response);
    $this->assertSame($response, $result);
  }

  /**
   * Tests the loginCallback method with empty payload.
   *
   * @covers ::loginCallback
   */
  public function testLoginCallbackWithEmptyPayload(): void {
    $response = ['payload' => []];

    $this->helloClient->expects($this->never())
      ->method('loginUpdate');

    $result = $this->helloConfigFactory->loginCallback($response);
    $this->assertSame($response, $result);
  }

  /**
   * Tests the logoutCallback method.
   *
   * @covers ::logoutCallback
   */
  public function testLogoutCallback(): void {
    $this->helloClient->expects($this->once())
      ->method('logOut');

    $this->helloConfigFactory->logoutCallback();
  }

  /**
   * Tests the createConfig method.
   *
   * @covers ::createConfig
   */
  public function testCreateConfig(): void {
    // Invoke the createConfig method.
    $result = $this->helloConfigFactory->createConfig();

    // Assert that the result is an instance of HelloConfig.
    $this->assertInstanceOf(HelloConfig::class, $result);

    // Assert the HelloConfig object is configured correctly.
    $this->assertSame('https://api.example.com', $result->getApiRoute());
    $this->assertSame('https://api.example.com?op=auth', $result->getApiRoute() . '?op=auth');
    $this->assertSame('https://api.example.com?op=login', $result->getApiRoute() . '?op=login');
    $this->assertSame('https://api.example.com?op=logout', $result->getApiRoute() . '?op=logout');
    $this->assertSame('app123', $result->getClientId());
    $this->assertSame('secret456', $result->getSecret());
  }

}
