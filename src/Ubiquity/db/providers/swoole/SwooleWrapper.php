<?php
namespace Ubiquity\db\providers\swoole;

use Ubiquity\db\providers\AbstractDbWrapper;
use Ubiquity\db\providers\TraitHasPool;

/**
 * Ubiquity\db\providers\swoole$SwooleWrapper
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 * @property \Swoole\Coroutine\MySQL $dbInstance
 *
 */
class SwooleWrapper extends AbstractDbWrapper {
	use TraitHasPool;

	private $inTransaction = false;

	private static $quotes = [
		'mysql' => '`',
		'pgsql' => ''
	];

	public function __construct($dbType = 'mysql') {
		$this->quote = self::$quotes[$dbType] ?? '';
	}

	public function getPoolClass() {
		return \Ubiquity\db\pooling\ConnectionPool::class;
	}

	public function queryColumn($sql, $columnNumber = null) {
		$stmt = $this->getInstance()->prepare($sql);
		if ($stmt->execute()) {
			$row = $stmt->fetch();
			return (\is_numeric($columnNumber)) ? \array_values($row)[$columnNumber] : $row[$columnNumber];
		}
	}

	public function getDSN(string $serverName, string $port, string $dbName, string $dbType = 'mysql') {
		return 'swoole.coroutine.mysql:dbname=' . $dbName . ';host=' . $serverName . ';charset=UTF8;port=' . $port;
	}

	public function fetchAllColumn($statement, array $values = null, $column = null) {
		$st = new SwooleStatement($this->getInstance(), $statement);
		return $st->fetchColumn($column ?? 0);
	}

	public function ping() {
		return (1 === \intval($this->queryColumn('SELECT 1', 0)));
	}

	public function commit() {
		$this->getInstance()->commit();
		$this->inTransaction = true;
	}

	public function prepareStatement($sql) {
		$instance = $this->getInstance();
		$st = $instance->prepare($sql);
		return new SwooleStatement($instance, $st);
	}

	public function queryAll($sql, $fetchStyle = null) {
		return $this->getInstance()->query($sql);
	}

	public function releasePoint($level) {}

	public function lastInsertId($name = null) {
		return $this->getInstance()->insert_id;
	}

	public function nestable() {
		return false;
	}

	public static function getAvailableDrivers() {
		return [
			'mysql'
		];
	}

	public function rollbackPoint($level) {}

	public function getTablesName() {}

	public function getStatement($sql) {
		\preg_match_all('/:([[:alpha:]]+)/', $sql, $params);
		$sql = \preg_replace('/:[[:alpha:]]+/', '?', $sql);
		$instance = $this->getInstance();
		$st = $instance->prepare($sql);
		return new SwooleStatement($instance, $st, $params);
	}

	public function inTransaction() {
		return $this->inTransaction;
	}

	public function fetchAll($statement, array $values = null, $mode = null) {
		if ($statement->execute($values)) {
			return $statement->get_result();
		}
		return false;
	}

	public function _optPrepareAndExecute($sql, array $values = null, $one = false) {
		$statement = $this->_getStatement($sql);
		if ($statement->execute($values)) {
			$res= $statement->get_result();
			if($one){
				return \current($res);
			}
			return $res;
		}
		return false;
	}
	
	public function _optExecuteAndFetch($statement, array $values = null, $one = false) {
		if ($statement->execute($values)) {
			$res= $statement->get_result();
			if($one){
				return \current($res);
			}
			return $res;
		}
		return false;
	}

	public function query($sql) {
		return $this->getInstance()->query($sql);
	}

	public function fetchColumn($statement, array $values = null, $columnNumber = null) {
		if ($statement->execute()) {
			$row = $statement->fetch();
			return (\is_numeric($columnNumber)) ? \array_values($row)[$columnNumber] : $row[$columnNumber];
		}
	}

	public function execute($sql) {
		$instance = $this->getInstance();
		$instance->query($sql);
		return $instance->affected_rows;
	}

	public function fetchOne($statement, array $values = null, $mode = null) {
		if ($statement->execute($values)) {
			return $statement->fetch();
		}
		return false;
	}

	public function getFieldsInfos($tableName) {}

	public function bindValueFromStatement($statement, $parameter, $value) {
		$statement->bindValue($parameter, $value);
	}

	public function rollBack() {
		$this->getInstance()->rollback();
		$this->inTransaction = false;
	}

	public function getForeignKeys($tableName, $pkName, $dbName = null) {}

	public function beginTransaction() {
		$this->getInstance()->begin();
		$this->inTransaction = true;
	}

	public function statementRowCount($statement) {
		return $statement->affected_rows;
	}

	public function savePoint($level) {}

	public function executeStatement($statement, array $values = null) {
		return $statement->execute($values);
	}

	public function getPrimaryKeys($tableName) {}

	public function getRowNum(string $tableName, string $pkName, string $condition): int {}

	public function groupConcat(string $fields, string $separator): string {}
}

