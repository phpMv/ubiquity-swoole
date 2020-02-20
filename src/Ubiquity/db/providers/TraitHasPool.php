<?php
namespace Ubiquity\db\providers;

use Swoole\Coroutine;

/**
 * Ubiquity\db\providers$TraitHasPool
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 * @property array $statements
 *
 */
trait TraitHasPool {

	abstract function getPoolClass();

	abstract public function getStatement($sql, $dbInstance = null);

	/**
	 *
	 * @var \Ubiquity\db\pooling\ConnectionPool
	 */
	protected $connectionPool;

	protected function getInstance() {
		return $this->dbInstance;
	}

	protected function getUid() {
		return Coroutine::getuid();
	}

	public function connect($dbType, $dbName, $serverName, $port, $user, $password, array $options) {}

	public function pool() {
		return $this->dbInstance = $this->connectionPool->get();
	}

	public function freePool($db) {
		$this->connectionPool->put($db);
	}

	public function setPool($pool) {
		$this->connectionPool = $pool;
	}

	public function _getStatement(string $sql, $dbInstance = null) {
		// $key = '_st' . \md5($sql);
		// $instance = $dbInstance ?? $this->dbInstance;
		// return $instance->{$key} ??= $this->getStatement($sql, $instance);
		return $this->getStatement($sql, $dbInstance);
	}
}

