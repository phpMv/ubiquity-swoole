<?php
namespace Ubiquity\servers\swoole;

use Swoole\Http\Server;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Ubiquity\utils\http\foundation\SwooleHttp;
use Swoole\Process;

class SwooleServer {

	private $server;

	/**
	 *
	 * @var SwooleHttp
	 */
	private $httpInstance;

	private $config;

	private $basedir;

	private $options;

	/**
	 *
	 * @return int
	 */
	private function getPid(): int {
		$file = $this->getPidFile();
		if (! \file_exists($file)) {
			return 0;
		}
		$pid = (int) \file_get_contents($file);
		if (! $pid) {
			$this->removePidFile();
			return 0;
		}
		return $pid;
	}

	/**
	 * Get pid file.
	 *
	 * @return string
	 */
	private function getPidFile(): string {
		return $this->getOption('pid_file');
	}

	/**
	 * Remove the pid file.
	 */
	private function removePidFile(): void {
		$file = $this->getPidFile();
		if (\file_exists($file)) {
			\unlink($file);
		}
	}

	/**
	 * Configure the created server.
	 */
	private function configure($http) {
		$http->set($this->options);
	}

	public function init($config, $basedir) {
		$this->config = $config;
		$this->basedir = $basedir;
		$this->httpInstance = new SwooleHttp();
	}

	/**
	 * Get swoole configuration option value.
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function getOption(string $key) {
		$option = $this->options[$key];
		if (! $option) {
			throw new \InvalidArgumentException(sprintf('Parameter not found: %s', $key));
		}
		return $option;
	}

	public function setOptions($options = []) {
		$default = [
			'pid_file' => '/var/run/swoole_server.pid',
			'daemonize' => true
		];
		if (is_array($options)) {
			$this->options = $default + $options;
		} else {
			$this->options = $default;
		}
	}

	public function run($host, $port, $options = null) {
		$http = new Server($host, $port);
		$this->setOptions($options);
		$this->configure($http);
		$http->on('start', function ($server) use ($host, $port) {
			echo "Ubiquity-Swoole http server is started at {$host}:{$port}\n";
		});

		$http->on("request", function (Request $request, Response $response) {
			$this->handle($request, $response);
		});
		$this->server = $http;
		$http->start();
	}

	/**
	 * Stop the swoole server.
	 *
	 * @return bool
	 * @throws \Exception
	 */
	public function stop(): bool {
		$kill = Process::kill($this->getPid());
		if (! $kill) {
			throw new \Exception("Swoole server not stopped!");
		}
		return $kill;
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
}

