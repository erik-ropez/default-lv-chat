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

function cmd_equal( $params )
{
	global $this, $uid2sock, $conns;

	if ( !isset( $params[2] ) ) $ip = $this->hostlong; else
	{
		if ( preg_match( '/^\d+\.\d+\.\d+\.\d+$/', $params[2] ) ) $ip = ip2long( $params[2] ); else
		{
			$uid = getloginuid( $params[2] );
			if ( !$uid ) return 'Неизвестный ник';
			$ip = isset( $uid2sock[$uid] ) ? $conns[$uid2sock[$uid]]->hostlong : getmydatak( 'users2', $uid, 'host' );
		}
	}

	$s = 'IP: '.long2ip( $ip );
	$host = gethostbyaddr( long2ip( $ip ) );
	if ( strcmp( long2ip( $ip ), $host ) ) $s .= '<br>Хост: '.$host;

	if ( !isset( $params[4] ) ) $mask = ip2long( '255.255.255.255' ); else
	{
		if ( preg_match( '/^\d+\.\d+\.\d+\.\d+$/', $params[4] ) ) $mask = ip2long( $params[4] ); else
		{
			if ( $params[4] > 16 || $params[4] < 1 ) return 'Неправильная маска';
			$mask = 0xffffffff << $params[4];
		}
		$s .= '<br>Маска: '.long2ip( $mask );
		$ip = $ip & $mask;
		$s .= '<br>IP & Маска: '.long2ip( $ip );
	}

	$a = array();
	foreach ( $conns as $id => $c )
	{
		if ( !$conns[$id]->ready ) continue;
		if ( ( $conns[$id]->hostlong & $mask ) == $ip ) $a[] = $conns[$id]->login;
	}
	$s .= '<br>';
	if ( count( $a ) ) $s .= implode( '<br>', $a );
	else $s .= 'Из данной сети в чате никто не находится';

	return $s;
}

?>