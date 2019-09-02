<?php
namespace Ubiquity\db\providers\tarantool;


use Ubiquity\db\providers\TraitHasPool;
use Ubiquity\db\provider\mysqli\MysqliWrapper;

class MysqliSwooleWrapper extends MysqliWrapper {
use TraitHasPool;
}

