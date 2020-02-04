<?php
namespace Ubiquity\db\providers\swoole;

use Ubiquity\db\providers\pdo\PDOWrapper;
use Ubiquity\db\providers\TraitHasPool;
use Ubiquity\db\pooling\SwoolePDOConnectionPool;

class SwoolePDOWrapper extends PDOWrapper {
	use TraitHasPool;

	public function getPoolClass() {
		return SwoolePDOConnectionPool::class;
	}

	public function lastInsertId() {
		return $this->getInstance()->lastInsertId();
	}

	public function prepareStatement(string $sql) {
		return $this->getInstance()->prepare($sql);
	}

	public function getStatement($sql) {
		$st = $this->getInstance()->prepare($sql);
		$st->setFetchMode(\PDO::FETCH_ASSOC);
		return $st;
	}

	public function execute($sql) {
		return $this->getInstance()->exec($sql);
	}

	public function query(string $sql) {
		return $this->getInstance()->query($sql);
	}

	public function queryAll(string $sql, int $fetchStyle = null) {
		return $this->getInstance()
			->query($sql)
			->fetchAll($fetchStyle);
	}

	public function queryColumn(string $sql, int $columnNumber = null) {
		return $this->getInstance()
			->query($sql)
			->fetchColumn($columnNumber);
	}

	public function getTablesName() {
		$query = $this->getInstance()->query('SHOW TABLES');
		return $query->fetchAll(\PDO::FETCH_COLUMN);
	}

	public function inTransaction() {
		return $this->getInstance()->inTransaction();
	}

	public function commit() {
		return $this->getInstance()->commit();
	}

	public function rollBack() {
		return $this->getInstance()->rollBack();
	}

	public function beginTransaction() {
		return $this->getInstance()->beginTransaction();
	}

	public function savePoint($level) {
		$this->getInstance()->exec('SAVEPOINT LEVEL' . $level);
	}

	public function releasePoint($level) {
		$this->getInstance()->exec('RELEASE SAVEPOINT LEVEL' . $level);
	}

	public function rollbackPoint($level) {
		$this->getInstance()->exec('ROLLBACK TO SAVEPOINT LEVEL' . $level);
	}

	public function ping() {
		return ($this->getInstance() != null) && (1 === \intval($this->queryColumn('SELECT 1', 0)));
	}

	public function getPrimaryKeys($tableName) {
		$fieldkeys = array();
		$recordset = $this->getInstance()->query("SHOW KEYS FROM `{$tableName}` WHERE Key_name = 'PRIMARY'");
		$keys = $recordset->fetchAll(\PDO::FETCH_ASSOC);
		foreach ($keys as $key) {
			$fieldkeys[] = $key['Column_name'];
		}
		return $fieldkeys;
	}

	public function getForeignKeys($tableName, $pkName, $dbName = null) {
		$recordset = $this->getInstance()->query("SELECT *
												FROM
												 information_schema.KEY_COLUMN_USAGE
												WHERE
												 REFERENCED_TABLE_NAME = '" . $tableName . "'
												 AND REFERENCED_COLUMN_NAME = '" . $pkName . "'
												 AND TABLE_SCHEMA = '" . $dbName . "';");
		return $recordset->fetchAll(\PDO::FETCH_ASSOC);
	}

	public function getFieldsInfos($tableName) {
		$fieldsInfos = array();
		$recordset = $this->getInstance()->query("SHOW COLUMNS FROM `{$tableName}`");
		$fields = $recordset->fetchAll(\PDO::FETCH_ASSOC);
		foreach ($fields as $field) {
			$fieldsInfos[$field['Field']] = [
				"Type" => $field['Type'],
				"Nullable" => $field["Null"]
			];
		}
		return $fieldsInfos;
	}

	public function quoteValue($value, $type = null) {
		return $this->getInstance()->quote($value, $type);
	}
}

