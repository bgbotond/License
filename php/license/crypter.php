<?php

require_once( 'mutex.php' );
require_once( 'log.php'   );

class Crypter extends Log
{
	private $mMaxLength      = 100;
	private $mDelimiter      = ":::";
	private $mPrivateKeyPath = null;
	private $mPublicKeyPath  = null;
	private $mPassword       = null;
	private $mMutexPrivate   = null;
	private $mMutexPublic    = null;

	function __construct()
	{
		$this->mMutexPrivate = new Mutex();
		$this->mMutexPublic  = new Mutex();
		$this->setPrefix( "Crypter" );
	}
	
	public function setLog( $log )
	{
		parent::setLog( $log );
		$this->mMutexPrivate->setLog( $log );
		$this->mMutexPublic->setLog( $log );
	}
	
	public function generateKeys( $privateKeyPath, $publicKeyPath )
	{
		$this->log( "generate keys '$privateKeyPath', '$publicKeyPath'" );

		/* Create the private and public key */
		$res = openssl_pkey_new();

		/* Extract the private key from $res to $privKey */
		openssl_pkey_export( $res, $privKey );
		
		/* Extract the public key from $res to $pubKeyArr */
		$pubKeyArr = openssl_pkey_get_details( $res );
		$pubKey = $pubKeyArr["key"];

		$file = fopen( $privateKeyPath, "w" );
		fwrite( $file, $privKey );
		fclose( $file );
		chmod( $privateKeyPath, 0600 );  
		
		$file = fopen( $publicKeyPath, "w" );
		fwrite( $file, $pubKey );
		fclose( $file );
		chmod( $publicKeyPath, 0600 );  

		$this->setPrivateKeyPath( $privateKeyPath );
		$this->setPublicKeyPath( $publicKeyPath );
	}

	public function setPrivateKeyPath( $privateKeyPath )
	{
		if( $this->mPrivateKeyPath != null )
		{
			$this->log( "already inited with privateKeyPath '$this->mPrivateKeyPath'" );
			return;
		}
		
		$this->log( "set private key '$privateKeyPath'" );
		$this->mPrivateKeyPath = $privateKeyPath;
		$this->mMutexPrivate->init( $privateKeyPath );
	}

	public function getPrivateKeyPath()
	{
		return $this->mPrivateKeyPath;
	}

	public function setPublicKeyPath( $publicKeyPath )
	{
		if( $this->mPublicKeyPath != null )
		{
			$this->log( "already inited with publicKeyPath '$this->mPublicKeyPath'" );
			return;
		}
	
		$this->log( "set public key '$publicKeyPath'" );
		$this->mPublicKeyPath = $publicKeyPath;
		$this->mMutexPublic->init( $publicKeyPath );
	}

	public function getPublicKeyPath()
	{
		return $this->mPublicKeyPath;
	} 

	public function setPassword( $password )
	{
		if( $this->mPassword != null )
		{
			$this->log( "already inited with password '$this->mPassword'" );
			return;
		}
	
		$this->log( "set password '$password'" );
		$this->mPassword = $password;
	}

	public function getPassword()
	{
		return $this->mPassword;
	}

	public function doPrivateEncrypt( $text )
	{
		if( $this->mPrivateKeyPath == null )
		{
			$this->log( "no private key set" );
			return "";
		}
	
		$this->log( "private encrypt with text: '$text'" );
		$originalArray = $this->cutter( $text, $this->getMaxLength());
		$privKeyFile = file_get_contents( $this->getPrivateKeyPath());
		$privKey = openssl_get_privatekey( $privKeyFile, $this->getPassword());
		$encrypted = "";
		$ret       = "";

		$first = true;
		foreach( $originalArray as $subOriginal )
		{
			if( $first == true )
				$first = false;
			else
				$ret .= $this->getDelimiter(); 

			openssl_private_encrypt( $subOriginal, $encrypted, $privKey, OPENSSL_PKCS1_PADDING );
			$ret .= chunk_split( base64_encode( $encrypted ), 64, "\n" );
		}

		$this->log( "private encrypt result: '$ret'" );
		return $ret;
	}

	public function doPrivateDecrypt( $text )
	{
		if( $this->mPrivateKeyPath == null )
		{
			$this->log( "no private key set" );
			return "";
		}
	
		$this->log( "private decrypt with text: '$text'" );
		$encryptedArray = explode( $this->getDelimiter(), $text );
		$privKeyFile = file_get_contents( $this->getPrivateKeyPath());
		$privKey = openssl_get_privatekey( $privKeyFile, $this->getPassword());
		$decrypted = "";
		$ret       = "";

		foreach( $encryptedArray as $subEncrypted )
		{
			$subEncrypted = base64_decode( $subEncrypted );
			openssl_private_decrypt( $subEncrypted, $decrypted, $privKey, OPENSSL_PKCS1_PADDING );
			$ret .= $decrypted; 
		}
		
		$this->log( "private decrypt result: '$ret'" );
		return $ret;
	}
	
	public function doPublicEncrypt( $text )
	{
		if( $this->mPublicKeyPath == null )
		{
			$this->log( "no public public key set" );
			return "";
		}
	
		$this->log( "public encrypt with text: '$text'" );
		$originalArray = $this->cutter( $text, $this->getMaxLength());
		$publicKeyFile = file_get_contents( $this->getPublicKeyPath());
		$publicKey = openssl_get_publickey( $publicKeyFile );
		$encrypted = "";
		$ret       = "";

		$first = true;
		foreach( $originalArray as $subOriginal )
		{
			if( $first == true )
				$first = false;
			else
				$ret .= $this->getDelimiter(); 

			openssl_public_encrypt( $subOriginal, $encrypted, $publicKey, OPENSSL_PKCS1_PADDING );
			$ret .= chunk_split( base64_encode( $encrypted ), 64, "\n" );
		}

		$this->log( "public encrypt result: '$ret'" );
		return $ret;
	}

	public function doPublicDecrypt( $text )
	{
		if( $this->mPublicKeyPath == null )
		{
			$this->log( "no public key set" );
			return "";
		}
	
		$this->log( "public decrypt with text: '$text'" );
		$encryptedArray = explode( $this->getDelimiter(), $text );
		$publicKeyFile = file_get_contents( $this->getPublicKeyPath());
		$publicKey = openssl_get_publickey( $publicKeyFile );
		$decrypted = "";
		$ret       = "";

		foreach( $encryptedArray as $subEncrypted )
		{
			$subEncrypted = base64_decode( $subEncrypted );
			openssl_public_decrypt( $subEncrypted, $decrypted, $publicKey, OPENSSL_PKCS1_PADDING );
			$ret .= $decrypted; 
		}

		$this->log( "public decrypt result: '$ret'" );
		return $ret;
	}
	
	private function getMaxLength()
	{
		return $this->mMaxLength;
	}
	
	private function getDelimiter()
	{
		return $this->mDelimiter;
	}
	
	private function cutter( $text, $length )
	{
		$ret = array();
		$beg = 0;
		while(( $subText = $this->separate( $text, $beg, $length )) != "" )
		{
			array_push( $ret, $subText );
		}
		
		return $ret;
	}
	
	private function separate( $text, &$beg, $length )
	{
		$maxLength = strlen( $text );
		
		if( $beg < 0 || $beg >= $maxLength )
			return "";
		
		$length  = min( $length, $maxLength - $beg );

		$subText = substr( $text, $beg, $length );
		
		$beg += $length;
		
		return $subText;
	}
}

?>