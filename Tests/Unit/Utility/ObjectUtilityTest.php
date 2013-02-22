<?php
/*                                                                     *
 * This file is brought to you by Georg Großberger                     *
 * (c) 2013 by Georg Großberger <contact@grossberger-ge.org>           *
 *                                                                     *
 * It is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU General Public License, either version 3       *
 * of the License, or (at your option) any later version.              *
 *                                                                     */

namespace T3x\ExtensionUploader\Tests\Unit\Utility;
use TYPO3\CMS\Extbase\Tests\Unit\BaseTestCase;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Test the object utility
 *
 * @package ExtensionUploader
 * @author Georg Großberger <contact@grossberger-ge.org>
 * @copyright 2013 by Georg Großberger
 * @license GPL v3 http://www.gnu.org/licenses/gpl-3.0.txt
 */
class ObjectUtilityTest extends BaseTestCase {

	/**
	 * @var \T3x\ExtensionUploader\Utility\ObjectUtility
	 */
	protected $util;

	protected function setUp() {
		$this->util = $this->objectManager->get($this->buildAccessibleProxy('T3x\ExtensionUploader\Utility\ObjectUtility'));
	}

	public function testGetFilesCollector() {

		$builtInFilters = array(
			'SystemMetaData',
			'ExtensionBuilder',
			'ExtensionManagerMetaData',
			'VcsMetaData'
		);

		$filesCollector = $this->getMock('T3x\ExtensionUploader\Upload\FilesCollector');
		$filesCollector->expects($this->exactly(count($builtInFilters)))->method('addFilesFilter');

		$signalSlotsDispatcher = $this->getMock('TYPO3\CMS\Extbase\SignalSlot\Dispatcher');
		$signalSlotsDispatcher->expects($this->once())->method('dispatch')->with('ExtensionUploader\Utility\ObjectUtility', 'createFilesCollector', array(&$builtInFilters));

		$objectManager = $this->getMock('TYPO3\CMS\Extbase\Object\ObjectManager');
		$objectManager->expects($this->at(0))->method('get')->with('T3x\ExtensionUploader\Upload\FilesCollector')->will($this->returnValue($filesCollector));

		foreach ($builtInFilters as $i => $filterName) {
			$filterClass = 'T3x\ExtensionUploader\FileFilter\\' . $filterName . 'Filter';
			$objectManager
				->expects($this->at($i+1))
				->method('get')->with($filterClass)
				->will($this->returnValue(\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance($filterClass)));
		}

		$this->util->injectSignals($signalSlotsDispatcher);
		$this->util->injectObjectManager($objectManager);
		$newFilesCollector = $this->util->getFilesCollector();

		$this->assertTrue($filesCollector === $newFilesCollector);
		$this->assertTrue(spl_object_hash($filesCollector) === spl_object_hash($newFilesCollector));
	}

	/**
	 * @expectedException \T3x\ExtensionUploader\Utility\InvalidObjectException
	 */
	public function testCreatingAFilesCollectorWithANotExistingClassThrowsAnException() {

		$connector = new \TYPO3\CMS\Extbase\SignalSlot\Dispatcher();
		$connector->connect(
			'ExtensionUploader\Utility\ObjectUtility', 'createFilesCollector',
			$this, 'addNonExistingFilterClass'
		);

		$this->util->injectSignals($connector);
		$this->util->getFilesCollector();
	}

	public function addNonExistingFilterClass(&$filterNames) {
		$filterNames[] = 'SomeClass\NotExisting' . uniqid() . 'InThisRealm';
	}

	/**
	 * @expectedException \T3x\ExtensionUploader\Utility\InvalidObjectException
	 */
	public function testCreatingAFilesCollectorWithAFilterNotImplementingTheFilterinterfaceThrowsAnException() {

		$connector = new \TYPO3\CMS\Extbase\SignalSlot\Dispatcher();
		$connector->connect(
			'ExtensionUploader\Utility\ObjectUtility', 'createFilesCollector',
			$this, 'addFileFilterNotImplementingTheRequiredInterface'
		);

		$this->util->injectSignals($connector);
		$this->util->getFilesCollector();
	}

	public function addFileFilterNotImplementingTheRequiredInterface(&$filterNames) {
		$namespace = 'SomeNotExistingNamespace';
		$className = 'NotExisting' . uniqid() . 'InThisRealm';

		eval('
		namespace ' . $namespace . ';

		class ' . $className . ' { }'

		);

		$filterNames[] = $namespace . '\\' . $className;
	}

	public function testCreateAConnection() {
		$wsdlUrl  = 'http://typo3.org/some.wsdl';
		$username = 'anonymous';
		$password = 'verySecurePassword';
		$client   = $this->getMock('SoapClient', array(), array(), '', FALSE);

		$repository = new \TYPO3\CMS\Extensionmanager\Domain\Model\Repository();
		$repository->setWsdlUrl($wsdlUrl);

		$connection = $this->getMock('T3x\ExtensionUploader\Upload\Connection');
		$connection->expects($this->once())->method('setWsdlUrl')->with($wsdlUrl);
		$connection->expects($this->once())->method('setUsername')->with($username);
		$connection->expects($this->once())->method('setPassword')->with($password);
		$connection->expects($this->once())->method('connectClient')->will($this->returnValue($client));
		$connection->expects($this->once())->method('setClient')->with($client);

		$objectManager = $this->getMock(get_class($this->objectManager));
		$objectManager->expects($this->once())->method('get')->with('T3x\ExtensionUploader\Upload\Connection')->will($this->returnValue($connection));

		$signal = $this->getMock('TYPO3\CMS\Extbase\SignalSlot\Dispatcher');
		$signal->expects($this->once())->method('dispatch')->with('ExtensionUploader\Utility\ObjectUtility', 'createConnection', array($connection, $repository, $username, $password));

		$this->util->injectSignals($signal);
		$this->util->injectObjectManager($objectManager);
		$firstConnection = $this->util->getSoapConnectionForRepository($repository, $username, $password);

		// Second call for the same repository must return the same instance
		// So the "once" expectations must not be violated as well
		$secondConnection = $this->util->getSoapConnectionForRepository($repository, $username, $password);
		$this->assertTrue($firstConnection === $secondConnection);
	}
}
