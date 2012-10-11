<?php

require_once( '../util.php'  );

$dir = "testdir";
echo "creating dir: $dir<br>";
echo "-------------------------------------<br>";
Util::createPath( $dir );

$string = "key1=value1:::key2=value2:::key3=value3";
$separator = ":::";
echo "string2ArrayValue: $string<br>";
$arrayValue = Util::string2ArrayValue( $string, $separator );
echo "received arrayValue: <br>";
var_dump( $arrayValue );
echo "<br>-------------------------------------<br>";

$string2 = "";
$string2 = Util::arrayValue2String( $arrayValue, $separator );
echo "arrayValue2String: $string2<br>";
echo "-------------------------------------<br>";

if( $string == $string2 )
	echo "the received string is the same<br>";
else
	echo "the received string is not the same<br>";

echo "-------------------------------------<br>";

$value1 = Util::getIni( "PATH", "PUBLIC_KEY_NAME" );
$value2 = Util::getIni( "KEY", "LAST_TIME" );
$value3 = Util::getIni( "_", "_", "default value" );

echo "values from ini file<br>";
echo "PATH - PUBLIC_KEY_NAME = $value1<br>";
echo "KEY - LAST_TIME = $value2<br>";
echo "_ - _ = $value3<br>";

echo "-------------------------------------<br>";

$length = 5;
$string = Util::genString( $length );
echo "generate $length length string: $string<br>";

$length = 10;
$string = Util::genString( $length );
echo "generate $length length string: $string<br>";

$length = 0;
$string = Util::genString( $length );
echo "generate full string: $string<br>";
echo "-------------------------------------<br>";

if( Util::mail( "test email", "this is a test mail from php" ))
	echo "send mail successfully<br>";
else
	echo "send mail unsuccessfully<br>";
?>
