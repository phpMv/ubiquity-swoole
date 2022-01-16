<?php
namespace Ubiquity\servers\swoole;

use Swoole\Http\Server;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Ubiquity\utils\http\foundation\SwooleHttp;
use Ubiquity\utils\base\MimeType;
use Swoole\Process;

class SwooleServer {

	private $mode;

	private $server;

	/**
	 *
	 * @var SwooleHttp
	 */
	private $httpInstance;

	private $config;

	private $basedir;

	private $options;

	private $events = [];

	private $servicesFile;

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

	private function addEvents($http) {
		foreach ($this->events as $event => $callback) {
			$http->on($event, $callback);
		}
	}

	public function __construct($mode = \SWOOLE_BASE) {
		$this->mode = $mode;
	}

	public function init($config, $basedir) {
		$this->config = $config;
		$this->basedir = $basedir;
		$this->httpInstance = new SwooleHttp();
		\Ubiquity\controllers\Startup::init($config);
		\Ubiquity\controllers\Startup::setHttpInstance($this->httpInstance);
	}

	/**
	 * Get swoole configuration option value.
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function getOption(string $key) {
		$option = $this->options[$key] ?? false;
		if (! $option) {
			throw new \InvalidArgumentException(sprintf('Parameter not found: %s', $key));
		}
		return $option;
	}

	public function setOptions($options = []) {
		$default = [
			'daemonize' => false,
			'reactor_num' => function_exists('swoole_cpu_num') ? swoole_cpu_num() : 2,
			'worker_num' => function_exists('swoole_cpu_num') ? swoole_cpu_num() : 4,
			'log_level' => 5,
			'enable_reuse_port' => true,
			'enable_coroutine' => false
		];
		if (\is_array($options)) {
			$this->options = \array_merge($default, $options);
		} else {
			$this->options = $default;
		}
	}

	public function run($host, $port, $options = null) {
		$http = new Server($host, $port, $this->mode, \SWOOLE_SOCK_TCP);
		$this->setOptions($options);
		$this->configure($http);
		$http->on('start', function ($server) use ($host, $port) {
			echo "Ubiquity-Swoole http server is started at {$host}:{$port}\n";
		});

		$http->on("request", function (Request $request, Response $response) {
			$this->handle($request, $response);
		});
		$this->addEvents($http);
		$this->server = $http;
		$http->start();
	}

	public function on($eventName, $callback) {
		$this->events[$eventName] = $callback;
	}

	/**
	 * Stop the swoole server.
	 *
	 * @return bool
	 * @throws \Exception
	 */
	public function stop(): bool {
		$pid = $this->getPid();
		if ($pid !== 0) {
			$kill = Process::kill($pid);
			if (! $kill) {
				throw new \Exception("Swoole server not stopped!");
			}
			return $kill;
		}
	}

	protected function handle(Request $request, Response $response) {
		$request->get['c'] = '';
		$response->status(200);
		$uriInfos = \Ubiquity\utils\http\URequest::parseURI($request->server['request_uri'], $this->basedir);
		$uri = $uriInfos['uri'];
		if ($uriInfos['isAction']) {
			$request->get['c'] = $uri;
		} else {
			if ($uriInfos['file']) {
				$mime = MimeType::getFileMimeType(\realpath($uri));
				$response->header('Content-Type', $mime . '; charset=utf-8');
				$response->end(\file_get_contents($this->basedir . '/../' . $uri));
			} else {
				$response->status(404);
				$response->header('Content-Type', 'text/plain; charset=utf-8');
				$response->end($uri . ' not found!');
			}
			return;
		}

		$this->httpInstance->setRequest($request, $response);
		$this->parseRequest($request);
		\ob_start();
		\Ubiquity\controllers\StartupAsync::forward($uri);
		$response->end(\ob_get_clean());
	}

	protected function parseRequest(Request $request) {
		$headers = [];
		foreach ($request->header as $key => $value) {
			if ($key == 'x-forwarded-proto' && $value == 'https') {
				$request->server['HTTPS'] = 'on';
			}
			$headerKey = 'HTTP_' . \strtoupper(\str_replace('-', '_', $key));
			$headers[$headerKey] = $value;
		}
		$_SERVER = \array_change_key_case(\array_merge($request->server, $headers), \CASE_UPPER);
		$_GET = $request->get ?? [];
		$_POST = $request->post ?? [];
		$_COOKIE = $request->cookie ?? [];
		$_FILES = $request->files ?? [];
	}

	/**
	 *
	 * @param mixed $servicesFile
	 */
	public function setServicesFile($servicesFile) {
		$this->servicesFile = $servicesFile;
	}
}

