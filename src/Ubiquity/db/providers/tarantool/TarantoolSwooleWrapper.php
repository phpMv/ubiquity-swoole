<?php
namespace Ubiquity\db\providers\tarantool;


use Ubiquity\db\providers\TraitHasPool;

class TarantoolSwooleWrapper extends TarantoolWrapper {
use TraitHasPool;
	public function getPoolClass() {
		return \Ubiquity\db\pooling\TarantoolPool::class;
	}

}

