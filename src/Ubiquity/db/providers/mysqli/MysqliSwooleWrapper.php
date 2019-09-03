<?php
namespace Ubiquity\db\providers\tarantool;


use Ubiquity\db\providers\TraitHasPool;

class MysqliSwooleWrapper extends MysqliWrapper {
use TraitHasPool;
	public function getPoolClass() {
		return \Ubiquity\db\pooling\MysqliPool::class;
	}
}

