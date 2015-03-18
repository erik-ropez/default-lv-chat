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

function cmd_onlyop( $params )
{
	global $this, $rooms, $roomaop, $rooms2sock, $conns, $uid2sock, $prisonroom, $defaultroom;
	$rid = isset( $rooms[$params[1]] ) ? $params[1] : $this->rid;
	if ( $this->ring && $this->uid != $rooms[$rid][founderuid] ) return '¬ы не €вл€етесь создателем комнаты '.$rooms[rid][caption];
	if ( !$rooms[$rid][founderuid] ) return 'ƒанна€ комната не может быть закрытой';
	if ( isset( $rooms[$rid][onlyop] ) )
	{
		unset( $rooms[$rid][onlyop] );
		$o = false;
		rooms_save();
		$this->write_all( '#'.$this->login.' перевел комнату '.$rooms[$rid][caption].' в открытый тип'.NL );
		return ' омната переведена в открытый тип';
	} else
	{
		$rooms[$rid][onlyop] = true;
		$o = true;
		rooms_save();
		$ra = $roomaop[$rid];
		$founder = $rooms[$rid][founderuid];
		$this->write_all( '#'.$this->login.' перевел комнату '.$rooms[$rid][caption].' в закрытый тип'.NL );
		foreach( $rooms2sock[$rid] as $id )
		{
			if ( !empty( $ra[$conns[$id]->uid] ) || $conns[$id]->uid == $founder ) continue;
			$r = $conns[$id]->deny ? $prisonroom : $defaultroom;
			$GLOBALS[this] =& $conns[$id];
			$a = array( '', $r );
			cmd_changeroom( $a );
		}
		return ' омната переведена в закрытый тип';
	}
}

?>