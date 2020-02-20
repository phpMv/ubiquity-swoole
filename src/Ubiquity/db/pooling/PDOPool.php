<?php
namespace Ubiquity\db\pooling;

class PDOPool extends AbstractConnectionPool {

	private $config;

	protected function createDbInstance() {
		$options[\PDO::ATTR_ERRMODE] = \PDO::ERRMODE_EXCEPTION;
		extract($this->config);
		$dbInstance = new \PDO($this->getDSN($serverName, $port, $dbName, $dbType), $user, $password, $options);
		return $dbInstance;
	}

	protected function setDbParams(&$dbConfig) {
		$this->config = $dbConfig;
	}

	protected function getDSN(string $serverName, string $port, string $dbName, string $dbType = 'mysql') {
		$charsetString = [
			'mysql' => 'charset=UTF8',
			'pgsql' => 'options=\'--client_encoding=UTF8\'',
			'sqlite' => ''
		][$dbType] ?? 'charset=UTF8';
		if ($dbType === 'sqlite') {
			return "sqlite:{$dbName}";
		}
		return $dbType . ":dbname={$dbName};host={$serverName};{$charsetString};port=" . $port;
	}
}

