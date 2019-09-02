<?php
namespace Ubiquity\db\pooling;

use Swoole\Coroutine\MySQL;

class ConnectionPool extends AbstractConnectionPool{
	private const DB_TYPES=['mysql'=>'Swoole\Coroutine\MySQL','postgre'=>'Swoole\Coroutine\PostgreSql'];
	
	private $server = [
		'charset' => 'utf8mb4',
		'timeout' => 1.000,
		'strict_type' => true
	];
	private $dbClass;
	
	protected function createDbInstance(){
		//$clazz=$this->dbClass;
		$db=new MySQL();
		if($db->connect($this->server)){
			return $db;
		}
		return false;
	}
	
	protected function setDbParams($dbConfig) {
		$this->server=['host'=>$dbConfig ['serverName']??'127.0.0.1','port'=>$dbConfig ['port']??3306,'user'=>$dbConfig ['user']??'root','password'=>$dbConfig ['password']??'','database'=>$dbConfig ['dbName']??'']+$this->server;
		//$this->dbClass=self::DB_TYPES[$db ['type']]??'Swoole\Coroutine\MySQL';
	}
}

