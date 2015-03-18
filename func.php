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

/*
**	Common functions 21.11.03
*/

function utime()
{
	list($u,$s) = explode( ' ', microtime() );
	return ( (float)$u + (float)$s );
}

function query( $sql )
{
	global $db_on, $db_log_query;

	if ( !$db_on )
	{
		global $db_host, $db_login, $db_paswd, $db_name;
		@mysql_connect( $db_host ? $db_host : 'localhost', $db_login, $db_paswd ) || die( mysql_error() );
		@mysql_select_db( $db_name ) || die ( mysql_error() );
		$db_on = true;
	}
	
	if ( $db_log_query )
	{
		global $db_log_query_time, $db_log_query_file;
		$t1 = utime();
		$r = mysql_query( $sql );
		$t2 = utime();
		if ( $t2 - $t1 >= $db_log_query_time )
		{
			$f = fopen( $db_log_query_file, 'a' );
			fwrite( $f, date( 'Y-m-d H:i:s' ).sprintf( "\t%.3f\t%d/%d\t%d\t%s\n", $t2 - $t1, @mysql_affected_rows(), @mysql_num_rows( $r ), mysql_errno(), $sql ) );
			fclose( $f );
		}
		return $r;
	} else return mysql_query( $sql );
}

function query_free( $sql )
{
	$r = query( $sql );
	mysql_free_result( $r );
}

function query_value( $sql )
{
	$r = query( $sql );
	if ( mysql_num_rows( $r ) )
	{
		$v = mysql_result( $r, 0, 0 );
		mysql_free_result( $r );
		return $v;
	}
	mysql_free_result( $r );
	return;
}

function query_row( $sql )
{
	$r = query( $sql );
	$a = mysql_fetch_row( $r );
	mysql_free_result( $r );
	return $a;
}

function query_test( $sql )
{
	$r = query( $sql );
	$a = mysql_num_rows( $r );
	mysql_free_result( $r );
	return $a;
}

function includeex( $f )
{
	global $get, $lang, $words;
	ob_start();
	include$f;
	$s = ob_get_contents();
	ob_end_clean();
	return $s;
}

function trimmax( &$i, $m )
{
	$i = (int)( $i );
	if ( $i < 0 || $i > $m ) $i = 0;
}

function _url( $argv )
{
	global $url_key, $url_keylen;
	$argc = count( $argv );
	for ( $i = 0; $i < $argc; $i++ ) $s .= str_pad( dechex( $argv[$i] ), 8, '0', STR_PAD_LEFT );
	$c = $argc * 8;
	for ( $i = 0; $i < $c; $i++ ) $g .= chr( ord( $s[$i] ) ^ ord( $url_key[$i % $url_keylen] ) );
	$a = array();
	$g = base64_encode( $g );
	$i = strpos( $g, '=' );
	if ( $i ) $g = substr( $g, 0, $i );
	if ( strlen( $g ) ) return 'i='.$g;
}

function url()
{
	global $url_key, $url_keylen;
	$argc = func_num_args() - 1;
	$argv = func_get_args();
	for ( $i = 1; $i <= $argc; $i++ ) $get[$i - 1] = $argv[$i];
	$a = array();
	$g = _url( $get );
	if ( strlen( $g ) ) $a[] = $g;
	if ( is_string( $argv[0] ) && strlen( $argv[0] ) ) $a[] = $argv[0];
	$a = join( '&', $a );
	if ( strlen( $a ) ) $a = '?'.$a;
	return $a;
}

function ur2()
{
	global $url_key, $url_keylen;
	$argc = func_num_args();
	$argv = func_get_args();
	for ( $i = 0; $i < $argc; $i++ ) $get[$i] = $argv[$i];
	$a = array();
	$a = _url( $get );
	if ( strlen( $a ) ) $a = '?'.$a;
	return $a;
}

function url_ref()
{
	$a = parse_url( $_SERVER[HTTP_REFERER] );
	$q = $a[query];
	$a = explode( '&', $q );
	$c = array();
	foreach ( $a as $b )
	{
		list($k,$v) = explode( '=', $b );
		$c[$k] = $v;
	}
	$get = _decodeurl( $c[i] );
	$argc = func_num_args() - 1;
	$argv = func_get_args();
	$s = $argv[0];
	for ( $i = 1; $i <= $argc; $i++ ) $get[$s + $i - 1] = $argv[$i];
	return '?'._url( $get );
}

function strtolower_ru( $s )
{
	static $RU_2_ru = array(
		'�' => '�',
		'�' => '�',
		'�' => '�',
		'�' => '�',
		'�' => '�',
		'�' => '�',
		'�' => '�',
		'�' => '�',
		'�' => '�',
		'�' => '�',
		'�' => '�',
		'�' => '�',
		'�' => '�',
		'�' => '�',
		'�' => '�',
		'�' => '�',
		'�' => '�',
		'�' => '�',
		'�' => '�',
		'�' => '�',
		'�' => '�',
		'�' => '�',
		'�' => '�',
		'�' => '�',
		'�' => '�',
		'�' => '�',
		'�' => '�',
		'�' => '�',
		'�' => '�',
		'�' => '�',
		'�' => '�',
		'�' => '�'
	);
	return strtr( strtolower( $s ), $RU_2_ru );
}

function win1251_2_utf8( $s )
{
	static $win1251_2_utf8 = array(
		'�' => 'Ё',
		'�' => 'ё',
		'�' => 'А',
		'�' => 'Б',
		'�' => 'В',
		'�' => 'Г',
		'�' => 'Д',
		'�' => 'Е',
		'�' => 'Ж',
		'�' => 'З',
		'�' => 'И',
		'�' => 'Й',
		'�' => 'К',
		'�' => 'Л',
		'�' => 'М',
		'�' => 'Н',
		'�' => 'О',
		'�' => 'П',
		'�' => 'Р',
		'�' => 'С',
		'�' => 'Т',
		'�' => 'У',
		'�' => 'Ф',
		'�' => 'Х',
		'�' => 'Ц',
		'�' => 'Ч',
		'�' => 'Ш',
		'�' => 'Щ',
		'�' => 'Ъ',
		'�' => 'Ы',
		'�' => 'Ь',
		'�' => 'Э',
		'�' => 'Ю',
		'�' => 'Я',
		'�' => 'а',
		'�' => 'б',
		'�' => 'в',
		'�' => 'г',
		'�' => 'д',
		'�' => 'е',
		'�' => 'ж',
		'�' => 'з',
		'�' => 'и',
		'�' => 'й',
		'�' => 'к',
		'�' => 'л',
		'�' => 'м',
		'�' => 'н',
		'�' => 'о',
		'�' => 'п',
		'�' => 'р',
		'�' => 'с',
		'�' => 'т',
		'�' => 'у',
		'�' => 'ф',
		'�' => 'х',
		'�' => 'ц',
		'�' => 'ч',
		'�' => 'ш',
		'�' => 'щ',
		'�' => 'ъ',
		'�' => 'ы',
		'�' => 'ь',
		'�' => 'э',
		'�' => 'ю',
		'�' => 'я'
	);
	
	return strtr( $s, $win1251_2_utf8 );
}

function ruutf( $s )
{
	return win1251_2_utf8( $s );
}

function win1257_2_utf8( $s )
{
	static $win1257_2_utf8 = array(
		'�' => 'Ā',
		'�' => 'Ē',
		'�' => 'Č',
		'�' => 'Ğ',
		'�' => 'Ķ',
		'�' => 'Ī',
		'�' => 'Ļ',
		'�' => 'Š',
		'�' => 'Ņ',
		'�' => 'Ū',
		'�' => 'Ž',
		'�' => 'ā',
		'�' => 'ē',
		'�' => 'č',
		'�' => 'ğ',
		'�' => 'ķ',
		'�' => 'ī',
		'�' => 'ļ',
		'�' => 'š',
		'�' => 'ņ',
		'�' => 'ū',
		'�' => 'ž'
	);

	return strtr( $s, $win1257_2_utf8 );
}

function lvutf( $s )
{
	return win1257_2_utf8( $s );
}

function echol()
{
	return func_get_arg( $GLOBALS[lang] - 1 );
}

function convtext( $s )
{
	return preg_replace( '/\n+/', '<br>', preg_replace( '/\r+/', '', htmlspecialchars( trim( $s ) ) ) );
}

?>