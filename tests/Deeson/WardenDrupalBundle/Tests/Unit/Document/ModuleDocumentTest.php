<?php

namespace Deeson\WardenDrupalBundle\Test\Unit\Document;

use Deeson\WardenDrupalBundle\Document\DrupalModuleDocument;

class ModuleDocumentTest extends \PHPUnit_Framework_TestCase {

  /**
   * @test
   * Tests the retrieval of the major version number from a version number.
   */
  public function testGetMajorVersion() {
    $this->assertEquals('7', DrupalModuleDocument::getMajorVersion('7.x-1.3'), '7 is the major version of 7.x-1.3');
    $this->assertNotEquals('1', DrupalModuleDocument::getMajorVersion('7.x-1.3'), '1 is not the major version of 7.x-1.3');
    $this->assertNotEquals('3', DrupalModuleDocument::getMajorVersion('7.x-1.3'), '3 is not the major version of 7.x-1.3');
    $this->assertEquals('8', DrupalModuleDocument::getMajorVersion('8.3.2'), '8 is the major version of 8.3.2');
  }

  /**
   * @test
   * Tests the right version info is returned for different version number formats.
   */
  public function testGetVersionInfo() {
    // Drupal 7 module version numbering.
    $a = DrupalModuleDocument::getVersionInfo('7.56');
    $this->assertEquals('7', $a['major'], '7 is the major version of 7.56');
    $this->assertEquals('56', $a['minor'], '56 is the minor version of 7.56');
    $this->assertNull($a['other'], 'There is no other version of 7.56');
    $this->assertNull($a['extra'], 'There is no extra version of 7.56');

    // Drupal 7 module version numbering.
    $a = DrupalModuleDocument::getVersionInfo('7.x-1.3');
    $this->assertEquals('7', $a['major'], '7 is the major version of 7.x-1.3');
    $this->assertEquals('1', $a['minor'], '1 is the minor version of 7.x-1.3');
    $this->assertEquals('3', $a['other'], '3 is the other version of 7.x-1.3');
    $this->assertNull($a['extra'], 'There is no extra version of 7.x-1.3');

    $a = DrupalModuleDocument::getVersionInfo('7.x-1.13');
    $this->assertEquals('7', $a['major'], '7 is the major version of 7.x-1.13');
    $this->assertEquals('1', $a['minor'], '1 is the minor version of 7.x-1.13');
    $this->assertEquals('13', $a['other'], '13 is the other version of 7.x-1.13');
    $this->assertNull($a['extra'], 'There is no extra version of 7.x-1.13');

    $a = DrupalModuleDocument::getVersionInfo('7.x-2.0-alpha1');
    $this->assertEquals('7', $a['major'], '7 is the major version of 7.x-2.0-alpha1');
    $this->assertEquals('2', $a['minor'], '2 is the minor version of 7.x-2.0-alpha1');
    $this->assertEquals('0', $a['other'], '0 is the other version of 7.x-2.0-alpha1');
    $this->assertEquals('-alpha1', $a['extra'], 'alpha1 is the other version of 7.x-2.0-alpha1');

    $a = DrupalModuleDocument::getVersionInfo('7.x-2.0-alpha15');
    $this->assertEquals('7', $a['major'], '7 is the major version of 7.x-2.0-alpha15');
    $this->assertEquals('2', $a['minor'], '2 is the minor version of 7.x-2.0-alpha15');
    $this->assertEquals('0', $a['other'], '0 is the other version of 7.x-2.0-alpha15');
    $this->assertEquals('-alpha15', $a['extra'], 'alpha1 is the other version of 7.x-2.0-alpha15');

    $a = DrupalModuleDocument::getVersionInfo('7.x-2.0-beta1');
    $this->assertEquals('7', $a['major'], '7 is the major version of 7.x-2.0-beta1');
    $this->assertEquals('2', $a['minor'], '2 is the minor version of 7.x-2.0-beta1');
    $this->assertEquals('0', $a['other'], '0 is the other version of 7.x-2.0-beta1');
    $this->assertEquals('-beta1', $a['extra'], 'alpha15 is the other version of 7.x-2.0-beta1');

    $a = DrupalModuleDocument::getVersionInfo('7.x-2.0-beta15');
    $this->assertEquals('7', $a['major'], '7 is the major version of 7.x-2.0-beta15');
    $this->assertEquals('2', $a['minor'], '2 is the minor version of 7.x-2.0-beta15');
    $this->assertEquals('0', $a['other'], '0 is the other version of 7.x-2.0-beta15');
    $this->assertEquals('-beta15', $a['extra'], 'beta15 is the other version of 7.x-2.0-beta15');

    $a = DrupalModuleDocument::getVersionInfo('7.x-2.0-rc2');
    $this->assertEquals('7', $a['major'], '7 is the major version of 7.x-2.0-rc2');
    $this->assertEquals('2', $a['minor'], '2 is the minor version of 7.x-2.0-rc2');
    $this->assertEquals('0', $a['other'], '0 is the other version of 7.x-2.0-rc2');
    $this->assertEquals('-rc2', $a['extra'], 'rc2 is the other version of 7.x-2.0-rc2');

    $a = DrupalModuleDocument::getVersionInfo('7.x-2.0-rc24');
    $this->assertEquals('7', $a['major'], '7 is the major version of 7.x-2.0-rc24');
    $this->assertEquals('2', $a['minor'], '2 is the minor version of 7.x-2.0-rc24');
    $this->assertEquals('0', $a['other'], '0 is the other version of 7.x-2.0-rc24');
    $this->assertEquals('-rc24', $a['extra'], 'rc24 is the other version of 7.x-2.0-rc24');

    // Drupal 8 version numbering.
    $a = DrupalModuleDocument::getVersionInfo('8.3.1');
    $this->assertEquals('8', $a['major'], '8 is the major version of 8.3.1');
    $this->assertEquals('3', $a['minor'], '3 is the minor version of 8.3.1');
    $this->assertEquals('1', $a['other'], '1 is the other version of 8.3.1');
    $this->assertNull($a['extra'], 'blank is the other version of 8.3.1');

    $a = DrupalModuleDocument::getVersionInfo('8.3.15');
    $this->assertEquals('8', $a['major'], '8 is the major version of 8.3.15');
    $this->assertEquals('3', $a['minor'], '3 is the minor version of 8.3.15');
    $this->assertEquals('15', $a['other'], '1 is the other version of 8.3.15');
    $this->assertNull($a['extra'], 'blank is the other version of 8.3.15');

    $a = DrupalModuleDocument::getVersionInfo('8.13.15');
    $this->assertEquals('8', $a['major'], '8 is the major version of 8.13.15');
    $this->assertEquals('13', $a['minor'], '3 is the minor version of 8.13.15');
    $this->assertEquals('15', $a['other'], '1 is the other version of 8.13.15');
    $this->assertNull($a['extra'], 'blank is the other version of 8.13.15');

    // Drupal 8 module version numbering.
    $a = DrupalModuleDocument::getVersionInfo('8.3.0-alpha1');
    $this->assertEquals('8', $a['major'], '8 is the major version of 8.3.0-alpha1');
    $this->assertEquals('3', $a['minor'], '3 is the minor version of 8.3.0-alpha1');
    $this->assertEquals('0', $a['other'], '1 is the other version of 8.3.0-alpha1');
    $this->assertEquals('-alpha1', $a['extra'], '-alpha1 is the other version of 8.3.0-alpha1');

    $a = DrupalModuleDocument::getVersionInfo('8.3.0-alpha15');
    $this->assertEquals('8', $a['major'], '8 is the major version of 8.3.0-alpha15');
    $this->assertEquals('3', $a['minor'], '3 is the minor version of 8.3.0-alpha15');
    $this->assertEquals('0', $a['other'], '15 is the other version of 8.3.0-alpha15');
    $this->assertEquals('-alpha15', $a['extra'], '-alpha1 is the other version of 8.3.0-alpha15');
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
    $this->assertEquals(TRUE, DrupalModuleDocument::isLatestVersion($moduleData), '7.x-1.3 is the latest version');

    $moduleData = array(
      'latestVersion' => '7.x-1.6',
      'version' => '7.x-1.3'
    );
    $this->assertEquals(FALSE, DrupalModuleDocument::isLatestVersion($moduleData), '7.x-1.3 is not the latest version');

    $moduleData = array(
      'latestVersion' => '7.x-2.0',
      'version' => '7.x-1.3'
    );
    $this->assertEquals(FALSE, DrupalModuleDocument::isLatestVersion($moduleData), '7.x-1.3 is not the latest version');
  }

  /**
   * @test
   * Tests the version equals check.
   */
  public function testVersionsEqual() {
    $moduleData = array();
    $this->assertEquals(FALSE, DrupalModuleDocument::versionsEqual($moduleData), 'There is no latestVersion set');

    $moduleData = array(
      'latestVersion' => '',
      'version' => '7'
    );
    $this->assertEquals(FALSE, DrupalModuleDocument::versionsEqual($moduleData), 'Latest version does not match');

    $moduleData = array(
      'latestVersion' => '7.x-1.3',
      'version' => '7.x-1.3'
    );
    $this->assertEquals(TRUE, DrupalModuleDocument::versionsEqual($moduleData), 'Versions matche');

    $moduleData = array(
      'latestVersion' => '7.x-1.3',
      'version' => '7.x-1.2'
    );
    $this->assertEquals(FALSE, DrupalModuleDocument::versionsEqual($moduleData), 'Latest version does not match');
  }

  /**
   * @test
   * Tests if a version number is a dev release.
   */
  public function testIsDevRelease() {
    $this->assertEquals(FALSE, DrupalModuleDocument::isDevRelease('7.x-1.3'), '7.x-1.3 is not a dev release');
    $this->assertEquals(TRUE, DrupalModuleDocument::isDevRelease('7.x-1.0-dev'), '7.x-1.0-dev is a dev release');
    $this->assertEquals(TRUE, DrupalModuleDocument::isDevRelease('7.x-1.0+5-dev'), '7.x-1.0+5-dev is a dev release');

    $this->assertEquals(FALSE, DrupalModuleDocument::isDevRelease('8.1.3'), '8.1.3 is not a dev release');
    $this->assertEquals(TRUE, DrupalModuleDocument::isDevRelease('8.1.0-dev'), '8.1.0-dev is a dev release');
    $this->assertEquals(TRUE, DrupalModuleDocument::isDevRelease('8.1.0+5-dev'), '8.1.0+5-dev is a dev release');
  }

  /**
   * @test
   * Tests if a module version is still supported or not.
   */
  public function testIsVersionUnsupported() {
    /**** No module version data. ****/
    $moduleVersions = array();
    $module = array(
      'name' => 'test_module',
      'version' => '7.x-1.1',
    );
    $this->assertEquals(TRUE, DrupalModuleDocument::isVersionUnsupported($moduleVersions, $module), '1: Version "7.x-1.1" is not supported due to no module data');

    /**** Recommended only module version data. ****/
    $moduleVersions = array(
      DrupalModuleDocument::MODULE_VERSION_TYPE_RECOMMENDED => array(
        'version' => '7.x-1.2',
      ),
    );

    $module = array(
      'name' => 'test_module',
      'version' => '7.x-1.1',
    );
    $this->assertEquals(FALSE, DrupalModuleDocument::isVersionUnsupported($moduleVersions, $module), '2: Version "7.x-1.1" is supported for recommended version');

    $moduleVersions = array(
      DrupalModuleDocument::MODULE_VERSION_TYPE_RECOMMENDED => array(
        'version' => '7.x-3.2',
      ),
    );

    $module = array(
      'name' => 'test_module',
      'version' => '7.x-1.1',
    );
    $this->assertEquals(TRUE, DrupalModuleDocument::isVersionUnsupported($moduleVersions, $module), '2: Version "7.x-1.1" is not supported for recommended version');

    $module = array(
      'name' => 'test_module',
      'version' => '7.x-2.1',
    );
    $this->assertEquals(TRUE, DrupalModuleDocument::isVersionUnsupported($moduleVersions, $module), '2: Version "7.x-2.1" is not supported for recommended version');

    $module = array(
      'name' => 'test_module',
      'version' => '7.x-3.1',
    );
    $this->assertEquals(FALSE, DrupalModuleDocument::isVersionUnsupported($moduleVersions, $module), '2: Version "7.x-2.1" is supported for recommended version');

    /**** Other only module version data. ****/
    $moduleVersions = array(
      DrupalModuleDocument::MODULE_VERSION_TYPE_OTHER => array(
        'version' => '7.x-1.19',
      )
    );
    $module = array(
      'name' => 'test_module',
      'version' => '7.x-1.1',
    );
    $this->assertEquals(FALSE, DrupalModuleDocument::isVersionUnsupported($moduleVersions, $module), '3: Version "7.x-1.1" is  supported for other version');

    $moduleVersions = array(
      DrupalModuleDocument::MODULE_VERSION_TYPE_OTHER => array(
        'version' => '7.x-3.19',
      )
    );
    $module = array(
      'name' => 'test_module',
      'version' => '7.x-1.1',
    );
    $this->assertEquals(TRUE, DrupalModuleDocument::isVersionUnsupported($moduleVersions, $module), '3: Version "7.x-1.1" is not supported for other version');

    $module = array(
      'name' => 'test_module',
      'version' => '7.x-2.1',
    );
    $this->assertEquals(TRUE, DrupalModuleDocument::isVersionUnsupported($moduleVersions, $module), '3: Version "7.x-2.1" is not supported for other version');

    $module = array(
      'name' => 'test_module',
      'version' => '7.x-3.1',
    );
    $this->assertEquals(FALSE, DrupalModuleDocument::isVersionUnsupported($moduleVersions, $module), '3: Version "7.x-3.1" is supported for other version');

    /**** Recommended and other module version data. ****/
    $moduleVersions = array(
      DrupalModuleDocument::MODULE_VERSION_TYPE_RECOMMENDED => array(
        'version' => '7.x-1.8',
      ),
      DrupalModuleDocument::MODULE_VERSION_TYPE_OTHER => array(
        'version' => '7.x-2.0-beta2',
      )
    );

    $module = array(
      'name' => 'test_module',
      'version' => '7.x-1.7',
    );
    $this->assertEquals(FALSE, DrupalModuleDocument::isVersionUnsupported($moduleVersions, $module), '4.1: Version "7.x-1.1" is supported for recommended or other version');

    $module = array(
      'name' => 'test_module',
      'version' => '7.x-2.0-beta1',
    );
    $this->assertEquals(FALSE, DrupalModuleDocument::isVersionUnsupported($moduleVersions, $module), '4.1: Version "7.x-2.0-beta1" is supported for recommended or other version');

    $moduleVersions = array(
      DrupalModuleDocument::MODULE_VERSION_TYPE_RECOMMENDED => array(
        'version' => '7.x-3.2',
      ),
      DrupalModuleDocument::MODULE_VERSION_TYPE_OTHER => array(
        'version' => '7.x-2.6',
      )
    );
    $module = array(
      'name' => 'test_module',
      'version' => '7.x-1.1',
    );
    $this->assertEquals(TRUE, DrupalModuleDocument::isVersionUnsupported($moduleVersions, $module), '4.2: Version "7.x-1.1" is not supported for recommended or other version');

    $module = array(
      'name' => 'test_module',
      'version' => '7.x-2.1',
    );
    $this->assertEquals(FALSE, DrupalModuleDocument::isVersionUnsupported($moduleVersions, $module), '4.2: Version "7.x-2.1" is supported for recommended or other version');

    $module = array(
      'name' => 'test_module',
      'version' => '7.x-3.1',
    );
    $this->assertEquals(FALSE, DrupalModuleDocument::isVersionUnsupported($moduleVersions, $module), '4.2: Version "7.x-3.1" is supported for recommended or other version');

    $moduleVersions = array(
      DrupalModuleDocument::MODULE_VERSION_TYPE_RECOMMENDED => array(
        'version' => '7.x-2.2',
      ),
      DrupalModuleDocument::MODULE_VERSION_TYPE_OTHER => array(
        'version' => '7.x-3.1',
      )
    );
    $module = array(
      'name' => 'test_module',
      'version' => '7.x-1.1',
    );
    $this->assertEquals(TRUE, DrupalModuleDocument::isVersionUnsupported($moduleVersions, $module), '4.3: Version "7.x-1.1" is not supported for recommended or other version');

    $module = array(
      'name' => 'test_module',
      'version' => '7.x-2.1',
    );
    $this->assertEquals(FALSE, DrupalModuleDocument::isVersionUnsupported($moduleVersions, $module), '4.3: Version "7.x-2.1" is supported for recommended or other version');

    $module = array(
      'name' => 'test_module',
      'version' => '7.x-3.1',
    );
    $this->assertEquals(FALSE, DrupalModuleDocument::isVersionUnsupported($moduleVersions, $module), '4.3: Version "7.x-3.1" is supported for recommended or other version');

    // @todo add d8 version unsupported tests.
  }
}
