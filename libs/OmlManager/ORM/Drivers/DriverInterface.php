<?php
/**
 * Created by Dmitri Russu. <dmitri.russu@gmail.com>
 * Date: 15.04.2014
 * Time: 21:06
 * ${NAMESPACE}${NAME} 
 */

namespace OmlManager\ORM\Drivers;

interface DriverInterface {
	public function connect();

	/**
	 * @param $sql
	 * @param $preparedStatement
	 * @return DriverInterface
	 */
	public function query($sql, array $preparedStatement);
	public function fetchAll($object = 'stdClass');
	public function fetchOne($object = 'stdClass');

	/**
	 * @param $query
	 * @param array $prepare
	 * @return \PDOStatement
	 */
	public function execute($query, array $prepare);
	public function fetchFields();

	public function getDataTypes();
	public function getConnection();

	public function getAllEntitiesName();
	public function getAllForeignKey($tableName, $byReferencedTable = true);
	public function getEntityRelation($entityName, $fieldName);
	public function getEntityInfo($tableName);
}
