<?PHP

include '../trace.php';

$trace = new Trace();
$trace->setLog( true );
$trace->init( "log.log" );

for( $i = 0; $i < 10000; $i++ )
{
	$trace->write( "TEST1", "message 1" );
	$trace->write( "TEST1", "warning message 1", Trace::WARNING );
}

echo "done<br>";

echo "file content all:<br>";
$trace->read();
echo "file content WARNING:<br>";
$trace->read( Trace::WARNING );
echo "file content INFORMATION:<br>";
$trace->read( Trace::INFORMATION );

?>