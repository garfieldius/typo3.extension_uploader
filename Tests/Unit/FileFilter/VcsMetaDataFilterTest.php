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
use T3x\ExtensionUploader\FileFilter\VcsMetaDataFilter;

/**
 * Test for the VCS meta data filter
 *
 * @package ExtensionUploader
 * @author Georg Großberger <contact@grossberger-ge.org>
 * @copyright 2013 by Georg Großberger
 * @license GPL v3 http://www.gnu.org/licenses/gpl-3.0.txt
 */
class VcsMetaDataFilterTest extends ExtensionUploaderTestCase {

	/**
	 * @var VcsMetaDataFilter
	 */
	protected $fixture;

	/**
	 * @var string
	 */
	protected $extPath;

	protected function setUp() {
		$this->extPath = ExtensionManagementUtility::extPath('extension_uploader');
		$this->fixture = GeneralUtility::makeInstance('T3x\ExtensionUploader\FileFilter\VcsMetaDataFilter');
	}

	public function testHtaccessIsAllowed() {
		$result = $this->fixture->excludeFile($this->extPath . 'Resources/Private/.htaccess');
		$this->assertFalse($result);
	}

	public function testUnkownFiltypeWithDotIsAllowed() {
		$result = $this->fixture->excludeFile($this->extPath . 'Resources/Private/.private-file');
		$this->assertFalse($result);
	}

	public function testSubversionIsNotAllowed() {
		$result = $this->fixture->excludeFile($this->extPath . 'Resources/.svn');
		$this->assertTrue($result);
	}

	public function testSubversionFolderIsNotAllowed() {
		$result = $this->fixture->excludeFile($this->extPath . 'Resources/.svn/some-file');
		$this->assertTrue($result);
	}

	public function testGitIsNotAllowed() {
		$result = $this->fixture->excludeFile($this->extPath . '.gitignore');
		$this->assertTrue($result);
	}

	public function testGitFolderIsNotAllowed() {
		$result = $this->fixture->excludeFile($this->extPath . '.git/commit-msg');
		$this->assertTrue($result);
	}

	public function testMercurialIsNotAllowed() {
		$result = $this->fixture->excludeFile($this->extPath . '.hgignore');
		$this->assertTrue($result);
	}

	public function testMercurialFolderIsNotAllowed() {
		$result = $this->fixture->excludeFile($this->extPath . '.hg/commit-msg');
		$this->assertTrue($result);
	}

	public function testBazaarIsNotAllowed() {
		$result = $this->fixture->excludeFile($this->extPath . '.bzrignore');
		$this->assertTrue($result);
	}

	public function testBazaarFolderIsNotAllowed() {
		$result = $this->fixture->excludeFile($this->extPath . '.bzr/commit-msg');
		$this->assertTrue($result);
	}
}
