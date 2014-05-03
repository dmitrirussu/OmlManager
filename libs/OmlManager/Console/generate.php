<?php
function __autoload( $className ) {

	require_once '../../'.$className .'.php';
}

$dataBaseName = $argv[1];
$entityName = (isset($argv[2]) ? $argv[2] : null);

echo "\nStart!";

if ( $entityName ) {

	$generate = new \OmlManager\ORM\SchemaEntitiesGenerator\EntityGenerator($dataBaseName, realpath(__DIR__));

	$generate->generateEntity($entityName);
}
else{

	$generate = new \OmlManager\ORM\SchemaEntitiesGenerator\SchemaGenerator($dataBaseName, realpath(__DIR__));

	$generate->generateSchemaEntities();
}
echo "\ndone!";