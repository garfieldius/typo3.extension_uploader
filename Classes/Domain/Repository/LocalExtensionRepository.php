<?php
/*                                                                     *
 * This file is brought to you by Georg Großberger                     *
 * (c) 2013 by Georg Großberger <contact@grossberger-ge.org>           *
 *                                                                     *
 * It is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU General Public License, either version 3       *
 * of the License, or (at your option) any later version.              *
 *                                                                     */

namespace T3x\ExtensionUploader\Domain\Repository;
use T3x\ExtensionUploader\UploaderException;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageQueue;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Extensionmanager\Domain\Repository\ExtensionRepository;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Repository of extensions in the "local" space
 *
 * @package ExtensionUploader
 * @author Georg Großberger <contact@grossberger-ge.org>
 * @copyright 2013 by Georg Großberger
 * @license GPL v3 http://www.gnu.org/licenses/gpl-3.0.txt
 */
class LocalExtensionRepository extends ExtensionRepository {

	/**
	 * @var \TYPO3\CMS\Extensionmanager\Utility\ListUtility
	 */
	protected $listUtility;

	/**
	 * @var \T3x\ExtensionUploader\Utility\StatesUtility
	 */
	protected $statesUtility;

	/**
	 * @var boolean
	 */
	protected $silenceExceptions = FALSE;

	/**
	 * The flash messages. Use $this->flashMessageContainer->add(...) to add a new Flash message.
	 *
	 * @var \TYPO3\CMS\Extbase\Mvc\Controller\FlashMessageContainer
	 * @api
	 */
	protected $flashMessageContainer;

	/**
	 * @param \TYPO3\CMS\Extensionmanager\Utility\ListUtility $listUtility
	 */
	public function injectListUtility(\TYPO3\CMS\Extensionmanager\Utility\ListUtility $listUtility) {
		$this->listUtility = $listUtility;
	}

	/**
	 * @param \T3x\ExtensionUploader\Utility\StatesUtility $statesUtility
	 */
	public function injectStatesUtility(\T3x\ExtensionUploader\Utility\StatesUtility $statesUtility) {
		$this->statesUtility = $statesUtility;
	}

	/**
	 * Injects the flash messages container
	 *
	 * @param \TYPO3\CMS\Extbase\Mvc\Controller\FlashMessageContainer $flashMessageContainer
	 * @return void
	 */
	public function injectFlashMessageContainer(\TYPO3\CMS\Extbase\Mvc\Controller\FlashMessageContainer $flashMessageContainer) {
		$this->flashMessageContainer = $flashMessageContainer;
	}

	/**
	 * @param boolean $silent
	 * @return LocalExtensionRepository
	 */
	public function setSilenceExceptions($silent) {
		$this->silenceExceptions = (boolean) $silent;
		return $this;
	}

	/**
	 * Find all extensions in the typo3conf/ext directory, even uninstalled
	 *
	 * @return array
	 */
	public function findAll() {

		$availableLocalExtensions = $this->listUtility->getAvailableAndInstalledExtensionsWithAdditionalInformation();
		$relPath = str_replace(PATH_site, '', PATH_typo3conf . 'ext');
		$extensions = array();
		$forcedDefaultDependencies = serialize(array(
			'depends'   => array(
				'typo3' => TYPO3_version . '-' . TYPO3_branch . '.99'
			),
			'conflicts' => array(),
			'suggests'  => array()
		));

		foreach ($availableLocalExtensions as $extKey => $extensionConfig) {

			if (strpos($extensionConfig['siteRelPath'], $relPath) === FALSE) {
				continue;
			}
			try {
				/* @var $extension \T3x\ExtensionUploader\Domain\Model\LocalExtension */
				if (isset($extensionConfig['terObject']) && $extensionConfig['terObject'] instanceof \TYPO3\CMS\Extensionmanager\Domain\Model\Extension) {
					$extension = $this->findOneByExtensionKeyAndVersion($extKey, $extensionConfig['terObject']->getVersion());
					$extension->setKnownToTer(TRUE);
				} else {
					$extension = GeneralUtility::makeInstance('T3x\ExtensionUploader\Domain\Model\LocalExtension');
					$extension->setExtensionKey($extKey);
					$extension->setKnownToTer(FALSE);
				}

				$extension->setVersion($extensionConfig['version']);
				$extension->setTitle($extensionConfig['title']);
				$extension->setDescription($extensionConfig['description']);
				$extension->setLoaded(ExtensionManagementUtility::isLoaded($extKey));
				$extension->setSiteRelPath($extensionConfig['siteRelPath']);
				$extension->setState($this->statesUtility->getStateIdForKey($extensionConfig['state']));

				if (!empty($extensionConfig['constraints']) && is_array($extensionConfig['constraints'])) {
					$extension->setSerializedDependencies(serialize($extensionConfig['constraints']));
				} else {
					$extension->setSerializedDependencies($forcedDefaultDependencies);
				}

				if (!empty($extensionConfig['category'])) {
					$extension->setCategoryByKey($extensionConfig['category']);
				}
				if (!empty($extensionConfig['author'])) {
					$extension->setAuthorName($extensionConfig['author']);
				}
				if (!empty($extensionConfig['author_email'])) {
					$extension->setAuthorEmail($extensionConfig['author_email']);
				}
				if (!empty($extensionConfig['author_company'])) {
					$extension->setAuthorCompany($extensionConfig['author_company']);
				}
				if (!empty($extensionConfig['CGLcompliance'])) {
					$extension->setCglCompliance($extensionConfig['CGLcompliance']);
				}
				if (!empty($extensionConfig['CGLcompliance_note'])) {
					$extension->setCglComplianceNote($extensionConfig['CGLcompliance_note']);
				}
				if (!empty($extensionConfig['uploadfolder'])) {
					$extension->setUploadFolder(TRUE);
				}
				if (!empty($extensionConfig['shy'])) {
					$extension->setShy(TRUE);
				}
				if (!empty($extensionConfig['clearCacheOnLoad'])) {
					$extension->setClearCachesOnLoad(TRUE);
				}
				if (!empty($extensionConfig['createDirs'])) {
					$extension->setCreateDirectories($extensionConfig['createDirs']);
				}
				if (!empty($extensionConfig['module'])) {
					$extension->setModule($extensionConfig['module']);
				}
				if (!empty($extensionConfig['modify_tables'])) {
					$extension->setModifiedTables($extensionConfig['modify_tables']);
				}
				if (!empty($extensionConfig['priority'])) {
					$extension->setPriority($extensionConfig['priority']);
				}
				if (!empty($extensionConfig['lockType'])) {
					$extension->setLockType($extensionConfig['lockType']);
				}
				if (!empty($extensionConfig['docPath'])) {
					$extension->setDocumentationPath($extensionConfig['docPath']);
				}

				$extensions[$extKey] = $extension;

			} catch (UploaderException $exception) {
				if ($this->silenceExceptions === TRUE) {
					$message = LocalizationUtility::translate(
						'error.' . $exception->getCode(),
						'extension_uploader',
						array($extKey, $exception->getMessage())
					);
					$this->flashMessageContainer->add($message ?: $exception->getMessage(), '', FlashMessage::ERROR);
				} else {
					throw $exception;
				}
			}
		}

		ksort($extensions);
		return $extensions;
	}

	/**
	 * @param string $extensionKey
	 * @return \T3x\ExtensionUploader\Domain\Model\LocalExtension
	 * @throws UnknownExtensionException
	 */
	public function findOneByExtensionKey($extensionKey) {
		$extensions = $this->findAll();
		if (!isset($extensions[$extensionKey])) {
			throw new UnknownExtensionException("Extension key '$extensionKey' is not a known local extension");
		}
		return $extensions[$extensionKey];
	}
}
