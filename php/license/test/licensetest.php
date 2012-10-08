<?php

require_once( '../manager.php'  );
require_once( '../license.php' );
require_once( '../database.php' );
require_once( '../util.php' );

// generate products
$manager = new Manager();
//$manager->setLog( true );

$manager->createProduct( "TouchMovie", "MOME thesis" );
$manager->createProduct( "nibbal"    , "basketball game with kinect" );
$manager->generateReport( "DBreport.txt" );

$product = "nibbal";

// get key
$database = new Database();
//$database->setLog( true );
$database->init( Util::getIni( "PATH", "DB_PATH" ) . "/" . Util::getIni( "PATH", "DB_NAME" ));

$publicKey = $database->getValue( $product, Util::getIni( "KEY", "PUBLIC_KEY" ));

echo "publicKey: $publicKey<br>";

$crypter = new Crypter();
$crypter->setPublicKeyPath( $publicKey );
//"BElt66hbsbIxJ5ckIrL8DMbzlqytDfigtwJxQ7BxqbwsH6aC9feLDDA4WxrWpV0a oUA27zxhVjOJ1WQUjo4d1vNftojJI3B1p0PvSWDeQQE21yEyqNgAzk/PY+86SEpy RBWMuTSDbjXFFNP3GOUVUaDrP2CYIc5z8ghF/EZ7D2c="

$data        = "";
Util::addValue( $data, "TIME", gmdate( "YmdHis" ), ":::" );
Util::addValue( $data, "RANDOM", "iuhsdffgohsdfioguhdiough", ":::" );
$dataEncrypt = $crypter->doPublicEncrypt( $data );
$dataSend    = array();
$dataSend[PRODUCT] = $product;
$dataSend[DATA]    = $dataEncrypt;

$license = new License();
$license->setLog( true );
$dataReceive = $license->process( $dataSend );

$dataDecrypt = $crypter->doPublicDecrypt( $dataReceive );

echo "data: '$data'<br>";
echo "dataEncrypt: '$dataEncrypt'<br>";
echo "dataSend: '$dataSend'<br>";
echo "dataReceive: '$dataReceive'<br>";
echo "dataDecrypt: '$dataDecrypt'<br>";

?>
