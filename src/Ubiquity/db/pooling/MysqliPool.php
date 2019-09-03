<?php
namespace Ubiquity\db\pooling;


class MysqliPool extends AbstractConnectionPool{
	
	private $config;

	protected function createDbInstance(){
		extract($this->config);
		$dbInstance = new \mysqli( $serverName??'127.0.0.1',$user??'root',$password??'',$dbName??'', $port??3306);
		$dbInstance->set_charset("utf8");
		if(is_array($options)){
			foreach ($options as $key=>$value){
				$dbInstance->set_opt($key, $value);
			}
		}
		return $dbInstance;
	}
	
	protected function setDbParams(&$dbConfig) {
		$this->config=$dbConfig;
	}
}

