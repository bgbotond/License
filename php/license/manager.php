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
		$this->mTrace->write( "MANAGER", "product create '$name' ($desc)" );
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
		return true;
	}

	public function updateProduct( $name, $desc )
	{
		$this->mTrace->write( "MANAGER", "product update '$name' ($desc)" );
		$this->log( "update product '$name' ($desc)" );
		$ret = $this->mDatabase->updProduct( $name, $desc );
		
		if( $ret == false )
		{
			$this->mTrace->write( "MANAGER", "product doesn't exist '$name'" );
			$this->log( "product doesn't exist $name" );
			return false;
		}

		return true;
	}

	public function destroyProduct( $name )
	{
		$this->mTrace->write( "MANAGER", "product destroy '$name'" );
		$this->log( "destroy product '$name'" );
		$ret = $this->mDatabase->delProduct( $name );
		
		if( $ret == false )
		{
			$this->mTrace->write( "MANAGER", "product doesn't exist '$name'" );
			$this->log( "product doesn't exist $name" );
			return false;
		}

		return true;
	}
	
	public function setProductValue( $name, $key, $value )
	{
		$this->mTrace->write( "MANAGER", "product set value '$name' ($key - $value)" );
		$this->log( "set product value '$name' ($key - $value)" );
		$ret = $this->mDatabase->addValue( $name, $key, $value );

		if( $ret == false )
		{
			$this->mTrace->write( "MANAGER", "product doesn't exist '$name'" );
			$this->log( "product doesn't exist $name" );
			return false;
		}

		return true;
	}

	public function delProductValue( $name, $key )
	{
		$this->mTrace->write( "MANAGER", "product del value '$name' ($key)" );
		$this->log( "del product value '$name' ($key)" );
		$ret = $this->mDatabase->delValue( $name, $key );

		if( $ret == false )
		{
			$this->mTrace->write( "MANAGER", "product doesn't exist '$name'" );
			$this->log( "product doesn't exist $name" );
			return false;
		}

		return true;
	}

	public function getProductValue( $name, $key )
	{
		$this->log( "get product value '$name' ($key)" );

		$ret = $this->mDatabase->findProduct( $name );

		if( $ret == false )
		{
			$this->log( "product doesn't exist $name" );
			return null;
		}

		$value = $this->mDatabase->getValue( $name, $key );
		
		if( $value == "" )
		{
			$this->log( "product key doesn't exist '$key" );
			return null;
		}
		
		return $value;
	}

	public function createUser( $user, $pass )
	{
		$this->mTrace->write( "MANAGER", "user create '$user' ($pass)" );
		$this->log( "create user '$user' ($pass)" );
		$ret = $this->mDatabase->addUser( $user, $pass );
		
		if( $ret == false )
		{
			$this->mTrace->write( "MANAGER", "user already exist '$user'" );
			$this->log( "user already exist $user" );
			return false;
		}
		
		return true;
	}
	
	public function updateUser( $user, $pass )
	{
		$this->mTrace->write( "MANAGER", "user update '$user' ($pass)" );
		$this->log( "update user '$user' ($pass)" );
		$ret = $this->mDatabase->updUser( $user, $pass );
		
		if( $ret == false )
		{
			$this->mTrace->write( "MANAGER", "user doesn't exist '$user'" );
			$this->log( "user  doesn't exist $user" );
			return false;
		}

		return true;
	}

	public function destroyUser( $user )
	{
		$this->mTrace->write( "MANAGER", "user destroy '$user'" );
		$this->log( "destroy user '$user'" );
		$ret = $this->mDatabase->delUser( $user );
		
		if( $ret == false )
		{
			$this->mTrace->write( "MANAGER", "user doesn't exist '$user'" );
			$this->log( "user doesn't exist $user" );
			return false;
		}

		return true;
	}

	public function checkUser( $user, $pass )
	{
		$this->mTrace->write( "MANAGER", "check user '$user'" );
		$this->log( "check user '$user'" );
		$ret = $this->mDatabase->checkUser( $user, $pass );
		
		if( $ret == false )
		{
			$this->mTrace->write( "MANAGER", "user doesn't exist or incorrect password '$user' ($pass)" );
			$this->log( "user doesn't exist or incorrect password '$user' ($pass)" );
			return false;
		}

		return true;
	}
	
	public function getProductAll()
	{
		$rows = $this->mDatabase->getProductAll();
		return $rows;
	}
	
	public function getValueAll( $product )
	{
		$rows = $this->mDatabase->getValueAll( $product );
		return $rows;
	}

	public function getUserAll()
	{
		$rows = $this->mDatabase->getUserAll();
		return $rows;
	}
	
	public function generateDBChart( $tableName, $columnNames, $where )
	{
		$rows = $this->mDatabase->getRows( $tableName, $columnNames, $where );
	
		return $this->generateChart( $tableName, $columnNames, $rows );
	}

	public function generateTraceChart( $type )
	{
		$columnNames = "";
		$rows = $this->mTrace->getRows( $columnNames, $type );
	
		return $this->generateChart( "Trace", $columnNames, $rows );
	}

	public function generateIniChart( $section )
	{
		$columnNames = "";
		$rows = Util::getIniRows( $columnNames, $section );
	
		return $this->generateChart( "Ini", $columnNames, $rows );
	}
	
	public function generateChart( $tableName, $columnNames, &$rows )
	{
		$columns = explode( ",", $columnNames );

		$table = "<table border = '1' cellpadding = '5' cellspacing = '0'>";
		$table .= "<thead>";
//		$table .= "<tr><th colspan = '" . count( $columns ) ."'>$tableName</th></tr>";
		$table .= "<caption><b>$tableName</b></caption>";
		$table .= "<tr>";
		foreach( $columns as $column )
		{
			$column = trim( $column );
			$table .= "<th>$column</th>";
		}
		$table .= "</tr>";
		$table .= "</thead>";

		$table .= "<tbody>";
		foreach( $rows as $row )
		{
			$table .= "<tr>";
			foreach( $columns as $column )
			{
				$column = trim( $column );
				$table .= "<td>" . $row[$column] . "</td>";
			}
			$table .= "</tr>"; 
		} 

		$table .= "</tbody>";
		$table .= "</table>";
		
		return $table;
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

		fwrite( $file, "\n" );
		$users = $this->mDatabase->getUserAll();
	
		foreach( $users as $user )
		{
			fwrite( $file, "user: $user[_user] - pass: '$user[_pass]'\n" );
		}

		fclose( $file );
	}
}

?>