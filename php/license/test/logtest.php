<?php

require_once( '../log.php' );

class TestClass extends Log
{
	public function __construct()
	{
		$this->setPrefix( 'TestClass' );
	}
	
	public function func()
	{
		$this->log( "function is called" );
	}
}

$a = new TestClass();
$a->setLog( true );
$a->func();
$a->func();

?>