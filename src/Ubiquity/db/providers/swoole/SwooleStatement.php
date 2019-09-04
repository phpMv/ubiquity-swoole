<?php
namespace Ubiquity\db\providers\swoole;

use Swoole\Coroutine\MySQL;
use Swoole\Coroutine\MySQL\Statement;

/**
 * Represents a Swoole Mysql statement (for compatibility reasons with other DBMS).
 *
 * Ubiquity\db\providers\swoole$SwooleStatement
 * This class is part of Ubiquity
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 *
 */
class SwooleStatement {
	
	/**
	 * @var MySQL
	 */
	private $dbInstance;
	
	/**
	 * @var Statement
	 */
	protected $statement;
	
	/**
	 * @var array
	 */
	protected $preparedParams;

	/**
	 * @var array
	 */
	protected $bindParams=[];
	
	protected $resultSet=[];
	
	protected $timeout=1.000;
	
	protected function replaceNamedParams($values){
		$params=\count($this->preparedParams[1]??[])>0?$this->preparedParams[1]:null;
		if($params!=null){
			$result=[];
			foreach ($params as $param){
				$result[]=$values[$param];
			}
			return $result;
		}
		return $values;
	}
	
	public function __construct(MySQL $dbInstance, Statement $statement,$params=null) {
		$this->dbInstance = $dbInstance;
		$this->statement = $statement;
		$this->preparedParams=$params;
	}
	
	public function bindValue($parameter, $value){
		if (!\is_string($parameter) && !\is_int($parameter)) {
			return false;
		}
		$parameter = \ltrim($parameter, ':');
		$this->bindParams[$parameter] = $value;
		return true;
	}
	
	/**
	 * Executes an SQL Update statement
	 *
	 * @param array $params
	 */
	public function execute(?array $values = null, ?float $timeout = null){
		if($this->statement){
			$params=$values??$this->bindParams;
			if($params!==null){
				$values=$this->replaceNamedParams($values);
			}
			$r = $this->statement->execute($values, $timeout ?? $this->timeout);
			$this->resultSet = ($ok = $r !== false) ? $r : [];
			$this->bindParams=[];
			return $ok;
		}
		return false;
	}
	
	public function get_result(){
		return $this->resultSet;
	}
	
	public function fetchAll(){
		return $this->resultSet;
	}
	
	public function fetchColumn($columnNumber=0){
		return \array_column(\is_numeric($columnNumber) ? $this->getBoth() : $this->resultSet,
			$columnNumber);
	}
	
	private function getBoth(){
		$temp = [];
		foreach ($this->resultSet as $row) {
			$row_set = [];
			$i = 0;
			foreach ($row as $key => $value) {
				$row_set[$key] = $value;
				$row_set[$i++] = $value;
			}
			$temp[] = $row_set;
		}
		return $temp;
	}
}

