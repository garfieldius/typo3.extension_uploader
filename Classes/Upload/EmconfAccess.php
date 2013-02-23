<?php
/*                                                                     *
 * This file is brought to you by Georg Großberger                     *
 * (c) 2013 by Georg Großberger <contact@grossberger-ge.org>           *
 *                                                                     *
 * It is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU General Public License, either version 3       *
 * of the License, or (at your option) any later version.              *
 *                                                                     */

namespace T3x\ExtensionUploader\Upload;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use T3x\ExtensionUploader\UploaderException;

/**
 * Access methods for managing the emconf data for the extension manager
 *
 * @package ExtensionUploader
 * @author Georg Großberger <contact@grossberger-ge.org>
 * @copyright 2013 by Georg Großberger
 * @license GPL v3 http://www.gnu.org/licenses/gpl-3.0.txt
 */
class EmconfAccess implements SingletonInterface {

	/**
	 * @var \TYPO3\CMS\Extensionmanager\Utility\EmConfUtility
	 */
	protected $emconfUtility;

	/**
	 * @param \TYPO3\CMS\Extensionmanager\Utility\EmConfUtility $emconfUtility
	 */
	public function injectEmconfUtility(\TYPO3\CMS\Extensionmanager\Utility\EmConfUtility $emconfUtility) {
		$this->emconfUtility = $emconfUtility;
	}

	/**
	 * @param $extensionKey
	 * @return array
	 * @throws UploaderException
	 */
	public function loadEmconf($extensionKey) {
		$data = $this->emconfUtility->includeEmConf(array(
			'key' => $extensionKey,
			'siteRelPath' => str_replace(PATH_site, '', PATH_typo3conf . 'ext/' . $extensionKey . '/')
		));
		if (!is_array($data) || empty($data)) {
			throw new UploaderException('Cannot load emconf for ' . $extensionKey, 1361628063);
		}
		return $data;
	}

	/**
	 * Wrapper to make PHPUnit happy
	 *
	 * @param string $data
	 * @param string $file
	 * @return boolean
	 */
	protected function writeFile($data, $file) {
		return GeneralUtility::writeFile($file, $data);
	}

	/**
	 * @param string $extensionKey
	 * @param string $newVersion
	 * @throws \T3x\ExtensionUploader\UploaderException
	 */
	public function updateEmconfVersion($extensionKey, $newVersion) {
		$emconf = $this->loadEmconf($extensionKey);
		$emconf['version'] = $newVersion;
		$emconfPhp = $this->emconfUtility->constructEmConf(array(
			'extKey'  => $extensionKey,
			'EM_CONF' => $emconf
		));

		$success = $this->writeFile($emconfPhp, PATH_typo3conf . 'ext/' . $extensionKey . '/ext_emconf.php');

		if (!$success) {
			throw new UploaderException('Cannot write ext_emconf.php file of extension ' . $extensionKey, 1361628410);
		}
	}
}
