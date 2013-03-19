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
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extensionmanager\Domain\Model\Repository;

/**
 * Worker of an extension upload process
 *
 * @package ExtensionUploader
 * @author Georg Großberger <contact@grossberger-ge.org>
 * @copyright 2013 by Georg Großberger
 * @license GPL v3 http://www.gnu.org/licenses/gpl-3.0.txt
 */
class Uploader {

	/**
	 * @var \T3x\ExtensionUploader\Utility\StatesUtility
	 */
	protected $statesUtility;

	/**
	 * @var \T3x\ExtensionUploader\Utility\ObjectUtility
	 */
	protected $objects;

	/**
	 * @var array
	 */
	protected $settings;

	/**
	 * @var \T3x\ExtensionUploader\Domain\Model\LocalExtension
	 */
	protected $extension;

	/**
	 * @var \TYPO3\CMS\Extensionmanager\Domain\Model\Repository
	 */
	protected $repository;

	/**
	 * @var \T3x\ExtensionUploader\Upload\ExtensionDataCollector
	 */
	protected $dataCollector;

	/**
	 * @var \T3x\ExtensionUploader\Upload\EmconfAccess
	 */
	protected $emconfAccess;
	/**
	 * @param \T3x\ExtensionUploader\Utility\StatesUtility $statesUtility
	 */
	public function injectStatesUtility(\T3x\ExtensionUploader\Utility\StatesUtility $statesUtility) {
		$this->statesUtility = $statesUtility;
	}

	/**
	 * @param \T3x\ExtensionUploader\Utility\ObjectUtility $objects
	 */
	public function injectObjects(\T3x\ExtensionUploader\Utility\ObjectUtility $objects) {
		$this->objects = $objects;
	}

	/**
	 * @param \T3x\ExtensionUploader\Upload\ExtensionDataCollector $dataCollector
	 */
	public function injectDataCollector(\T3x\ExtensionUploader\Upload\ExtensionDataCollector $dataCollector) {
		$this->dataCollector = $dataCollector;
	}

	/**
	 * @param \T3x\ExtensionUploader\Upload\EmconfAccess $emconfAccess
	 */
	public function injectEmconfAccess(\T3x\ExtensionUploader\Upload\EmconfAccess $emconfAccess) {
		$this->emconfAccess = $emconfAccess;
	}

	/**
	 * @param \TYPO3\CMS\Extensionmanager\Domain\Model\Repository $repository
	 */
	public function setRepository(Repository $repository) {
		$this->repository = $repository;
	}

	/**
	 * Sets Extension
	 * @param \T3x\ExtensionUploader\Domain\Model\LocalExtension $extension
	 * @return Uploader
	 */
	public function setExtension($extension) {
		$this->extension = $extension;
		return $this;
	}

	/**
	 * Sets Settings
	 * @param array $settings
	 * @return Uploader
	 */
	public function setSettings($settings) {
		$this->settings = $settings;
		return $this;
	}

	/**
	 * Validate given settings and clean them up if necessary
	 *
	 * @throws ValidationFailedException
	 */
	public function validate() {

		// Validate set state
		if (!isset($this->settings['state'])) {
			throw new ValidationFailedException('No state given', 1360445248);
		}

		$validStates = $this->statesUtility->getStates();
		if (!isset($validStates[ $this->settings['state'] ])) {
			throw new ValidationFailedException('State not supported', 1360445400);
		} else {
			$this->settings['state'] = $validStates[$this->settings['state']];
		}

		if (empty($this->settings['release'])) {
			throw new ValidationFailedException('No release type', 1360445614);
		}

		list($major, $minor, $bugfix) = explode('.', $this->extension->getVersion());
		switch ($this->settings['release']) {
			case 'major':
				$major++;
				$minor = 0;
				$bugfix = 0;
				break;

			case 'minor':
				$minor++;
				$bugfix = 0;
				break;

			case 'bugfix':
				$bugfix++;
				break;

			case 'custom':
				list($major, $minor, $bugfix) = explode('.', $this->settings['version']);
				break;

			default:
				throw new ValidationFailedException('Invalid release type', 1360445899);
		}

		$this->settings['version'] = $major . '.' . $minor . '.' . $bugfix;
		if (!preg_match('/^([0-9]{1,})\.([0-9]{1,})\.([0-9]{1,})$/', $this->settings['version'])) {
			throw new ValidationFailedException('Invalid version number', 1360445797);
		}

		if (VersionNumberUtility::convertVersionNumberToInteger($this->settings['version']) <= VersionNumberUtility::convertVersionNumberToInteger($this->extension->getVersion())) {
			throw new ValidationFailedException('Release version lower than released version', 1360446051);
		}

		if (empty($this->settings['username']) || empty($this->settings['password'])) {
			throw new ValidationFailedException('Incomplete credentials', 1360446180);
		}

		$this->settings['username'] = trim($this->settings['username']);
		$this->settings['password'] = trim($this->settings['password']);

		// Validate username
		if (!preg_match('/^[0-9a-z\-_]{3,}$/', $this->settings['username'])) {
			throw new ValidationFailedException('Username not valid', 1360446444);
		}

		// Validate password
		if (strlen($this->settings['password']) < 8) {
			throw new ValidationFailedException('Password is too short', 1360446283);
		}
	}

	public function upload() {
		$connection = $this->objects->getSoapConnectionForRepository(
			$this->repository,
			$this->settings['username'],
			$this->settings['password']
		);
		$connection->uploadExtension(array(
			'accountData'   => array(
				'username'  => $this->settings['username'],
				'password'  => $this->settings['password'],
			),
			'extensionData' => $this->dataCollector->getDataForExtension($this->extension, $this->settings),
			'filesData'     => $this->objects->getFilesCollector()->collectFilesOfExtension($this->extension->getExtensionKey())
		));
		$this->emconfAccess->updateEmconfVersion(
			$this->extension->getExtensionKey(),
			$this->settings['version']
		);
	}
}
