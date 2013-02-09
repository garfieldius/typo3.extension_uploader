<?php
/*                                                                     *
 * This file is brought to you by Georg Großberger                     *
 * (c) 2013 by Georg Großberger <contact@grossberger-ge.org>           *
 *                                                                     *
 * It is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU General Public License, either version 3       *
 * of the License, or (at your option) any later version.              *
 *                                                                     */

namespace T3x\ExtensionUploader\ViewHelpers;
use TYPO3\CMS\Fluid\ViewHelpers\Be\ContainerViewHelper;

/**
 * Customized container view helper
 *
 * @package ExtensionUploader
 * @author Georg Großberger <contact@grossberger-ge.org>
 * @copyright 2013 by Georg Großberger
 * @license GPL v3 http://www.gnu.org/licenses/gpl-3.0.txt
 */
class BackendContainerViewHelper extends ContainerViewHelper {

	public function initializeArguments() {
		$this->registerArgument('cssFiles', 'array', 'Additional CSS files', FALSE, array());
		$this->registerArgument('jsFiles', 'array', 'Additional JS files', FALSE, array());
	}

	/**
	 * Render the container
	 *
	 * @param string $pageTitle
	 * @param boolean $enableJumpToUrl
	 * @param boolean $enableClickMenu
	 * @param boolean $loadPrototype
	 * @param boolean $loadScriptaculous
	 * @param string $scriptaculousModule
	 * @param boolean $loadExtJs
	 * @param boolean $loadExtJsTheme
	 * @param string $extJsAdapter
	 * @param boolean $enableExtJsDebug
	 * @param null $addCssFile
	 * @param null $addJsFile
	 * @return string
	 */
	public function render($pageTitle = '', $enableJumpToUrl = TRUE, $enableClickMenu = TRUE, $loadPrototype = TRUE, $loadScriptaculous = FALSE, $scriptaculousModule = '', $loadExtJs = FALSE, $loadExtJsTheme = TRUE, $extJsAdapter = '', $enableExtJsDebug = FALSE, $addCssFile = NULL, $addJsFile = NULL) {

		$pageRenderer = $this->getDocInstance()->getPageRenderer();

		if (!empty($this->arguments['cssFiles']) && is_array($this->arguments['cssFiles'])) {
			foreach ($this->arguments['cssFiles'] as $css) {
				$pageRenderer->addCssFile($css);
			}
		}

		if (!empty($this->arguments['jsFiles']) && is_array($this->arguments['jsFiles'])) {
			foreach ($this->arguments['jsFiles'] as $js) {
				$pageRenderer->addJsFile($js);
			}
		}

		return parent::render($pageTitle, $enableJumpToUrl, $enableClickMenu, $loadPrototype, $loadScriptaculous, $scriptaculousModule, $loadExtJs, $loadExtJsTheme, $extJsAdapter, $enableExtJsDebug, '', '');
	}

}
