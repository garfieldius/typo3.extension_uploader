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
 * @package ExtensionBuilder
 * @author Georg Großberger <contact@grossberger-ge.org>
 * @copyright 2013 by Georg Großberger
 * @license GPL v3 http://www.gnu.org/licenses/gpl-3.0.txt
 */
class ExtensionDataCollector {

	/**
	 * @param \T3x\ExtensionUploader\Domain\Model\LocalExtension $extension
	 * @param $settings
	 * @return array
	 */
	public function getDataForExtension(LocalExtension $extension, $settings) {
		return array(
			'extensionKey' => utf8_encode($extension->getExtensionKey()),
			'version'      => utf8_encode($settings['version']),
			'metaData'     => array(
				'title'         => utf8_encode($extension->getTitle()),
				'description'   => utf8_encode($extension->getDescription()),
				'category'      => utf8_encode($extension->getCategory()),
				'state'         => utf8_encode($settings['state']),
				'authorName'    => utf8_encode($extension->getAuthorName()),
				'authorEmail'   => utf8_encode($extension->getAuthorEmail()),
				'authorCompany' => utf8_encode($extension->getAuthorCompany())
			),
			'technicalData' => array(
				'dependencies'     => $this->getDependenciesArray($extension),
				'loadOrder'        => utf8_encode($extension->getLoadOrder()),
				'uploadFolder'     => $extension->getUploadFolder(),
				'createDirs'       => utf8_encode($extension->getCreateDirectories()),
				'shy'              => $extension->getShy(),
				'modules'          => utf8_encode($extension->getModule()),
				'modifyTables'     => utf8_encode($extension->getModifiedTables()),
				'priority'         => utf8_encode($extension->getPriority()),
				'clearCacheOnLoad' => $extension->getClearCachesOnLoad(),
				'lockType'         => utf8_encode($extension->getLockType()),
				'docPath'          => utf8_encode($extension->getDocumentationPath()),
				'doNotLoadInFE'    => FALSE
			),
			'infoData' => array(
				'codeLines'                       => 0,
				'codeBytes'                       => 0,
				'codingGuidelinesCompliance'      => utf8_encode($extension->getCglCompliance()),
				'codingGuidelinesComplianceNotes' => utf8_encode($extension->getCglComplianceNote()),
				'uploadComment'                   => utf8_encode($settings['uploadComment']),
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
						'extensionKey' => utf8_encode($extensionKey),
						'versionRange' => utf8_encode($dependency),
					);
				}
			}
		}
		return $dependencies;
	}
}
