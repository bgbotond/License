<?php
require_once( 'license/license.php' );

$license = new License();

$dataReceive = $license->process( $_POST );

echo $dataReceive;
?>