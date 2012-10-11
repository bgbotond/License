<?php

require_once( 'log.php' );

class Mutex extends Log
{
	private $mLocked      = null;
	private $mFileName    = null;
	private $mFileHandler = null;

	function __construct()
	{
		$this->mLocked = false;
		$this->setPrefix( "Mutex" );
	}

	public function init( $fileName = '' )
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
			$this->log( "error file '$fileName' doesn't exist" );
			return false;
		}
		else
		{
			$this->mFileName = $fileName;
		}

		$this->log( "file '$this->mFileName' inited" );
		return true;
	}
	
	public function lock()
	{
		if( $this->mLocked )
		{
			$this->log( "the file is already locked" );
			return true;
		}

		if(( $this->mFileHandler = fopen( $this->mFileName, "rw" )) == false )
		{
			$this->log( "error opening mutex file: '$this->mFileName'" );
			return false;
		}
		
		if( flock( $this->mFileHandler, LOCK_EX ) == false )
		{
			$this->log( "error locking mutex file" );
			return false;
		}

		$this->mLocked = true;
		$this->log( "file '$this->mFileName' locked" );
		return true;
	}

	public function unlock()
	{
		if( ! $this->mLocked )
		{
			$this->log( "the file is not locked" );
			return true;
		}

		if( flock( $this->mFileHandler, LOCK_UN ) == false )
		{
			$this->log( "error unlocking file" );
			return false;
		}

		fclose( $this->mFileHandler );
		$this->mFileHandler = null;

		$this->mLocked = false;
		$this->log( "file '$this->mFileName' unlocked" );
		return true;
	}
}

?>
