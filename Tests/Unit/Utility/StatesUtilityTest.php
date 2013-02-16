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

	public function testGetStatesReturnsValidStates() {
		$utility = $this->objectManager->create('T3x\ExtensionUploader\Utility\StatesUtility');
		$expected = array(
			0 => LocalizationUtility::translate('state.alpha', 'extension_uploader'),
			1 => LocalizationUtility::translate('state.beta', 'extension_uploader'),
			2 => LocalizationUtility::translate('state.stable', 'extension_uploader'),
			3 => LocalizationUtility::translate('state.experimental', 'extension_uploader'),
			4 => LocalizationUtility::translate('state.test', 'extension_uploader'),
			5 => LocalizationUtility::translate('state.obsolete', 'extension_uploader')
		);
		$this->assertEquals($expected, $utility->getStates());
	}
}
