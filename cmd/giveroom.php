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

function cmd_giveroom( $params )
{
	global $this, $rooms, $roomaop, $roomdeny, $conns, $uid2sock;
	$uid = getloginuid( $params[1] );
	if ( !$uid ) return 'Неизвестный ник';
	$rid = $this->rid;
	if ( $this->ring && $this->uid != $rooms[$rid][founderuid] ) return 'Вы не являетесь создателем комнаты '.$rooms[rid][caption];
	if ( !$rooms[$rid][founderuid] ) return 'Данная комната не может иметь хозяина';
	unset( $roomaop[$rid][$uid] );
	unset( $roomdeny[$rid][$uid] );
	$roomaop[$rid][$this->uid] = $this->login;
	ra_save();
	rd_save();
	$rooms[$rid][founderuid] = $uid;
	$rooms[$rid][founderlogin] = $params[1];
	rooms_save();
	if ( $uid2sock[$uid] && $conns[$uid2sock[$uid]]->rid == $rid )
		$this->write_room( 'java_chnuser2('.$uid.',0);'.NL, true );
	$this->write_room( 'java_chnuser2('.$this->uid.',3);'.NL, true );
	$this->send( '['.$this->login.'] передал Вам комнату '.$rooms[$rid][caption], $uid, 0 );
	return 'Комната передана';
}

?>