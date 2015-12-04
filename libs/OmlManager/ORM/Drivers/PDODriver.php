<?php
/**
 * Created by Dmitri Russu. <dmitri.russu@gmail.com>
 * Date: 15.04.2014
 * Time: 21:06
 * ${NAMESPACE}${NAME}
 */
namespace OmlManager\ORM\Drivers;



class PDODriver implements DriverInterface, DriverTransactionInterface {

	const DRIVER_MYSQL = 'mysql';
	const DRIVER_MSSQL = 'mssql';
	const DRIVER_ORACLE = 'oci';

	/**
	 * @var DriversConfig
	 */
	private $config;

	/**
	 * @var \PDO
	 */
	private $driver;
	private $driverName;

	private static $_DATA_TYPES = array(
		1 => 'tinyint',
		2 => 'smallint',
		3 => 'int',
		4 => 'float',
		5 => 'double',
		7 => 'timestamp',
		8 => 'bigint',
		9 => 'mediumint',
		10 => 'date',
		11 => 'time',
		12 => 'datetime',
		13 => 'year',
		16 => 'bit',
		252 => 'string', //BLOB etc image
		253 => 'varchar',
		254 => 'char',
		246 => 'decimal'
	);

	/**
	 * @var \PDOStatement
	 */
	private $queryResult;

	public function __construct(DriverConfigInterface $config, $driverName = self::DRIVER_MYSQL) {

		$this->config = $config;
		$this->driverName = $driverName;
	}

	public function connect() {
		$options = array(
			\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
		);

		$dbName = ($this->config->getDataBaseName() ? "dbname={$this->config->getDataBaseName()}" : '');
		$this->driver = new \PDO("{$this->driverName}:host={$this->config->getDataBaseHost()};port={$this->config->getDataBasePort()};{$dbName}",
		$this->config->getDataBaseUser(),
		$this->config->getDataBasePassword(), $options);

		$this->driver->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
	}

	public function getConnection() {
		return $this->driver;
	}

	public function query($sql, array $preparedStatement) {
		$this->execute($sql, $preparedStatement);

		return $this;
	}

	public function fetchAll($object = 'stdClass') {
		if ($object === \PDO::FETCH_ASSOC) {
			return $this->queryResult->fetchAll(\PDO::FETCH_CLASS);
		}

		return (empty($this->queryResult) ? false :$this->queryResult->fetchAll(\PDO::FETCH_CLASS, $object));
	}

	public function fetchOne($object = 'stdClass') {
		if ($object === \PDO::FETCH_ASSOC) {
			return $this->queryResult->fetch(\PDO::FETCH_CLASS);
		}

		return (empty($this->queryResult) ? false : $this->queryResult->fetchObject($object));
	}

	/**
	 * @param $query
	 * @param array $prepare
	 * @return \PDOStatement
	 */
	public function execute($query, array $prepare) {
		$this->queryResult = $this->driver->prepare( $query );
		$this->queryResult->execute( $prepare );

		return $this->queryResult;
	}

	/**
	 * Get DataBase Tables
	 * @return mixed
	 */
	public function getAllEntitiesName() {
		$sql = "SELECT TABLE_NAME AS table_name FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = :table_schema";

		$query = $this->query($sql, array(':table_schema' => $this->config->getDataBaseName()));
		$result = $query->fetchAll();

		return $result;
	}

	/**
	 * Get Entity Foreign Keys
	 * @param $tableName
	 * @param bool $byReferencedTable
	 * @return mixed
	 */
	public function getAllForeignKey($tableName, $byReferencedTable = true) {

		$where = 'TABLE_NAME = :referenced_table_name AND REFERENCED_TABLE_NAME IS NOT NULL';

		if ( $byReferencedTable ) {
			$where = 'REFERENCED_TABLE_NAME = :referenced_table_name';
		}

		$sql = "SELECT
					TABLE_NAME,
					CONSTRAINT_NAME,
					COLUMN_NAME,
					REFERENCED_COLUMN_NAME,
					REFERENCED_TABLE_NAME
				FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
				WHERE {$where} AND CONSTRAINT_SCHEMA = :schema_name";

		$query = $this->driver->query($sql,
			array(':referenced_table_name' => $tableName, ':schema_name' => $this->config->getDataBaseName()));

		$result = $query->fetchAll();
		return $result;
	}

	/**
	 *
	 * @param $entityName
	 * @param $fieldName
	 * @return mixed
	 */
	public function getEntityRelation($entityName, $fieldName) {
		$sql = "SELECT COLUMN_KEY FROM INFORMATION_SCHEMA.COLUMNS
					WHERE
						TABLE_NAME = :table_name AND
						TABLE_SCHEMA = :table_schema AND
						COLUMN_NAME = :column_name";

		$query = $this->driver->query($sql,
			array(':table_name' => $entityName, ':table_schema' => $this->config->getDataBaseName(),
				':column_name' =>$fieldName
			));

		$result = $query->fetchObject();
		return $result;
	}

	/**
	 * get Entity Info
	 * @param $tableName
	 * @return mixed
	 */
	public function getEntityInfo($tableName) {
		$query = $this->driver->query("DESCRIBE {$this->config->getDataBaseName()}.$tableName", array());

		return $query->fetchAll();
	}

	public function fetchFields() {

	}

	public function getDataTypes() {

		return self::$_DATA_TYPES;
	}

	public function beginTransaction() {

		$this->driver->setAttribute(\PDO::ATTR_AUTOCOMMIT, false);

		return $this->driver->beginTransaction();
	}

	public function commitTransaction() {
		$result = $this->driver->commit();

		$this->driver->setAttribute(\PDO::ATTR_AUTOCOMMIT, true);

		return $result;
	}

	public function rollbackTransaction() {
		$result = $this->driver->rollBack();

		$this->driver->setAttribute(\PDO::ATTR_AUTOCOMMIT, true);

		return $result;
	}

	public function __destruct() {

		unset($this->driver);
	}
} 