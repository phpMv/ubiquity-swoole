<?php
namespace Ubiquity\orm;

use Ubiquity\db\SqlUtils;
use Ubiquity\controllers\Startup;
use Ubiquity\db\providers\swoole\ConnectionPool;
use Ubiquity\db\SwooleDatabase;

class SwooleDAO extends DAO {
	/**
	 * @var ConnectionPool
	 */
	private static $pool;
	
	public static function initPooling(&$config, $offset = null){
		$db = $offset ? ($config ['database'] [$offset] ?? ($config ['database'] ?? [ ])) : ($config ['database'] ['default'] ?? $config ['database']);
		self::$pool=new ConnectionPool($db ['type'], $db ['serverName'] ?? '127.0.0.1', $db ['port'] ?? 3306, $db ['user'] ?? 'root', $db ['password'] ?? '', $db ['dbName']);
		
	}
	/**
	 * Returns the database instance defined at $offset key in config
	 *
	 * @param string $offset
	 * @return \Ubiquity\db\Database
	 */
	public static function getDatabase($offset = 'default') {
		$uid=self::uid();
		if (! isset ( self::$db [$offset][$uid] )) {
			self::startDatabase ( Startup::$config, $offset );
		}
		SqlUtils::$quote = '`';
		return self::$db [$offset][$uid];
	}
	
	/**
	 * Establishes the connection to the database using the $config array
	 *
	 * @param array $config the config array (Startup::getConfig())
	 */
	public static function startDatabase(&$config, $offset = null) {
		$db = $offset ? ($config ['database'] [$offset] ?? ($config ['database'] ?? [ ])) : ($config ['database'] ['default'] ?? $config ['database']);
		if ($db ['dbName'] !== '') {
			$uid=self::uid();
			self::$db [$offset][$uid] = new SwooleDatabase( $uid,self::$pool, $db ['type'], $db ['dbName'], $db ['serverName'] ?? '127.0.0.1', $db ['port'] ?? 3306, $db ['user'] ?? 'root', $db ['password'] ?? '', $db ['options'] ?? [ ], $db ['cache'] ?? false );
		}
	}
	
	/**
	 * gets a new DbConnection from pool
	 *
	 * @param string $offset
	 * @return mixed
	 */
	public static function pool($offset = 'default') {
		$uid=self::uid();
		if (! isset ( self::$db [$offset][$uid] )) {
			self::startDatabase ( Startup::$config, $offset );
		}
		return self::$db [$offset][$uid]->pool ();
	}
	
	public static function freePool($db){
		self::$pool->put($db);
	}
	
	private static function uid(){
		return \Swoole\Coroutine::getuid();
	}
}

