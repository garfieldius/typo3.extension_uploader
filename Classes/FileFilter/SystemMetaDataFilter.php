<?php
/*                                                                     *
 * This file is brought to you by Georg Großberger                     *
 * (c) 2013 by Georg Großberger <contact@grossberger-ge.org>           *
 *                                                                     *
 * It is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU General Public License, either version 3       *
 * of the License, or (at your option) any later version.              *
 *                                                                     */

namespace T3x\ExtensionUploader\FileFilter;

/**
 * Checks if the file contains meta data of the OS
 *
 * @package packagename
 * @author Georg Großberger <contact@grossberger-ge.org>
 * @copyright 2013 by Georg Großberger
 * @license GPL v3 http://www.gnu.org/licenses/gpl-3.0.txt
 */
class SystemMetaDataFilter implements FileFilterInterface {

	protected $excludedNames = array(
		'.DS_Store',
		'.DS_Store?',
		'.Spotlight-V100',
		'.Trashes',
		'Icon?',
		'ehthumbs.db',
		'Thumbs.db'
	);

	/**
	 * Gives the absolute path as parameter and expects the returning
	 * boolean to be true if the file should be skiped when packaging
	 * the extension
	 * @param $file string
	 * @return boolean
	 */
	public function excludeFile($file) {
		$parts = explode('/', $file);
		foreach ($parts as $filePathPart) {
			if (substr($filePathPart, 0, 2) === '._') {
				return TRUE;
			}
			if (in_array($filePathPart, $this->excludedNames, TRUE)) {
				return TRUE;
			}
		}
		return FALSE;
	}

}
