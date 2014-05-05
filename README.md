OmlManager v1.0, Object Manipulation Language
==========
This is an ORM DataBase Manager. Which is easier to learn and better for using

About ORM.
http://en.wikipedia.org/wiki/Object-relational_mapping

===========
1. Config databases
===========
	libs/OmlManager/Config/databases.ini
```ini
	[db_test]
	driver = pdo_mysql;
	host = localhost;
	db_name = test;
	user = root;
	password = ;
	port = '';
	socket = '';

```

===
2. Generate Database entities (Console command)
===

```php

	//generate all database entities
	php generate.php [DB_CONF_NAME]

	//generate only one entity
	php generate.php [DB_CONF_NAME] [TABLE_NAME]


===
3. Way of Using OmlORManager


```php
	//Run transaction
	OmlORManager::ddl()->package(new \TestPackage\Test())->beginTransaction();
    OmlORManager::ddl()->package(new \TestPackage\Test())->commitTransaction();
    OmlORManager::ddl()->package(new \TestPackage\Test())->rollbackTransaction();

	//OR

	\TestPackage\Models\Users::oml()->beginTransaction();
	\TestPackage\Models\Users::oml()->commitTransaction();
	\TestPackage\Models\Users::oml()->rollbackTransaction();

	//Fetch objects
	$users = OmlORManager::oml()->model(new \TestPackage\Models\Users())->fetch();
	var_dump($users);

