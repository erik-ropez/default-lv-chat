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

function cmd_remroom( $params )
{
	global $this, $rooms, $rooms2sock, $conns, $defaultroom, $prisonroom;
	$rid = isset( $rooms[$params[1]] ) ? $params[1] : $this->rid;
	if ( $this->ring && $this->uid != $rooms[$rid][founderuid] ) return '¬ы не €вл€етесь создателем комнаты '.$rooms[rid][caption];
	$c = $rooms[$rid][caption];
	unset( $rooms[$rid] );
	rooms_save();
	$t = $this;
	$t->write_all( 'java_remroom('.$rid.');'.NL );
	foreach ( $rooms2sock[$rid] as $id )
	{
		$r = $conns[$id]->deny ? $prisonroom : $defaultroom;
		$GLOBALS[this] =& $conns[$id];
		$a = array( '', $r );
		cmd_changeroom( $a );
	}
	unset( $GLOBALS[roomsaop][$rid] );
	unset( $GLOBALS[roomsdeny][$rid] );
	ra_save();
	rd_save();
	unset( $GLOBALS[rooms2sock][$rid] );
	unset( $GLOBALS[mess][$rid] );
	unset( $GLOBALS[mess_public][$rid] );
	$t->write_all( '#'.$t->login.' уничтожил комнату '.$c.NL );
	return '¬ы уничтожили комнату';
}

?>