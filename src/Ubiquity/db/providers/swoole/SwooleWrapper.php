<?php
namespace Ubiquity\db\providers\swoole;

use Ubiquity\db\providers\AbstractDbWrapper;

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
	
	public function __construct($dbType = 'mysql') {
		$this->quote = '`';
	}
	
	public function queryColumn($sql, $columnNumber = null) {
		$stmt = $this->dbInstance->prepare($sql);
		if($stmt->execute()){
			$row=$stmt->fetch();
			return (\is_numeric($columnNumber))?\array_values($row)[$columnNumber]:$row[$columnNumber];
		}
	}

	public function getDSN(string $serverName, string $port, string $dbName, string $dbType = 'mysql') {
		return 'swoole.coroutine.mysql:dbname=' . $dbName . ';host=' . $serverName . ';charset=UTF8;port=' . $port;
	}
	public function fetchAllColumn($statement, array $values = null, $column = null) {
		$st=new SwooleStatement($this->dbInstance,$statement);
		return $st->fetchColumn($column??0);
	}

	public function ping() {
		return ($this->dbInstance && 1 === \intval ( $this->queryColumn( 'SELECT 1' ,0 ) ));
	}

	public function commit() {
		$this->dbInstance->commit();
		$this->inTransaction = true;
	}

	public function prepareStatement($sql) {
		$st=$this->dbInstance->prepare ( $sql );
		return new SwooleStatement($this->dbInstance,$st);
	}

	public function queryAll($sql, $fetchStyle = null) {
		return $this->dbInstance->query ( $sql );
	}

	public function releasePoint($level) {}

	public function lastInsertId() {
		return $this->dbInstance->insert_id;
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
		$st=$this->dbInstance->prepare ( $sql);
		return new SwooleStatement($this->dbInstance,$st,$params);
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

	public function query($sql) {
		return $this->dbInstance->query($sql);
	}

	public function fetchColumn($statement, array $values = null, $columnNumber = null) {
		if($statement->execute()){
			$row=$statement->fetch();
			return (\is_numeric($columnNumber))?\array_values($row)[$columnNumber]:$row[$columnNumber];
		}
	}

	public function execute($sql) {
		$this->dbInstance->query($sql);
		return $this->dbInstance->affected_rows;
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
		$this->dbInstance->rollback();
		$this->inTransaction=false;
	}

	public function getForeignKeys($tableName, $pkName, $dbName = null) {}

	public function beginTransaction() {
		$this->dbInstance->begin();
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
		return $this->dbInstance=$this->connectionPool->get();
	}
	
	public function freePool($db) {
		$this->connectionPool->put($db);
	}
	public function setPool($pool) {
		$this->connectionPool=$pool;
	}
	public function _getStatement(string $sql) {
		$uid = $this->connectionPool->getUid ( $sql );
		if (! isset ( $this->statements [$uid] )) {
			$this->statements [$uid] = $this->getStatement ( $sql );
		}
		return $this->statements [$uid];
	}
}

