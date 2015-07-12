<?php
namespace T3x\ExtensionUploader\Tests\Unit\Upload;
/*                                                                     *
 * This file is brought to you by Georg Großberger                     *
 * (c) 2013 by Georg Großberger <contact@grossberger-ge.org>           *
 *                                                                     *
 * It is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU General Public License, either version 3       *
 * of the License, or (at your option) any later version.              *
 *                                                                     */

use T3x\ExtensionUploader\FileFilter\ExtensionBuilderFilter;
use T3x\ExtensionUploader\FileFilter\ExtensionManagerMetaDataFilter;
use T3x\ExtensionUploader\FileFilter\SystemMetaDataFilter;
use T3x\ExtensionUploader\FileFilter\VcsMetaDataFilter;
use T3x\ExtensionUploader\Tests\Unit\ExtensionUploaderTestCase;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Test for the files collector
 *
 * @package ExtensionUploader
 * @author Georg Großberger <contact@grossberger-ge.org>
 * @copyright 2013 by Georg Großberger
 * @license GPL v3 http://www.gnu.org/licenses/gpl-3.0.txt
 */
class FilesCollectorTest extends ExtensionUploaderTestCase {

	public function setUp() {
		foreach (array('testA', 'testB', 'testC', '.gitinfo') as $filename) {
			$content = '';
			$length = mt_rand(100, 10000);
			$chars = '"\'\\/,.-_:;#+`´?=)(/&%$§!<> abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
			$charLength = strlen($chars) - 1;

			for ($i=0; $i < $length; $i++) {
				$content .= substr($chars, mt_rand(0, $charLength), 1);
			}
			file_put_contents(ExtensionManagementUtility::extPath('extension_uploader') . $filename, $content);
		}
	}

	public function tearDown() {
		foreach (array('testA', 'testB', 'testC', '.gitinfo') as $filename) {
			$file = ExtensionManagementUtility::extPath('extension_uploader') . $filename;
			if (is_file($file)) {
				unlink($file);
			}
		}
	}

	public function testCollectFilesOfExtension() {

		$expected = array();
		$files = array();
		foreach (array('testA', 'testB', 'testC') as $filename) {
			$file = ExtensionManagementUtility::extPath('extension_uploader') . $filename;
			$content = file_get_contents($file);
			$id = md5($content);
			$expected[ $filename ] =  array(
				'name'             => utf8_encode($filename),
				'size'             => strlen($content),
				'modificationTime' => intval(filemtime($file)),
				'isExecutable'     => intval(is_executable($file)),
				'content'          => $content,
				'contentMD5'       => $id,
				'content_md5'      => $id
			);
			$files[] = $file;
		}
		$path = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('extension_uploader');

		$collector = $this->getMock('T3x\ExtensionUploader\Upload\FilesCollector', array('collectAllFilesInDirectory'));
		$collector->expects($this->once())->method('collectAllFilesInDirectory')->with($path)->will($this->returnValue($files));
		$actual = $collector->collectFilesOfExtension('extension_uploader');
		$this->assertEquals($expected, $actual);
	}

	/**
	 * @expectedException \T3x\ExtensionUploader\Upload\NoFileAccessException
	 */
	public function testCollectingNotAccessibleFileThrowsException() {
		$files = array();
		foreach (array('testA', 'testB', 'notExistingC') as $filename) {
			$file = ExtensionManagementUtility::extPath('extension_uploader') . $filename;
			$files[] = $file;
		}
		$path = ExtensionManagementUtility::extPath('extension_uploader');

		$collector = $this->getMock('T3x\ExtensionUploader\Upload\FilesCollector', array('collectAllFilesInDirectory'));
		$collector->expects($this->once())->method('collectAllFilesInDirectory')->with($path)->will($this->returnValue($files));

		$collector->addFilesFilter(new VcsMetaDataFilter());
		$collector->collectFilesOfExtension('extension_uploader');
	}

	public function testFilesCollectorGrabsAllFilesInAnExtensionDirectory() {
		$this->tearDown();

		// TODO: This hardcoded list is not a good idea, replace it with something better yet as reliable
		$expected = array(
			'ext_icon.gif',
			'ext_localconf.php',
			'ext_tables.php',
			'ext_typoscript_setup.txt',
			'Readme.rst',
			'doc/manual.sxw',
			'Build/UnitTestsuite.xml',
			'Classes/UploaderException.php',
			'Classes/Controller/UploaderCommandController.php',
			'Classes/Controller/UploaderController.php',
			'Classes/Domain/Model/LocalExtension.php',
			'Classes/Domain/Repository/LocalExtensionRepository.php',
			'Classes/Domain/Repository/UnknownExtensionException.php',
			'Classes/FileFilter/ExtensionBuilderFilter.php',
			'Classes/FileFilter/ExtensionManagerMetaDataFilter.php',
			'Classes/FileFilter/FileFilterInterface.php',
			'Classes/FileFilter/SystemMetaDataFilter.php',
			'Classes/FileFilter/VcsMetaDataFilter.php',
			'Classes/Upload/Connection.php',
			'Classes/Upload/ConnectionException.php',
			'Classes/Upload/EmconfAccess.php',
			'Classes/Upload/ExtensionDataCollector.php',
			'Classes/Upload/FilesCollector.php',
			'Classes/Upload/NoFileAccessException.php',
			'Classes/Upload/Uploader.php',
			'Classes/Upload/ValidationFailedException.php',
			'Classes/Utility/InvalidObjectException.php',
			'Classes/Utility/ObjectUtility.php',
			'Classes/Utility/StatesUtility.php',
			'Documentation/Index.rst',
			'Documentation/License.rst',
			'Documentation/Useage.rst',
			'Documentation/Images/List.png',
			'Documentation/Images/Overview.png',
			'Documentation/Images/Settings.png',
			'Resources/Private/.htaccess',
			'Resources/Private/Language/locallang.xlf',
			'Resources/Private/Layouts/Default.html',
			'Resources/Private/Templates/Uploader/List.html',
			'Resources/Private/Templates/Uploader/Settings.html',
			'Resources/Public/Javascript/List.js',
			'Resources/Public/Javascript/Settings.js',
			'Resources/Public/Stylesheet/Uploader.css',
			'Tests/Unit/Controller/UploaderCommandControllerTest.php',
			'Tests/Unit/Controller/UploaderControllerTest.php',
			'Tests/Unit/Domain/Model/LocalExtensionTest.php',
			'Tests/Unit/Domain/Repository/LocalExtensionRepositoryTest.php',
			'Tests/Unit/FileFilter/ExtensionBuilderFilterTest.php',
			'Tests/Unit/FileFilter/ExtensionManagerMetaDataFilterTest.php',
			'Tests/Unit/FileFilter/SystemMetaDataFilterTest.php',
			'Tests/Unit/FileFilter/VcsMetaDataFilterTest.php',
			'Tests/Unit/Upload/ConnectionTest.php',
			'Tests/Unit/Upload/EmconfAccessTest.php',
			'Tests/Unit/Upload/ExtensionDataCollectorTest.php',
			'Tests/Unit/Upload/FilesCollectorTest.php',
			'Tests/Unit/Upload/UploaderTest.php',
			'Tests/Unit/Utility/ObjectUtilityTest.php',
			'Tests/Unit/Utility/StatesUtilityTest.php',
			'Tests/Unit/ExtensionUploaderTestCase.php',
		);
		$collector = $this->objectManager->get('T3x\ExtensionUploader\Upload\FilesCollector');

		$collector->addFilesFilter(new VcsMetaDataFilter());
		$collector->addFilesFilter(new ExtensionBuilderFilter());
		$collector->addFilesFilter(new SystemMetaDataFilter());
		$collector->addFilesFilter(new ExtensionManagerMetaDataFilter());
		$actual = $collector->collectFilesOfExtension('extension_uploader');

		$this->assertEquals(count($expected), count($actual));

		foreach ($actual as $fileInfo) {
			$this->assertTrue(in_array($fileInfo['name'], $expected, TRUE), 'Unexpected file ' . $fileInfo['name']);
			$expected = array_filter($expected, function($file) use($fileInfo) {
				return $file !== $fileInfo['name'];
			});
		}
		$this->assertEquals(0, count($expected), count($expected) . " expected files have not been found, first: " . $expected[0]);
	}
}
