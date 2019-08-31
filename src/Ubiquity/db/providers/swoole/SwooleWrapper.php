<?php
namespace Ubiquity\db\providers\swoole;

use Ubiquity\db\providers\AbstractDbWrapper;
use Swoole\Coroutine;

/**
 * Ubiquity\db\providers\swoole$SwooleWrapper
 * This class is part of Ubiquity
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 * @property \Swoole\Coroutine\MySQL $dbInstance
 *
 */
class SwooleWrapper extends AbstractDbWrapper {
	
	/**
	 * @var ConnectionPool
	 */
	private $connectionPool;
	
	private $inTransaction=false;
	
	private $dbs=[];
	
	public function __construct($dbType = 'mysql') {
		$this->quote = '`';
	}
	
	public function queryColumn($sql, $columnNumber = null) {
		$stmt = $this->getInstance()->prepare($sql);
		if($stmt->execute()){
			$row=$stmt->fetch();
			return (\is_numeric($columnNumber))?\array_values($row)[$columnNumber]:$row[$columnNumber];
		}
	}

	public function getDSN(string $serverName, string $port, string $dbName, string $dbType = 'mysql') {
		return 'swoole.coroutine.mysql:dbname=' . $dbName . ';host=' . $serverName . ';charset=UTF8;port=' . $port;
	}
	public function fetchAllColumn($statement, array $values = null, $column = null) {
		$st=new SwooleStatement($this->getInstance(),$statement);
		return $st->fetchColumn($column??0);
	}

	public function ping() {
		return (1 === \intval ( $this->queryColumn( 'SELECT 1' ,0 ) ));
	}

	public function commit() {
		$this->getInstance()->commit();
		$this->inTransaction = true;
	}

	public function prepareStatement($sql) {
		$instance=$this->getInstance();
		$st=$instance->prepare ( $sql );
		return new SwooleStatement($instance,$st);
	}

	public function queryAll($sql, $fetchStyle = null) {
		return $this->getInstance()->query ( $sql );
	}

	public function releasePoint($level) {}

	public function lastInsertId() {
		return $this->getInstance()->insert_id;
	}

	public function nestable() {
		return false;
	}

	public static function getAvailableDrivers() {
		return ['mysql'];
	}

	public function rollbackPoint($level) {}

	public function getTablesName() {}

	public function getStatement($sql) {
		\preg_match_all('/:([[:alpha:]]+)/', $sql,$params);
		$sql=\preg_replace('/:[[:alpha:]]+/','?',$sql);
		$instance=$this->getInstance();
		$st=$instance->prepare ( $sql);
		return new SwooleStatement($instance,$st,$params);
	}

	public function connect($dbType, $dbName, $serverName, $port, $user, $password, array $options) {
		
	}

	public function inTransaction() {
		return $this->inTransaction;
	}

	public function fetchAll($statement, array $values = null, $mode = null) {
		if ($statement->execute($values)){
			return $statement->get_result();
		}
		return false;
	}
	
	public function _optPrepareAndExecute($sql,array $values=null){
		$uid=Coroutine::getuid();
		if (! isset ( $this->statements [$uid] )) {
			\preg_match_all('/:([[:alpha:]]+)/', $sql,$params);
			$sql=\preg_replace('/:[[:alpha:]]+/','?',$sql);
			$instance=$this->dbs[$uid];
			$st=$instance->prepare ( $sql);
			$this->statements [$uid]= new SwooleStatement($this->dbs[$uid],$st,$params);
		}
		if ($this->statements [$uid]->execute($values)){
			return $this->statements [$uid]->get_result();
		}
		return false;
	}

	public function query($sql) {
		return $this->getInstance()->query($sql);
	}

	public function fetchColumn($statement, array $values = null, $columnNumber = null) {
		if($statement->execute()){
			$row=$statement->fetch();
			return (\is_numeric($columnNumber))?\array_values($row)[$columnNumber]:$row[$columnNumber];
		}
	}

	public function execute($sql) {
		$instance=$this->getInstance();
		$instance->query($sql);
		return $instance->affected_rows;
	}

	public function fetchOne($statement, array $values = null, $mode = null) {
		if ($statement->execute($values)){
			return $statement->fetch();
		}
		return false;
	}

	public function getFieldsInfos($tableName) {}

	public function bindValueFromStatement($statement, $parameter, $value) {
		$statement->bindValue($parameter,$value);
	}

	public function rollBack() {
		$this->getInstance()->rollback();
		$this->inTransaction=false;
	}

	public function getForeignKeys($tableName, $pkName, $dbName = null) {}

	public function beginTransaction() {
		$this->getInstance()->begin();
		$this->inTransaction=true;
	}

	public function statementRowCount($statement) {
		return $statement->affected_rows;
	}

	public function savePoint($level) {}

	public function executeStatement($statement, array $values = null) {
		return $statement->execute ( $values );
	}

	public function getPrimaryKeys($tableName) {}
	
	public function pool() {
		return $this->dbs[Coroutine::getuid()]=$this->connectionPool->get();
	}
	
	public function freePool($db) {
		$this->connectionPool->put($db);
		unset($this->dbs[Coroutine::getuid()]);
	}
	public function setPool($pool) {
		$this->connectionPool=$pool;
	}
	public function _getStatement(string $sql) {
		$uid = $sql.Coroutine::getuid();
		if (! isset ( $this->statements [$uid] )) {
			$this->statements [$uid] = $this->getStatement ( $sql );
		}
		return $this->statements [$uid];
	}
	
	protected function getInstance(){
		return $this->dbs[Coroutine::getuid()];
	}
	
	protected function getUid(){
		return Coroutine::getuid();
	}
}

