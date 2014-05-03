<?php

function __autoload( $className ) {

	require_once 'libs\\'. $className .'.php';
}

use \OmlManager\ORM\OmlORManager;

try {


	\TestPackage\Models\Users::oml()->beginTransaction();

	$user = new \TestPackage\Models\Users();

	$user->setName('UserName');
	$user->setAge(12);

	$result = OmlORManager::oml()->model($user)->flush();

	\TestPackage\Models\Users::oml()->commitTransaction();

	echo '<pre>';
		var_dump(\TestPackage\Models\Users::oml()->fetch());
	echo '</pre>';

}
 catch (Exception $e) {

	print_r($e->getMessage());
//	print_r($e->getTrace());
}


