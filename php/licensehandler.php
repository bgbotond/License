<?php
chdir( "license" );
require_once( 'license.php' );

$license = new License();

$dataReceive = $license->process( $_POST );
echo $dataReceive;
?>