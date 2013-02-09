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

/**
 * A soap connection with the TER
 *
 * @package ExtensionBuilder
 * @author Georg Großberger <contact@grossberger-ge.org>
 * @copyright 2013 by Georg Großberger
 * @license GPL v3 http://www.gnu.org/licenses/gpl-3.0.txt
 */
class Connection {

	/**
	 * @var \SoapClient
	 */
	protected $client;

	/**
	 * @param string $wsdlUrl
	 * @param string $username
	 * @param string $password
	 * @throws ConnectionException
	 * @throws
	 */
	public function __construct($wsdlUrl, $username, $password) {
		if (!filter_var($wsdlUrl, FILTER_VALIDATE_URL)) {
			throw new ConnectionException('No valid WSDL URL', 1360447912);
		}

		if (!extension_loaded('soap') || !class_exists('\SoapClient')) {
			throw new ConnectionException('No soap client', 1360448130);
		}

		try {
			$this->client = new \SoapClient($wsdlUrl, array(
				'keep_alive'   => TRUE,
				'username'     => $username,
				'password'     => $password,
				'exceptions'   => TRUE,
				'cache_wsdl'   => WSDL_CACHE_DISK,
				'soap_version' => SOAP_1_2
			));
		} catch (\SoapFault $exception) {
			throw $this->soapFaultToInternalException($exception);
		}
	}

	protected function soapFaultToInternalException(\SoapFault $oldException) {
		return new ConnectionException($oldException->getMessage(), 1360448742, $oldException);
	}
}
