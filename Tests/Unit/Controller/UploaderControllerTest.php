<?php
/*                                                                     *
 * This file is brought to you by Georg Großberger                     *
 * (c) 2013 by Georg Großberger <contact@grossberger-ge.org>           *
 *                                                                     *
 * It is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU General Public License, either version 3       *
 * of the License, or (at your option) any later version.              *
 *                                                                     */

namespace T3x\ExtensionUploader\Tests\Unit\Controller;
use TYPO3\CMS\Extbase\Tests\Unit\BaseTestCase;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Test the controller
 *
 * @package ExtensionUploader
 * @author Georg Großberger <contact@grossberger-ge.org>
 * @copyright 2013 by Georg Großberger
 * @license GPL v3 http://www.gnu.org/licenses/gpl-3.0.txt
 */
class UploaderControllerTest extends BaseTestCase {

	/**
	 * @var \T3x\ExtensionUploader\Controller\UploaderController
	 */
	protected $controller;

	protected function setUp() {
		$this->objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance($this->buildAccessibleProxy(get_class($this->objectManager)));
		$this->controller = $this->objectManager->create($this->buildAccessibleProxy('T3x\ExtensionUploader\Controller\UploaderController'));
	}

	private function getRepositoryMock() {
		return $this->getMockBuilder('T3x\ExtensionUploader\Domain\Repository\LocalExtensionRepository')->disableOriginalConstructor()->getMock();
	}

	public function testListAction() {

		$dummyCollection = array(
			'extA' => '123',
			'extB' => '456'
		);

		$view = $this->getMock('TYPO3\CMS\Fluid\View\TemplateView', array('__construct', 'assign'));
		$view->expects($this->once())->method('assign')->with('extensions', $dummyCollection);

		$repository = $this->getRepositoryMock();
		$repository->expects($this->once())->method('findAll')->will($this->returnValue($dummyCollection));
		$repository->expects($this->once())->method('setSilenceExceptions')->will($this->returnValue($repository));

		$this->controller->_set('extensions', $repository);
		$this->controller->_set('view', $view);

		$this->controller->listAction();
	}

	public function testSettingsAction() {

		$statesDummies = array(
			0 => 'alpha',
			1 => 'beta',
			2 => 'stable',
			3 => 'experimental',
			4 => 'test',
			5 => 'obsolete',
			6 => 'excludeFromUpdates'
		);
		$settings = array(
			'version' => '1.2.3',
			'state' => 'alpha'
		);
		$testRepos = array(
			new \TYPO3\CMS\Extensionmanager\Domain\Model\Repository($this->objectManager)
		);

		$extension = new \T3x\ExtensionUploader\Domain\Model\LocalExtension();
		$extension->setExtensionKey('extension_uploader');

		$extensionRepository = $this->getRepositoryMock();
		$extensionRepository->expects($this->once())->method('findOneByExtensionKey')->will($this->returnValue($extension));

		$repositories = $this->getMockBuilder('TYPO3\CMS\Extensionmanager\Domain\Repository\RepositoryRepository')->disableOriginalConstructor()->getMock();
		$repositories->expects($this->once())->method('findAll')->will($this->returnValue($testRepos));

		$utility = $this->getMock('T3x\ExtensionUploader\Utility\StatesUtility');
		$utility->expects($this->once())->method('getStates')->will($this->returnValue($statesDummies));

		$view = $this->getMock('TYPO3\CMS\Fluid\View\TemplateView', array('__construct', 'assignMultiple'));
		$view->expects($this->once())->method('assignMultiple')->with(array(
			'extension' => $extension,
			'states' => $statesDummies,
			'repositories' => $testRepos,
			'settings' => $settings
		));

		$this->controller->injectExtensions($extensionRepository);
		$this->controller->injectStatesUtility($utility);
		$this->controller->injectRepositories($repositories);
		$this->controller->_set('view', $view);
		$this->controller->settingsAction('extension_uploader', $settings);
	}

	public function testSuccessfulUploadAction() {
		$extension = new \T3x\ExtensionUploader\Domain\Model\LocalExtension();
		$extension->setExtensionKey('extension_uploader');
		$extension->_setClone(TRUE);

		$settings = array(
			'version' => '1.2.3',
			'state' => 'alpha'
		);
		$testRepo = new \TYPO3\CMS\Extensionmanager\Domain\Model\Repository($this->objectManager);
		$testRepo->_setClone(TRUE);

		$repository = $this->getRepositoryMock();
		$repository->expects($this->once())->method('findOneByExtensionKey')->with('extension_uploader')->will($this->returnValue($extension));

		$message = LocalizationUtility::translate('upload.success', 'extension_uploader', array('extension_uploader', '1.2.3'));
		$flashMessages = $this->getMock('TYPO3\CMS\Extbase\Mvc\Controller\FlashMessageContainer');
		$flashMessages->expects($this->once())->method('add')->with($message);

		$uploader = $this->getMock('T3x\ExtensionUploader\Upload\Uploader');
		$uploader->expects($this->once())->method('setExtension')->with($extension);
		$uploader->expects($this->once())->method('setSettings')->with($settings);
		$uploader->expects($this->once())->method('setRepository')->with($testRepo);
		$uploader->expects($this->once())->method('validate');
		$uploader->expects($this->once())->method('upload');
		$uploader->expects($this->once())->method('getReleasedVersion')->will($this->returnValue($settings['version']));

		$controller = $this->getMock('T3x\ExtensionUploader\Controller\UploaderController', array('redirect'));
		$controller->expects($this->once())->method('redirect')->with('list');

		$controller->injectUploader($uploader);
		$controller->injectExtensions($repository);
		$controller->injectFlashMessageContainer($flashMessages);
		$controller->uploadAction('extension_uploader', $settings, $testRepo);
	}

	public function testFailedUploadAction() {
		$extension = new \T3x\ExtensionUploader\Domain\Model\LocalExtension();
		$extension->setExtensionKey('extension_uploader');
		$extension->_setClone(TRUE);

		$exception = new \T3x\ExtensionUploader\UploaderException('Something went wrong', 1);

		$settings = array(
			'version' => '1.2.3',
			'state' => 'alpha'
		);
		$testRepo = new \TYPO3\CMS\Extensionmanager\Domain\Model\Repository($this->objectManager);
		$testRepo->_setClone(TRUE);

		$uploader = $this->getMock('T3x\ExtensionUploader\Upload\Uploader');
		$uploader->expects($this->once())->method('setExtension')->with($extension);
		$uploader->expects($this->once())->method('setSettings')->with($settings);
		$uploader->expects($this->once())->method('setRepository')->with($testRepo);
		$uploader->expects($this->once())->method('validate')->will($this->throwException($exception));

		$repository = $this->getRepositoryMock();
		$repository->expects($this->once())->method('findOneByExtensionKey')->with('extension_uploader')->will($this->returnValue($extension));

		$controller = $this->getMock('T3x\ExtensionUploader\Controller\UploaderController', array('redirect'));
		$controller->expects($this->once())->method('redirect')->with('settings', NULL, NULL, array('extensionKey' => 'extension_uploader', 'settings' => $settings));

		$flashMessages = $this->getMock('TYPO3\CMS\Extbase\Mvc\Controller\FlashMessageContainer');
		$flashMessages->expects($this->once())->method('add')->with(LocalizationUtility::translate('exception.1', 'extension_uploader'), '', \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);

		$controller->injectUploader($uploader);
		$controller->injectExtensions($repository);
		$controller->injectFlashMessageContainer($flashMessages);
		$controller->uploadAction('extension_uploader', $settings, $testRepo);
	}
}
