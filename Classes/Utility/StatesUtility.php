<?php
/*                                                                     *
 * This file is brought to you by Georg Großberger                     *
 * (c) 2013 by Georg Großberger <contact@grossberger-ge.org>           *
 *                                                                     *
 * It is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU General Public License, either version 3       *
 * of the License, or (at your option) any later version.              *
 *                                                                     */

namespace T3x\ExtensionUploader\Utility;
use TYPO3\CMS\Extensionmanager\Domain\Model\Extension;
use T3x\ExtensionUploader\UploaderException;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Utility that provides data for the template, called by the list action
 *
 * @package ExtensionUploader
 * @author Georg Großberger <contact@grossberger-ge.org>
 * @copyright 2013 by Georg Großberger
 * @license GPL v3 http://www.gnu.org/licenses/gpl-3.0.txt
 */
class StatesUtility {

	/**
	 * @var array
	 */
	protected $keyToIndexMap;

	/**
	 * @return array
	 */
	public function getStates() {
		$dummyInstance = new Extension();
		$options = array();

		foreach ($dummyInstance->getDefaultState() as $index => $key) {
			if ($index < 6) {
				$options[$index] = LocalizationUtility::translate('state.' . $key, 'ExtensionUploader');
			}
		}
		return $options;
	}

	/**
	 * Get the numeric index of a state by its key
	 * alpha => 0, beta => 1, ...
	 *
	 * @param string $stateKey
	 * @return integer
	 * @throws \T3x\ExtensionUploader\UploaderException
	 */
	public function getStateIdForKey($stateKey) {
		if (!$this->keyToIndexMap) {

			$dummyInstance = new Extension();
			$this->keyToIndexMap = array();

			foreach ($dummyInstance->getDefaultState() as $index => $key) {
				if ($index < 6) {
					$this->keyToIndexMap[$key] = $index;
				}
			}
		}

		$stateKey = strtolower($stateKey);

		if (!isset($this->keyToIndexMap[$stateKey])) {
			throw new UploaderException('Invalid state key', 1361547441);
		}
		return $this->keyToIndexMap[$stateKey];
	}
}
