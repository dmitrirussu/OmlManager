<?php
/**
 * Created by Dumitru Russu.
 * Date: 01.05.2014
 * Time: 15:47
 * OmlManager\ORM\Console${NAME}
 */

namespace OmlManager\ORM\SchemaEntitiesGenerator;

use OmlManager\ORM\Drivers\DriversConfig;
use OmlManager\ORM\Drivers\DriverManagerConnection;


abstract class Generator {

	public $dataBaseName;
	public $schemaName;
	public $realPath;
	public $schemaNameSpace = '';

	/**
	 * @var DriversConfig
	 */
	private $driverConfig;

	/**
	 * @var \OmlManager\ORM\Drivers\DriverInterface
	 */
	private $driver;

	protected $driverConfName;
	protected $driverName;

	public function __construct($dbConfName, $path = null, $nameSpace = null) {
		$this->driverConfig = new DriversConfig($dbConfName);

		$this->dataBaseName = $this->driverConfig->getDataBaseName();
		$this->schemaName = implode(array_map('ucfirst', explode('_', strtolower($this->dataBaseName))));
		$this->schemaNameSpace = $nameSpace;
		$this->driverConfName = $this->driverConfig->getDriverConfName();
		$this->driverName = $this->driverConfig->getDataBaseDriverName();

		$this->realPath = ($path ? $path : realpath(__DIR__));

		$driverManagerConnection = new DriverManagerConnection($this->driverConfig);
		$this->driver = $driverManagerConnection->getDriver();
	}

	abstract public function generateSchemaEntities();
	abstract protected function createPackageClass();

	abstract public function generateEntity($entityName);
	abstract protected function createModelEntityClass($entityName);
	abstract protected function createStoreEntityClass($entityName);

	/**
	 * Get DataBase Tables
	 * @return mixed
	 */
	protected function getAllEntitiesName() {
		return $this->driver->getAllEntitiesName();
	}

	/**
	 * Get Entity Foreign Keys
	 * @param $tableName
	 * @param bool $byReferencedTable
	 * @return mixed
	 */
	protected function getAllForeignKey($tableName, $byReferencedTable = true) {
		return $this->driver->getAllForeignKey($tableName, $byReferencedTable);
	}

	/**
	 *
	 * @param $entityName
	 * @param $fieldName
	 * @return mixed
	 */
	protected function getEntityRelation($entityName, $fieldName) {
		return $this->driver->getEntityRelation($entityName, $fieldName);
	}

	/**
	 * get Entity Info
	 * @param $tableName
	 * @return mixed
	 */
	protected function getEntityInfo($tableName) {
		return $this->driver->getEntityInfo($tableName);
	}


	protected function createPackageFolder() {

		//create package Of DataBase entities
		if ( !is_dir($this->realPath . '/' . $this->schemaName.'Package/Model/') ) {
			//create Folder of Models
			mkdir($this->realPath . '/' . $this->schemaName.'Package/Model/', 0777, true);
		}

		//create Folder of Storing Models Methods
		if ( !is_dir($this->realPath . '/' . $this->schemaName.'Package/Store/') ) {

			mkdir($this->realPath . '/' . $this->schemaName.'Package/Store/', 0777, true);
		}
	}

	/**
	 * @param $type
	 * @return bool|mixed
	 */
	protected function parseFiledType($type) {
		preg_match('/[a-z]+/', $type, $typeResult);

		return $typeResult ? (string)end($typeResult) : false;
	}

	/**
	 * @param $type
	 * @return bool|mixed
	 */
	protected function parseFieldLength($type) {
		preg_match('/[0-9]+/', $type, $length);

		return $length ? (int)end($length) : false;
	}
}

class GeneratorException extends \Exception {

}