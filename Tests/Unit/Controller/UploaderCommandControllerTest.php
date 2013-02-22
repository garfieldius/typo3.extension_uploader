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
class UploaderCommandControllerTest extends BaseTestCase {

	/**
	 * @var \T3x\ExtensionUploader\Controller\UploaderCommandController
	 */
	protected $controller;

	protected function setUp() {
		$this->controller = $this->objectManager->get($this->buildAccessibleProxy('T3x\ExtensionUploader\Controller\UploaderCommandController'));
	}

	public function testUploadAction() {
		$username = 'myuser';
		$password = 'verySecurePassword';
		$state    = 'beta';
		$stateId  = 1;
		$release  = 'minor';
		$extKey   = 'dummy_extension';
		$comment  = 'Testing the upload command controller';

		$extension = new \T3x\ExtensionUploader\Domain\Model\LocalExtension();
		$extension->setExtensionKey($extKey);
		$extension->_setClone(TRUE);

		$repository = new \TYPO3\CMS\Extensionmanager\Domain\Model\Repository();
		$repository->_setClone(TRUE);

		$repositories = $this->getMock('TYPO3\CMS\Extensionmanager\Domain\Repository\RepositoryRepository');
		$repositories
			->expects($this->once())
			->method('findOneTypo3OrgRepository')
			->will($this->returnValue($repository));

		$extensions = $this->getMock('T3x\ExtensionUploader\Domain\Repository\LocalExtensionRepository');
		$extensions
			->expects($this->once())
			->method('findOneByExtensionKey')
			->with($extKey)
			->will($this->returnValue($extension));

		$states = $this->getMock('T3x\ExtensionUploader\Utility\StatesUtility');
		$states
			->expects($this->once())
			->method('getStateIdForKey')
			->with($state)
			->will($this->returnValue($stateId));

		$uploader = $this->getMock('T3x\ExtensionUploader\Upload\Uploader');
		$uploader
			->expects($this->once())
			->method('setExtension')
			->with($extension);
		$uploader
			->expects($this->once())
			->method('setRepository')
			->with($repository);
		$uploader
			->expects($this->once())
			->method('setSettings')
			->with(array(
				'state'         => $stateId,
				'version'       => '',
				'release'       => $release,
				'username'      => $username,
				'password'      => $password,
				'uploadComment' => $comment
			));
		$uploader
			->expects($this->once())
			->method('validate');
		$uploader
			->expects($this->once())
			->method('upload');

		$response = $this->getMock('TYPO3\CMS\Extbase\Mvc\Cli\Response');
		$response->expects($this->once())->method('appendContent')->withAnyParameters();
		$this->controller->_set('response', $response);

		$this->controller->injectExtensions($extensions);
		$this->controller->injectStatesUtility($states);
		$this->controller->injectUploader($uploader);
		$this->controller->injectRepositories($repositories);
		$this->controller->uploadCommand($extKey, $username, $password, $state, $release, '', $comment);
	}

	public function testUploadSetsReleaseToCustomIfAVersionWasSet() {

		$username = 'myuser';
		$password = 'verySecurePassword';
		$state    = 'beta';
		$stateId  = 1;
		$release  = 'minor';
		$extKey   = 'dummy_extension';
		$comment  = 'Testing the upload command controller';
		$version  = '1.2.3';

		$extension = new \T3x\ExtensionUploader\Domain\Model\LocalExtension();
		$extension->setExtensionKey($extKey);
		$extension->_setClone(TRUE);

		$repository = new \TYPO3\CMS\Extensionmanager\Domain\Model\Repository();
		$repository->_setClone(TRUE);

		$repositories = $this->getMock('TYPO3\CMS\Extensionmanager\Domain\Repository\RepositoryRepository');
		$repositories
			->expects($this->once())
			->method('findOneTypo3OrgRepository')
			->will($this->returnValue($repository));

		$extensions = $this->getMock('T3x\ExtensionUploader\Domain\Repository\LocalExtensionRepository');
		$extensions
			->expects($this->once())
			->method('findOneByExtensionKey')
			->with($extKey)
			->will($this->returnValue($extension));

		$states = $this->getMock('T3x\ExtensionUploader\Utility\StatesUtility');
		$states
			->expects($this->once())
			->method('getStateIdForKey')
			->with($state)
			->will($this->returnValue($stateId));

		$uploader = $this->getMock('T3x\ExtensionUploader\Upload\Uploader');
		$uploader
			->expects($this->once())
			->method('setExtension')
			->with($extension);
		$uploader
			->expects($this->once())
			->method('setRepository')
			->with($repository);
		$uploader
			->expects($this->once())
			->method('setSettings')
			->with(array(
			'state'         => $stateId,
			'version'       => $version,
			'release'       => 'custom',
			'username'      => $username,
			'password'      => $password,
			'uploadComment' => $comment
		));
		$uploader
			->expects($this->once())
			->method('validate');
		$uploader
			->expects($this->once())
			->method('upload');

		$response = $this->getMock('TYPO3\CMS\Extbase\Mvc\Cli\Response');
		$response->expects($this->once())->method('appendContent')->withAnyParameters();
		$this->controller->_set('response', $response);

		$this->controller->injectExtensions($extensions);
		$this->controller->injectStatesUtility($states);
		$this->controller->injectUploader($uploader);
		$this->controller->injectRepositories($repositories);
		$this->controller->uploadCommand($extKey, $username, $password, $state, $release, $version, $comment);
	}

	public function testUploadUsesCurrentStateIfNoneGiven() {

		$username = 'myuser';
		$password = 'verySecurePassword';
		$state    = '';
		$stateId  = 1;
		$release  = 'minor';
		$extKey   = 'dummy_extension';
		$comment  = 'Testing the upload command controller';
		$version  = '1.2.3';

		$extension = $this->getMock('T3x\ExtensionUploader\Domain\Model\LocalExtension', array('getState'));
		$extension
			->expects($this->once())
			->method('getState')
			->will($this->returnValue($stateId));
		$extension->_setClone(TRUE);

		$repository = new \TYPO3\CMS\Extensionmanager\Domain\Model\Repository();
		$repository->_setClone(TRUE);

		$repositories = $this->getMock('TYPO3\CMS\Extensionmanager\Domain\Repository\RepositoryRepository');
		$repositories
			->expects($this->once())
			->method('findOneTypo3OrgRepository')
			->will($this->returnValue($repository));

		$extensions = $this->getMock('T3x\ExtensionUploader\Domain\Repository\LocalExtensionRepository');
		$extensions
			->expects($this->once())
			->method('findOneByExtensionKey')
			->with($extKey)
			->will($this->returnValue($extension));

		$states = $this->getMock('T3x\ExtensionUploader\Utility\StatesUtility');
		$states
			->expects($this->never())
			->method('getStateIdForKey');

		$uploader = $this->getMock('T3x\ExtensionUploader\Upload\Uploader');
		$uploader
			->expects($this->once())
			->method('setExtension')
			->with($extension);
		$uploader
			->expects($this->once())
			->method('setRepository')
			->with($repository);
		$uploader
			->expects($this->once())
			->method('setSettings')
			->with(array(
			'state'         => $stateId,
			'version'       => $version,
			'release'       => 'custom',
			'username'      => $username,
			'password'      => $password,
			'uploadComment' => $comment
		));
		$uploader
			->expects($this->once())
			->method('validate');
		$uploader
			->expects($this->once())
			->method('upload');

		$response = $this->getMock('TYPO3\CMS\Extbase\Mvc\Cli\Response');
		$response->expects($this->once())->method('appendContent')->withAnyParameters();
		$this->controller->_set('response', $response);

		$this->controller->injectExtensions($extensions);
		$this->controller->injectStatesUtility($states);
		$this->controller->injectUploader($uploader);
		$this->controller->injectRepositories($repositories);
		$this->controller->uploadCommand($extKey, $username, $password, $state, $release, $version, $comment);
	}

	public function testExceptionsAreCaughtAndLocalized() {
		$exceptionCode   = 1;
		$exception       = new \T3x\ExtensionUploader\Upload\ValidationFailedException('Test', $exceptionCode);
		$expectedMessage = LocalizationUtility::translate('exception.' . $exceptionCode, 'extension_uploader');
		$extKey          = 'dummy_extension';

		$extensions = $this->getMock('T3x\ExtensionUploader\Domain\Repository\LocalExtensionRepository');
		$extensions
			->expects($this->once())
			->method('findOneByExtensionKey')
			->with($extKey)
			->will($this->throwException($exception));

		$response = $this->getMock($this->buildAccessibleProxy('TYPO3\CMS\Extbase\Mvc\Cli\Response'));
		$response
			->expects($this->once())
			->method('appendContent')
			->with($expectedMessage . PHP_EOL);

		$this->controller->_set('response', $response);
		$this->controller->injectExtensions($extensions);
		$this->controller->uploadCommand($extKey, '', '');
	}
}
