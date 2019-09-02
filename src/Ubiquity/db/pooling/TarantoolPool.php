<?php
namespace Ubiquity\db\pooling;


class TarantoolPool extends AbstractConnectionPool{
	
	private $dsn;

	protected function createDbInstance(){
		return \Tarantool\Client\Client::fromDsn ($this->dsn);
	}
	
	protected function setDbParams($dbConfig) {
		$opts='';
		\extract($dbConfig);
		if($user!=null){
			$infoUser=$user;
			if($password!=null){
				$infoUser.=':'.$password;
			}
			$serverName=$infoUser.'@'.$serverName??'127.0.0.1';
		}
		if(\count($options)>0){
			$opts='?'.\http_build_query($options);
		}
		$this->dsn='tcp://'.$serverName.':'. ($port??3301).$opts;
	}
}

