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
	private $pool_count = 0;
	
	public function __construct($dbType,$host,$port,$user,$password,$database){
		$this->pool = new \SplQueue;
		$this->server=['host'=>$host,''=>$port,'user'=>$user,'$password'=>$password,'database'=>$database]+$this->server;
		$this->dbClass=self::DB_TYPES[$dbType]??'Swoole\Coroutine\MySQL';
	}

	public function put($db){
		$this->pool->enqueue($db);
		$this->pool_count++;
	}
	public function get(){
		if ($this->pool_count > 0) {
			$this->pool_count--;
			return $this->pool->dequeue();
		}
		// No idle connection, time to create a new connection
		$clazz=$this->dbClass;
		$db = new $clazz();
		$db->connect($this->server);
		if ($db == false) {
			return false;
		}
		return $db;
	}
}

