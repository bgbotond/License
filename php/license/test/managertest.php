<?php

require_once( '../manager.php'  );

$manager = new Manager();
//$manager->setLog( true );
//
//$manager->createProduct( "TouchMovie", "MOME thesis" );
//$manager->createProduct( "nibbal"    , "basketball game with kinect" );
//
//$manager->setProductValue( "TouchMovie", "DATE_EXPIRE", "UNLIMITED" );
//$manager->setProductValue( "nibbal", "DATE_EXPIRE", "20120915" );
//
//$manager->createUser( "bgbotond", "secret" );
//
//$value = $manager->getProductValue( "nibbal", "DATE_EXPIRE" );
//echo "value of nibbal DATE_EXPIRE: $value<br>";
//
//$manager->generateReport( "DBreport.txt" );

$table = $manager->generateDBChart( "ProductTab", "_id, _name, _desc", "" );

echo $table;

?>