<?php

require_once( 'trace.php'    );
require_once( 'database.php' );
require_once( 'crypter.php'  );
require_once( 'util.php'     );
require_once( 'log.php'     );

class License extends Log
{
	private $mTrace        = null;
	private $mDatabase     = null;
	private $mCrypter      = null;
	private $mProduct      = null;
	private $mClient       = null;
	private $mWarning      = "";

	function __construct()
	{
		$this->mTrace    = new Trace();
		$this->mDatabase = new Database();
		$this->mCrypter  = new Crypter();
		
		$this->setPrefix( "License" );
		
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
//		$this->mTrace->setLog( $log );
//		$this->mDatabase->setLog( $log );
//		$this->mCrypter->setLog( $log );
	}
	
	private function setClient( $client )
	{
		$this->mClient = $client;
	}

	private function getClient()
	{
		return $this->mClient;
	}

	private function setProduct( $product )
	{
		$this->mProduct = $product;
	}

	private function getProduct()
	{
		return $this->mProduct;
	}

	public function process( $input )
	{
		$response = "";
		
		$this->log( "license process started" );
		
		Util::addValue( $response, "RANDOM", Util::genString(), ":::" );
		
		if( $this->processInput( $input, $response ) == false )
		{
			$this->sendMail();
			$this->log( "license process failed" );
//			Util::addValue( $response, "REASON", $this->mWarning, ":::" );
			Util::addValue( $response, "RESULT", "FAIL", ":::" );
		}
		else
		{
			$this->log( "license process passed" );
			Util::addValue( $response, "RESULT", "PASS", ":::" );
		}

		$dataEncrypt = $this->mCrypter->doPrivateEncrypt( $response );

		return $dataEncrypt;
	}
	
	private function processInput( $input, &$response )
	{
		$this->setClient( $_SERVER[REMOTE_ADDR] );

		$this->mTrace->write( $this->getClient(), "process request started from client" );

		if( ! is_array( $input )
		 || sizeof( $input ) != 2 )
		{
			$this->mTrace->write( $this->getClient(), "wrong input data" );
			return false;
		}

		// product
		$value = $input[0];
		$this->log( "process product: $value" );
		if( $this->processProduct( $value ) == false )
		{
			$this->mTrace->write( $this->getClient(), "process product failed '$value'", Trace::WARNING );
			return false;
		}
		
		$this->mTrace->write( $this->getClient(), "process product: '" . $this->getProduct() . "'" );
		$this->log( "process product success" );

		// data
		$value = $input[1];
		$this->log( "process data: $value" );
		if( $this->processData( $value, $response ) == false )
		{
			$this->mTrace->write( $this->getClient(), "process data failed", Trace::WARNING );
			return false;
		}
		$this->log( "process data success" );

		$this->mTrace->write( $this->getClient(), "process request finished successfully" );
		return true;
	}
	
	private function processProduct( $product )
	{
		if( $product == "" )
		{
			$this->mWarning = $this->mTrace->write( $this->getClient(), "empty product", Trace::WARNING );
			return false;
		}

		if( $this->mDatabase->findProduct( $product ) == false )
		{
			$this->mWarning = $this->mTrace->write( $this->getClient(), "no product found '$product'", Trace::WARNING );
			return false;
		}
		
		$this->setProduct( $product );
		return true;
	}
	
	private function processData( $data, &$response )
	{
		$privateKey = $this->mDatabase->getValue( $this->getProduct(), Util::getIni( "KEY", "PRIVATE_KEY" ));
		
		if( $privateKey == "" )
		{
			$this->mWarning = $this->mTrace->write( $this->getClient(), "database problem - key is missing '" . Util::getIni( "KEY", "PRIVATE_KEY" ) . "'", Trace::WARNING );
			return false;
		}
		
		$this->mCrypter->setPrivateKeyPath( $privateKey );
		$dataDecrypt = $this->mCrypter->doPrivateDecrypt( $data );
		
		if( $dataDecrypt == "" )
		{
			$this->mWarning = $this->mTrace->write( $this->getClient(), "data couldn't be decrypted with private key", Trace::WARNING );
			return false;
		}
		
		return $this->processDataDecrypt( $dataDecrypt, $response );
	}
	
	private function processDataDecrypt( $dataDecrypt, &$response )
	{
		$arrayValue = Util::string2ArrayValue( $dataDecrypt, ":::" );
		foreach( $arrayValue as $key => $value )
		{
			switch( $key )
			{
			case "TIME" :
				{
					if( $this->checkUserTime( $value ) == false )
						return false;
				}
				break;
			case "MAC"  :
				{
					// do nothing
//					if( $this->checkUserMac( $value ) == false )
//						return false;
				}
				break;
			case "RANDOM" :
				{
					// do nothing
				}
				break;
			default     :
				{
					$this->mWarning = $this->mTrace->write( $this->getClient(), "unknown request data key '$key'", Trace::WARNING );
					return false;
				}
				break;
			}
		}

		if( $this->checkDateExpire() == false )
			return false;

		$this->fillTimeLimit( $response );

		return true;
	}
		
	private function checkUserTime( $time )
	{
		$lastTime = $this->mDatabase->getValue( $this->getProduct(), Util::getIni( "KEY", "LAST_TIME" ));
		
		if( $lastTime == "" )
		{
			$this->mDatabase->addValue( $this->getProduct(), Util::getIni( "KEY", "LAST_TIME" ), $time );
		}
		else if( intval( $lastTime ) > intval( $time ))
		{
			$this->mWarning = $this->mTrace->write( $this->getClient(), "last connect time problem '$lastTime' > '$time'", Trace::WARNING );
			return false;
		}
		
		return true;
	}
	
	private function checkUserMac( $mac )
	{
		// check MAC address possibility
		$lastMAC = $this->mDatabase->getValue( $this->getProduct(), Util::getIni( "KEY", "MAC" ) );
		
		if( $lastMAC == "" )
		{
			$this->mDatabase->addValue( $this->getProduct(), Util::getIni( "KEY", "MAC" ), $mac );
		}
		else if( $lastMAC != $mac )
		{
			$this->mWarning = $this->mTrace->write( $this->getClient(), "last connect MAC problem '$lastMAC' != '$mac'", Trace::WARNING );
			return false;
		}

		return true;
	}
	
	private function checkDateExpire()
	{
		$dateExpire = $this->mDatabase->getValue( $this->getProduct(), Util::getIni( "KEY", "DATE_EXPIRE" ));
		$dateNow    = gmdate( "Ymd" );
		
		$this->log( "check expire date: $dateExpire (now: $dateNow)" );
		
		if( $dateExpire != ""
		 && $dateExpire != "UNLIMITED"
		 && intval( $dateNow ) > intval( $dateExpire ))
		{
			$this->log( "product expiredcheck expire date: $dateExpire (now: $dateNow)" );
			$this->mWarning = $this->mTrace->write( $this->getClient(), "license date dexpired '$dateExpire'", Trace::WARNING );
			return false;
		}

		return true;
	}

	private function fillTimeLimit( &$response )
	{
		$timeLimit = $this->mDatabase->getValue( $this->getProduct(), Util::getIni( "KEY", "TIME_LIMIT" ));
		
		if( $timeLimit != "" )
		{
			Util::addValue( $response, Util::getIni( "KEY", "TIME_LIMIT" ), $timeLimit, ":::" );
			$this->mTrace->write( $this->getClient(), "time limit set '$timeLimit'" );
		}
	}
	
	private function sendMail()
	{
		$mMessage  = "Generated warning email sent by License server\n";
		$mMessage .= "----------------------------------------------\n";
		$mMessage .= "Product: '" . $this->getProduct() . "'\n";
		$mMessage .= "Time: '"    . Util::getTime()     . "'\n";
		$mMessage .= "Client: '"  . $this->getClient()  . "'\n";
		$mMessage .= "Message: "  . $this->mWarning;
		
		if( Util::mail( "License server warning", $mMessage ) == false )
			$this->mTrace->write( $this->getClient(), "couldn't sent mail", Trace::WARNING );
	}
}

?>