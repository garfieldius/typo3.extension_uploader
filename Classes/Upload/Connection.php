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

/**
 * A soap connection with the TER
 *
 * @package ExtensionUploaderex
 * @author Georg Großberger <contact@grossberger-ge.org>
 * @copyright 2013 by Georg Großberger
 * @license GPL v3 http://www.gnu.org/licenses/gpl-3.0.txt
 */
class Connection {

	const HEADER_NAMESPACE = 'http://www.typo3.org/wsdl/tx_ter/';

	/**
	 * @var string
	 */
	protected $wsdlUrl;

	/**
	 * @var string
	 */
	protected $username;

	/**
	 * @var string
	 */
	protected $password;

	/**
	 * @var \SoapClient
	 */
	protected $client;


	protected $token = NULL;

	/**
	 * @var \TYPO3\CMS\Core\Log\Logger
	 */
	protected $log;

	public function initializeObject() {
		$this->log = GeneralUtility::makeInstance('TYPO3\CMS\Core\Log\LogManager')->getLogger(__CLASS__);
	}

	/**
	 * @param \SoapClient $client
	 */
	public function setClient(\SoapClient $client) {
		$this->client = $client;
	}

	/**
	 * Sets Password
	 *
	 * @param string $password
	 * @return $this
	 */
	public function setPassword($password) {
		$this->password = $password;
		return $this;
	}

	/**
	 * Sets Username
	 * @param string $username
	 * @return Connection
	 */
	public function setUsername($username) {
		$this->username = $username;
		return $this;
	}

	/**
	 * Sets WsdlUrl
	 * @param string $wsdlUrl
	 * @return Connection
	 */
	public function setWsdlUrl($wsdlUrl) {
		$this->wsdlUrl = $wsdlUrl;
		return $this;
	}

	/**
	 * @throws ConnectionException
	 * @return \SoapClient
	 */
	public function connectClient() {
		if (!filter_var($this->wsdlUrl, FILTER_VALIDATE_URL)) {
			throw new ConnectionException('No valid WSDL URL', 1360447912);
		}

		if (!extension_loaded('soap') || !class_exists('\SoapClient')) {
			throw new ConnectionException('No soap client', 1360448130);
		}

		try {
			$client = new \SoapClient($this->wsdlUrl, array(
				'username'     => $this->username,
				'password'     => $this->password,
				'exceptions'   => TRUE,
				'trace'        => TRUE,
				'cache_wsdl'   => WSDL_CACHE_DISK,
				'soap_version' => SOAP_1_2
			));
		} catch (\SoapFault $exception) {
			throw $this->soapFaultToInternalException($exception);
		}
		return $client;
	}

	/**
	 * @param \SoapFault $originalException
	 * @return ConnectionException
	 */
	protected function soapFaultToInternalException(\SoapFault $originalException) {
		$this->log->error("Extension Upload: " . $originalException->getMessage(), array($originalException));
		return new ConnectionException($originalException->getMessage(), 1360448742, $originalException);
	}

	/**
	 * @param array $data
	 * @return array
	 */
	public function uploadExtension(array $data) {
		return $this->doRequest('uploadExtension', $data);
	}

	/**
	 * @param string $function
	 * @param array $data
	 * @return array
	 * @throws ConnectionException
	 */
	protected function doRequest($function, array $data) {
		try {
			if ($this->client instanceof \SoapClient !== TRUE) {
				throw new ConnectionException('No soap client set', 1361042666);
			}

			$authHeader = new \stdClass();

			if ($this->token) {
				$authHeaderName = 'HeaderAuthenticate';
				$authHeader->reactid = $this->token;
			} else {
				$authHeaderName = 'HeaderLogin';
				$authHeader->username = $this->username;
				$authHeader->password = $this->password;
			}

			$soapHeader = new \SoapHeader(self::HEADER_NAMESPACE, $authHeaderName, $authHeader, TRUE);
			$response   = $this->client->__soapCall($function, $data, NULL, $soapHeader);

			if (
				property_exists($this->client, 'headersIn') &&
				isset($this->client->headersIn['HeaderAuthenticate']) &&
				is_object($this->client->headersIn['HeaderAuthenticate'])
			) {
				$this->token = $this->client->headersIn['HeaderAuthenticate']->reactid;
			}

			$this->log->debug('SOAP Response', array($response));

			return $response;
		} catch (\SoapFault $exception) {
			throw $this->soapFaultToInternalException($exception);
		}
	}
}
