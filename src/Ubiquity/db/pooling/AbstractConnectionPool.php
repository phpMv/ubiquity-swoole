<?php
namespace Ubiquity\db\pooling;

abstract class AbstractConnectionPool extends AbstractConnectionPool_ {

	protected $pool;

	abstract protected function createDbInstance();

	public function __construct(&$config, $offset = null, int $capacity = 16) {
		parent::__construct($config, $offset, $capacity);
		while ($capacity > 0) {
			$db = $this->createDbInstance();
			if ($db !== false) {
				$this->pool->push($db);
				$capacity --;
			}
		}
	}

	public function put($db) {
		$this->pool->push($db);
	}

	public function get() {
		return $this->pool->pop();
	}

	public function close(): void {
		$this->pool->close();
		$this->pool = null;
	}
}

