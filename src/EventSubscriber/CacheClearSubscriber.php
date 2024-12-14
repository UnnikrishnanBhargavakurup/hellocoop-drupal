<?php

namespace Drupal\hellocoop\EventSubscriber;

use Drupal\Core\Config\ConfigEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Rebuilds the route cache after configuration changes.
 */
class CacheClearSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    // Subscribe to the config save event.
    $events[ConfigEvents::SAVE][] = ['onConfigSave'];
    return $events;
  }

  /**
   * Rebuilds the route cache when the 'hellocoop.settings' config is saved.
   */
  public function onConfigSave($event) {
    $config_name = $event->getConfig()->getName();

    // Check if the 'hellocoop.settings' configuration was saved.
    if ($config_name == 'hellocoop.settings') {
      // Rebuild the route cache.
      \Drupal::service('router.builder')->rebuild();
    }
  }

}
