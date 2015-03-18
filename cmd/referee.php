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

function cmd_referee( $params )
{
	global $referee, $cmdz_exc;

	if ( isset( $params[2] ) )
	{
		$l = strtolower( $params[2] );
		if ( isset( $referee[$l] ) )
		{
			dumpquery( 'delete from referee where uid='.$referee[$l] );
			unset( $referee[$l] );
			$a = array_keys( $cmdz_exc[undeny], $l );
			foreach ( $a as $i ) unset( $cmdz_exc[undeny][$i] );
			return '['.$params[2].'] удален из списка судей';
		} else
		{
			if ( !( $uid = getloginuid( $l ) ) ) return 'Неизвестный акаунт';
			dumpquery( 'insert into referee values ('.$uid.')' );
			$referee[$l] = $uid;
			$cmdz_exc[undeny][] = $l;
			return '['.$params[2].'] добавлен в список судей';
		}
	} else
	{
		$a = array_keys( $referee );
		sort( $a );
		return implode( '<br>', $a );
	}
}

?>