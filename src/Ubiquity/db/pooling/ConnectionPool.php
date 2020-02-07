<?php
namespace Ubiquity\db\pooling;

class ConnectionPool extends AbstractConnectionPool {

	private const DB_TYPES = [
		'mysql' => '\\Swoole\\Coroutine\\MySQL',
		'pgsql' => '\\Swoole\\Coroutine\\PostgreSQL'
	];

	private $server = [
		'charset' => 'utf8mb4',
		'timeout' => 1.000,
		'strict_type' => true
	];

	private $dbClass;

	protected function createDbInstance() {
		$clazz = $this->dbClass;
		$db = new $clazz();
		if ($db->connect($this->server)) {
			echo "connection.";
			return $db;
		}
		return false;
	}

	protected function setDbParams(&$dbConfig) {
		if ($dbConfig['type'] == 'mysql') {
			$this->server = [
				'host' => $dbConfig['serverName'] ?? '127.0.0.1',
				'port' => $dbConfig['port'] ?? 3306,
				'user' => $dbConfig['user'] ?? 'root',
				'password' => $dbConfig['password'] ?? '',
				'database' => $dbConfig['dbName'] ?? ''
			] + $this->server;
		} else {
			\extract($dbConfig);
			$this->server = "host={$serverName};port={$port};dbname={$database};user={$user};password={$password}";
		}
		$this->dbClass = self::DB_TYPES[$dbConfig['type'] ?? 'mysql'] ?? self::DB_TYPES['mysql'];
	}
}

