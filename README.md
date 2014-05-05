OmlManager v1.0
==========

OmlManager - Object Manipulation Language, Dabatabse ORM - Object Relational Mapping

==
1. Config databases

	libs/OmlManager/Config/databases.ini

	[db_test]
	driver = pdo_mysql;
	host = localhost;
	db_name = test;
	user = root;
	password = ;
	port = '';
	socket = '';

2. Generate Database entities (Console command)

	//generate all database entities
	php generate.php [DB_CONF_NAME]

	//generate only one entity
	php generate.php [DB_CONF_NAME] [TABLE_NAME]