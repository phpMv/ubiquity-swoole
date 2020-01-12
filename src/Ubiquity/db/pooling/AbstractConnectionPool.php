<?php
namespace Ubiquity\db\pooling;

use Swoole\Coroutine\Channel;

abstract class AbstractConnectionPool{
	
	protected $pool;
	
	abstract protected function createDbInstance();
	
	abstract protected function setDbParams(&$dbConfig);

	public function __construct(&$config, $offset=null,int $capacity=16){
		$db = $offset ? ($config ['database'] [$offset] ?? ($config ['database'] ?? [ ])) : ($config ['database'] ['default'] ?? $config ['database']);
		$this->pool = new Channel($capacity);
		$this->setDbParams($db);
		while($capacity>0){
			$db=$this->createDbInstance();
			if($db!==false){
				$this->pool->push($db);
				$capacity--;
			}
		}
	}
	
	public function put($db){
		$this->pool->push($db);
	}
	public function get(){
		return $this->pool->pop();
	}
	
	public function close():void{
		$this->pool->close();
		$this->pool = null;
	}
}

