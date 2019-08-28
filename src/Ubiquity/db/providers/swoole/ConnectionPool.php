<?php
namespace Ubiquity\db\providers\swoole;

class ConnectionPool{
	private const DB_TYPES=['mysql'=>'Swoole\Coroutine\MySQL','postgre'=>'Swoole\Coroutine\PostgreSql'];
	
	private $server = [
		'host' => '127.0.0.1',
		'port' => 3306,
		'user' => 'root',
		'password' => '',
		'database' => '',
		'charset' => 'utf8mb4',
		'timeout' => 1.000,
		'strict_type' => true
	];
	private $dbClass;
	private $pool;
	
	public function __construct($dbType,$host,$port,$user,$password,$database){
		$this->pool = new \SplQueue;
		$this->server=['host'=>$host,'port'=>$port,'user'=>$user,'password'=>$password,'database'=>$database]+$this->server;
		$this->dbClass=self::DB_TYPES[$dbType]??'Swoole\Coroutine\MySQL';
	}
	
	public function put($db){
		$this->pool->enqueue($db);
	}
	public function get(){
		if (!$this->pool->isEmpty()) {
			return $this->pool->dequeue();
		}
		$clazz=$this->dbClass;
		$db = new $clazz();
		if($db->connect($this->server)){
			return $db;
		}
		return false;
	}
	
	public function getUid($value){
		return $value.\Swoole\Coroutine::getuid();
	}
}

