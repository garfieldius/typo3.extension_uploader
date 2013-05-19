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
use T3x\ExtensionUploader\Domain\Model\LocalExtension;

/**
 * Collection (meta) data about an extension used during the upload
 *
 * @package ExtensionUploader
 * @author Georg Großberger <contact@grossberger-ge.org>
 * @copyright 2013 by Georg Großberger
 * @license GPL v3 http://www.gnu.org/licenses/gpl-3.0.txt
 */
class ExtensionDataCollector {

	/**
	 * @param \T3x\ExtensionUploader\Domain\Model\LocalExtension $extension
	 * @param array $settings
	 * @return array
	 */
	public function getDataForExtension(LocalExtension $extension, array $settings) {
		return array(
			'extensionKey' => $extension->getExtensionKey(),
			'version'      => $settings['version'],
			'metaData'     => array(
				'title'         => $extension->getTitle(),
				'description'   => $extension->getDescription(),
				'category'      => strtolower($extension->getCategoryString()),
				'state'         => strtolower($settings['state']),
				'authorName'    => $extension->getAuthorName(),
				'authorEmail'   => $extension->getAuthorEmail(),
				'authorCompany' => $extension->getAuthorCompany()
			),
			'technicalData' => array(
				'dependencies'     => $this->getDependenciesArray($extension),
				'loadOrder'        => $extension->getLoadOrder(),
				'uploadFolder'     => $extension->getUploadFolder(),
				'createDirs'       => $extension->getCreateDirectories(),
				'shy'              => $extension->getShy(),
				'modules'          => $extension->getModule(),
				'modifyTables'     => $extension->getModifiedTables(),
				'priority'         => $extension->getPriority(),
				'clearCacheOnLoad' => $extension->getClearCachesOnLoad(),
				'lockType'         => $extension->getLockType(),
				'docPath'          => $extension->getDocumentationPath(),
				'doNotLoadInFE'    => FALSE
			),
			'infoData' => array(
				'codeLines'                       => 0,
				'codeBytes'                       => 0,
				'codingGuidelinesCompliance'      => $extension->getCglCompliance(),
				'codingGuidelinesComplianceNotes' => $extension->getCglComplianceNote(),
				'uploadComment'                   => $settings['uploadComment'],
				'techInfo'                        => array()
			)
		);
	}

	/**
	 * @param \T3x\ExtensionUploader\Domain\Model\LocalExtension $extension
	 * @return array
	 */
	protected function getDependenciesArray(LocalExtension $extension) {
		$dependencies = array();

		foreach (unserialize($extension->getSerializedDependencies()) as $dependencyType => $entries) {
			foreach ($entries as $extensionKey => $dependency) {
				if (!empty($dependency) && !empty($extensionKey) && is_string($extensionKey)) {
					$dependencies[] = array(
						'kind'         => $dependencyType,
						'extensionKey' => $extensionKey,
						'versionRange' => $dependency,
					);
				}
			}
		}
		return $dependencies;
	}
}
