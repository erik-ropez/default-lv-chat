<?

define( 'DEBUG', 0 );
define( 'TRACE', 0 );
define( 'WRITESTATE', 0 );
define( 'WRITEMINISTATE', 1 );
define( 'WRITESTEP', 0 );
define( 'NL', "\r\n" );
define( 'MESSBUFLEN', 50 );

$bind = array(
	array( 'host' => '127.0.0.1', 'port' => '21' ),
	array( 'host' => '127.0.0.1', 'port' => '80' ),
	array( 'host' => '127.0.0.1', 'port' => '4173' )
//	array( 'host' => '127.0.0.1', 'port' => '4174' )
);

$db_login = 'login';
$db_paswd = 'paswd';
$db_name = 'name';
$url_key = 'key';
$url_keylen = strlen($url_key);
$db_log_query = true;
$db_log_query_time = 0;
$db_log_query_file = 'log/db.log';

?>