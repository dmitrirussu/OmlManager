<?php
/**
 * Created by Dmitri Russu. <dmitri.russu@gmail.com>
 * Date: 21.04.2014
 * Time: 22:10
 * OmlManager\ORM\OMLQuery${NAME}
 */

namespace OmlManager\ORM\Query\OML;

use OmlManager\ORM\Drivers\DriverTransactionInterface;
use OmlManager\ORM\OmlORManager;
use OmlManager\ORM\Models\Reader;
use OmlManager\ORM\Query\Expression\Expression;
use OmlManager\ORM\Query\Expression\ExpressionInterface;

class OMLQueryManager implements OMLQueryManagerInterface, OMLQueryMangerOperationsInterface,
									OMLQueryManagerDeleteOperation, OMLQueryManagerBatchOperation, DriverTransactionInterface {

	private $model;

	public function __construct() {}

	/**
	 * @param $model
	 * @return $this|OMLQueryMangerOperationsInterface|OMLQueryManagerDeleteOperation|DriverTransactionInterface
	 * @throws OMLQueryManagerExceptions
	 */
	public function model($model) {

		if ( !is_object($model) ) {

			throw new OMLQueryManagerExceptions('Model cannot be string!');
		}
		elseif(is_array($model)) {

			throw new OMLQueryManagerExceptions('Model cannot be, array!');
		}

		$this->model = $model;

		return $this;
	}

	/**
	 * @param $id
	 * @return mixed
	 * @throws OMLQueryManagerExceptions
	 */
	public function fetchByPk($id) {

		if ( empty($this->model) ) {

			throw new OMLQueryManagerExceptions('Model Cannot be empty');
		}

		if (empty($id)) {

			throw new OMLQueryManagerExceptions('Primary Key value cannot be empty');
		}

		$reader = new Reader($this->model);

		$exp = new Expression();
		$exp->field($reader->getModelPrimaryKey())->equal($id);

		return OmlORManager::dml()->select()->model($this->model)->expression($exp)->fetchOne();
	}

	/**
	 * @param $fieldName
	 * @param $value
	 * @param string $operator
	 * @throws OMLQueryManagerExceptions
	 * @return mixed
	 */
	public function fetchOne($fieldName, $value, $operator = '=') {

		if ( empty($this->model) ) {

			throw new OMLQueryManagerExceptions('Model Cannot be empty');
		}

		if (empty($fieldName) || empty($value) || empty($operator)) {

			throw new OMLQueryManagerExceptions('Primary Key value cannot be empty');
		}

		$exp = new Expression();
		$exp->field($fieldName)->operation($value, $operator);

		return OmlORManager::dml()->select()->model($this->model)->expression($exp)->fetchOne();
	}

	/**
	 * @param $fieldName
	 * @param $value
	 * @param string $operator
	 * @param array $limit
	 * @throws OMLQueryManagerExceptions
	 * @return mixed
	 */
	public function fetchAll($fieldName, $value, $operator = '=', array $limit = array()) {

		if ( empty($this->model) ) {

			throw new OMLQueryManagerExceptions('Model Cannot be empty');
		}

		if (empty($fieldName) || empty($value) || empty($operator)) {

			throw new OMLQueryManagerExceptions('Primary Key value cannot be empty');
		}

		$exp = new Expression();
		$exp->field($fieldName)->operation($value, $operator);

		$omeletteManager = OmlORManager::dml()->select()->model($this->model)->expression($exp);

		if ( $limit ) {
			$startOffset = (isset($limit[1]) ? $limit[0] : 0);
			$endOffset = (!isset($limit[1]) ? $limit[0] : $limit[1]);

			$omeletteManager->limit($startOffset, $endOffset);
		}

		return $omeletteManager->fetchAll();
	}

	/**
	 * @param ExpressionInterface $exp
	 * @throws OMLQueryManagerExceptions
	 * @return mixed
	 */
	public function fetchOneBy(ExpressionInterface $exp) {

		if ( empty($this->model) ) {

			throw new OMLQueryManagerExceptions('Model Cannot be empty');
		}

		return OmlORManager::dml()->select()->model($this->model)->expression($exp)->fetchOne();
	}

	/**
	 * @param ExpressionInterface $exp
	 * @param array $limit
	 * @throws OMLQueryManagerExceptions
	 * @return mixed
	 */
	public function fetchAllBy(ExpressionInterface $exp, array $limit = array()) {

		if ( empty($this->model) ) {

			throw new OMLQueryManagerExceptions('Model Cannot be empty');
		}

		$omeletteManager = OmlORManager::dml()->select()->model($this->model)->expression($exp);

		if ( $limit ) {

			$startOffset = (isset($limit[1]) ? $limit[0] : 0);
			$endOffset = (!isset($limit[1]) ? $limit[0] : $limit[1]);

			$omeletteManager->limit($startOffset, $endOffset);
		}

		return $omeletteManager->fetchAll();
	}

	/**
	 * @param array $limit
	 * @throws OMLQueryManagerExceptions
	 * @return mixed
	 */
	public function fetch(array $limit = array()) {

		if ( empty($this->model) ) {

			throw new OMLQueryManagerExceptions('Model Cannot be empty');
		}

		$omeletteManager = OmlORManager::dml()->select()->model($this->model)->expression(new Expression('1=1'));

		if ( $limit ) {
			$startOffset = (isset($limit[1]) ? $limit[0] : 0);
			$endOffset = (!isset($limit[1]) ? $limit[0] : $limit[1]);

			$omeletteManager->limit($startOffset, $endOffset);
		}

		return $omeletteManager->fetchAll();
	}

	/**
	 * @param array $models
	 * @throws OMLQueryManagerExceptions
	 * @return bool
	 */
	public function deleteBatch(array $models) {

		if ( empty($models) ) {

			throw new OMLQueryManagerExceptions('Param should be Array of Models');
		}

		return OmlORManager::dml()->delete()->models($models)->flush();
	}

	/**
	 * @throws OMLQueryManagerExceptions
	 * @return bool
	 */
	public function delete() {

		if ( empty($this->model) ) {

			throw new OMLQueryManagerExceptions('Model Cannot be empty');
		}

		return OmlORManager::dml()->delete()->model($this->model)->flush();
	}

	/**
	 * @param ExpressionInterface $exp
	 * @throws OMLQueryManagerExceptions
	 * @return bool
	 */
	public function deleteBy(ExpressionInterface $exp) {

		if ( empty($this->model) ) {

			throw new OMLQueryManagerExceptions('Model Cannot be empty');
		}

		return OmlORManager::dml()->delete()->model($this->model)->expression($exp)->flush();
	}

	/**
	 * @param $fieldName
	 * @param $value
	 * @param string $operator
	 * @throws OMLQueryManagerExceptions
	 * @return bool
	 */
	public function deleteByField($fieldName, $value, $operator = '=') {

		if ( empty($this->model) ) {

			throw new OMLQueryManagerExceptions('Model Cannot be empty');
		}

		$exp = new Expression();
		$exp->field($fieldName)->operation($value, $operator);

		return OmlORManager::dml()->delete()->model($this->model)->expression($exp)->flush();
	}

	/**
	 * @throws OMLQueryManagerExceptions
	 * @return bool
	 */
	public function flush() {

		if ( empty($this->model) ) {

			throw new OMLQueryManagerExceptions('Model Cannot be empty');
		}

		$modelReader = new Reader($this->model);

		//insert new Model
		if ( !$modelReader->getModelPrimaryKeyValue() ) {

			return OmlORManager::dml()->insert()->model($this->model)->flush();
		}

		//update Model
		return OmlORManager::dml()->update()->model($this->model)->flush();
	}

	public function flushBatch(array $models) {

		if ( empty($models) ) {

			throw new OMLQueryManagerExceptions('Param should be array of Models Cannot be empty');
		}


		if ( is_array($models) ) {

			foreach($models AS $model) {
				$modelReader = new Reader($model);
				//insert new Model

				if ( !$modelReader->getModelPrimaryKeyValue() ) {

					$result = OmlORManager::dml()->insert()->model($model)->flush();
				}
				else {
					//update Model
					$result = OmlORManager::dml()->update()->model($model)->flush();
				}


				if ( empty($result) ) {

					return false;
				}
			}
		}

		return true;
	}

	public function beginTransaction() {
		if ( empty($this->model) ) {

			throw new OMLQueryManagerExceptions('Model Cannot be empty');
		}


		return OmlORManager::ddl()->package($this->model, true)->beginTransaction();
	}

	public function commitTransaction() {

		if ( empty($this->model) ) {

			throw new OMLQueryManagerExceptions('Model Cannot be empty');
		}

		return OmlORManager::ddl()->package($this->model, true)->commitTransaction();
	}

	public function rollbackTransaction() {

		if ( empty($this->model) ) {

			throw new OMLQueryManagerExceptions('Model Cannot be empty');
		}


		return OmlORManager::ddl()->package($this->model, true)->rollbackTransaction();
	}
}

class OMLQueryManagerExceptions extends \Exception {

}
