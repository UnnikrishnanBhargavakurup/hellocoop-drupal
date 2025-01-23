<?php

declare(strict_types=1);

namespace Drupal\HelloLogin;

use HelloCoop\HelloClient;

class HelloClientFactory {

  protected $configFactory;

  public function __construct(HelloConfigFactory $configFactory) {
    $this->configFactory = $configFactory;
  }

  public function createClient($httpRequestService, $httpResponseService, $pageRenderer): HelloClient {
    $config = $this->configFactory->createConfig();
    return new HelloClient($config, $httpRequestService, $httpResponseService, $pageRenderer);
  }
}
