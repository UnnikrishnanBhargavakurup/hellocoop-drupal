<?php

namespace Drupal\hellocoop\Service;

use HelloCoop\Config\HelloConfig;

class HelloConfigFactory {
  protected $someDependency1;
  protected $someDependency2;

  public function __construct($someDependency1, $someDependency2) {
    $this->someDependency1 = $someDependency1;
    $this->someDependency2 = $someDependency2;
  }

  public function createConfig($apiRoute, $appId, $secret) {
    $config = new HelloConfig($apiRoute, $appId, $secret);

    // Set up callbacks with injected dependencies.
    $config->setCallbackOne(function () {
      return $this->someDependency1->doSomething();
    });

    $config->setCallbackTwo(function () {
      return $this->someDependency2->doSomethingElse();
    });

    return $config;
  }
}
