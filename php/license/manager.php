<?php

require_once( 'trace.php'    );
require_once( 'database.php' );
require_once( 'crypter.php'  );
require_once( 'log.php'      );
require_once( 'util.php'     );

class Manager extends Log
{
	private $mTrace        = null;
	private $mDatabase     = null;
	private $mCrypter      = null;
	
	function __construct()
	{
		$this->mTrace    = new Trace();
		$this->mDatabase = new Database();
		$this->mCrypter  = new Crypter();
		
		$this->setPrefix( "Manager" );
		
		$this->init();
	}

	private function init()
	{
		$this->initDB();
		$this->initTrace();
	}

	private function initDB()
	{
		$DBPath = Util::getIni( "PATH", "DB_PATH" ) . "/" . Util::getIni( "PATH", "DB_NAME" );
		$this->log( "initialize DB '$DBPath'" );
		Util::createPath( Util::getIni( "PATH", "DB_PATH" ));
		$this->mDatabase->init( $DBPath );
	}
	
	private function initTrace()
	{
		$tracePath = Util::getIni( "PATH", "TRACE_PATH" ) . "/" . Util::getIni( "PATH", "TRACE_NAME" );
		$this->log( "initialize trace '$tracePath'" );
		Util::createPath( Util::getIni( "PATH", "TRACE_PATH" ));
		$this->mTrace->init( $tracePath );
	}
	
	public function setLog( $log )
	{
		parent::setLog( $log );
		$this->mTrace->setLog( $log );
		$this->mDatabase->setLog( $log );
		$this->mCrypter->setLog( $log );
	}
	
	public function createProduct( $name, $desc )
	{
		$this->mTrace->write( "MANAGER", "product creating '$name' ($desc)" );
		$this->log( "create product '$name' ($desc)" );
		$ret = $this->mDatabase->addProduct( $name, $desc );
		
		if( $ret == false )
		{
			$this->mTrace->write( "MANAGER", "product already exist '$name'" );
			$this->log( "product already exist $name" );
			return false;
		}
		
		$keyPath    = Util::getIni( "PATH", "DB_PATH" ) . "/" . $name;
		$privateKey = $keyPath . "/" . Util::getIni( "PATH", "PRIVATE_KEY_NAME" );
		$publicKey  = $keyPath . "/" . Util::getIni( "PATH", "PUBLIC_KEY_NAME" );
		
		$this->log( "generate key pairs '$privateKey', '$publicKey'" );
		Util::createPath( $keyPath );
		$this->mCrypter->generateKeys( $privateKey, $publicKey );
		
		$this->mDatabase->addValue( $name, Util::getIni( "KEY", "PRIVATE_KEY" ), $privateKey );
		$this->mDatabase->addValue( $name, Util::getIni( "KEY", "PUBLIC_KEY"  ), $publicKey  );
		$this->mTrace->write( "MANAGER", "keys generated '$privateKey', '$publicKey'" );
		$this->mTrace->write( "MANAGER", "product created '$name' ($desc)" );
		return true;
	}

	public function setProductValue( $name, $key, $value )
	{
		$this->mTrace->write( "MANAGER", "set product value '$name' ($key - $value)" );
		$this->log( "set product value '$name' ($key - $value)" );

		$ret = $this->mDatabase->findProduct( $name );

		if( $ret == false )
		{
			$this->mTrace->write( "MANAGER", "product doesn't exist '$name'" );
			$this->log( "product doesn't exist $name" );
			return false;
		}

		$this->mDatabase->addValue( $name, $key, $value );
	}

	public function getProductValue( $name, $key )
	{
		$this->mTrace->write( "MANAGER", "get product value '$name' ($key)" );
		$this->log( "get product value '$name' ($key)" );

		$ret = $this->mDatabase->findProduct( $name );

		if( $ret == false )
		{
			$this->mTrace->write( "MANAGER", "product doesn't exist '$name'" );
			$this->log( "product doesn't exist $name" );
			return null;
		}

		$value = $this->mDatabase->getValue( $name, $key );
		
		if( $value == "" )
		{
			$this->mTrace->write( "MANAGER", "product key doesn't exist '$key'" );
			$this->log( "product key doesn't exist '$key" );
			return null;
		}
		
		return $value;
	}

	public function getProductAll()
	{
		$this->mTrace->write( "MANAGER", "get all products" );
		$rows = $this->mDatabase->getProductAll();
		return $rows;
	}
	
	public function getValueAll( $product )
	{
		$this->mTrace->write( "MANAGER", "get all values for product: $product" );
		$rows = $this->mDatabase->getValueAll( $product );
		return $rows;
	}
	
	public function generateReport( $fileName )
	{
		$this->log( "create report in file '$fileName'" );
		$file = fopen( $fileName, "w" );

		$products = $this->mDatabase->getProductAll();
	
		foreach( $products as $product )
		{
			fwrite( $file, "id: $product[_id] - name: '$product[_name]' - desc: '$product[_desc]'\n" );
			fwrite( $file, "{\n" );
			
			$values = $this->mDatabase->getValueAll( $product[_name] );
			
			foreach( $values as $value )
			{
				fwrite( $file, "\tkey: '$value[_key]' = '$value[_value]'\n" );
			}
			
			fwrite( $file, "}\n" );
		}
				
		fclose( $file );
	}
}

?>