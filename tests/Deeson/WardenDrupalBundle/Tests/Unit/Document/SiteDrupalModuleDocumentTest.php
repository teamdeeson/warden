<?php

namespace Deeson\WardenDrupalBundle\Document;

use Deeson\WardenBundle\Document\SiteDocument;
use Deeson\WardenDrupalBundle\Managers\DrupalModuleManager;

class SiteDrupalModuleDocumentTest extends \PHPUnit_Framework_TestCase {

  /**
   * @test
   * Test the setting and getting of the modules latest version.
   */
  public function testSetGetModulesLatestVersion() {
    $modules = [
      'test' => [
        'name' => 'test',
        'version' => '7.x-1.1'
      ]
    ];

    $siteModuleDocument = new SiteDrupalModuleDocument();
    $siteModuleDocument->setModules($modules);

    $this->assertArrayHasKey('test', $siteModuleDocument->getModules(), 'The modules did not contain the key of "test"');

    $testModule = $siteModuleDocument->getModules()['test'];
    $this->assertArrayHasKey('version', $testModule, 'The modules did not contain the key of "version"');
    $this->assertArrayNotHasKey('latestVersion', $testModule, 'The modules did contain the key of "latestVersion"');
    $this->assertArrayNotHasKey('isUnsupported', $testModule, 'The modules did contain the key of "isUnsupported"');
  }

  /**
   * @test
   * Tests that we can get teh latest module version.
   */
  public function testGetModuleLatestVersion() {
    $siteModuleDocument = new SiteDrupalModuleDocument();

    $module = [
      'latestVersion' => '7.x-1.1',
    ];
    $this->assertTrue($siteModuleDocument->getModuleLatestVersion($module) === '7.x-1.1', 'The module does not have the latest version set');

    $module = [];
    $this->assertEmpty($siteModuleDocument->getModuleLatestVersion($module) === '7.x-1.1', 'The module is not empty for the latest version');
  }

  /**
   * @test
   * Tests that a module is using the supported version.
   */
  public function testIsModuleSupported() {
    $siteModuleDocument = new SiteDrupalModuleDocument();

    $module = [
      'isUnsupported' => '1',
    ];
    $this->assertEquals('1', $siteModuleDocument->isModuleSupported($module), 'The module does not have the is supported set');

    $module = [
      'isUnsupported' => '0',
    ];
    $this->assertEquals('0', $siteModuleDocument->isModuleSupported($module), 'The module does not have the is supported set');

    $module = [];
    $this->assertFalse($siteModuleDocument->isModuleSupported($module), 'The module is not empty for the is supported');
  }

  /**
   * @test
   * Tests if a module version is a security release.
   */
  public function testGetModuleIsSecurity() {
    $siteModuleDocument = new SiteDrupalModuleDocument();

    $module = [
      'version' => '7.x-1.1',
      'isSecurity' => '1',
    ];
    $this->assertEquals('1', $siteModuleDocument->getModuleIsSecurity($module), 'The module does not have the is isSecurity set');

    $module = [
      'version' => '7.x-1.1',
      'isSecurity' => '0',
    ];
    $this->assertEquals('0', $siteModuleDocument->getModuleIsSecurity($module), 'The module does not have the is isSecurity set');

    $module = [
      'version' => '7.x-1.1'
    ];
    $this->assertFalse($siteModuleDocument->getModuleIsSecurity($module), 'The module is not empty for the is isSecurity');

    $module = [
      'version' => '7.x-1.1-dev'
    ];
    $this->assertFalse($siteModuleDocument->getModuleIsSecurity($module), 'The module is a dev version and is not false for the is isSecurity');
  }

  /**
   * @test
   * Tests if a module version is a dev release.
   */
  public function testGetModuleIsDevRelease() {
    $siteModuleDocument = new SiteDrupalModuleDocument();

    $module = [
      'version' => '7.x-1.1-dev',
    ];
    $this->assertTrue($siteModuleDocument->getModuleIsDevRelease($module), 'The module is a dev release');

    $module = [
      'version' => '7.x-1.1',
    ];
    $this->assertFalse($siteModuleDocument->getModuleIsDevRelease($module), 'The module is not a dev release');
  }

  /**
   * @test
   * Test that a module can be updated with a new version number.
   */
  public function testUpdateNewModuleWithNewVersion() {
    $modules = [
      'test' => [
        'name' => 'test',
        'version' => '7.x-1.1'
      ]
    ];

    $siteModuleDocument = new SiteDrupalModuleDocument();
    $siteModuleDocument->setModules($modules);

    $moduleData = [
      'version' => '7.x-1.2',
    ];
    $siteModuleDocument->updateModule('test', $moduleData);

    $expectedModule = [
      'test' => [
        'name' => 'test',
        'version' => '7.x-1.1',
        'latestVersion' => '7.x-1.2',
      ]
    ];

    $this->assertEquals($expectedModule, $siteModuleDocument->getModules(), 'The updated module doesn\'t match');
  }

  /**
   * @test
   * Test that a module can be updated with a new version number.
   */
  public function testUpdateExistingModuleWithNewVersion() {
    $modules = [
      'test' => [
        'name' => 'test',
        'version' => '7.x-1.1'
      ]
    ];

    $siteModuleDocument = new SiteDrupalModuleDocument();
    $siteModuleDocument->setModules($modules);

    $moduleLatestVersion = [
      'test' => [
        'recommended' => [
          'version' => '7.x-1.2',
          'isSecurity' => '0',
        ]
      ]
    ];
    $siteModuleDocument->setModulesLatestVersion($moduleLatestVersion);

    $moduleData = [
      'test' => [
        'name' => 'test',
        'version' => '7.x-1.2'
      ]
    ];
    $siteModuleDocument->setModules($moduleData, true);

    $expectedModule = [
      'test' => [
        'name' => 'test',
        'version' => '7.x-1.2',
        'latestVersion' => '7.x-1.2',
      ]
    ];

    $this->assertEquals($expectedModule, $siteModuleDocument->getModules(), 'The updated module doesn\'t match');
  }

  /**
   * @test
   * Test that a module can be updated to update the security flag.
   */
  public function testUpdateNewModuleSecurityFlag() {
    $modules = [
      'test' => [
        'name' => 'test',
        'version' => '7.x-1.1'
      ]
    ];

    $siteModuleDocument = new SiteDrupalModuleDocument();
    $siteModuleDocument->setModules($modules);

    $moduleData = [
      'isSecurity' => '1'
    ];
    $siteModuleDocument->updateModule('test', $moduleData);

    $expectedModule = [
      'test' => [
        'name' => 'test',
        'version' => '7.x-1.1',
        'isSecurity' => '1',
      ]
    ];

    $this->assertEquals($expectedModule, $siteModuleDocument->getModules(), 'The updated module doesn\'t match');
  }

  /**
   * @test
   * Test that a module can be updated to update the security flag.
   */
  public function testUpdateNewModuleWithNewVersionAndSecurityFlag() {
    $modules = [
      'test' => [
        'name' => 'test',
        'version' => '7.x-1.1'
      ]
    ];

    $siteModuleDocument = new SiteDrupalModuleDocument();
    $siteModuleDocument->setModules($modules);

    $moduleData = [
      'version' => '7.x-1.2',
      'isSecurity' => '1'
    ];
    $siteModuleDocument->updateModule('test', $moduleData);

    $expectedModule = [
      'test' => [
        'name' => 'test',
        'version' => '7.x-1.1',
        'latestVersion' => '7.x-1.2',
        'isSecurity' => '1',
      ]
    ];

    $this->assertEquals($expectedModule, $siteModuleDocument->getModules(), 'The updated module doesn\'t match');
  }

  /**
   * @test
   * Test updating a module with a new site.
   */
  public function testUpdateModulesWithNewSite() {
    $modules = [
      'test' => [
        'name' => 'test',
        'version' => '7.x-1.1'
      ]
    ];

    $siteModuleDocument = new SiteDrupalModuleDocument();
    $siteModuleDocument->setModules($modules);

    $drupalModuleDocument = new DrupalModuleDocument();
    $drupalModuleDocument->setName('test');

    $moduleManager = $this->createMock(DrupalModuleManager::class);
    $moduleManager->expects($this->any())
      ->method('findByProjectName')
      ->willReturn($drupalModuleDocument);

    $siteDocument = new SiteDocument();
    $siteDocument->setId(1);

    $siteModuleDocument->updateModules($moduleManager, $siteDocument);

    $moduleSitesExpected = [
      [
        'id' => 1,
        'name' => '[Site Name]',
        'version' => '7.x-1.1',
        'url' => ''
      ]
    ];

    $this->assertEquals($moduleSitesExpected, $drupalModuleDocument->getSites(), 'The updated module didn\'t have what was expected.');
  }

  /**
   * @test
   * Test update a module data with updated data for a site.
   */
  public function testUpdateModulesWithUpdatedSiteData() {
    $modules = [
      'test' => [
        'name' => 'test',
        'version' => '7.x-1.1'
      ]
    ];

    $siteModuleDocument = new SiteDrupalModuleDocument();
    $siteModuleDocument->setModules($modules);

    $drupalModuleDocument = new DrupalModuleDocument();
    $drupalModuleDocument->setName('test');
    $drupalModuleDocument->addSite(1, 'test site', 'http://www.example.com', '7.x-1.1');

    $moduleManager = $this->createMock(DrupalModuleManager::class);
    $moduleManager->expects($this->any())
      ->method('findByProjectName')
      ->willReturn($drupalModuleDocument);

    $siteDocument = new SiteDocument();
    $siteDocument->setId(1);

    $siteModuleDocument->updateModules($moduleManager, $siteDocument);

    $moduleSitesExpected = [
      [
        'id' => 1,
        'name' => 'test site',
        'version' => '7.x-1.1',
        'url' => 'http://www.example.com'
      ]
    ];

    $this->assertEquals($moduleSitesExpected, $drupalModuleDocument->getSites(), 'The updated module didn\'t have what was expected.');
  }

  /**
   * @test
   */
  public function testGetModulesRequiringUpdates() {
    $modules = [
      'test' => [
        'name' => 'test',
        'version' => '7.x-1.1'
      ],
      'test2' => [
        'name' => 'test2',
        'version' => '7.x-1.1'
      ]
    ];

    $siteModuleDocument = new SiteDrupalModuleDocument();
    $siteModuleDocument->setModules($modules);

    $latestVersion = [
      'test' => [
        'recommended' => [
          'version' => '7.x-1.2',
          'isSecurity' => '1'
        ]
      ],
      'test2' => [
        'recommended' => [
          'version' => '7.x-1.2',
          'isSecurity' => '0'
        ]
      ],
    ];

    $siteModuleDocument->setModulesLatestVersion($latestVersion);

    $expectedModuleList = [
      '0test' => [
        'name' => 'test',
        'version' => '7.x-1.1',
        'isUnsupported' => '',
        'latestVersion' => '7.x-1.2',
        'isSecurity' => '1'
      ],
      '1test2' => [
        'name' => 'test2',
        'version' => '7.x-1.1',
        'isUnsupported' => '',
        'latestVersion' => '7.x-1.2',
        'isSecurity' => '0'
      ]
    ];

    $modulesNeedUpdate = $siteModuleDocument->getModulesRequiringUpdates();
    $this->assertEquals($expectedModuleList, $modulesNeedUpdate, 'The modules needing updates list doesn\'t match what was expected');
  }

  /**
   * @test
   */
  public function testAddSafeVersionFlag() {
    $modules = [
      'test' => [
        'name' => 'test',
        'version' => '7.x-1.1'
      ]
    ];

    $siteModuleDocument = new SiteDrupalModuleDocument();
    $siteModuleDocument->setModules($modules);

    $latestVersion = [
      'test' => [
        'recommended' => [
          'version' => '7.x-1.2',
          'isSecurity' => '1'
        ]
      ]
    ];

    $siteModuleDocument->setModulesLatestVersion($latestVersion);

    $user = 'admin';
    $reason = 'this is my reason';
    $dateTime = new \DateTime();
    $now = $dateTime->format('Y-m-d\TH:i:s');
    $siteModuleDocument->addSafeVersionFlag($user, 'test', $reason);

    $expectedModule = [
      'test' => [
        'name' => 'test',
        'version' => '7.x-1.1',
        'latestVersion' => '7.x-1.2',
        'isSecurity' => '1',
        'isUnsupported' => false,
        'flag' => [
          'safeVersion' => [
            [
              'user' => $user,
              'datetime' => $now,
              'version' => '7.x-1.1',
              'reason' => $reason,
            ]
          ]
        ]
      ]
    ];

    $this->assertEquals($expectedModule, $siteModuleDocument->getModules(), 'The updated module doesn\'t match');
  }

  /**
   * @test
   */
  public function testModulesHasSafeVersionFlag() {
    $module = [
      'name' => 'test',
      'version' => '7.x-1.1',
      'flag' => [
        'safeVersion' => [
          [
            'user' => 'admin',
            'datetime' => '2020-01-01T12:00:00',
            'version' => '7.x-1.1',
            'reason' => 'some reason',
          ]
        ]
      ]
    ];

    $this->assertTrue(SiteDrupalModuleDocument::modulesHasSafeVersionFlag($module), 'The safe version is not set');
  }

  /**
   * @test
   */
  public function testModulesHasSafeVersionFlagDoesNotMatch() {
    $module = [
      'name' => 'test',
      'version' => '7.x-1.2',
      'flag' => [
        'safeVersion' => [
          [
            'user' => 'admin',
            'datetime' => '2020-01-01T12:00:00',
            'version' => '7.x-1.1',
            'reason' => 'some reason',
          ]
        ]
      ]
    ];

    $this->assertFalse(SiteDrupalModuleDocument::modulesHasSafeVersionFlag($module), 'The safe version is not set');
  }

  /**
   * @test
   */
  public function testModulesHasSafeVersionFlagWithNoFlag() {
    $module = [
      'name' => 'test',
      'version' => '7.x-1.2'
    ];

    $this->assertFalse(SiteDrupalModuleDocument::modulesHasSafeVersionFlag($module), 'The safe version flag is set');
  }

}
