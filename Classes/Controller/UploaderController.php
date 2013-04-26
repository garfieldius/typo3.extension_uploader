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
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Controller for the extension upload
 *
 * @package ExtensionUploader
 * @author Georg Großberger <contact@grossberger-ge.org>
 * @copyright 2013 by Georg Großberger
 * @license GPL v3 http://www.gnu.org/licenses/gpl-3.0.txt
 */
class UploaderController extends ActionController {

	/**
	 * @var \T3x\ExtensionUploader\Domain\Repository\LocalExtensionRepository
	 */
	protected $extensions;

	/**
	 * @var \T3x\ExtensionUploader\Upload\Uploader
	 */
	protected $uploader;

	/**
	 * @var \T3x\ExtensionUploader\Utility\StatesUtility
	 */
	protected $statesUtility;

	/**
	 * @var \TYPO3\CMS\Extensionmanager\Domain\Repository\RepositoryRepository
	 */
	protected $repositories;

	/**
	 * Injects the installed extensions repository
	 *
	 * @param \T3x\ExtensionUploader\Domain\Repository\LocalExtensionRepository $extensions
	 */
	public function injectExtensions(\T3x\ExtensionUploader\Domain\Repository\LocalExtensionRepository $extensions) {
		$this->extensions = $extensions;
	}

	/**
	 * Injects the uploader
	 *
	 * @param \T3x\ExtensionUploader\Upload\Uploader $uploader
	 */
	public function injectUploader(\T3x\ExtensionUploader\Upload\Uploader $uploader) {
		$this->uploader = $uploader;
	}

	/**
	 * @param \T3x\ExtensionUploader\Utility\StatesUtility $statesUtility
	 */
	public function injectStatesUtility(\T3x\ExtensionUploader\Utility\StatesUtility $statesUtility) {
		$this->statesUtility = $statesUtility;
	}

	/**
	 * @param \TYPO3\CMS\Extensionmanager\Domain\Repository\RepositoryRepository $repositories
	 */
	public function injectRepositories(\TYPO3\CMS\Extensionmanager\Domain\Repository\RepositoryRepository $repositories) {
		$this->repositories = $repositories;
	}

	public function listAction() {
		$this->extensions->setSilenceExceptions(TRUE);
		$this->view->assign('extensions', $this->extensions->findAll());
	}

	/**
	 * Set release options
	 *
	 * @param string $extensionKey
	 * @param array $settings
	 * @return void
	 */
	public function settingsAction($extensionKey, array $settings = array()) {
		$this->view->assignMultiple(array(
			'extension'    => $this->extensions->findOneByExtensionKey($extensionKey),
			'states'       => $this->statesUtility->getStates(),
			'repositories' => $this->repositories->findAll(),
			'settings'     => $settings
		));
	}

	/**
	 * Perform the actual upload
	 *
	 * @param string $extensionKey
	 * @param array $settings
	 * @param \TYPO3\CMS\Extensionmanager\Domain\Model\Repository $repository
	 * @return void
	 */
	public function uploadAction($extensionKey, array $settings, \TYPO3\CMS\Extensionmanager\Domain\Model\Repository $repository) {
		try {
			$this->uploader->setExtension($this->extensions->findOneByExtensionKey($extensionKey));
			$this->uploader->setSettings($settings);
			$this->uploader->setRepository($repository);
			$this->uploader->validate();
			$this->uploader->upload();

			$message = LocalizationUtility::translate('upload.success', 'extension_uploader', array($extensionKey, $this->uploader->getReleasedVersion()));
			$this->flashMessageContainer->add($message);
			$this->redirect('list');

		} catch (\T3x\ExtensionUploader\UploaderException $e) {
			$message = LocalizationUtility::translate('exception.' . $e->getCode(), 'extension_uploader');
			$this->flashMessageContainer->add($message, '', FlashMessage::ERROR);
			$this->redirect('settings', NULL, NULL, array('extensionKey' => $extensionKey, 'settings' => $settings));
		}
	}
}
