<?php

function __autoload( $className ) {

	require_once 'libs\\'. $className .'.php';
}

use \OmlManager\ORM\OmlORManager;

try {





	$exp = new \OmlManager\ORM\Query\Expression\Expression();

	$exp->field('cn_key')->in(array('bk_dashboard', 'bk_login'));

	$cotrollers = OmlORManager::dml()->select()
		->model(new \LaunchPackage\Model\Controllers())
		->expression($exp)->fetchAll();

	echo '<pre>';
		var_dump($cotrollers);
	echo '</pre>';

}
 catch (Exception $e) {

	print_r($e->getMessage());
}


