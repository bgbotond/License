<?php

require_once( 'manager.php' );

if( $_SERVER['REQUEST_METHOD'] == 'POST' )
{
	$username = $_POST['username'];
	$password = $_POST['password'];

	$username = htmlspecialchars( $username );
	$password = htmlspecialchars( $password );
	
	$manager = new Manager();

	session_start();
	if( $manager->checkUser( $username, $password ))
	{
		$_SESSION['login']   = "1";
		$_SESSION['actuser'] = $username;
		header( "Location: admin_login.php" );
	}
	else
	{
		$_SESSION['login']   = "";
		$_SESSION['actuser'] = "";
		header( "Location: ../login.php" );
	}
}
else
{
	header( "Location: ../login.php" );
}

?>