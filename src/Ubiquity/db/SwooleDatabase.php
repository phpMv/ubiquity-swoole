<?php
namespace Ubiquity\db;

use Ubiquity\db\providers\swoole\SwooleWrapper;

class SwooleDatabase extends Database {
	
	private $uid;
	public function __construct($uid, $pool,$dbType, $dbName, $serverName = "127.0.0.1", $port = "3306", $user = "root", $password = "", $options = [], $cache = false){
		parent::__construct(SwooleWrapper::class, $dbType, $dbName,$serverName,$port,$user,$password,$options,$cache);
		$this->uid=$uid;
		$this->setPool($pool);
	}
	
	/**
	 *
	 * @param string $sql
	 * @return object statement
	 */
	private function getStatement($sql) {
		$uid=$this->uid;
		if (! isset ( $this->statements [$sql][$uid] )) {
			$this->statements [$sql][$uid] = $this->wrapperObject->getStatement ( $sql );
		}
		return $this->statements [$sql][$uid];
	}
	
	/**
	 *
	 * @param string $sql
	 * @return object statement
	 */
	public function getUpdateStatement($sql) {
		$uid=$this->uid;
		if (! isset ( $this->updateStatements [$sql][$uid] )) {
			$this->updateStatements [$sql][$uid] = $this->wrapperObject->getStatement ( $sql );
		}
		return $this->updateStatements [$sql][$uid];
	}
	
}

