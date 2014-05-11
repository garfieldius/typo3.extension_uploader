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
use T3x\ExtensionUploader\FileFilter\ExtensionManagerMetaDataFilter;

/**
 * Test for the VCS meta data filter
 *
 * @package ExtensionUploader
 * @author Georg Großberger <contact@grossberger-ge.org>
 * @copyright 2013 by Georg Großberger
 * @license GPL v3 http://www.gnu.org/licenses/gpl-3.0.txt
 */
class ExtensionManagerMetaDataFilterTest extends ExtensionUploaderTestCase {

	/**
	 * @var ExtensionManagerMetaDataFilter
	 */
	protected $fixture;

	/**
	 * @var string
	 */
	protected $extPath;

	protected function setUp() {
		$this->extPath = ExtensionManagementUtility::extPath('extension_uploader');
		$this->fixture = GeneralUtility::makeInstance('T3x\ExtensionUploader\FileFilter\ExtensionManagerMetaDataFilter');
	}

	public function testNestedEmconfIsAllowed() {
		$result = $this->fixture->excludeFile($this->extPath . 'Resources/Private/PHP/ext_emconf.php');
		$this->assertFalse($result);
	}

	public function testMainEmconfIsNotAllowed() {
		$result = $this->fixture->excludeFile($this->extPath . 'ext_emconf.php');
		$this->assertTrue($result);
	}
}
