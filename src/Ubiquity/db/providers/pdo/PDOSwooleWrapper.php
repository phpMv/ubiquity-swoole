<?php
namespace Ubiquity\db\providers\pdo;

use Ubiquity\db\providers\TraitHasPool;

class PDOSwooleWrapper extends PDOWrapper {
	use TraitHasPool;

	public function getPoolClass() {
		return \Ubiquity\db\pooling\PDOPool::class;
	}
}

