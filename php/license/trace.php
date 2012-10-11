<?php

require_once( 'mutex.php' );
require_once( 'log.php'   );
require_once( 'util.php'  );

/*
	trace layout
	
	TIME|LEVEL|OWNER|MESSAGE
	
	where
	
	TIME    - YYYY.MM.DD hh.mm.ss
	LEVEL   - INFORMATION or WARNING
	OWNER   - The message publisher
	MESSAGE - The message itself
*/

class Trace extends Log
{
	private $mFileName = null;
	private $mMutex    = null;

	const INFORMATION  = "INFORMATION";
	const WARNING      = "WARNING";

	public function __construct()
	{
		$this->mMutex = new Mutex();
		$this->setPrefix( "Trace" );
	}

	public function setLog( $log )
	{
		parent::setLog( $log );
		$this->mMutex->setLog( $log );
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
			chmod( $fileName, 0600 );  
			$this->log( "file '$fileName' has been created" );
		}

		$this->mMutex->init( $fileName );
		
		$this->mFileName = $fileName;
		$this->log( "file '$this->mFileName' inited" );
		return true;
	}
	
	public function write( $owner, $text, $type = Trace::INFORMATION )
	{
		$this->log( "write into file '$this->mFileName'" );
		$this->mMutex->lock();
		$time = Util::getTime();
		$file = fopen( $this->mFileName, "a" );
		$line = $time . "|" . $type . "|" . $owner . "|" . $text . "\n";
		fwrite( $file, $line );
		fclose( $file );
		$this->mMutex->unlock();
		
		return $line;
	}

	public function read( $type = "" )
	{
		$this->log( "read from file '$this->mFileName'" );
		$this->mMutex->lock();

		$file = fopen( $this->mFileName, "r" );

		while( feof( $file ) == false )
		{
			$line = fgets( $file, 1024 );

			if( $type != "" )
			{
				if( strpos( $line, $type ) !== 20 )	// 20 -> begining of type after time information
				{
					continue;
				}
			}
			echo "$line<br>";
		}

		fclose( $file );
		$this->mMutex->unlock();
	}
	
	public function getRows( &$columnNames, $type = "" )
	{
		$rows = array();
		$columnNames = "Time, Level, Owner, Message";
		
		$this->log( "getRows from file '$this->mFileName'" );
		$this->mMutex->lock();

		$file = fopen( $this->mFileName, "r" );

		while( feof( $file ) == false )
		{
			$line = fgets( $file, 1024 );

			if( $type != "" )
			{
				if( strpos( $line, $type ) !== 20 )	// 20 -> begining of type after time information
				{
					continue;
				}
			}
			
			if( $line == "" )
				continue;
			
			$entries = explode( "|", $line );
			$row = array();
			$row['Time']    = $entries[0];
			$row['Level']   = $entries[1];
			$row['Owner']   = $entries[2];
			$row['Message'] = $entries[3];

			array_push( $rows, $row );
		}

		fclose( $file );
		$this->mMutex->unlock();
		
		return $rows;
	}	
};

?>