<?php
/*                                                                     *
 * This file is brought to you by Georg Großberger                     *
 * (c) 2013 by Georg Großberger <contact@grossberger-ge.org>           *
 *                                                                     *
 * It is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU General Public License, either version 3       *
 * of the License, or (at your option) any later version.              *
 *                                                                     */

namespace T3x\ExtensionUploader\Domain\Model;
use TYPO3\CMS\Extensionmanager\Domain\Model\Extension;
use T3x\ExtensionUploader\UploaderException;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Model of a local extension
 *
 * @package ExtensionUploader
 * @author Georg Großberger <contact@grossberger-ge.org>
 * @copyright 2013 by Georg Großberger
 * @license GPL v3 http://www.gnu.org/licenses/gpl-3.0.txt
 */
class LocalExtension extends Extension {

	/**
	 * If a property is dirty
	 * Always return false, because we do not want to trigger a save operation
	 * because this local extension may or may not have a DB object, either way
	 * it is up to the extension manager to handle those.
	 *
	 * @param string|null $propertyName
	 * @return boolean
	 */
	public function _isDirty($propertyName = NULL) {
		return FALSE;
	}

	/**
	 * @var boolean
	 */
	protected $loaded = FALSE;

	/**
	 * @var boolean
	 */
	protected $knownToTer = FALSE;

	/**
	 * @var string
	 */
	protected $siteRelPath = '';

	/**
	 * @var string
	 */
	protected $authorCompany = '';

	/**
	 * @var string
	 */
	protected $cglCompliance = '';

	/**
	 * @var string
	 */
	protected $cglComplianceNote = '';

	/**
	 * @var string
	 */
	protected $loadOrder = '';

	/**
	 * @var boolean
	 */
	protected $uploadFolder = FALSE;

	/**
	 * @var string
	 */
	protected $createDirectories = '';

	/**
	 * @var boolean
	 */
	protected $shy = FALSE;

	/**
	 * @var string
	 */
	protected $module = '';

	/**
	 * @var string
	 */
	protected $modifiedTables = '';

	/**
	 * @var string
	 */
	protected $priority = '';
	/**
	 * @var boolean
	 */
	protected $clearCachesOnLoad = FALSE;

	/**
	 * @var string
	 */
	protected $lockType = '';

	/**
	 * @var string
	 */
	protected $documentationPath = '';

	/**
	 * Sets LockType
	 * @param string $lockType
	 * @return LocalExtension
	 */
	public function setLockType($lockType) {
		$this->lockType = $lockType;
		return $this;
	}

	/**
	 * Returns LockType
	 * @return string
	 */
	public function getLockType() {
		return $this->lockType;
	}

	/**
	 * @param string $categoryKey
	 * @return LocalExtension
	 * @throws \T3x\ExtensionUploader\UploaderException
	 */
	public function setCategoryByKey($categoryKey) {
		foreach (self::$defaultCategories as $index => $key) {
			if ($key === $categoryKey) {
				$this->setCategory($index);
				return $this;
			}
		}

		$message =
			'Invalid category key \'' .
			$categoryKey .
			'\' in extension ' .
			$this->getExtensionKey() .
			' must be one of: ' .
			implode(', ', self::$defaultCategories);

		throw new UploaderException($message, 1361548543);
	}

	/**
	 * Sets DocumentationPath
	 * @param string $documentationPath
	 * @return LocalExtension
	 */
	public function setDocumentationPath($documentationPath) {
		$this->documentationPath = $documentationPath;
		return $this;
	}

	/**
	 * Returns DocumentationPath
	 * @return string
	 */
	public function getDocumentationPath() {
		return $this->documentationPath;
	}

	/**
	 * Sets AuthorCompany
	 * @param string $authorCompany
	 * @return LocalExtension
	 */
	public function setAuthorCompany($authorCompany) {
		$this->authorCompany = $authorCompany;
		return $this;
	}

	/**
	 * Returns AuthorCompany
	 * @return string
	 */
	public function getAuthorCompany() {
		return $this->authorCompany;
	}

	/**
	 * Sets CglCompliance
	 * @param string $cglCompliance
	 * @return LocalExtension
	 */
	public function setCglCompliance($cglCompliance) {
		$this->cglCompliance = $cglCompliance;
		return $this;
	}

	/**
	 * Returns CglCompliance
	 * @return string
	 */
	public function getCglCompliance() {
		return $this->cglCompliance;
	}

	/**
	 * Sets CglComplianceNote
	 * @param string $cglComplianceNote
	 * @return LocalExtension
	 */
	public function setCglComplianceNote($cglComplianceNote) {
		$this->cglComplianceNote = $cglComplianceNote;
		return $this;
	}

	/**
	 * Returns CglComplianceNote
	 * @return string
	 */
	public function getCglComplianceNote() {
		return $this->cglComplianceNote;
	}

	/**
	 * Sets Loaded
	 *
	 * @param boolean $loaded
	 * @return LocalExtension
	 */
	public function setLoaded($loaded) {
		$this->loaded = $loaded;
		return $this;
	}

	/**
	 * Returns Loaded
	 * @return boolean
	 */
	public function getLoaded() {
		return $this->loaded;
	}

	/**
	 * Sets KnownToTer
	 * @param boolean $knownToTer
	 *
	 * @return LocalExtension
	 */
	public function setKnownToTer($knownToTer) {
		$this->knownToTer = $knownToTer;
		return $this;
	}

	/**
	 * Returns KnownToTer
	 * @return boolean
	 */
	public function getKnownToTer() {
		return $this->knownToTer;
	}

	/**
	 * @return string
	 */
	public function getStateKey() {
		if (isset(self::$defaultStates[$this->getState()])) {
			return self::$defaultStates[$this->getState()];
		} else {
			return self::$defaultStates[999];
		}
	}

	/**
	 * Sets SiteRelPath
	 * @param string $siteRelPath
	 * @return LocalExtension
	 */
	public function setSiteRelPath($siteRelPath) {
		$this->siteRelPath = $siteRelPath;
		return $this;
	}

	/**
	 * Returns SiteRelPath
	 * @return string
	 */
	public function getSiteRelPath() {
		return $this->siteRelPath;
	}

	/**
	 * Sets ClearCachesOnLoad
	 * @param boolean $clearCachesOnLoad
	 * @return LocalExtension
	 */
	public function setClearCachesOnLoad($clearCachesOnLoad) {
		$this->clearCachesOnLoad = $clearCachesOnLoad;
		return $this;
	}

	/**
	 * Returns ClearCachesOnLoad
	 * @return boolean
	 */
	public function getClearCachesOnLoad() {
		return $this->clearCachesOnLoad;
	}

	/**
	 * Sets CreateDirectories
	 * @param string $createDirectories
	 * @return LocalExtension
	 */
	public function setCreateDirectories($createDirectories) {
		$this->createDirectories = $createDirectories;
		return $this;
	}

	/**
	 * Returns CreateDirectories
	 * @return string
	 */
	public function getCreateDirectories() {
		return $this->createDirectories;
	}

	/**
	 * Sets LoadOrder
	 * @param string $loadOrder
	 * @return LocalExtension
	 */
	public function setLoadOrder($loadOrder) {
		$this->loadOrder = $loadOrder;
		return $this;
	}

	/**
	 * Returns LoadOrder
	 * @return string
	 */
	public function getLoadOrder() {
		return $this->loadOrder;
	}

	/**
	 * Sets ModifiedTables
	 * @param string $modifiedTables
	 * @return LocalExtension
	 */
	public function setModifiedTables($modifiedTables) {
		$this->modifiedTables = $modifiedTables;
		return $this;
	}

	/**
	 * Returns ModifiedTables
	 * @return string
	 */
	public function getModifiedTables() {
		return $this->modifiedTables;
	}

	/**
	 * Sets Module
	 * @param string $module
	 * @return LocalExtension
	 */
	public function setModule($module) {
		$this->module = $module;
		return $this;
	}

	/**
	 * Returns Module
	 * @return string
	 */
	public function getModule() {
		return $this->module;
	}

	/**
	 * Sets Priority
	 * @param string $priority
	 * @return LocalExtension
	 */
	public function setPriority($priority) {
		$this->priority = $priority;
		return $this;
	}

	/**
	 * Returns Priority
	 * @return string
	 */
	public function getPriority() {
		return $this->priority;
	}

	/**
	 * Sets Shy
	 * @param boolean $shy
	 * @return LocalExtension
	 */
	public function setShy($shy) {
		$this->shy = $shy;
		return $this;
	}

	/**
	 * Returns Shy
	 * @return boolean
	 */
	public function getShy() {
		return $this->shy;
	}

	/**
	 * Sets UploadFolder
	 * @param boolean $uploadFolder
	 * @return LocalExtension
	 */
	public function setUploadFolder($uploadFolder) {
		$this->uploadFolder = $uploadFolder;
		return $this;
	}

	/**
	 * Returns UploadFolder
	 * @return boolean
	 */
	public function getUploadFolder() {
		return $this->uploadFolder;
	}

}
