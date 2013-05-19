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
use T3x\ExtensionUploader\Domain\Model\LocalExtension;
use T3x\ExtensionUploader\Upload\ExtensionDataCollector;

/**
 * Test the data collector
 *
 * @package ExtensionUploader
 * @author Georg Großberger <contact@grossberger-ge.org>
 * @copyright 2013 by Georg Großberger
 * @license GPL v3 http://www.gnu.org/licenses/gpl-3.0.txt
 */
class ExtensionDataCollectorTest extends BaseTestCase {

	public function testGetDataForExtension() {
		$collector = new ExtensionDataCollector();
		$extension = new LocalExtension();
		$settings = array(
			'version' => '1.2.3',
			'uploadComment' => 'Load me',
			'state' => 'Alpha'
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
			'extensionKey' => 'dummy_extension',
			'version'      => '1.2.3',
			'metaData'     => array(
				'title'         => 'Dummy Extension',
				'description'   => 'Does nothing',
				'category'      => 'fe',
				'state'         => 'alpha',
				'authorName'    => 'John Doe',
				'authorEmail'   => 'john.doe@example.tld',
				'authorCompany' => 'Example Inc.'
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
				'loadOrder'        => 'top',
				'uploadFolder'     => FALSE,
				'createDirs'       => '',
				'shy'              => FALSE,
				'modules'          => 'mod1',
				'modifyTables'     => '',
				'priority'         => '',
				'clearCacheOnLoad' => TRUE,
				'lockType'         => '',
				'docPath'          => '',
				'doNotLoadInFE'    => FALSE
			),
			'infoData' => array(
				'codeLines'                       => 0,
				'codeBytes'                       => 0,
				'codingGuidelinesCompliance'      => '',
				'codingGuidelinesComplianceNotes' => '',
				'uploadComment'                   => $settings['uploadComment'],
				'techInfo'                        => array()
			)
		);
		$actual = $collector->getDataForExtension($extension, $settings);
		$this->assertEquals($expected, $actual);
	}
}
