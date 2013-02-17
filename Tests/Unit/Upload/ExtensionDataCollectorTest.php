<?php
/*                                                                     *
 * This file is brought to you by Georg Großberger                     *
 * (c) 2013 by Georg Großberger <contact@grossberger-ge.org>           *
 *                                                                     *
 * It is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU General Public License, either version 3       *
 * of the License, or (at your option) any later version.              *
 *                                                                     */

namespace T3x\ExtensionUploader\Tests\Unit\Upload;
use TYPO3\CMS\Extbase\Tests\Unit\BaseTestCase;

/**
 * Test the data collector
 *
 * @package ExtensionBuilder
 * @author Georg Großberger <contact@grossberger-ge.org>
 * @copyright 2013 by Georg Großberger
 * @license GPL v3 http://www.gnu.org/licenses/gpl-3.0.txt
 */
class ExtensionDataCollectorTest extends BaseTestCase {

	public function testGetDataForExtension() {
		$collector = new \T3x\ExtensionUploader\Upload\ExtensionDataCollector();
		$extension = new \T3x\ExtensionUploader\Domain\Model\LocalExtension();
		$settings = array(
			'version' => '1.2.3',
			'uploadComment' => 'Load me',
			'state' => 'alpha'
		);
		$extension->setExtensionKey('dummy_extension');
		$extension->setTitle('Dummy Extension');
		$extension->setDescription('Does nothing');
		$extension->setCategory(2);
		$extension->setAuthorName('John Doe');
		$extension->setAuthorEmail('john.doe@example.tld');
		$extension->setAuthorCompany('Example Inc.');
		$extension->setSerializedDependencies(serialize(array(
			'depends' => array(
				'typo3' => '4.0.0-4.7.99'
			),
			'suggests' => array(
				'typo3' => '4.5.99-4.7.99'
			),
			'conflicts' => array(
				'dbal' => '0.0.1-9.99.999'
			)
		)));
		$extension->setLoadOrder('top');
		$extension->setModule('mod1');
		$extension->setClearCachesOnLoad(TRUE);

		$expected = array(
			'extensionKey' => utf8_encode($extension->getExtensionKey()),
			'version'      => utf8_encode($settings['version']),
			'metaData'     => array(
				'title'          => utf8_encode($extension->getTitle()),
				'description'   => utf8_encode($extension->getDescription()),
				'category'       => utf8_encode($extension->getCategory()),
				'state'          => utf8_encode($settings['state']),
				'authorName'    => utf8_encode($extension->getAuthorName()),
				'authorEmail'   => utf8_encode($extension->getAuthorEmail()),
				'authorCompany' => utf8_encode($extension->getAuthorCompany())
			),
			'technicalData' => array(
				'dependencies'     => array(
					array(
						'kind' => 'depends',
						'extensionKey' => 'typo3',
						'versionRange' => '4.0.0-4.7.99'
					),
					array(
						'kind' => 'suggests',
						'extensionKey' => 'typo3',
						'versionRange' => '4.5.99-4.7.99'
					),
					array(
						'kind' => 'conflicts',
						'extensionKey' => 'dbal',
						'versionRange' => '0.0.1-9.99.999'
					)
				),
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
		$actual = $collector->getDataForExtension($extension, $settings);
		$this->assertEquals($expected, $actual);
	}
}
