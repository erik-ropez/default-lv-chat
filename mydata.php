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

function getmyvalue( $tag, $fn, $lock = false )
{
	$p = $GLOBALS[data_dir].'/'.$tag.'/'.( $fn % 100 ).'/'.$fn;
	if ( !file_exists( $p ) ) return;
	if ( !$lock ) return file_get_contents( $p );
	$f = fopen( $p, 'r' );
	flock( $f, LOCK_SH );
	$v = fgets( $f, 102400 );
	flock( $f, LOCK_UN );
	fclose( $f );
	return $v;
}

function savemyvalue( $tag, $fn, $value, $lock = false )
{
	if (!strcmp($tag, 'login2uid')) {
		$d = $GLOBALS[data_dir].'/login2uid/'.$fn{0}.$fn{1};
		if ( !file_exists( $d ) ) mkdir( $d );
		$p = $d.'/'.$fn;
		$f = fopen( $p, 'w' );
		fwrite( $f, $value );
		fclose( $f );
		return;
	}

	$d = $GLOBALS[data_dir].'/'.$tag.'/'.( $fn % 100 );
	if ( !file_exists( $d ) ) mkdir( $d );
	$p = $d.'/'.$fn;
	$f = fopen( $p, 'w' );
	if ( $lock ) flock( $f, LOCK_EX );
	fwrite( $f, $value );
	if ( $lock ) flock( $f, LOCK_UN );
	fclose( $f );
}

function getmydata( $tag, $fn, $lock = false )
{
	return unserialize( getmyvalue( $tag, $fn, $lock ) );
}

function savemydata( $tag, $fn, $data, $lock = false )
{
	savemyvalue( $tag, $fn, serialize( $data ), $lock );
}

function getmydatak( $tag, $fn, $key, $lock = false )
{
	$a = getmydata( $tag, $fn, $lock );
	if ( !is_array( $key ) ) return $a[$key];
	$b = array();
	foreach ( $key as $i => $k ) $b[$i] = $a[$k];
	return $b;
}

function savemydatak( $tag, $fn, $key, $data, $lock = false )
{
	$a = getmydata( $tag, $fn, $lock );
	if ( is_array( $key ) )
	{
		foreach ( $key as $i => $k )
			$a[$k] = $data[$i];
	} else $a[$key] = $data;
	savemydata( $tag, $fn, $a, $lock );
}

?>