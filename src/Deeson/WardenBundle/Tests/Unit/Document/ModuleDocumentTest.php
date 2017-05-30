<?php

namespace Deeson\WardenBundle\Test\Unit\Document;

use Deeson\WardenBundle\Document\ModuleDocument;

class ModuleDocumentTest extends \PHPUnit_Framework_TestCase {

  /**
   * @test
   * Tests the retrieval of the major version number from a version number.
   */
  public function testGetMajorVersion() {
    $this->assertEquals('7', ModuleDocument::getMajorVersion('7.x-1.3'), '7 is the major version of 7.x-1.3');
    $this->assertNotEquals('1', ModuleDocument::getMajorVersion('7.x-1.3'), '1 is not the major version of 7.x-1.3');
    $this->assertNotEquals('3', ModuleDocument::getMajorVersion('7.x-1.3'), '3 is not the major version of 7.x-1.3');
    $this->assertEquals('8', ModuleDocument::getMajorVersion('8.3.2'), '8 is the major version of 8.3.2');
  }

  /**
   * @test
   * Tests the right version info is returned for different version number formats.
   */
  public function testGetVersionInfo() {
    // Drupal 7 version numbering.
    $a = ModuleDocument::getVersionInfo('7.x-1.3');
    $this->assertEquals('7', $a['major'], '7 is the major version of 7.x-1.3');
    $this->assertEquals('1', $a['minor'], '1 is the minor version of 7.x-1.3');
    $this->assertEquals('3', $a['other'], '3 is the other version of 7.x-1.3');
    $this->assertNull($a['extra'], 'There is no extra version of 7.x-1.3');

    $a = ModuleDocument::getVersionInfo('7.x-2.0-alpha1');
    $this->assertEquals('7', $a['major'], '7 is the major version of 7.x-2.0-alpha1');
    $this->assertEquals('2', $a['minor'], '2 is the minor version of 7.x-2.0-alpha1');
    $this->assertEquals('0', $a['other'], '0 is the other version of 7.x-2.0-alpha1');
    $this->assertEquals('-alpha1', $a['extra'], 'alpha1 is the other version of 7.x-2.0-alpha1');

    $a = ModuleDocument::getVersionInfo('7.x-2.0-alpha15');
    $this->assertEquals('7', $a['major'], '7 is the major version of 7.x-2.0-alpha15');
    $this->assertEquals('2', $a['minor'], '2 is the minor version of 7.x-2.0-alpha15');
    $this->assertEquals('0', $a['other'], '0 is the other version of 7.x-2.0-alpha15');
    $this->assertEquals('-alpha15', $a['extra'], 'alpha1 is the other version of 7.x-2.0-alpha15');

    $a = ModuleDocument::getVersionInfo('7.x-2.0-beta1');
    $this->assertEquals('7', $a['major'], '7 is the major version of 7.x-2.0-beta1');
    $this->assertEquals('2', $a['minor'], '2 is the minor version of 7.x-2.0-beta1');
    $this->assertEquals('0', $a['other'], '0 is the other version of 7.x-2.0-beta1');
    $this->assertEquals('-beta1', $a['extra'], 'alpha15 is the other version of 7.x-2.0-beta1');

    $a = ModuleDocument::getVersionInfo('7.x-2.0-beta15');
    $this->assertEquals('7', $a['major'], '7 is the major version of 7.x-2.0-beta15');
    $this->assertEquals('2', $a['minor'], '2 is the minor version of 7.x-2.0-beta15');
    $this->assertEquals('0', $a['other'], '0 is the other version of 7.x-2.0-beta15');
    $this->assertEquals('-beta15', $a['extra'], 'beta15 is the other version of 7.x-2.0-beta15');

    $a = ModuleDocument::getVersionInfo('7.x-2.0-rc2');
    $this->assertEquals('7', $a['major'], '7 is the major version of 7.x-2.0-rc2');
    $this->assertEquals('2', $a['minor'], '2 is the minor version of 7.x-2.0-rc2');
    $this->assertEquals('0', $a['other'], '0 is the other version of 7.x-2.0-rc2');
    $this->assertEquals('-rc2', $a['extra'], 'rc2 is the other version of 7.x-2.0-rc2');

    $a = ModuleDocument::getVersionInfo('7.x-2.0-rc24');
    $this->assertEquals('7', $a['major'], '7 is the major version of 7.x-2.0-rc24');
    $this->assertEquals('2', $a['minor'], '2 is the minor version of 7.x-2.0-rc24');
    $this->assertEquals('0', $a['other'], '0 is the other version of 7.x-2.0-rc24');
    $this->assertEquals('-rc24', $a['extra'], 'rc24 is the other version of 7.x-2.0-rc24');

    // Drupal 8 version numbering.
    $a = ModuleDocument::getVersionInfo('8.3.1');
    $this->assertEquals('8', $a['major'], '8 is the major version of 8.3.1');
    $this->assertEquals('3', $a['minor'], '3 is the minor version of 8.3.1');
    $this->assertEquals('1', $a['other'], '1 is the other version of 8.3.1');
    $this->assertNull($a['extra'], 'blank is the other version of 8.3.1');

    $a = ModuleDocument::getVersionInfo('8.3.0-alpha1');
    $this->assertEquals('8', $a['major'], '8 is the major version of 8.3.0-alpha1');
    $this->assertEquals('3', $a['minor'], '3 is the minor version of 8.3.0-alpha1');
    $this->assertEquals('1', $a['other'], '1 is the other version of 8.3.0-alpha1');
    $this->assertEquals('-alpha1', $a['extra'], '-alpha1 is the other version of 8.3.0-alpha1');
  }

  /**
   * @test
   * Tests the check for is a module is the latest version.
   */
  public function testIsLatestVersion() {
    $moduleData = array(
      'latestVersion' => '7.x-1.3',
      'version' => '7.x-1.3'
    );
    $this->assertEquals(TRUE, ModuleDocument::isLatestVersion($moduleData), '7.x-1.3 is the latest version');

    $moduleData = array(
      'latestVersion' => '7.x-1.6',
      'version' => '7.x-1.3'
    );
    $this->assertEquals(FALSE, ModuleDocument::isLatestVersion($moduleData), '7.x-1.3 is not the latest version');

    $moduleData = array(
      'latestVersion' => '7.x-2.0',
      'version' => '7.x-1.3'
    );
    $this->assertEquals(FALSE, ModuleDocument::isLatestVersion($moduleData), '7.x-1.3 is not the latest version');
  }

  /**
   * @test
   * Tests the version equals check.
   */
  public function testVersionsEqual() {
    $moduleData = array();
    $this->assertEquals(FALSE, ModuleDocument::versionsEqual($moduleData), 'There is no latestVersion set');

    $moduleData = array(
      'latestVersion' => '',
      'version' => '7'
    );
    $this->assertEquals(FALSE, ModuleDocument::versionsEqual($moduleData), 'Latest version does not match');

    $moduleData = array(
      'latestVersion' => '7.x-1.3',
      'version' => '7.x-1.3'
    );
    $this->assertEquals(TRUE, ModuleDocument::versionsEqual($moduleData), 'Versions matche');

    $moduleData = array(
      'latestVersion' => '7.x-1.3',
      'version' => '7.x-1.2'
    );
    $this->assertEquals(FALSE, ModuleDocument::versionsEqual($moduleData), 'Latest version does not match');
  }

  /**
   * @test
   * Tests if a version number is a dev release.
   */
  public function testIsDevRelease() {
    $this->assertEquals(FALSE, ModuleDocument::isDevRelease('7.x-1.3'), '7.x-1.3 is not a dev release');
    $this->assertEquals(TRUE, ModuleDocument::isDevRelease('7.x-1.0-dev'), '7.x-1.0-dev is a dev release');
    $this->assertEquals(TRUE, ModuleDocument::isDevRelease('7.x-1.0+5-dev'), '7.x-1.0+5-dev is a dev release');
  }

}
