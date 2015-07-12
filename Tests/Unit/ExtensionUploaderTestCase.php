<?php
namespace T3x\ExtensionUploader\Tests\Unit;
/*                                                                     *
 * This file is brought to you by Georg Großberger                     *
 * (c) 2014 by Georg Großberger <contact@grossberger-ge.org>           *
 *                                                                     *
 * It is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU General Public License, either version 3       *
 * of the License, or (at your option) any later version.              *
 *                                                                     */

use TYPO3\CMS\Core\Tests\BaseTestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Helper for unit tests that require an object manager
 *
 * @package ExtensionUploader
 * @author Georg Großberger <contact@grossberger-ge.org>
 * @copyright 2014 by Georg Großberger
 * @license GPL v3 http://www.gnu.org/licenses/gpl-3.0.txt
 */
class ExtensionUploaderTestCase extends BaseTestCase {

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * Inject a fresh clone of the object manager
	 *
	 * @return void
	 */
	public function runBare() {

		$GLOBALS['TYPO3_DB'] = $this->getMock('TYPO3\CMS\Core\Database\DatabaseConnection');
		$GLOBALS['TYPO3_LOADED_EXT'] = array();

		/** @var \TYPO3\CMS\Core\Cache\CacheManager $cm */
		$cm = GeneralUtility::makeInstance('TYPO3\CMS\Core\Cache\CacheManager');
		$cacheConfig = array(
			'backend' => 'TYPO3\\CMS\\Core\\Cache\\Backend\\TransientMemoryBackend',
			'frontend' => 'TYPO3\\CMS\\Core\\Cache\\Frontend\\VariableFrontend'
		);
		$cm->setCacheConfigurations(array(
			'extbase_object' => $cacheConfig,
			'extbase_datamapfactory_datamap' => $cacheConfig,
		));

		$objectManager = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
		$this->objectManager = clone $objectManager;
		parent::runBare();
	}
}
