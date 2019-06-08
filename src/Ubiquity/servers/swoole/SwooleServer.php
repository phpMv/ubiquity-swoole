<?php
namespace Ubiquity\servers\swoole;

use Swoole\Http\Server;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Ubiquity\utils\http\foundation\SwooleHttp;

class SwooleServer {

	private $server;

	/**
	 *
	 * @var SwooleHttp
	 */
	private $httpInstance;

	private $config;

	private $basedir;

	public function init($config, $basedir) {
		$this->config = $config;
		$this->basedir = $basedir;
		$this->httpInstance = new SwooleHttp();
		\pcntl_signal(\SIGTERM, [
			$this,
			'stop'
		]);
		\pcntl_signal(\SIGINT, [
			$this,
			'stop'
		]);
	}

	public function run($host, $port) {
		$http = new Server($host, $port);
		$http->on('start', function ($server) use ($host, $port) {
			echo "Ubiquity-Swoole http server is started at {$host}:{$port}\n";
		});

		$http->on("request", function (Request $request, Response $response) {
			$this->handle($request, $response);
			\pcntl_signal_dispatch();
		});
		$this->server = $http;
		$http->start();
	}

	protected function handle(Request $request, Response $response) {
		$request->get['c'] = '';
		$response->status(200);
		$uri = ltrim(urldecode(parse_url($request->server['request_uri'], PHP_URL_PATH)), '/');
		if ($uri == null || ! file_exists($this->basedir . '/../' . $uri)) {
			$request->get['c'] = $uri;
		} else {
			$response->end(file_get_contents($this->basedir . '/../' . $uri));
			return;
		}

		$this->httpInstance->setRequest($request, $response);
		$this->parseRequest($request);
		\ob_start();
		\Ubiquity\controllers\Startup::setHttpInstance($this->httpInstance);
		\Ubiquity\controllers\Startup::run($this->config);
		$content = ob_get_clean();
		$response->end($content);
	}

	protected function parseRequest(Request $request) {
		$headers = [];
		foreach ($request->header as $key => $value) {
			if ($key == 'x-forwarded-proto' && $value == 'https') {
				$request->server['HTTPS'] = 'on';
			}
			$headerKey = 'HTTP_' . strtoupper(str_replace('-', '_', $key));
			$headers[$headerKey] = $value;
		}
		$_SERVER = array_change_key_case(array_merge($request->server, $headers), CASE_UPPER);
		$_GET = $request->get ?? [];
		$_POST = $request->post ?? [];
		$_COOKIE = $request->cookie ?? [];
		$_FILES = $request->files ?? [];
	}

	public function stop() {
		$this->server->shutdown();
		echo "Ubiquity-Swoole http stopping\n";
	}
}

