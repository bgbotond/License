<?php

include "../crypter.php";

if( file_exists( "key" ) == false )
{
	mkdir( "key" );
}

$crypt = new Crypter();

$crypt->generateKeys( "key/private2.pem", "key/public2.pem" );

//$crypt->setPublicKeyPath( "public.pem" );
//$crypt->setPrivateKeyPath( "private.pem" );
//$crypt->setPassword( "secret" );

$dataOrig           = "Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.";
$dataPrivateEncrypt = "";
$dataPrivateDecrypt = "";
$dataPublicEncrypt  = "";
$dataPublicDecrypt  = "";

$dataPublicEncrypt  = $crypt->doPublicEncrypt ( $dataOrig          );
$dataPrivateDecrypt = $crypt->doPrivateDecrypt( $dataPublicEncrypt );

$dataPrivateEncrypt = $crypt->doPrivateEncrypt( $dataOrig           );
$dataPublicDecrypt  = $crypt->doPublicDecrypt ( $dataPrivateEncrypt );

echo "encrypt and decrypt method test for arbitrary length of text using openssl<BR>";
echo "<BR>";
echo "original text: \"$dataOrig\"<BR>";
echo "<BR>";
echo "--- private encrypt -> public decrypt ---<BR>";
echo "text encrypt: \"$dataPrivateEncrypt\"<BR>";
echo "text decrypt: \"$dataPublicDecrypt\"<BR>";

if( $dataOrig == $dataPublicDecrypt )
{
	echo "SUCCESS<BR>";
}
else
{
	echo "NOT SUCCESS<BR>";
}

echo "<BR>";
echo "--- public encrypt -> private decrypt ---<BR>";
echo "text encrypt: \"$dataPublicEncrypt\"<BR>";
echo "text decrypt: \"$dataPrivateDecrypt\"<BR>";

if( $dataOrig == $dataPrivateDecrypt )
{
	echo "SUCCESS<BR>";
}
else
{
	echo "NOT SUCCESS<BR>";
}

?>