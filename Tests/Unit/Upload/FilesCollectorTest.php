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
 * Test for the files collector
 *
 * @package ExtensionBuilder
 * @author Georg Großberger <contact@grossberger-ge.org>
 * @copyright 2013 by Georg Großberger
 * @license GPL v3 http://www.gnu.org/licenses/gpl-3.0.txt
 */
class FilesCollectorTest extends BaseTestCase {

	public function setUp() {
		foreach (array('testA', 'testB', 'testC', '.gitinfo') as $filename) {
			$content = '';
			$length = mt_rand(100, 10000);
			$chars = '"\'\\/,.-_:;#+`´?=)(/&%$§!<> abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
			$charLength = strlen($chars) - 1;

			for ($i=0; $i < $length; $i++) {
				$content .= substr($chars, mt_rand(0, $charLength), 1);
			}
			file_put_contents(PATH_site . 'typo3temp/' . $filename, $content);
		}
	}

	public function tearDown() {
		foreach (array('testA', 'testB', 'testC', '.gitinfo') as $filename) {
			unlink(PATH_site . 'typo3temp/' . $filename);
		}
	}

	public function testCollectFilesOfExtension() {

		$expected = array();
		$files = array();
		foreach (array('testA', 'testB', 'testC') as $filename) {
			$file = PATH_site . 'typo3temp/' . $filename;
			$expected[ md5_file($file) ] = 'typo3temp/' . $filename;
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
			$file = PATH_site . 'typo3temp/' . $filename;
			$files[] = $file;
		}
		$path = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('extension_uploader');

		$collector = $this->getMock('T3x\ExtensionUploader\Upload\FilesCollector', array('collectAllFilesInDirectory'));
		$collector->expects($this->once())->method('collectAllFilesInDirectory')->with($path)->will($this->returnValue($files));

		$collector->addFilesFilter(new \T3x\ExtensionUploader\FileFilter\VcsMetaDataFilter());
		$collector->collectFilesOfExtension('extension_uploader');
	}

	public function testGetExcludedFiles() {

		$expectedIncluded = array();
		$expectedExcluded = array();
		$files = array();
		foreach (array('testA', 'testB', 'testC', '.gitinfo') as $filename) {
			$file = PATH_site . 'typo3temp/' . $filename;
			if ($filename === '.gitinfo') {
				$expectedExcluded[] = $file;
			} else {
				$expectedIncluded[ md5_file($file) ] = 'typo3temp/' . $filename;
			}
			$files[] = $file;
		}
		$path = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('extension_uploader');

		$collector = $this->getMock('T3x\ExtensionUploader\Upload\FilesCollector', array('collectAllFilesInDirectory'));
		$collector->expects($this->once())->method('collectAllFilesInDirectory')->with($path)->will($this->returnValue($files));

		$collector->addFilesFilter(new \T3x\ExtensionUploader\FileFilter\VcsMetaDataFilter());
		$actualIncluded = $collector->collectFilesOfExtension('extension_uploader');
		$this->assertEquals($expectedIncluded, $actualIncluded);
		$this->assertEquals($expectedExcluded, $collector->getExcludedFiles());
	}
}
