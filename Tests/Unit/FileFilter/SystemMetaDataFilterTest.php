<?php
namespace T3x\ExtensionUploader\Tests\Unit\FileFilter;
/*                                                                     *
 * This file is brought to you by Georg Großberger                     *
 * (c) 2013 by Georg Großberger <contact@grossberger-ge.org>           *
 *                                                                     *
 * It is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU General Public License, either version 3       *
 * of the License, or (at your option) any later version.              *
 *                                                                     */

use T3x\ExtensionUploader\Tests\Unit\ExtensionUploaderTestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use T3x\ExtensionUploader\FileFilter\SystemMetaDataFilter;

/**
 * Test for the VCS meta data filter
 *
 * @package ExtensionUploader
 * @author Georg Großberger <contact@grossberger-ge.org>
 * @copyright 2013 by Georg Großberger
 * @license GPL v3 http://www.gnu.org/licenses/gpl-3.0.txt
 */
class SystemMetaDataFilterTest extends ExtensionUploaderTestCase {

	/**
	 * @var SystemMetaDataFilter
	 */
	protected $fixture;

	/**
	 * @var string
	 */
	protected $extPath;

	protected function setUp() {
		$this->extPath = ExtensionManagementUtility::extPath('extension_uploader');
		$this->fixture = GeneralUtility::makeInstance('T3x\ExtensionUploader\FileFilter\SystemMetaDataFilter');
	}

	public function testWindowsThumbsCacheIsNotAllowed() {
		$result = $this->fixture->excludeFile($this->extPath . 'Resources/Public/Icons/Thumbs.db');
		$this->assertTrue($result);
	}

	public function testDotUnderscorePrefixedFileIsNotAllowed() {
		$result = $this->fixture->excludeFile($this->extPath . '._ext_localconf.php');
		$this->assertTrue($result);
	}

	public function testDotPrefixFileIsAllowed() {
		$result = $this->fixture->excludeFile($this->extPath . '.ext_localconf.php');
		$this->assertFalse($result);
	}

	public function testMacOsMetaDataDirectoryIsNotAllowed() {
		$result = $this->fixture->excludeFile($this->extPath . 'Resources/.DS_Store/somefile');
		$this->assertTrue($result);
	}
}
