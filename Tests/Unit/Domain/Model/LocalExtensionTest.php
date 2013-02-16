<?php
/*                                                                     *
 * This file is brought to you by Georg Großberger                     *
 * (c) 2013 by Georg Großberger <contact@grossberger-ge.org>           *
 *                                                                     *
 * It is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU General Public License, either version 3       *
 * of the License, or (at your option) any later version.              *
 *                                                                     */

namespace T3x\ExtensionUploader\Tests\Unit\Domain\Model;
use T3x\ExtensionUploader\Domain\Model\LocalExtension;

/**
 * Test for the local extension model
 *
 * @package ExtensionUploader
 * @author Georg Großberger <contact@grossberger-ge.org>
 * @copyright 2013 by Georg Großberger
 * @license GPL v3 http://www.gnu.org/licenses/gpl-3.0.txt
 */
class LocalExtensionTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var LocalExtension
	 */
	protected $object;

	protected function setUp() {
		$this->object = new LocalExtension;
	}

	public function testisDirtyAlwaysReturnsFalse() {
		$this->assertFalse($this->object->_isDirty());
		$this->assertFalse($this->object->_isDirty('extensionKey'));
		$this->object->_setProperty('title', 'Some Name');
		$this->assertFalse($this->object->_isDirty());
	}

	public function testLockType() {
		$this->assertEquals('', $this->object->getLockType());
		$this->assertEquals('xxx', $this->object->setLockType('xxx')->getLockType());
	}
	public function testDocumentationPath() {
		$this->assertEquals('', $this->object->getDocumentationPath());
		$this->assertEquals('doc/', $this->object->setDocumentationPath('doc/')->getDocumentationPath());
		$this->assertEquals('Documentation/', $this->object->setDocumentationPath('Documentation/')->getDocumentationPath());
	}

	public function testAuthorCompany() {
		$this->assertEquals('', $this->object->getAuthorCompany());
		$this->assertEquals('Monsters Inc.', $this->object->setAuthorCompany('Monsters Inc.')->getAuthorCompany());
	}

	public function testCglCompliance() {
		$this->assertEquals('', $this->object->getCglCompliance());
		$this->assertEquals('partial', $this->object->setCglCompliance('partial')->getCglCompliance());
	}

	public function testCglComplianceNote() {
		$this->assertEquals('', $this->object->getCglComplianceNote());
		$this->assertEquals('dunno exactly', $this->object->setCglComplianceNote('dunno exactly')->getCglComplianceNote());
	}

	public function testLoaded() {
		$this->assertFalse($this->object->getLoaded());
		$this->assertTrue($this->object->setLoaded(TRUE)->getLoaded());
	}

	public function testKnownToTer() {
		$this->assertFalse($this->object->getKnownToTer());
		$this->assertTrue($this->object->setKnownToTer(TRUE)->getKnownToTer());
	}

	public function testShy() {
		$this->assertFalse($this->object->getShy());
		$this->assertTrue($this->object->setShy(TRUE)->getShy());
	}

	public function testClearCacheOnLoad() {
		$this->assertFalse($this->object->getClearCachesOnLoad());
		$this->assertTrue($this->object->setClearCachesOnLoad(TRUE)->getClearCachesOnLoad());
	}

	public function testUploadFolder() {
		$this->assertFalse($this->object->getUploadFolder());
		$this->assertTrue($this->object->setUploadFolder(TRUE)->getUploadFolder());
	}

	public function testStateKey() {
		$this->assertEquals('alpha', $this->object->getStateKey());
		$this->object->setState(1);
		$this->assertEquals('beta', $this->object->getStateKey());
	}

	public function testSiteRelPath() {
		$this->assertEquals('', $this->object->getSiteRelPath());
		$this->object->setSiteRelPath('typo3conf/ext/extension_uploader/');
		$this->assertEquals('typo3conf/ext/extension_uploader/', $this->object->getSiteRelPath());
	}

	public function testCreateDirectories() {
		$this->assertEquals('', $this->object->getCreateDirectories());
		$this->object->setCreateDirectories('uploads/tx_extensionuploader/');
		$this->assertEquals('uploads/tx_extensionuploader/', $this->object->getCreateDirectories());
	}

	public function testLoadOrder() {
		$this->assertEquals('', $this->object->getLoadOrder());
		$this->object->setLoadOrder('top');
		$this->assertEquals('top', $this->object->getLoadOrder());
	}

	public function testModifiedTables() {
		$this->assertEquals('', $this->object->getModifiedTables());
		$this->object->setModifiedTables('tt_content,pages');
		$this->assertEquals('tt_content,pages', $this->object->getModifiedTables());
	}

	public function testModule() {
		$this->assertEquals('', $this->object->getModule());
		$this->object->setModule('extension_uploader');
		$this->assertEquals('extension_uploader', $this->object->getModule());
	}

	public function testPriority() {
		$this->assertEquals('', $this->object->getPriority());
		$this->object->setPriority('high');
		$this->assertEquals('high', $this->object->getPriority());
	}
}
