<?php

class Log
{
	private $mLog    = null;
	private $mPrefix = "Log";

	protected function setPrefix( $prefix )
	{
		$this->mPrefix = $prefix;
	}
	
	protected function log( $text )
	{
		if( $this->getLog() == false )
			return;
			
		echo "$this->mPrefix: $text<BR>";
	}
	
	public function setLog( $log )
	{
		$this->mLog = $log;
	}

	public function getLog()
	{
		return $this->mLog;
	}
}

?>