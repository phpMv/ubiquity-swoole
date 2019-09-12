<?php
namespace Ubiquity\db\providers\swoole;

use Ubiquity\db\providers\TraitHasPool;

/**
 * Ubiquity\db\providers\swoole$SwoolePoolWrapper
 * This class is part of Ubiquity
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 * @property \Swoole\Coroutine\MySQL $dbInstance
 *
 */
class SwoolePoolWrapper extends SwooleWrapper {
use TraitHasPool;
	public function getPoolClass() {
		return \Ubiquity\db\pooling\ConnectionPool::class;
	}	
}

