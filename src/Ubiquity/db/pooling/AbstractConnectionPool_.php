<?php
namespace Ubiquity\db\pooling;

abstract class AbstractConnectionPool_ {

	abstract protected function setDbParams(&$dbConfig);

	public function __construct(&$config, $offset = null, int $capacity = 16) {
		$dbConfig = $offset ? ($config['database'][$offset] ?? ($config['database'] ?? [])) : ($config['database']['default'] ?? $config['database']);
		$this->setDbParams($dbConfig);
	}

	abstract public function put($db);

	abstract public function get();

	abstract public function close(): void;
}

