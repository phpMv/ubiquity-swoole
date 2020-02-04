<?php
namespace Ubiquity\db\pooling;

use Swoole\Database\PDOPool;
use Swoole\Database\PDOConfig;

class SwoolePDOConnectionPool extends AbstractSwoolePool {

	protected function setDbParams($dbConfig) {
		$this->pool = new PDOPool((new PDOConfig())->withHost($dbConfig['host'])
			->withPort($dbConfig['port'])
			->
		// ->withUnixSocket('/tmp/mysql.sock')
		withDbName($dbConfig['dbName'])
			->withCharset('utf8mb4')
			->withUsername($dbConfig['user'])
			->withPassword($dbConfig['password']), $this->capacity);
	}
}

