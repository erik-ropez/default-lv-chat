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

function debug( $s )
{
	if ( DEBUG ) echo $s; else mytrace( $s );
}

function mymax( &$a )
{
	if ( count( $a ) )
	{
		end( $a );
		return key( $a ) + 1;
	} else return 0;
}

function mytrace( $s )
{
	if ( TRACE ) trigger_error( $s );
}

function writeministate() {
	global $socks, $conns, $uid2sock, $iteration, $server_started;
	static $f = false;
	if (!WRITEMINISTATE) return;
	if ($f === false) $f = fopen('log/ministate', 'w');
	ftruncate($f, 0);
	fseek($f, 0, SEEK_SET);
	fwrite($f,
		'uptime='.(time() - $server_started).NL.
		'iteration='.$iteration.NL.
		'socks='.count($socks).NL.
		'conns='.count($conns).NL.
		'uid2sock='.count($uid2sock).NL);
	fflush($f);
}

function writestate($ws = false)
{
	global $socks, $conns, $uid2sock, $iteration, $server_started;
	static $f = false;
	if (!$ws && !WRITESTATE) return;
	if ( $f === false ) $f = fopen( 'log/state', 'w' );
	$t = time() - $server_started;
	ftruncate( $f, 0 );
	fseek( $f, 0, SEEK_SET );
	fwrite( $f,
		'uptime: '.(int)( $t / 60 ).':'.( $t % 60 ).NL.
		'iteration: '.$iteration.NL.
		'socks/conns/uid2sock: '.count( $socks ).'/'.count( $conns ).'/'.count( $uid2sock ).NL.
		'socks = '.var_export( $socks, true ).NL.
		'conns = '.var_export( $conns, true ).NL.
		'uid2sock = '.var_export( $uid2sock, true ).NL.
		''
	);
	fflush( $f );
}

function writestep( $s )
{
	static $f = false;
	static $f2 = false;
	static $a = array();
	static $u = false;
	if ( !WRITESTEP ) return;
	$i = mymax( $a );
	if ( $u === false ) $u = utime();
	$u2 = utime();
	$t = $u2 - $u;
	$u = $u2;
	if ( $t > 2 )
	{
		if ( $f2 === false )
		{
			$f2 = fopen( 'log/longstep', 'a' );
			fwrite( $f2, "----------\n" );
		}
		fwrite( $f2, sprintf( "%.3f\t%s\n", $t, $a[$i - 1] ) );
		fflush( $f2 );
	}
	$a[$i] = sprintf( "%d\t%.3f\t%s", $i, $t, $s );
	unset( $a[$i - 20] );
	if ( $f === false ) $f = fopen( 'log/step', 'w' );
	ftruncate( $f, 0 );
	fseek( $f, 0, SEEK_SET );
	fwrite( $f, implode( "\n", $a ) );
	fflush( $f );
}

function dumpquery( $sql )
{
	static $f = false;
	if ( $f === false ) $f = fopen( 'log/querys', 'a' );
	flock( $f, LOCK_EX );
	fseek( $f, 0, SEEK_END );
	fwrite( $f, $sql."\n" );
	fflush( $f );
	flock( $f, LOCK_UN );
}

function getloginuid( $login )
{
	$login = strtolower( $login );
	$p = $GLOBALS[data_dir].'/login2uid/'.$login{0}.$login{1}.'/'.$login;
	if ( !file_exists( $p ) ) return;
	return file_get_contents( $p );
}

function rooms_save()
{
	$f = fopen( $GLOBALS[data_dir].'/rooms', 'w' );
	fwrite( $f, serialize( $GLOBALS[rooms] ) );
	fclose( $f );
}

function ra_save()
{
	$f = fopen( $GLOBALS[data_dir].'/roomaop', 'w' );
	fwrite( $f, serialize( $GLOBALS[roomaop] ) );
	fclose( $f );
}

function rd_save()
{
	$f = fopen( $GLOBALS[data_dir].'/roomdeny', 'w' );
	fwrite( $f, serialize( $GLOBALS[roomdeny] ) );
	fclose( $f );
}

?>