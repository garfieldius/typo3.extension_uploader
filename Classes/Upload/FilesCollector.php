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
use TYPO3\CMS\Core\Utility\GeneralUtility;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use T3x\ExtensionUploader\FileFilter\FileFilterInterface;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Utility for the file operations during the upload
 *
 * @package ExtensionUploader
 * @author Georg Großberger <contact@grossberger-ge.org>
 * @copyright 2013 by Georg Großberger
 * @license GPL v3 http://www.gnu.org/licenses/gpl-3.0.txt
 */
class FilesCollector implements SingletonInterface {

	/**
	 * @var array
	 */
	protected $filters = array();

	/**
	 * @var array
	 */
	protected $excludedFiles = array();

	/**
	 * @param \T3x\ExtensionUploader\FileFilter\FileFilterInterface $filterObject
	 * @return FilesCollector
	 */
	public function addFilesFilter(FileFilterInterface $filterObject) {
		$this->filters[] = $filterObject;
		return $this;
	}

	/**
	 * Get all files in a directory
	 * @TODO: Use some TYPO3 API??, might be superfluous
	 *
	 * @param string $extensionPath
	 * @return \RecursiveIteratorIterator
	 */
	protected function collectAllFilesInDirectory($extensionPath) {
		return new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator(
				$extensionPath,
				RecursiveDirectoryIterator::FOLLOW_SYMLINKS | RecursiveDirectoryIterator::UNIX_PATHS | RecursiveDirectoryIterator::SKIP_DOTS
			),
			RecursiveIteratorIterator::CHILD_FIRST,
			RecursiveIteratorIterator::LEAVES_ONLY
		);
	}

	/**
	 * Collect all files from an extension folder
	 *
	 * @param $extensionKey string
	 * @return array
	 * @throws NoFileAccessException
	 */
	public function collectFilesOfExtension($extensionKey) {
		$md5FilesList = array();
		$filesList    = $this->collectAllFilesInDirectory(ExtensionManagementUtility::extPath($extensionKey));
		$absolutePrefixLength = strlen(PATH_site);

		foreach ($filesList as $file) {
			$file = (string) $file;

			$fh = fopen($file, 'r+');
			if (!is_resource($fh)) {
				throw new NoFileAccessException('Cannot read file ' . $file, 1360446565);
			}
			fclose($fh);

			$include = TRUE;
			foreach ($this->filters as $filter) {
				/* @var $filter FileFilterInterface */
				$include = (!$filter->excludeFile($file) && $include);
			}

			if ($include) {
				$md5FilesList[ md5_file($file) ] = substr($file, $absolutePrefixLength);
			} else {
				$this->excludedFiles[] = $file;
			}
		}
		return $md5FilesList;
	}

	/**
	 * Returns excluded files
	 *
	 * @return array
	 */
	public function getExcludedFiles() {
		return $this->excludedFiles;
	}

}
