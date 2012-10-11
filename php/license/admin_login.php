<?PHP

require_once( 'manager.php' );

$actuser = "";
$control = "";
$content = "Select content!";
$name    = "";
$desc    = "";
$key     = "";
$value   = "";
$user    = "";
$pass    = "";
$manager = new Manager();

function setVariable( $varName )
{
	if( isset( $_POST[ $varName ] ) && $_POST[ $varName ] != "" )
	{
		return trim( $_POST[ $varName ]);
	}

	return "";
}

session_start();
if( !( isset( $_SESSION['login'] ) && $_SESSION['login'] != "" ))
{
	header( "Location: ../login.php");
}

if( isset( $_SESSION[ "actuser" ] ) && $_SESSION[ "actuser" ] != "" )
{
	$actuser = $_SESSION[ "actuser" ];
}

if( $_SERVER['REQUEST_METHOD'] == 'POST' )
{
	/**/ if( isset( $_POST['Logout'] ))
	{
		$_SESSION['login']   = "";
		$_SESSION['actuser'] = "";
		header( "Location: ../login.php" );
	}
	else if( isset( $_POST['ProductTab'] )
	      || isset( $_POST['ProductTab_Create'] )
	      || isset( $_POST['ProductTab_Delete'] )
	      || isset( $_POST['ProductTab_Update'] ))
	{
		$name = setVariable( "name" );
		$desc = setVariable( "desc" );

		if( $name != "" )
		{
			if( isset( $_POST['ProductTab_Create'] ))
				$manager->createProduct( $name, $desc );
			else if( isset( $_POST['ProductTab_Delete'] ))
				$manager->destroyProduct( $name );
			else if( isset( $_POST['ProductTab_Update'] ))
				$manager->updateProduct( $name, $desc );
		}
		
		$control  = "<FORM NAME ='ProductTabForm' METHOD ='POST' ACTION ='admin_login.php'>";
		$control .= "Name <INPUT type = 'text' name ='name' value='$name'>";
		$control .= "Desc <INPUT type = 'text' name ='desc' value='$desc'>";
		$control .= "<INPUT type = 'submit' name = 'ProductTab_Create' value = 'Create' style='height:23px; width:80px; margin:5;'>";
		$control .= "<INPUT type = 'submit' name = 'ProductTab_Delete' value = 'Delete' style='height:23px; width:80px; margin:5;'>";
		$control .= "<INPUT type = 'submit' name = 'ProductTab_Update' value = 'Update' style='height:23px; width:80px; margin:5;'>";
		$control .= "</FORM>";
		
	
		$content = $manager->generateDBChart( "ProductTab", "_id, _name, _desc", "" );
	}
	else if( isset( $_POST['ValueTab'] )
	      || isset( $_POST['ValueTab_Create'] )
	      || isset( $_POST['ValueTab_Delete'] )
	      || isset( $_POST['ValueTab_Update'] ))
	{
		$name  = setVariable( "name"  );
		$key   = setVariable( "key"   );
		$value = setVariable( "value" );

		if( $name != "" && $key != "" )
		{
			if( isset( $_POST['ValueTab_Create'] ))
				$manager->setProductValue( $name, $key, $value );
			else if( isset( $_POST['ValueTab_Delete'] ))
				$manager->delProductValue( $name, $key );
			else if( isset( $_POST['ValueTab_Update'] ))
				$manager->setProductValue( $name, $key, $value );
		}
		
		$control  = "<FORM NAME ='ValueTabForm' METHOD ='POST' ACTION ='admin_login.php'>";
		$control .= "Name  <INPUT type = 'text' name ='name'  value='$name' >";
		$control .= "Key   <INPUT type = 'text' name ='key'   value='$key'  >";
		$control .= "Value <INPUT type = 'text' name ='value' value='$value'>";
		$control .= "<INPUT type = 'submit' name = 'ValueTab_Create' value = 'Create' style='height:23px; width:80px; margin:5;'>";
		$control .= "<INPUT type = 'submit' name = 'ValueTab_Delete' value = 'Delete' style='height:23px; width:80px; margin:5;'>";
		$control .= "<INPUT type = 'submit' name = 'ValueTab_Update' value = 'Update' style='height:23px; width:80px; margin:5;'>";
		$control .= "</FORM>";
	
		$content = $manager->generateDBChart( "ValueTab", "_id, _key, _value", "" );
	}
	else if( isset( $_POST['UserTab'] )
	      || isset( $_POST['UserTab_Create'] )
	      || isset( $_POST['UserTab_Delete'] )
	      || isset( $_POST['UserTab_Update'] ))
	{
		$user  = setVariable( "user" );
		$pass  = setVariable( "pass"  );

		if( $user != "" && $user != $actuser )
		{
			if( isset( $_POST['UserTab_Create'] ))
				$manager->createUser( $user, $pass );
			else if( isset( $_POST['UserTab_Delete'] ))
				$manager->destroyUser( $user );
			else if( isset( $_POST['UserTab_Update'] ))
				$manager->updateUser( $user, $pass );
		}
		
		$control  = "<FORM NAME ='UserTabForm' METHOD ='POST' ACTION ='admin_login.php'>";
		$control .= "User <INPUT type = 'text' name ='user' value='$user' >";
		$control .= "Pass <INPUT type = 'text' name ='pass' value='$pass' >";
		$control .= "<INPUT type = 'submit' name = 'UserTab_Create' value = 'Create' style='height:23px; width:80px; margin:5;'>";
		$control .= "<INPUT type = 'submit' name = 'UserTab_Delete' value = 'Delete' style='height:23px; width:80px; margin:5;'>";
		$control .= "<INPUT type = 'submit' name = 'UserTab_Update' value = 'Update' style='height:23px; width:80px; margin:5;'>";
		$control .= "</FORM>";
	
		$content = $manager->generateDBChart( "UserTab", "_user, _pass", "" );
	}
	else if( isset( $_POST['Trace'] ))
	{
		$content = $manager->generateTraceChart( "" );
	}
}
?>

<html>
	<head>
		<title>License Server Admin</title>
	</head>
	<body>
		<div id="container" >
			<div id="header" align="center" style="height:80px; margin-top:30px">
				<b>License Server Admin</b><br>
				Current user: <?php echo $actuser; ?>
			</div>

			<div id="menu" style="width:100px;float:left;">
			
				<FORM NAME ="menuForm" METHOD ="POST" ACTION ="admin_login.php">
					<INPUT type = "submit" name = "Logout"     value = "Logout"     style="height:23px; width:80px; margin-top:5;">
					<INPUT type = "submit" name = "ProductTab" value = "ProductTab" style="height:23px; width:80px; margin-top:5;">
					<INPUT type = "submit" name = "ValueTab"   value = "ValueTab"   style="height:23px; width:80px; margin-top:5;">
					<INPUT type = "submit" name = "UserTab"    value = "UserTab"    style="height:23px; width:80px; margin-top:5;">
					<INPUT type = "submit" name = "Trace"      value = "Trace"      style="height:23px; width:80px; margin-top:5;">
				</FORM>
			</div>

			<div id="content" align="center" style="float:rigth;float:bottom;">
				<?php echo $control; ?>
				<?php echo $content; ?>
			</div>
		</div>
	</body>
</html>
