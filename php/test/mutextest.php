<?php

include '../mutex.php';

$fileName = "mutex_file_test.txt";

touch( $fileName );

$mutex = new Mutex();
$mutex->setLog( true );
$mutex->init( $fileName );

if( $mutex->lock())
{
	echo "write something into file<BR>";
	$fileHandler = fopen( $fileName, "w" );
	fwrite( $fileHandler, "insert some text into file" );
	fclose( $fileHandler );
	$mutex->unlock();
}

if( $mutex->lock())
{
	echo "read the file content<BR>";
	$fileHandler = fopen( $fileName, "r" );
	$line = fgets( $fileHandler, 1024 );
	fclose( $fileHandler );
	echo( "file '$fileName' content: $line<BR>" );
	$mutex->unlock();
}

?>