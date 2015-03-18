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

function cconn_post( &$this, $str )
{
	global $socks, $uid2sock, $conns, $http_header_post;

	debug( 'POST data: '.$str );
	$a = explode( '&', $str );
	foreach ( $a as $b )
	{
		$c = explode( '=', $b );
		$d[$c[0]] = $c[1];
	}
	if ( isset( $uid2sock[$d[uid]] ) )
	{
		$id = $uid2sock[$d[uid]];
		if ( $conns[$id]->ready && $conns[$id]->http && !strcmp( $conns[$id]->sendtag, $d[sendtag] ) )
				cconn_parse( $conns[$id], rawurldecode( preg_replace( '/\+/', ' ', $d[mess] ) ) );
	}
	@socket_write( $this->sock, $http_header_post );
	$this->closeme = true;
	@socket_close( $this->sock );
	$socks[$this->id] = null;
	unset( $socks[$this->id] );
	unset( $conns[$this->id] );

}

?>