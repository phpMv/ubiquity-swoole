<?php
namespace Ubiquity\db\providers\swoole;

use Swoole\Coroutine\MySQL;

class ConnectionPool{
	private const DB_TYPES=['mysql'=>'Swoole\Coroutine\MySQL','postgre'=>'Swoole\Coroutine\PostgreSql'];
	
	private $server = [
		'charset' => 'utf8mb4',
		'timeout' => 1.000,
		'strict_type' => true
	];
	private $dbClass;
	private $pool;
	
	private $dbs=[];
	
	public function __construct(&$config, $offset=null){
		$db = $offset ? ($config ['database'] [$offset] ?? ($config ['database'] ?? [ ])) : ($config ['database'] ['default'] ?? $config ['database']);
		$this->pool = new \SplQueue();
		$this->server=['host'=>$db ['serverName']??'127.0.0.1','port'=>$db ['port']??3306,'user'=>$db ['user']??'root','password'=>$db ['password']??'','database'=>$db ['dbName']??'']+$this->server;
		//$this->dbClass=self::DB_TYPES[$db ['type']]??'Swoole\Coroutine\MySQL';
	}
	
	public function put($db){
		$this->pool->enqueue($db);
	}
	public function get(){
		$uid=\Swoole\Coroutine::getuid();
		if(isset($this->dbs[$uid])){
			return $this->dbs[$uid];
		}
		if (!$this->pool->isEmpty()) {
			return $this->dbs[$uid]=$this->pool->dequeue();
		}
		//$clazz=$this->dbClass;
		$db=$this->dbs[$uid] = new MySQL();
		if($db->connect($this->server)){
			return $db;
		}
		return false;
	}
	
	public function getUid($value){
		return $value.\Swoole\Coroutine::getuid();
	}
}

