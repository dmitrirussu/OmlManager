<?php
/**
 * Created by Dmitri Russu. <dmitri.russu@gmail.com>
 * Date: 22.04.2014
 * Time: 21:30
 * OmlManager\ORM\Models${NAME}
 */

namespace OmlManager\ORM\Models;

use OmlManager\ORM\Query\Types\ValueTypes;

class Reader implements ReaderInterface {

	private $model;

	/**
	 * @var \ReflectionProperty
	 */
	private $modelInfo;

	/**
	 * @var \ReflectionClass
	 */
	private $reflectionModelClass;

	/**
	 * @var \ReflectionProperty
	 */
	private $property;
	private $modelPrimaryKey;

	public function __construct($modelObject) {

		if ( !is_object($modelObject) ) {

			throw new ReaderException('Missing model Object');
		}

		$this->model = $modelObject;

		$parentReflection = $this->reflectionModelClass = new \ReflectionClass($this->model);
		$this->modelInfo = $this->readTokensFromDocComment($this->reflectionModelClass->getDocComment());

		if ( !isset($this->reflectionModelClass->getParentClass()->name) ) {

			throw new ReaderException('Missing parent class for Model ' . $this->reflectionModelClass->getName());
		}

		if( strpos($this->reflectionModelClass->getParentClass()->name, '\\Store\\') !== false) {
			$parentReflection = new \ReflectionClass($this->reflectionModelClass->getParentClass()->name);
		}

		$parentClass = $parentReflection->getParentClass()->name;

		$parentClass = new PackageReader(new $parentClass);

		if ( empty($parentClass) ) {

			throw new ReaderException('Missing parent class for Model ' . $this->reflectionModelClass->getName());
		}

		$this->modelInfo = array_merge($this->modelInfo, $parentClass->getInfo());

		if ( empty($this->modelInfo) ) {

			throw new ReaderException('Missing table name info for Model ' . $this->reflectionModelClass->getName());
		}
	}

	/**
	 * Get Model
	 * @return mixed
	 */
	public function getModel() {

		return $this->model;
	}

	/**
	 * Get Model Primary Key
	 * @return mixed
	 */
	public function getModelPrimaryKey() {

		$properties = $this->getModelPropertiesTokens();


		foreach($properties AS $property) {
			if ( isset($property['primary_key'])) {

				$this->modelPrimaryKey = $property['field'];
				break;
			}
		}

		return $this->modelPrimaryKey;
	}

	/**
	 * Get Model Primary Key Value
	 * @return mixed
	 */
	public function getModelPrimaryKeyValue() {

		$reflector = new \ReflectionObject($this->getModel());
		$reflectorProperty = $reflector->getProperty($this->getModelPrimaryKey());
		$reflectorProperty->setAccessible(true);
		$propertyValue = $reflectorProperty->getValue($this->getModel());


		return $propertyValue;
	}

	/**
	 * Set Model Primary Key Value
	 * @param $value
	 * @return $this
	 */
	public function setModelPrimaryKeyValue($value) {

		$reflector = new \ReflectionObject($this->getModel());

		if (  $reflector->hasProperty($this->getModelPrimaryKey()) ) {
			$reflectorProperty = $reflector->getProperty($this->getModelPrimaryKey());
			$reflectorProperty->setAccessible(true);
			$reflectorProperty->setValue($this->getModel(), $value);
		}

		return $this;
	}

	/**
	 * Get Model Table Name
	 * @return string
	 * @throws ReaderException
	 */
	public function getModelTableName() {

		if ( !isset($this->modelInfo['table']) ) {

			throw new ReaderException('Missing table name for Model ' . $this->reflectionModelClass->getName());
		}

		return trim($this->modelInfo['table']);
	}

	/**
	 * get Model DataBase Name
	 * @return string
	 * @throws ReaderException
	 */
	public function getModelDataBaseName() {
		if ( !isset($this->modelInfo['database']) ) {

			throw new ReaderException('Missing database name for Model ' . $this->reflectionModelClass->getName());
		}

		return trim($this->modelInfo['database']);
	}

	/**
	 * Get Model Driver Conf Name
	 * @return string
	 * @throws ReaderException
	 */
	public function getModelDataDriverConfName() {
		if ( !isset($this->modelInfo['driver_conf']) ) {

			throw new ReaderException('Missing database driver_conf for Model' . $this->reflectionModelClass->getName());
		}

		return trim($this->modelInfo['driver_conf']);
	}

	/**
	 * Get Model Properties
	 * @return \ReflectionProperty[]
	 * @throws ReaderException
	 */
	public function getModelProperties() {
		if ( !($properties = $this->reflectionModelClass->getProperties()) ) {

			throw new ReaderException('Missing properties for Model -> ' . $this->reflectionModelClass->getName());
		}

		return $properties;
	}

	/**
	 * Get Model Properties
	 * @return array
	 * @throws ReaderException
	 */
	public function getModelPropertiesTokens() {

		if ( !($properties = $this->reflectionModelClass->getProperties()) ) {
			throw new ReaderException('Missing properties for Model -> ' . $this->reflectionModelClass->getName());
		}

		$propertyTokens = array();

		foreach($properties as $property) {
			$this->property = new \ReflectionProperty($this->model, $property->name);

			$propertyTokens[] = $this->getPropertyTokens();
		}

		return $propertyTokens;
	}

	/**
	 * Get Properties Tokens
	 * @return array
	 * @throws ReaderException
	 */
	private function getPropertyTokens() {

			if ( !is_object($this->property) ) {
				throw new ReaderException('Missing property object');
			}
			$tokens = $this->readTokensFromDocComment($this->property->getDocComment());

			if ( empty($tokens) ) {
				throw new ReaderException('Missing property \''. $this->property->getName() .
					'\' info, Model ' .
					$this->reflectionModelClass->getName());
			}

			if ( !isset($tokens['field']) ) {

				throw new ReaderException('Missing \'field\', property \''. $this->property->getName() .
					'\', Model ' .
					$this->reflectionModelClass->getName());

			}

			if( !isset($tokens['type']) ) {

				throw new ReaderException('Missing \'type\', property \''. $this->property->getName() .
					'\', Model ' .
					$this->reflectionModelClass->getName());
			}

			$inArray = in_array($tokens['type'], array(ValueTypes::VALUE_TYPE_CHAR, ValueTypes::VALUE_TYPE_VARCHAR, ValueTypes::VALUE_TYPE_INT));

			if( isset($tokens['type']) && (!isset($tokens['length']) || ($tokens['length'] <= 0)) && $inArray) {

				throw new ReaderException('Missing \'length\', property \''. $this->property->getName() .
					'\', Model ' .
					$this->reflectionModelClass->getName());
			}

		return $tokens;
	}

	/**
	 * Read tokens from Doc Comment
	 * @param $docComment
	 * @return array
	 * @throws \Exception
	 */
	private function readTokensFromDocComment($docComment) {

		$docComment =  trim(preg_replace('/[=,}{]/', ' ', $docComment));
		preg_match_all('/@([a-zA-Z_]+)\s+(.*?)\ /s', $docComment, $matches);

		if ( !isset($matches[1]) || !isset($matches[2]) ) {

			throw new ReaderException('Missing tokens');
		}

		$tokens = array_combine($matches[1], $matches[2]);

		return $tokens;
	}

	/**
	 * get Value By Field Name
	 * @param $fieldName
	 * @return mixed
	 */
	public function getValueByFieldName($fieldName) {
		$fieldName = trim($fieldName);
		$reflector = new \ReflectionObject($this->model);
		$reflectorProperty = $reflector->getProperty($fieldName);
		$reflectorProperty->setAccessible(true);
		$propertyValue = $reflectorProperty->getValue($this->model);

		return $propertyValue;
	}

	/**
	 * Set Model Field value
	 * @param $fields
	 * @return mixed
	 */
	public function setModelFieldsValues($fields) {

		foreach ($fields as $fieldName => $value) {

			$fieldName = trim($fieldName);
			$reflector = new \ReflectionObject($this->model);

			if ( $reflector->hasProperty($fieldName)) {
				$reflectorProperty = $reflector->getProperty($fieldName);
				$reflectorProperty->setAccessible(true);
				$reflectorProperty->setValue($this->model, trim($value));
			}
		}

		return $this;
	}

	public function __destruct() {
		unset($this->model);
		unset($this->property);
		unset($this->modelInfo);
		unset($this->modelPrimaryKey);
		unset($this->reflectionModelClass);
	}
}
