<?php
/**
 * Created by Dmitri Russu. <dmitri.russu@gmail.com>
 * Date: 15.04.2014
 * Time: 21:06
 * ${NAMESPACE}${NAME} 
 */
namespace OmlManager\ORM\Drivers;



use OmlManager\ORM\Models\Reader;

class MongoDriver implements DriverInterface, DriverTransactionInterface {

	const DRIVER_MONGODB = 'mongodb';

	private static $_DATA_TYPES = array(
		1 => 'double',
		2 => 'string',
		3 => 'object',
		4 => 'array',
		5 => 'binary',
		6 => 'undefined', //deprecated
		7 => 'object_id',
		8 => 'boolean',
		9 => 'date',
		10 => 'null',
		11 => 'regular_expression',
		13 => 'javascript',
		14 => 'symbol',
		15 => 'javascript_scope',
		16 => 'int_32',
		17 => 'timestamp',
		18 => 'int_64',
		255 => 'min_key',
		127 => 'max_key'
	);

	/**
	 * @var DriversConfig
	 */
	private $config;

	/**
	 * @var \MongoDB
	 */
	private $driver;
	private $driverName = self::DRIVER_MONGODB;

	/**
	 * @var \MongoCollection
	 */
	private $queryResult;

	public function __construct(DriverConfigInterface $config, $driverName = self::DRIVER_MONGODB) {
		$this->config = $config;
		$this->driverName = $driverName;
	}

	/**
	 *
	 */
	public function connect() {

		$options = array(
			'connect' => true,
		);

		if ( $this->config->getDataBaseUser() ) {
			$options['username'] = $this->config->getDataBaseUser();
		}

		if ( $this->config->getDataBasePassword() ) {
			$options['password'] = $this->config->getDataBaseUser();
		}

		$this->driver = new \MongoClient(
			"mongodb://{$this->config->getDataBaseHost()}:{$this->config->getDataBasePort()}",
			$options
		);

		$this->driver = $this->driver->selectDB($this->config->getDataBaseName());
	}

	/**
	 * @return \MongoClient
	 */
	public function getConnection() {
		return $this->driver;
	}

	/**
	 * @param $className
	 * @param array $preparedStatement
	 * @return \MongoCollection
	 */
	public function query($collectionName, array $preparedStatement) {
		return $this->execute($collectionName, $preparedStatement);
	}

	public function fetchAll($collectionName = '') {
		$this->execute($collectionName, array());
		return $this->queryResult->find();
	}

	public function fetchOne($collectionName = '') {
		$this->execute($collectionName, array());
		return $this->queryResult->findOne();
	}

	public function getResult() {
		return $this->queryResult;
	}

	/**
	 * @param $className
	 * @param array $prepare
	 * @return \MongoCollection
	 * @throws \OmlManager\ORM\Models\ReaderException
	 */
	public function execute($collectionName, array $prepare) {
		return $this->queryResult = $this->driver->{$collectionName};
	}


	/**
	 * Get DataBase Tables
	 * @return mixed
	 */
	public function getAllEntitiesName() {
		$results = $this->driver->listCollections();

		$entities = array_map(function($entity){
			/**
			 * @var $entity \MongoCollection
			 */
			$table = new \stdClass();
			$table->table_name = $entity->getName();
			return $table;
		}, $results);

		return $entities;
	}

	/**
	 * Get Entity Foreign Keys
	 * @param $tableName
	 * @param bool $byReferencedTable
	 * @return mixed
	 */
	public function getAllForeignKey($tableName, $byReferencedTable = true) {
		//todo
	}

	public function getEntityInfo($tableName) {
		$table = $this->driver->selectCollection($tableName);


		$fields = $table->findOne();
		if ( empty($fields) ) {
			return array();
		}

		$fieldsResult = array();

		foreach($fields AS $field => $value) {
			foreach($this->getDataTypes() AS $key => $type) {
				try {
					$book = $table->findOne(array($field => array('$type' => $key)));
					if ( $book ) {
						$object = new \stdClass();
						$object->Field = $field;
						$object->Type = $type;
						$object->Extra = ($field === '_id' ? 'auto_increment' : '');
						$object->Key = ($field === '_id' ? 'PRI' : '');
						$fieldsResult[] = $object;
					}
				}
				catch(\Exception $e) {

				}
			}
		}
		return $fieldsResult;
	}

	/**
	 *
	 * @param $entityName
	 * @param $fieldName
	 * @return mixed
	 */
	public function getEntityRelation($entityName, $fieldName) {
		//todo
	}



	public function fetchFields() {
		return array();
	}

	public function getDataTypes() {

		return self::$_DATA_TYPES;
	}

	public function beginTransaction() {

		return true;
	}

	public function commitTransaction() {
		return true;
	}

	public function rollbackTransaction() {
		return true;
	}

	public function __destruct() {

		unset($this->driver);
	}
}