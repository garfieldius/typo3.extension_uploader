<?php
/*                                                                     *
 * This file is brought to you by Georg Großberger                     *
 * (c) 2013 by Georg Großberger <contact@grossberger-ge.org>           *
 *                                                                     *
 * It is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU General Public License, either version 3       *
 * of the License, or (at your option) any later version.              *
 *                                                                     */

namespace T3x\ExtensionUploader\Tests\Unit\Utility;
use TYPO3\CMS\Extbase\Tests\Unit\BaseTestCase;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Test the states utility
 *
 * @package ExtensionUploader
 * @author Georg Großberger <contact@grossberger-ge.org>
 * @copyright 2013 by Georg Großberger
 * @license GPL v3 http://www.gnu.org/licenses/gpl-3.0.txt
 */
class StatesUtilityTest extends BaseTestCase {

	/**
	 * @var \T3x\ExtensionUploader\Utility\StatesUtility
	 */
	protected $util;

	protected function setUp() {
		$this->util = $this->objectManager->get('T3x\ExtensionUploader\Utility\StatesUtility');
	}

	protected function tearDown() {
		$this->util = NULL;
	}

	public function testGetStatesReturnsValidStates() {
		$expected = array(
			0 => LocalizationUtility::translate('state.alpha', 'extension_uploader'),
			1 => LocalizationUtility::translate('state.beta', 'extension_uploader'),
			2 => LocalizationUtility::translate('state.stable', 'extension_uploader'),
			3 => LocalizationUtility::translate('state.experimental', 'extension_uploader'),
			4 => LocalizationUtility::translate('state.test', 'extension_uploader'),
			5 => LocalizationUtility::translate('state.obsolete', 'extension_uploader')
		);
		$this->assertEquals($expected, $this->util->getStates());
	}

	public function testGetStateIdForKeyReturnsValidIndex() {
		$this->assertEquals(0, $this->util->getStateIdForKey('alpha'));
		$this->assertEquals(1, $this->util->getStateIdForKey('beta'));
		$this->assertEquals(2, $this->util->getStateIdForKey('stable'));
		$this->assertEquals(3, $this->util->getStateIdForKey('experimental'));
		$this->assertEquals(4, $this->util->getStateIdForKey('test'));
		$this->assertEquals(5, $this->util->getStateIdForKey('obsolete'));
	}

	public function testGetStateIdForKeyIgnoresUppercaseCharacters() {
		$this->assertEquals(0, $this->util->getStateIdForKey('Alpha'));
		$this->assertEquals(1, $this->util->getStateIdForKey('Beta'));
		$this->assertEquals(2, $this->util->getStateIdForKey('Stable'));
		$this->assertEquals(3, $this->util->getStateIdForKey('eXperimental'));
		$this->assertEquals(4, $this->util->getStateIdForKey('TesT'));
		$this->assertEquals(5, $this->util->getStateIdForKey('OBSOLETE'));
	}

	/**
	 * @expectedException \T3x\ExtensionUploader\UploaderException
	 */
	public function testInvalidStateThrowsException() {
		$this->util->getStateIdForKey('alpha');
		$this->util->getStateIdForKey('beta');
		$this->util->getStateIdForKey('gamma');
	}
}
