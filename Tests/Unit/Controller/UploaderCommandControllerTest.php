<?php
/*                                                                     *
 * This file is brought to you by Georg Großberger                     *
 * (c) 2013 by Georg Großberger <contact@grossberger-ge.org>           *
 *                                                                     *
 * It is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU General Public License, either version 3       *
 * of the License, or (at your option) any later version.              *
 *                                                                     */

namespace T3x\ExtensionUploader\Tests\Unit\Controller;
use TYPO3\CMS\Extbase\Tests\Unit\BaseTestCase;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Test the controller
 *
 * @package ExtensionUploader
 * @author Georg Großberger <contact@grossberger-ge.org>
 * @copyright 2013 by Georg Großberger
 * @license GPL v3 http://www.gnu.org/licenses/gpl-3.0.txt
 */
class UploaderCommandControllerTest extends BaseTestCase {

	/**
	 * @var \T3x\ExtensionUploader\Controller\UploaderCommandController
	 */
	protected $controller;

	protected function setUp() {
		$this->controller = $this->objectManager->get($this->buildAccessibleProxy('T3x\ExtensionUploader\Controller\UploaderCommandController'));
	}

	public function testUploadAction() {
		$this->markTestIncomplete('To be implemented');
	}
}
