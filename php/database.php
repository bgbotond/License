<?PHP

require_once( 'mutex.php' );
require_once( 'log.php'   );

/*
	database tables
	
	+----------------+  +-----------------+
	|   ProductTab   |  |    ValueTab     |
	+----------------+  +-----------------+
	| _id    integer |  | _id     integer |
	| _name  string  |  | _key    string  |
	| _desc  string  |  | _value  string  |
	+----------------+  +-----------------+
*/

class Database extends Log
{
	private $mFileName = null;
	private $mDBFile   = null;
	private $mDBLite   = null;
	private $mMutex    = null;

	private function getFileName()
	{
		return $this->mFileName;
	}

	private function getDBLite()
	{
		return $this->mDBLite;
	}
	
	private function open()
	{
		$this->mMutex->lock();
		$this->log( "open database" );
		$this->mDBLite = new PDO( "sqlite:" . $this->getFileName());
		
		// check if the DB tables exist
		$result = $this->getDBLite()->query( "SELECT name FROM sqlite_master WHERE type = 'table' AND name = 'ProductTab'" );
		$rows = $result->fetch( PDO::FETCH_ASSOC );
		if( ! $rows )
		{
			$this->log( "create tables" );
			$this->getDBLite()->exec( "CREATE TABLE ValueTab  ( _id INTEGER            , _key  STRING        NOT NULL, _value STRING )" );
			$this->getDBLite()->exec( "CREATE TABLE ProductTab( _id INTEGER PRIMARY KEY, _name STRING UNIQUE NOT NULL, _desc  STRING )" );
		}
	}

	private function close()
	{
		$this->log( "close database" );
		unset( $this->mDBLite );
		$this->mDBLite = null;
		$this->mMutex->unlock();
	}
	
	public function setLog( $log )
	{
		parent::setLog( $log );
		$this->mMutex->setLog( $log );
	}
	
	public function __construct()
	{
		$this->mLog      = false;
		$this->mMutex    = new Mutex();
		$this->setPrefix( "Database" );
	}

	public function init( $fileName )
	{
		if( $this->mFileName != null )
		{
			$this->log( "already inited with file '$this->mFileName'" );
			return false;
		}
	
		if( empty( $fileName ))
		{
			$this->log( "error no filename specified" );
			return false;
		}
		else if( file_exists( $fileName ) == false )
		{
			touch( $fileName );
			$this->log( "file '$fileName' has been created" );
		}

		$this->mMutex->init( $fileName );
		
		$this->mFileName = $fileName;
		$this->log( "file '$this->mFileName' inited" );
		return true;
	}
	
	// -----------------------
	// ProductTab functions
	// -----------------------
	public function addProduct( $name, $desc )
	{
		$this->open();
		$ret = $this->_addProduct( $name, $desc );
		$this->close();

		return $ret;
	}

	public function delProduct( $name )
	{
		$this->open();
		$ret = $this->_delProduct( $name );
		$this->close();
		
		return $ret;
	}
	
	public function findProduct( $name )
	{
		$this->open();
		$id = $this->_findProduct( $name );
		$this->close();
		
		if( $id == -1 )
			return false;
		
		return true;
	}
	
	public function getProductAll()
	{
		$this->open();
		$rows = $this->_getProductAll( $name );
		$this->close();
		
		return $rows;
	}
	
	private function _addProduct( $name, $desc )
	{
		$this->log( "addProduct $name, $desc" );
		$id = $this->_findProduct( $name );
		
		if( $id != -1 )
		{
			$this->log( "product already exist $id" );
			return false;
		}
		
		$id = $this->_getNextId();
		$this->getDBLite()->exec( "INSERT INTO ProductTab VALUES ( $id, '$name', '$desc' )" );
		$this->log( "inserted" );
		
		return true;
	}

	private function _delProduct( $name )
	{
		$this->log( "delProduct $name" );
		$id = $this->_findProduct( $name );
		
		if( $id == -1 )
		{
			$this->log( "product not found" );
			return false;
		}

		$this->getDBLite()->exec( "DELETE FROM ValueTab   WHERE _id = $id" );
		$this->getDBLite()->exec( "DELETE FROM ProductTab WHERE _id = $id" );
		$this->log( "deleted" );
		
		return true;
	}
	
	private function _findProduct( $name )
	{
		$this->log( "findProduct $name" );
		$result = $this->getDBLite()->query( "SELECT _id FROM ProductTab WHERE _name = '$name'" );
		$rows = $result->fetch( PDO::FETCH_ASSOC );
		if( $rows )
		{
			$this->log( "found $rows[_id]" );
			return $rows[_id];
		}
		
		$this->log( "not found" );
		return -1;
	}

	private function _getProductAll()
	{
		$this->log( "getProductAll" );
		$result = $this->getDBLite()->query( "SELECT * FROM ProductTab ORDER BY _id" );
		$rows = $result->fetchAll( PDO::FETCH_ASSOC );

		$this->log( "return array" );
		return $rows;
	}
	
	public function printProductAll()
	{
		$this->log( "printProductAll" );
		$rows = $this->getProductAll();

		foreach( $rows as $row )
		{
			echo "$row[_id] - $row[_name] - $row[_desc]<BR>";
		}
	}
	
	// -----------------------
	//	ValueTab functions
	// -----------------------
	public function addValue( $name, $key, $value )
	{
		$this->open();
		$ret = $this->_addValue( $name, $key, $value );
		$this->close();
		
		return $ret;
	}
	
	public function delValue( $name, $key )
	{
		$this->open();
		$ret = $this->_delValue( $name, $key );
		$this->close();

		return $ret;
	}
	
	public function getValue( $name, $key )
	{
		$this->open();
		$value = $this->_getValue( $name, $key );
		$this->close();
		
		return $value;
	}
	
	public function getValueAll( $name )
	{
		$this->open();
		$rows = $this->_getValueAll( $name );
		$this->close();
		
		return $rows;
	}
	
	private function _addValue( $name, $key, $value )
	{
		$this->log( "addValue $name, $key, $value" );
		$id = $this->_findProduct( $name );
		
		if( $id == -1 )
		{
			$this->log( "no product found" );
			return false;
		}
		
		$this->_delValue( $name, $key );
		
		$this->getDBLite()->exec( "INSERT INTO ValueTab VALUES ( $id, '$key', '$value' )" );
		$this->log( "inserted" );
		
		return true;
	}

	private function _delValue( $name, $key )
	{
		$this->log( "delValue $name, $key" );
		$id = $this->_findProduct( $name );
		
		if( $id == -1 )
		{
			$this->log( "no product found" );
			return false;
		}
		
		$this->getDBLite()->exec( "DELETE FROM ValueTab WHERE _id = $id and _key = '$key'" );
		$this->log( "deleted" );

		return true;
	}

	private function _getValue( $name, $key )
	{
		$this->log( "getValue $name, $key" );
		$id = $this->_findProduct( $name );
		
		if( $id == -1 )
		{
			$this->log( "no product found" );
			return "";
		}
			
		$result = $this->getDBLite()->query( "SELECT _value FROM ValueTab WHERE _id = $id and _key = '$key'" );
		$rows = $result->fetch( PDO::FETCH_ASSOC );
		if( $rows )
		{
			$this->log( "found: $rows[_value]" );
			return $rows[_value];
		}
		
		$this->log( "not found" );
		return "";
	}

	private function _getValueAll( $name )
	{
		$this->log( "getValueAll $name" );
		$id = $this->_findProduct( $name );
		
		if( $id == -1 )
		{
			$this->log( "no product found" );
			return array();
		}
			
		$result = $this->getDBLite()->query( "SELECT * FROM ValueTab WHERE _id = $id" );
		$rows = $result->fetchAll( PDO::FETCH_ASSOC );
		$this->log( "return array" );
		return $rows;
	}
	
	public function printValueAll( $name )
	{
		$this->log( "printValueAll" );
		$rows = $this->getValueAll( $name );
		if( $rows )
		{
			foreach( $rows as $row )
			{
				echo "$name - $row[_id] - $row[_key] - $row[_value]<BR>";
			}
		}
	}

	private function _getNextId()
	{
		$this->log( "getNextId" );
		$id = 1;
		
		$result = $this->getDBLite()->query( "SELECT max( _id ) FROM ProductTab" );
		$rows = $result->fetch( PDO::FETCH_NUM );
		if( $rows )
		{
			$this->log( "found max value in DB" );
			$id = $rows[0] + 1;
		}
		
		$this->log( "getNextId: $id" );
		return $id;
	}
}

?>