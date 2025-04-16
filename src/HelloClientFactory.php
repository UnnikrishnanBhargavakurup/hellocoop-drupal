<?php

declare(strict_types=1);

namespace Drupal\hello_login;

use HelloCoop\HelloClient;
use HelloCoop\Config\ConfigInterface;
use Drupal\hello_login\OpenIDProviderCommands\CommandHandler;


class HelloClientFactory {

  protected $configFactory;

  public function __construct(HelloConfigFactory $configFactory) {
    $this->configFactory = $configFactory;
  }

  public function createClient($httpRequestService, $httpResponseService, $pageRenderer): HelloClient {
    /** @var ConfigInterface $config  */
    $config = $this->configFactory->createConfig();
    $config->setCommandHandler(new CommandHandler($config, $httpResponseService));
    return new HelloClient($config, $httpRequestService, $httpResponseService, $pageRenderer);
  }
}
