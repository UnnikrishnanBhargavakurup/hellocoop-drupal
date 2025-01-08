<?php

namespace Drupal\hellocoop;

use HelloCoop\HelloClient;

class HelloClientFactory {

  protected $configFactory;

  public function __construct(HelloConfigFactory $configFactory) {
    $this->configFactory = $configFactory;
  }

  public function createClient($httpRequestService, $httpResponseService, $pageRenderer) {
    $config = $this->configFactory->createConfig();
    return new HelloClient($config, $httpRequestService, $httpResponseService, $pageRenderer);
  }
}
