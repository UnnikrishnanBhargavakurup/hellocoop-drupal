<?php

namespace Drupal\hello_login\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Dynamically alters the API route.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    $config = \Drupal::config('hello_login.settings');
    $api_route = $config->get('api_route') ?? '/api/hellocoop';

    if ($route = $collection->get('hello_login.api_endpoint')) {
      $route->setPath($api_route);
    }
  }

}
