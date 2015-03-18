<?
/*

    Copyright (C) 2006 Erik Bonder <ropez@default.lv>

    This file is part of DeFault.Chat.

    DeFault.Chat is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    DeFault.Chat is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with DeFault.Chat; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/
?>
<?

function myerror( $errno, $errstr, $errfile, $errline )
{
	static $errors = array( E_ERROR, E_WARNING, E_PARSE, E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR, E_COMPILE_WARNING, E_USER_NOTICE );
	if ( !in_array( $errno, $errors ) ) return;
	$f = fopen( 'log/error', 'a' );
	fputs( $f, date( 'Y-m-d H:i:s' ).': '.$errfile.': '.$errline.': '.$errstr.NL );
	fclose( $f );
}

$data_dir = 'data';
include'mydata.php';
include'config.php';
include'func.php';
include'myfunc.php';
include'cconn.php';
include'cmd.php';

if ( !DEBUG )
{
	error_reporting( 0 );
	set_error_handler( 'myerror' );
} else set_time_limit( 0 );

ob_implicit_flush();

// Main arrays

$socks = array();	// array of client sockets; id_sock => socket
$conns = array();	// array of cconn; id_sock => cconn
$uid2sock = array();	// uid => id_sock
$rooms = array();	// rid => (caption, topic)
$rooms2sock = array();	// rid => array( id_sock )
$mess = array();
$mess_public = array();
$roomaop = array();
$roomdeny = array();

// Rooms

$rooms = unserialize( file_get_contents( $GLOBALS[data_dir].'/rooms' ) );
foreach ( $rooms as $rid => $room )
{
	$rooms2sock[$rid] = array();
	$mess[$rid] = array();
	$mess_public[$rid] = array();
	if ( $room[prison] ) $prisonroom = $rid;
}
$defaultroom = min( array_keys( $rooms ) );

$roomaop = unserialize( file_get_contents( $GLOBALS[data_dir].'/roomaop' ) );
$roomdeny = unserialize( file_get_contents( $GLOBALS[data_dir].'/roomdeny' ) );

// HTTP headers

$http_header = file_get_contents( 'http_header' );
$http_header_failed = file_get_contents( 'http_header_failed' );
$http_header_post = file_get_contents( 'http_header_post' );

// Referee

$referee = array();
$cmdz_exc[undeny] = array();
$r = query( 'select t1.uid,t2.login from referee as t1 left join users as t2 on t1.uid=t2.id order by t2.login' );
while ( list($i,$l) = mysql_fetch_row( $r ) )
{
	$l = strtolower( $l );
	$referee[$l] = $i;
	$cmdz_exc[undeny][] = $l;
}

// Deny

$denyz = array();
$r = query( 'select type,caption,description from deny_type where b_hidden=0' );
while ( list($t,$c,$d) = mysql_fetch_row( $r ) )
{
	$denyz[$t] = array( 'caption' => $c, 'descr' => $d );
	$r2 = query( 'select level,caption,description,ring,time,credits from deny_level where type='.$t.' && b_hidden=0' );
	while ( list($l,$c,$d,$g,$m,$s) = mysql_fetch_row( $r2 ) )
		$denyz[$t][$l] = array( 'caption' => $c, 'descr' => $d, 'ring' => $g, 'time' => $m, 'credits' => $s );
}

// Clubs
$clubs = unserialize( file_get_contents( $GLOBALS[data_dir].'/clubs' ) );
$clubs_cnt = array();

// Other stuff

query( 'truncate online' );

mysql_close();

$i_img_counter = 1;

$f_input = fopen('log/input', 'w+');

?>