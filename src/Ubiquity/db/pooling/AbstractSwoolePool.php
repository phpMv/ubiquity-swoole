<?php
namespace Ubiquity\db\pooling;

abstract class AbstractSwoolePool extends AbstractConnectionPool_ {

	/**
	 *
	 * @var \Swoole\ConnectionPool
	 */
	protected $pool;

	protected $capacity;

	public function __construct(&$config, $offset = null, int $capacity = 16) {
		$this->capacity = $capacity;
		parent::__construct($config, $offset, $capacity);
	}

	public function fill() {
		$this->pool->fill();
	}

	public function put($db) {
		$this->pool->put($db);
	}

	public function get() {
		return $this->get();
	}

	public function close(): void {
		$this->pool->close();
	}
}

