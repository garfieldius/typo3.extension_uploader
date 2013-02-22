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
	 *
	 * @param string $extensionPath
	 * @return \RecursiveIteratorIterator
	 */
	protected function collectAllFilesInDirectory($extensionPath) {
		return GeneralUtility::getAllFilesAndFoldersInPath(array(), $extensionPath, '', TRUE);
	}

	/**
	 * Collect all files from an extension folder
	 *
	 * @param $extensionKey string
	 * @return array
	 * @throws NoFileAccessException
	 */
	public function collectFilesOfExtension($extensionKey) {
		$uploadFilesList = array();
		$path         = PATH_site . 'typo3conf/ext/' . $extensionKey . '/';
		$absolutePrefixLength = strlen($path);

		foreach ($this->collectAllFilesInDirectory($path) as $file) {
			$file = (string) $file;

			if (is_dir($file)) {
				continue;
			}

			$include = TRUE;
			foreach ($this->filters as $filter) {
				/* @var $filter FileFilterInterface */
				$include = (!$filter->excludeFile($file) && $include);
			}

			if ($include) {
				$content = file_get_contents($file);
				if (!is_string($content)) {
					throw new NoFileAccessException('Cannot read file ' . $file, 1360446565);
				}
				$relativeFile = substr($file, $absolutePrefixLength);
				$id           = md5($content);
				$uploadFilesList[ utf8_encode($relativeFile) ] = array(
					'name'             => utf8_encode($relativeFile),
					'size'             => strlen($content),
					'modificationTime' => (integer) filemtime($file),
					'isExecutable'     => (integer) is_executable($file),
					'content'          => $content,
					'contentMD5'       => $id,
					'content_md5'      => $id
				);
			}
		}
		return $uploadFilesList;
	}
}
