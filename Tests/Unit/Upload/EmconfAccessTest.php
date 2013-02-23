<?php
/*                                                                     *
 * This file is brought to you by Georg Großberger                     *
 * (c) 2013 by Georg Großberger <contact@grossberger-ge.org>           *
 *                                                                     *
 * It is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU General Public License, either version 3       *
 * of the License, or (at your option) any later version.              *
 *                                                                     */

namespace T3x\ExtensionUploader\Tests\Unit\Upload;
use TYPO3\CMS\Extbase\Tests\Unit\BaseTestCase;

/**
 * Test for the emconf access
 *
 * @package ExtensionBuilder
 * @author Georg Großberger <contact@grossberger-ge.org>
 * @copyright 2013 by Georg Großberger
 * @license GPL v3 http://www.gnu.org/licenses/gpl-3.0.txt
 */
class EmconfAccessTest extends BaseTestCase {

	/**
	 * @var \T3x\ExtensionUploader\Upload\EmconfAccess
	 */
	protected $object;

	public function setUp() {
		$this->object = clone $this->objectManager->get('T3x\ExtensionUploader\Upload\EmconfAccess');
	}

	public function testLoadEmconf() {
		$conf = $this->object->loadEmconf('extension_uploader');
		$this->assertTrue(is_array($conf));
		$this->assertEquals('module', $conf['category']);
	}

	/**
	 * @expectedException \T3x\ExtensionUploader\UploaderException
	 */
	public function testLoadEmconfThrowsExceptionIfNoConfigurationIsLoaded() {
		$extensionKey = 'dummy_ext';
		$emconfUtil = $this->getMock('TYPO3\CMS\Extensionmanager\Utility\EmConfUtility');
		$emconfUtil
			->expects($this->once())
			->method('includeEmConf')
			->with(array(
				'key' => $extensionKey,
				'siteRelPath' => str_replace(PATH_site, '', PATH_typo3conf . 'ext/' . $extensionKey . '/')
			))
			->will($this->returnValue(FALSE));

		$this->object->injectEmconfUtility($emconfUtil);
		$this->object->loadEmconf($extensionKey);
	}

	public function testUpdateEmconf() {
		$newVersion = '1.2.4';
		$dummyConf = array(
			'title' => 'Dummy Extension',
			'version' => $newVersion
		);
		$emConf = '$EMCONF[$_EXTKEY] = array();';

		$object = $this->getMock('T3x\ExtensionUploader\Upload\EmconfAccess', array('writeFile', 'loadEmconf'));
		$object
			->expects($this->once())
			->method('loadEmconf')
			->with('dummy_ext')
			->will($this->returnValue($dummyConf));

		$object
			->expects($this->once())
			->method('writeFile')
			->with($emConf, PATH_typo3conf . 'ext/dummy_ext/ext_emconf.php')
			->will($this->returnValue(TRUE));

		$emconfUtil = $this->getMock('TYPO3\CMS\Extensionmanager\Utility\EmConfUtility');
		$emconfUtil
			->expects($this->once())
			->method('constructEmConf')
			->with(array(
				'extKey'  => 'dummy_ext',
				'EM_CONF' => $dummyConf
			))
			->will($this->returnValue($emConf));

		$object->injectEmconfUtility($emconfUtil);
		$object->updateEmconfVersion('dummy_ext', $newVersion);
	}

	/**
	 * @expectedException \T3x\ExtensionUploader\UploaderException
	 */
	public function testUpdateEmconfThrowsExceptionIfFileWasNotWritten() {
		$newVersion = '1.2.4';
		$dummyConf = array(
			'title' => 'Dummy Extension',
			'version' => $newVersion
		);
		$emConf = '$EMCONF[$_EXTKEY] = array();';

		$object = $this->getMock('T3x\ExtensionUploader\Upload\EmconfAccess', array('writeFile', 'loadEmconf'));
		$object
			->expects($this->once())
			->method('loadEmconf')
			->with('dummy_ext')
			->will($this->returnValue($dummyConf));

		$object
			->expects($this->once())
			->method('writeFile')
			->with($emConf, PATH_typo3conf . 'ext/dummy_ext/ext_emconf.php')
			->will($this->returnValue(FALSE));

		$emconfUtil = $this->getMock('TYPO3\CMS\Extensionmanager\Utility\EmConfUtility');
		$emconfUtil
			->expects($this->once())
			->method('constructEmConf')
			->with(array(
				'extKey'  => 'dummy_ext',
				'EM_CONF' => $dummyConf
			))
			->will($this->returnValue($emConf));

		$object->injectEmconfUtility($emconfUtil);
		$object->updateEmconfVersion('dummy_ext', $newVersion);
	}
}
