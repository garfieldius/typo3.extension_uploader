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
	 *
	 * @param string|null $propertyName
	 * @return boolean
	 */
	public function _isDirty($propertyName = NULL) {
		return FALSE;
	}

	/**
	 * Error message
	 *
	 * @var string
	 */
	protected $error = '';

	/**
	 * @var boolean
	 */
	protected $loaded;

	/**
	 * @var boolean
	 */
	protected $knownToTer;

	/**
	 * @var string
	 */
	protected $siteRelPath;

	/**
	 * Sets Error
	 * @param string $error
	 * @return LocalExtension
	 */
	public function setError($error) {
		$this->error = $error;
		return $this;
	}

	/**
	 * Returns Error
	 *
	 * @return string
	 */
	public function getError() {
		return $this->error;
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
		return $this->getDefaultState($this->getState());
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
}
