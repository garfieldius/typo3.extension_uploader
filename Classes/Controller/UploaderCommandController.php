<?php
/*                                                                     *
 * This file is brought to you by Georg Großberger                     *
 * (c) 2013 by Georg Großberger <contact@grossberger-ge.org>           *
 *                                                                     *
 * It is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU General Public License, either version 3       *
 * of the License, or (at your option) any later version.              *
 *                                                                     */

namespace T3x\ExtensionUploader\Controller;
use TYPO3\CMS\Extbase\Mvc\Controller\CommandController;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Upload an extension to the TER via the cli
 *
 * @package ExtensionUploader
 * @author Georg Großberger <contact@grossberger-ge.org>
 * @copyright 2013 by Georg Großberger
 * @license GPL v3 http://www.gnu.org/licenses/gpl-3.0.txt
 */
class UploaderCommandController extends CommandController {

	/**
	 * @var \T3x\ExtensionUploader\Upload\Uploader
	 */
	protected $uploader;

	/**
	 * @var \T3x\ExtensionUploader\Domain\Repository\LocalExtensionRepository
	 */
	protected $extensions;

	/**
	 * @var \TYPO3\CMS\Extensionmanager\Domain\Repository\RepositoryRepository
	 */
	protected $repositories;

	/**
	 * @param \T3x\ExtensionUploader\Upload\Uploader $uploader
	 */
	public function injectUploader(\T3x\ExtensionUploader\Upload\Uploader $uploader) {
		$this->uploader = $uploader;
	}

	/**
	 * @param \T3x\ExtensionUploader\Domain\Repository\LocalExtensionRepository $extensions
	 */
	public function injectExtensions(\T3x\ExtensionUploader\Domain\Repository\LocalExtensionRepository $extensions) {
		$this->extensions = $extensions;
	}

	/**
	 * @param \TYPO3\CMS\Extensionmanager\Domain\Repository\RepositoryRepository $repositories
	 */
	public function injectRepositories(\TYPO3\CMS\Extensionmanager\Domain\Repository\RepositoryRepository $repositories) {
		$this->repositories = $repositories;
	}

	/**
	 * Upload an extension to TER
	 *
	 * An extension with the given extension key will be entirely uploaded and a new
	 * version will be set.
	 *
	 * You can eiter provide the argument 'release' or 'version'.
	 * If the argument 'release' is set, the version number will be increased by one,
	 * following numbers will be set to '0'. eg.: a 'major' release with the current
	 * version '1.2.3' will set the version to '2.0.0', in case of a 'minor' release
	 * to '1.3.0' and a bugfix release will be '1.2.4'.
	 * If a version is given it must be a semver formated version number, like TYPO3
	 * itself uses.
	 * A version number as argument, disables the 'release' argument.
	 *
	 * The state must be a string indicating the current stability or nature of the
	 * extension. If no state is given, the extensions current state
	 * (as defined in the emconf) will be used.
	 *
	 * @param string $extkey The key of the extension
	 * @param string $username Your username for the TER (typo3.org)
	 * @param string $password Your password for the TER (typo3.org)
	 * @param string $state The state of your extension. Must be 'alpha', 'beta', 'stable', 'test', 'experimental' or 'obsolete'
	 * @param string $release The release type, must be 'bugfix', 'minor' or 'major'. Ignored if version is set
	 * @param string $version The new version of this release. Must be higher than the last. Will be set automatically if release is set properly
	 * @param string $comment The upload comment
	 * @return void
	 */
	public function uploadCommand($extkey, $username, $password, $state = '', $release = '', $version = '', $comment = '') {
		try {
			$extension = $this->extensions->findOneByExtensionKey($extkey);

			if (!empty($version)) {
				$release = 'custom';
			}

			if (empty($state)) {
				$stateId = $extension->getState();
			} else {
				$dummyObject = new \T3x\ExtensionUploader\Domain\Model\LocalExtension();
				$stateId = -1;
				foreach ($dummyObject->getDefaultState() as $index => $key) {
					if ($key === $state) {
						$stateId = $index;
						break;
					}
				}
			}

			$this->uploader->setExtension($extension);
			$this->uploader->setRepository($this->repositories->findOneTypo3OrgRepository());
			$this->uploader->setSettings(array(
				'state'         => $stateId,
				'version'       => $version,
				'release'       => $release,
				'username'      => $username,
				'password'      => $password,
				'uploadComment' => $comment
			));
			$this->uploader->validate();
			$this->uploader->upload();

			$message = LocalizationUtility::translate('upload.success.cli', 'extension_uploader', array($extkey));
		} catch (\T3x\ExtensionUploader\UploaderException $e) {
			$message = LocalizationUtility::translate('exception.' . $e->getCode(), 'extension_uploader');
		}
		$this->outputLine($message);
	}
}
