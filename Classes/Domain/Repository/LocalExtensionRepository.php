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
use TYPO3\CMS\Core\Utility\GeneralUtility;
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
	 * @inject
	 * @var \TYPO3\CMS\Extensionmanager\Utility\ListUtility
	 */
	protected $listUtility;

	/**
	 * @param \TYPO3\CMS\Extensionmanager\Utility\ListUtility $listUtility
	 */
	public function injectListUtility(\TYPO3\CMS\Extensionmanager\Utility\ListUtility $listUtility) {
		$this->listUtility = $listUtility;
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

			if (isset($extensionConfig['terObject']) && $extensionConfig['terObject'] instanceof \TYPO3\CMS\Extensionmanager\Domain\Model\Extension) {
				$extension = $this->findOneByExtensionKeyAndVersion($extKey, $extensionConfig['terObject']->getVersion());
				$extension->setKnownToTer(TRUE);
			} else {
				$extension = GeneralUtility::makeInstance('T3x\ExtensionUploader\Domain\Model\LocalExtension');
				$extension->setExtensionKey($extKey);
				$extension->setKnownToTer(FALSE);
			}
			/* @var $extension \T3x\ExtensionUploader\Domain\Model\LocalExtension */
			$extension->setVersion($extensionConfig['version']);
			$extension->setTitle($extensionConfig['title']);
			$extension->setLoaded(ExtensionManagementUtility::isLoaded($extKey));
			$extension->setSiteRelPath($extensionConfig['siteRelPath']);

			if (!empty($extensionConfig['constraints']) && is_array($extensionConfig['constraints'])) {
				$extension->setSerializedDependencies(serialize($extensionConfig['constraints']));
			} else {
				$extension->setSerializedDependencies($forcedDefaultDependencies);
			}

			if (!empty($extensionConfig['author_company'])) {
				$extension->setAuthorCompany($extensionConfig['author_company']);
			}
			if (!empty($extensionConfig['CGLcompliance'])) {
				$extension->setCglCompliance($extensionConfig['CGLcompliance']);
			}
			if (!empty($extensionConfig['author_company'])) {
				$extension->setCglComplianceNote($extensionConfig['CGLcompliance_note']);
			}
			if (!empty($extensionConfig['uploadFolder'])) {
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
				$extension->setCreateDirectories($extensionConfig['module']);
			}
			if (!empty($extensionConfig['modify_tables'])) {
				$extension->setModifiedTables($extensionConfig['modify_tables']);
			}
			if (!empty($extensionConfig['priority'])) {
				$extension->setPriority($extensionConfig['priority']);
			}
			if (!empty($extensionConfig['lockType'])) {
				$extension->setPriority($extensionConfig['lockType']);
			}
			if (!empty($extensionConfig['docPath'])) {
				$extension->setDocumentationPath($extensionConfig['docPath']);
			}

			$extensions[$extKey] = $extension;
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
