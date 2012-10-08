<?PHP

include '../database.php';

$db = new Database();
$db->setLog( true );
$db->init( "database.db" );
$db->addProduct( "prod1", "desc1" );
$db->addProduct( "prod2", "desc2" );
$db->printProductAll();

$db->delProduct( "prod1" );
$db->printProductAll();
$db->addProduct( "prod1", "desc1" );
$db->printProductAll();


$db->addValue( "prod1", "key11", "value11" );
$db->addValue( "prod1", "key12", "value12" );

$db->addValue( "prod2", "key21", "value21" );
$db->addValue( "prod2", "key22", "value22" );
$db->addValue( "prod2", "key23", "value23" );

$db->printValueAll( "prod1" );
$db->printValueAll( "prod2" );

$value = $db->getValue( "prod1", "key11" );

echo( "value of 'prod1', 'key11' is $value<BR>" );

$db->delValue( "prod2", "key22" );
$db->printValueAll( "prod2" );

?>