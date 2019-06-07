<?php
namespace Ubiquity\servers\swoole;

use Swoole\Http\Server;
use Swoole\Http\Request;
use Swoole\Http\Response;

class SwooleServer {

	private $server;

	private $config;

	private $basedir;

	public function init($config, $basedir) {
		$this->config = $config;
		$this->basedir = $basedir;
	}

	public function run($host, $port) {
		$http = new Server($host, $port);
		$http->on('start', function ($server) use ($host, $port) {
			echo "Ubiquity-Swoole http server is started at {$host}:{$port}\n";
		});

		$http->on("request", function (Request $request, Response $response) {
			$response->header("Content-Type", "text/plain");
			$response->end("Hello World\n");
		});

		$http->start();
	}
}

