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

	abstract public function getStatement($sql);

	/**
	 *
	 * @var \Ubiquity\db\pooling\ConnectionPool
	 */
	protected $connectionPool;

	protected function getInstance() {
		return $this->dbi;
	}

	protected function getUid() {
		return Coroutine::getuid();
	}

	public function connect($dbType, $dbName, $serverName, $port, $user, $password, array $options) {}

	public function pool() {
		return $this->dbi = $this->connectionPool->get();
	}

	public function freePool($db) {
		$this->connectionPool->put($db);
	}

	public function setPool($pool) {
		$this->connectionPool = $pool;
	}

	public function _getStatement(string $sql) {
		return $this->getStatement($sql);
	}
}

