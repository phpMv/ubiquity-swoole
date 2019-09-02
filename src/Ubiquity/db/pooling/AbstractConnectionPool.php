<?php
namespace Ubiquity\db\pooling;

use Swoole\Coroutine\Channel;
use Swoole\Coroutine;

abstract class AbstractConnectionPool{
	
	protected $pool;
	
	abstract protected function createDbInstance();
	
	abstract protected function setDbParams(&$dbConfig);

	public function __construct(&$config, $offset=null,int $capacity=1000){
		$db = $offset ? ($config ['database'] [$offset] ?? ($config ['database'] ?? [ ])) : ($config ['database'] ['default'] ?? $config ['database']);
		$this->pool = new Channel($capacity);
		$this->setDbParams($db);
	}
	
	public function put($db){
		$this->pool->push($db);
	}
	public function get(){
		if (!$this->pool->isEmpty()) {
			return $this->pool->pop();
		}
		return $this->createDbInstance();
	}
	
	public function go(callable $asyncCall){
		$dbInstance=$this->get();
		Coroutine::create($asyncCall);
		$this->put($dbInstance);
	}
}

