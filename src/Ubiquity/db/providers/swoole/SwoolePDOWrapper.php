<?php
namespace Ubiquity\db\providers\swoole;

use Ubiquity\db\providers\pdo\PDOWrapper;
use Ubiquity\db\providers\TraitHasPool;
use Ubiquity\db\pooling\SwoolePDOConnectionPool;

class SwoolePDOWrapper extends PDOWrapper {
	use TraitHasPool;

	public function getPoolClass() {
		return SwoolePDOConnectionPool::class;
	}
}

