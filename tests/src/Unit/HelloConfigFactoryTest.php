<?php

namespace Drupal\Tests\hellocoop\Unit;

use Drupal\Core\Config\Config;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\hellocoop\HelloClient;
use Drupal\hellocoop\HelloConfigFactory;
use HelloCoop\Config\HelloConfig;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\hellocoop\HelloConfigFactory
 *
 * Unit test for the HelloConfigFactory class.
 */
class HelloConfigFactoryTest extends UnitTestCase {

  /**
   * The mock HelloClient service.
   *
   * @var \Drupal\hellocoop\HelloClient|\PHPUnit\Framework\MockObject\MockObject
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
   * @var \Drupal\hellocoop\HelloConfigFactory
   */
  protected $helloConfigFactory;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Create mocks for dependencies.
    $this->helloClient = $this->createMock(HelloClient::class);
    $this->configFactory = $this->createMock(ConfigFactoryInterface::class);

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
    $settings = [
      'api_route' => 'https://api.example.com',
      'app_id' => 'app123',
      'secret' => 'secret456',
    ];

    // Mock the configuration object.
    $config = $this->createMock(Config::class);
    $config->method('get')
      ->willReturnCallback(function ($key) use ($settings) {
        return $settings[$key] ?? NULL;
      });

    $this->configFactory->expects($this->once())
      ->method('get')
      ->with('hellocoop.settings')
      ->willReturn($config);

    $result = $this->helloConfigFactory->createConfig();

    // Assert that the result is an instance of HelloConfig.
    $this->assertInstanceOf(HelloConfig::class, $result);

    // Assert the HelloConfig object is configured correctly.
    $this->assertEquals('https://api.example.com', $result->getApiRoute());
    $this->assertEquals('https://api.example.com?op=auth', $result->getAuthUrl());
    $this->assertEquals('https://api.example.com?op=login', $result->getLoginUrl());
    $this->assertEquals('https://api.example.com?op=logout', $result->getLogoutUrl());
    $this->assertEquals('app123', $result->getAppId());
    $this->assertEquals('secret456', $result->getSecret());
  }
}
