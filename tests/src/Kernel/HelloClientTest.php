<?php

namespace Drupal\Tests\hellocoop\Kernel;

use Drupal\KernelTests\KernelTestBase;

/**
 * Tests the HelloClient service.
 */
class HelloClientTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['file', 'hellocoop'];

  public function testFileService() {
    $fileRepository = $this->container->get('file.repository');
    $this->assertNotNull($fileRepository, 'File repository service is available.');
  }
}
