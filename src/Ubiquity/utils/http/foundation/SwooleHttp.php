<?php
namespace Ubiquity\utils\http\foundation;

/**
 * Http instance for Swoole.
 * Ubiquity\utils\http\foundation$SwooleHttp
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 *         
 */
class SwooleHttp extends AbstractHttp {

	private $headers = [];

	private $responseCode = 200;

	/**
	 *
	 * @var \Swoole\Http\Response
	 */
	private $response;

	/**
	 *
	 * @var \Swoole\Http\Request
	 */
	private $request;

	public function getAllHeaders() {
		return $this->headers;
	}

	public function header($key, $value, $replace = null, $http_response_code = null) {
		$this->headers[$key] = $value;
		if ($http_response_code != null) {
			$this->responseCode = $http_response_code;
			$this->response->status($http_response_code);
		}
		$this->response->header($key, $value);
	}

	/**
	 *
	 * @return int
	 */
	public function getResponseCode() {
		return $this->responseCode;
	}

	/**
	 *
	 * @param mixed $headers
	 */
	private function setHeaders($headers) {
		foreach ($headers as $k => $header) {
			if (is_array($header) && sizeof($header) == 1) {
				$this->headers[$k] = current($header);
			} else {
				$this->headers[$k] = $header;
			}
		}
		$this->response->header = $this->headers;
	}

	/**
	 *
	 * @param int $responseCode
	 */
	public function setResponseCode($responseCode) {
		if ($responseCode != null) {
			$this->responseCode = $responseCode;
			$this->response->status($responseCode);
		}
	}

	public function headersSent(string &$file = null, int &$line = null) {
		return headers_sent($file, $line);
	}

	public function getInput() {
		return $this->request->getData();
	}

	/**
	 *
	 * @param \Swoole\Http\Request $request
	 * @param \Swoole\Http\Response $response
	 */
	public function setRequest(\Swoole\Http\Request $request, \Swoole\Http\Response $response) {
		$this->response = $response;
		$this->request = $request;
		$default = [
			'content-type' => current($request->header['accept']),
			'server' => $request->request['server'] ?? 'Swoole'
		];
		if (is_array($response->header)) {
			$headers = (array_merge($default, $response->header));
		} else {
			$headers = $default;
		}
		$this->setHeaders($headers);
	}
}

