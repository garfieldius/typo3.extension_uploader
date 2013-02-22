<?php
/*                                                                     *
 * This file is brought to you by Georg Großberger                     *
 * (c) 2013 by Georg Großberger <contact@grossberger-ge.org>           *
 *                                                                     *
 * It is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU General Public License, either version 3       *
 * of the License, or (at your option) any later version.              *
 *                                                                     */

namespace T3x\ExtensionUploader\Tests\Unit\Domain\Repository;
use TYPO3\CMS\Extbase\Tests\Unit\BaseTestCase;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Test the local extension repository
 *
 * @package ExtensionUploader
 * @author Georg Großberger <contact@grossberger-ge.org>
 * @copyright 2013 by Georg Großberger
 * @license GPL v3 http://www.gnu.org/licenses/gpl-3.0.txt
 */
class LocalExtensionRepositoryTest extends BaseTestCase {

	/**
	 * @var \T3x\ExtensionUploader\Domain\Repository\LocalExtensionRepository
	 */
	protected $repository;

	protected function setUp() {
		$this->repository = $this->objectManager->get('T3x\ExtensionUploader\Domain\Repository\LocalExtensionRepository');
	}

	public function testFindAllWithTerExtension() {

		$fetchedExtension = new \TYPO3\CMS\Extensionmanager\Domain\Model\Extension();
		$fetchedExtension->setExtensionKey('extension_uploader');
		$fetchedExtension->setTitle('Extension Uploader');
		$fetchedExtension->setVersion('1.2.3');

		$expectedExtension = new \T3x\ExtensionUploader\Domain\Model\LocalExtension();
		$expectedExtension->setExtensionKey('extension_uploader');
		$expectedExtension->setTitle('Extension Uploader');
		$expectedExtension->setVersion('1.2.3');
		$expectedExtension->setKnownToTer(TRUE);

		$extensionsData = array(
			'extension_uploader' => array(
				'title' => 'Extension Uploader',
				'description' => 'Extension Uploader',
				'version' => '1.2.3',
				'terObject' => $fetchedExtension,
				'siteRelPath' => ExtensionManagementUtility::siteRelPath('extension_uploader'),
				'state' => 'alpha'
			),
			'extensionmanager' => array(
				'title' => 'Extension Manager',
				'siteRelPath' => ExtensionManagementUtility::siteRelPath('extensionmanager')
			)
		);

		$listUtility = $this->getMock('TYPO3\CMS\Extensionmanager\Utility\ListUtility');
		$listUtility->expects($this->once())->method('getAvailableAndInstalledExtensionsWithAdditionalInformation')->will($this->returnValue($extensionsData));

		$statesUtility = $this->getMock('T3x\ExtensionUploader\Utility\StatesUtility');
		$statesUtility->expects($this->once())->method('getStateIdForKey')->with('alpha')->will($this->returnValue(0));

		$expected = array(
			'extension_uploader' => $expectedExtension
		);
		$repository = $this->getMock(get_class($this->repository), array('findOneByExtensionKeyAndVersion'));
		$repository->injectListUtility($listUtility);
		$repository->injectStatesUtility($statesUtility);
		$repository->expects($this->once())->method('findOneByExtensionKeyAndVersion')->with($fetchedExtension->getExtensionKey(), $fetchedExtension->getVersion())->will($this->returnValue($expectedExtension));
		$actual = $repository->findAll();

		$this->assertEquals($expected, $actual);
	}

	public function testFindAllWithLocalOnlyExtension() {
		$fetchedExtension = new \TYPO3\CMS\Extensionmanager\Domain\Model\Extension();
		$fetchedExtension->setExtensionKey('extension_uploader');
		$fetchedExtension->setTitle('Extension Uploader');
		$fetchedExtension->setVersion('1.2.3');

		$expectedExtension = new \T3x\ExtensionUploader\Domain\Model\LocalExtension();
		$expectedExtension->setExtensionKey('extension_uploader');
		$expectedExtension->setTitle('Extension Uploader');
		$expectedExtension->setVersion('1.2.3');
		$expectedExtension->setKnownToTer(FALSE);
		$expectedExtension->setLoaded(TRUE);
		$expectedExtension->setSiteRelPath(ExtensionManagementUtility::siteRelPath('extension_uploader'));
		$expectedExtension->setSerializedDependencies(serialize(array(
			'depends'   => array(
				'typo3' => TYPO3_version . '-' . TYPO3_branch . '.99'
			),
			'conflicts' => array(),
			'suggests'  => array()
		)));

		$extensionsData = array(
			'extension_uploader' => array(
				'title' => 'Extension Uploader',
				'version' => '1.2.3',
				'siteRelPath' => ExtensionManagementUtility::siteRelPath('extension_uploader'),
				'state' => 'alpha'
			),
			'extensionmanager' => array(
				'title' => 'Extension Manager',
				'siteRelPath' => ExtensionManagementUtility::siteRelPath('extensionmanager')
			)
		);

		$listUtility = $this->getMock('TYPO3\CMS\Extensionmanager\Utility\ListUtility');
		$listUtility->expects($this->once())->method('getAvailableAndInstalledExtensionsWithAdditionalInformation')->will($this->returnValue($extensionsData));

		$statesUtility = $this->getMock('T3x\ExtensionUploader\Utility\StatesUtility');
		$statesUtility->expects($this->once())->method('getStateIdForKey')->with('alpha')->will($this->returnValue(0));

		$expected = array(
			'extension_uploader' => $expectedExtension
		);

		$this->repository->injectListUtility($listUtility);
		$this->repository->injectStatesUtility($statesUtility);
		$actual = $this->repository->findAll();

		$this->assertEquals($expected, $actual);
	}

	public function testFindAllNoDependenciesForcesDefaultDependencies() {
		$fetchedExtension = new \TYPO3\CMS\Extensionmanager\Domain\Model\Extension();
		$fetchedExtension->setExtensionKey('extension_uploader');
		$fetchedExtension->setTitle('Extension Uploader');
		$fetchedExtension->setVersion('1.2.3');

		$expectedExtension = new \T3x\ExtensionUploader\Domain\Model\LocalExtension();
		$expectedExtension->setExtensionKey('extension_uploader');
		$expectedExtension->setTitle('Extension Uploader');
		$expectedExtension->setVersion('1.2.3');
		$expectedExtension->setKnownToTer(FALSE);
		$expectedExtension->setLoaded(TRUE);
		$expectedExtension->setSiteRelPath(ExtensionManagementUtility::siteRelPath('extension_uploader'));
		$expectedExtension->setSerializedDependencies(serialize(array(
			'depends'   => array(
				'typo3' => TYPO3_version . '-' . TYPO3_branch . '.99'
			),
			'conflicts' => array(),
			'suggests'  => array()
		)));

		$extensionsData = array(
			'extension_uploader' => array(
				'title' => 'Extension Uploader',
				'version' => '1.2.3',
				'siteRelPath' => ExtensionManagementUtility::siteRelPath('extension_uploader'),
				'state' => 'alpha',
				'constraints' => NULL
			)
		);

		$listUtility = $this->getMock('TYPO3\CMS\Extensionmanager\Utility\ListUtility');
		$listUtility->expects($this->once())->method('getAvailableAndInstalledExtensionsWithAdditionalInformation')->will($this->returnValue($extensionsData));

		$statesUtility = $this->getMock('T3x\ExtensionUploader\Utility\StatesUtility');
		$statesUtility->expects($this->once())->method('getStateIdForKey')->with('alpha')->will($this->returnValue(0));

		$expected = array(
			'extension_uploader' => $expectedExtension
		);

		$this->repository->injectListUtility($listUtility);
		$this->repository->injectStatesUtility($statesUtility);
		$actual = $this->repository->findAll();

		$this->assertEquals($expected, $actual);
	}

	protected function getRepositoryMockForFindOneByExtensionKey() {
		$extension = new \T3x\ExtensionUploader\Domain\Model\LocalExtension();
		$extension->setExtensionKey('extension_uploader');
		$extension->setTitle('Extension Uploader');
		$fakeReturnValues = array(
			$extension->getExtensionKey() => $extension
		);
		$mock = $this->getMock(get_class($this->repository), array('findAll'));
		$mock->expects($this->once())->method('findAll')->will($this->returnValue($fakeReturnValues));
		return $mock;
	}

	public function testFindOneByExtensionKey() {
		$extensionKey = 'extension_uploader';
		$extension = $this->getRepositoryMockForFindOneByExtensionKey()->findOneByExtensionKey($extensionKey);
		$this->assertEquals($extensionKey, $extension->getExtensionKey());
		$this->assertTrue($extension instanceof \T3x\ExtensionUploader\Domain\Model\LocalExtension);
	}

	/**
	 * @expectedException \T3x\ExtensionUploader\Domain\Repository\UnknownExtensionException
	 */
	public function testFindOneByExtensionKeyThrowsExceptionIfUnknownExtensionIsRequested() {
		$extensionKey = 'blabla_ext_' . uniqid();
		$this->getRepositoryMockForFindOneByExtensionKey()->findOneByExtensionKey($extensionKey);
	}

	public function testExtensionNotKnownToTerHasAllPropertiesSetViaEmconfData() {

		$extensionData = array(
			'dummy_extension' => array(
				'siteRelPath' => str_replace(PATH_site, '', PATH_typo3conf . 'ext/dummy_extension/'),
				'title' => 'Dummy Extension',
				'description' => 'Some dummy extension data for testing',
				'category' => 'module',
				'author' => 'John Doe',
				'author_email' => 'john@doe.com',
				'author_company' => 'Doe Inc.',
				'shy' => '1',
				'priority' => 'top',
				'dependencies' => 'extbase,fluid,extensionmanager',
				'module' => 'mod1',
				'state' => 'beta',
				'internal' => '0',
				'uploadfolder' => '0',
				'createDirs' => 'folder1/folder2',
				'modify_tables' => 'tt_content',
				'clearCacheOnLoad' => 0,
				'lockType' => 'xy',
				'version' => '1.2.3',
				'CGLcompliance' => 'Pretty good',
				'CGLcompliance_note' => 'What else?',
				'docPath' => 'doc/',
				'constraints' => array(
					'depends' => array(
						'extbase' => '6.0-6.9.99',
						'fluid' => '6.0-6.9.99',
						'typo3' => '6.0-6.9.99',
						'extensionmanager' => '6.0.0-6.9.99',
					),
					'conflicts' => array(
					),
					'suggests' => array(
					),
				),
			)
		);

		$listUtility = $this->getMock('TYPO3\CMS\Extensionmanager\Utility\ListUtility');
		$listUtility
			->expects($this->once())
			->method('getAvailableAndInstalledExtensionsWithAdditionalInformation')
			->will($this->returnValue($extensionData));

		$statesUtility = $this->objectManager->get('T3x\ExtensionUploader\Utility\StatesUtility');

		$this->repository->injectListUtility($listUtility);
		$this->repository->injectStatesUtility($statesUtility);
		$actual = $this->repository->findOneByExtensionKey('dummy_extension');

		$this->assertEquals('dummy_extension', $actual->getExtensionKey());
		$this->assertEquals(1, $actual->getState());
		$this->assertEquals($extensionData['dummy_extension']['state'], $actual->getStateKey());
		$this->assertEquals($extensionData['dummy_extension']['title'], $actual->getTitle());
		$this->assertEquals($extensionData['dummy_extension']['description'], $actual->getDescription());
		$this->assertEquals(1, $actual->getCategory());
		$this->assertEquals($extensionData['dummy_extension']['author'], $actual->getAuthorName());
		$this->assertEquals($extensionData['dummy_extension']['author_email'], $actual->getAuthorEmail());
		$this->assertEquals($extensionData['dummy_extension']['author_company'], $actual->getAuthorCompany());
		$this->assertEquals((boolean) $extensionData['dummy_extension']['shy'], $actual->getShy());
		$this->assertEquals($extensionData['dummy_extension']['priority'], $actual->getPriority());
		$this->assertEquals($extensionData['dummy_extension']['module'], $actual->getModule());
		$this->assertEquals((boolean) $extensionData['dummy_extension']['uploadfolder'], $actual->getUploadFolder());
		$this->assertEquals($extensionData['dummy_extension']['createDirs'], $actual->getCreateDirectories());
		$this->assertEquals($extensionData['dummy_extension']['modify_tables'], $actual->getModifiedTables());
		$this->assertEquals((boolean) $extensionData['dummy_extension']['clearCacheOnLoad'], $actual->getClearCachesOnLoad());
		$this->assertEquals($extensionData['dummy_extension']['lockType'], $actual->getLockType());
		$this->assertEquals($extensionData['dummy_extension']['version'], $actual->getVersion());
		$this->assertEquals($extensionData['dummy_extension']['docPath'], $actual->getDocumentationPath());
		$this->assertEquals($extensionData['dummy_extension']['CGLcompliance'], $actual->getCglCompliance());
		$this->assertEquals($extensionData['dummy_extension']['CGLcompliance_note'], $actual->getCglComplianceNote());
	}
}
