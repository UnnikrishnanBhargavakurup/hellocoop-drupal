<?php

namespace Drupal\hellocoop\Routing;

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
    $config = \Drupal::config('hellocoop.settings');
    $api_route = $config->get('api_route') ?? '/api/hellocoop';

    if ($route = $collection->get('hellocoop.api_endpoint')) {
      $route->setPath($api_route);
    }

    // Alter the login route.
    if ($route = $collection->get('user.login')) {
      $route->setPath($api_route);
      $route->setDefaults([
        '_controller' => '\Drupal\hellocoop\Controller\HelloCoopController::loginRedirect',
        '_title' => 'Custom Login',
      ]);
    }

    // Alter the logout route.
    if ($route = $collection->get('user.logout')) {
      $route->setPath($api_route);
      $route->setDefaults([
        '_controller' => '\Drupal\hellocoop\Controller\HelloCoopController::logoutRedirect',
        '_title' => 'Custom Logout',
      ]);
    }
  }
}
