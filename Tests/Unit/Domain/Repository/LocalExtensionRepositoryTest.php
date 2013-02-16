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
				'terObject' => $fetchedExtension,
				'siteRelPath' => ExtensionManagementUtility::siteRelPath('extension_uploader')
			),
			'extensionmanager' => array(
				'title' => 'Extension Manager',
				'siteRelPath' => ExtensionManagementUtility::siteRelPath('extensionmanager')
			)
		);

		$listUtility = $this->getMock('TYPO3\CMS\Extensionmanager\Utility\ListUtility');
		$listUtility->expects($this->once())->method('getAvailableAndInstalledExtensionsWithAdditionalInformation')->will($this->returnValue($extensionsData));


		$expected = array(
			'extension_uploader' => $expectedExtension
		);
		$repository = $this->getMock(get_class($this->repository), array('findOneByExtensionKeyAndVersion'));
		$repository->injectListUtility($listUtility);
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
				'siteRelPath' => ExtensionManagementUtility::siteRelPath('extension_uploader')
			),
			'extensionmanager' => array(
				'title' => 'Extension Manager',
				'siteRelPath' => ExtensionManagementUtility::siteRelPath('extensionmanager')
			)
		);

		$listUtility = $this->getMock('TYPO3\CMS\Extensionmanager\Utility\ListUtility');
		$listUtility->expects($this->once())->method('getAvailableAndInstalledExtensionsWithAdditionalInformation')->will($this->returnValue($extensionsData));


		$expected = array(
			'extension_uploader' => $expectedExtension
		);

		$this->repository->injectListUtility($listUtility);
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
				'constraints' => NULL
			)
		);

		$listUtility = $this->getMock('TYPO3\CMS\Extensionmanager\Utility\ListUtility');
		$listUtility->expects($this->once())->method('getAvailableAndInstalledExtensionsWithAdditionalInformation')->will($this->returnValue($extensionsData));

		$expected = array(
			'extension_uploader' => $expectedExtension
		);

		$this->repository->injectListUtility($listUtility);
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
}
