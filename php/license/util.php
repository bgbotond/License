<?php

require_once( 'mutex.php' );

class Util
{
	private static $mSettings = false;
	
	/*
		create multiple directories
		path format: dir[/dir/dir...]
	*/
	public static function createPath( $path )
	{
		if( file_exists( $path ) == false )
		{
			$mode = 0700;
			mkdir( $path, $mode, TRUE );
		}
	}
	
	/*
		convert string to arrayValue
		text format        : "key1=value1[:::key2=value2:::key3=value3...]" in case of separator = ":::"
		output array value : array( key1 => value1 [, key2 => value2, key3 => value3 ...]
	*/
	public static function string2ArrayValue( $text, $separator )
	{
		$arrayValue = array();
		
		$pairs  = explode( $separator, $text );
		foreach( $pairs as $pair )
		{
			list( $key, $value ) = explode( "=", $pair );
			$arrayValue[$key] = $value;
		}
		
		return $arrayValue;
	}
	
	/*
		convert arrayValue to string
		array value        : array( key1 => value1 [, key2 => value2, key3 => value3 ...]
		output text format : "key1=value1[:::key2=value2:::key3=value3...]" in case of separator = ":::"
	*/
	public static function arrayValue2string( $arrayValue, $separator )
	{
		$text = "";
		$first = true;
		
		foreach( $arrayValue as $key => $value )
		{
			if( $first == true )
				$first = false;
			else
				$text .= $separator;
				
			$text .= $key . "=" . $value;
		}
		
		return $text;
	}

	/*
		add value to a string
		output text format : "[key1=value1:::key2=value2...:::]key3=value3" in case of separator = ":::"
	*/
	public static function addValue( &$text, $key, $value, $separator )
	{
		if( $text != "" )
			$text .= $separator;
			
		$text .= $key . "=" . $value;
	}

	/*
		get value from ini file
	*/
	public static function getIni( $section, $key, $default = null )
	{
		if( self::$mSettings === false )
		{
			$iniFile = "settings.ini";
			$mutex = new Mutex();
			$mutex->init( $iniFile );
			$mutex->lock();
			self::$mSettings = parse_ini_file( $iniFile, true );
			$mutex->unlock();
			$mutex = null;
		}
	
		if( array_key_exists( $section, self::$mSettings ))
		{
			foreach( self::$mSettings[$section] as $iniKey => $iniValue )
			{
				if ( $iniKey == $key )
					return $iniValue;
			}
		}
		
		return $default;
	}
	
	/*
		generate a given length string
	*/
	public static function genString( $length = 0 )
	{
		$code = md5( uniqid( rand(), true ));
		if( $length > 0 )
			$code = substr( $code, 0, $length );
			
		return $code;
	}
	
	/*
		send mail
	*/
	public static function mail( $subject, $message )
	{
		$to = self::getIni( "ENVIRONMENT", "MAIL_TO" );
		
		if( $to != "" )
		{
			return mail( $to, $subject, $message );
		}
		
		return false;
	}
	
	/*
		get time
	*/
	public static function getTime()
	{
		return date( "Y.m.d H.i.s", time());
	}
}

?>