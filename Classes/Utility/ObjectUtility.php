<?php
/*                                                                     *
 * This file is brought to you by Georg Großberger                     *
 * (c) 2013 by Georg Großberger <contact@grossberger-ge.org>           *
 *                                                                     *
 * It is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU General Public License, either version 3       *
 * of the License, or (at your option) any later version.              *
 *                                                                     */

namespace T3x\ExtensionUploader\Utility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extensionmanager\Domain\Model\Repository;
use TYPO3\CMS\Core\SingletonInterface;
use T3x\ExtensionUploader\FileFilter\FileFilterInterface;

/**
 * Contains helpers for objects of this extension
 *
 * @package ExtensionBuilder
 * @author Georg Großberger <contact@grossberger-ge.org>
 * @copyright 2013 by Georg Großberger
 * @license GPL v3 http://www.gnu.org/licenses/gpl-3.0.txt
 */
class ObjectUtility implements SingletonInterface {

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * @var \T3x\ExtensionUploader\Upload\FilesCollector
	 */
	protected $filesCollector;

	/**
	 * @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher
	 */
	protected $signals;

	/**
	 * @var array
	 */
	protected $connections = array();

	/**
	 * @param \TYPO3\CMS\Extbase\Object\ObjectManagerInterface $objectManager
	 */
	public function injectObjectManager(\TYPO3\CMS\Extbase\Object\ObjectManagerInterface $objectManager) {
		$this->objectManager = $objectManager;
	}

	/**
	 * @param \TYPO3\CMS\Extbase\SignalSlot\Dispatcher $signals
	 */
	public function injectSignals(\TYPO3\CMS\Extbase\SignalSlot\Dispatcher $signals) {
		$this->signals = $signals;
	}

	/**
	 * @return \T3x\ExtensionUploader\Upload\FilesCollector
	 * @throws InvalidObjectException
	 */
	public function getFilesCollector() {
		if (!$this->filesCollector) {

			$this->filesCollector = $this->objectManager->get('T3x\ExtensionUploader\Upload\FilesCollector');
			$filterNames = array(
				'SystemMetaData',
				'ExtensionBuilder',
				'ExtensionManagerMetaData',
				'VcsMetaData'
			);

			$this->signals->dispatch('ExtensionUploader\Utility\ObjectUtility', 'createFilesCollector', array(&$filterNames));

			foreach ($filterNames as $className) {

				// Make the instance
				if (strpos($className, '\\') === FALSE) {
					$className = 'T3x\ExtensionUploader\FileFilter\\' . $className . 'Filter';
				}

				if (!class_exists($className)) {
					throw new InvalidObjectException('Unknown class ' . $className);
				}

				$filter = GeneralUtility::makeInstance($className);

				// Check if object has a filter interface
				if ($filter instanceof FileFilterInterface) {
					$this->filesCollector->addFilesFilter($filter);
				} else {
					throw new InvalidObjectException("Instance of $className does not implement the FileFilterInterface");
				}
			}

			// Signal for adding custom filters
		}
		return $this->filesCollector;
	}

	/**
	 * @param \TYPO3\CMS\Extensionmanager\Domain\Model\Repository $repository
	 * @param string $username
	 * @param string $password
	 * @return \T3x\ExtensionUploader\Upload\Connection
	 */
	public function getSoapConnectionForRepository(Repository $repository, $username, $password) {
		$wsdl = $repository->getWsdlUrl();
		if (!isset($this->connections[$wsdl])) {
			$connection = $this->objectManager->create('T3x\ExtensionUploader\Upload\Connection');
			$connection->setWsdlUrl($repository->getWsdlUrl());
			$connection->setUsername($username);
			$connection->setPassword($password);
			$connection->setClient($connection->connectClient());
			$this->signals->dispatch('ExtensionUploader\Utility\ObjectUtility', 'createConnection', array($connection, $repository, $username, $password));
			$this->connections[$wsdl] = $connection;
		}
		return $this->connections[$wsdl];
	}
}
