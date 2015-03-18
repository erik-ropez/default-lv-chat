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

function cmd_topic( $params )
{
	global $this, $rooms, $roomaop, $cmdz_exc;
	$rid = $this->rid;
	if ( $this->ring && $rooms[$rid][founderuid] && $this->uid != $rooms[$rid][founderuid] && empty( $roomaop[$rid][$this->uid] ) && !$this->roomop[$rid] ) return 'У вас нет таких прав';
	if ( !$rooms[$rid][founderuid] && $this->ring > 1 && !in_array( strtolower( $this->login ), $cmdz_exc[topic] ) ) return 'У вас нет таких прав';
	if ( isset( $params[1] ) )
	{
		$t = preg_replace( '/\&lt\;img(\d+)\&gt\;/', '<img align=absmiddle src="http://static.default.lv/img/chat/symbols/\\1.gif" class=sym>', $params[1] );
		$t = str_replace( '|', '<font class=god>|</font>', $t );
//		dumpquery( 'update rooms set topic="'.$t.'" where id='.$this->rid );
		$rooms[$this->rid][topic] = $t;
		$rooms[$this->rid][topicwhouid] = $this->uid;
		$rooms[$this->rid][topicwhologin] = $this->login;
		$rooms[$this->rid][topicwhen] = time();
		rooms_save();
		$this->write_room(
			'#'.$this->login.' cменил тему'.NL.
			'java_topic(\''.$t.'\');'.NL
		);
		return 'Тема изменена';
	} else return date( 'Y-m-d H:i:s', $rooms[$this->rid][topicwhen] ).', '.$rooms[$this->rid][topicwhologin].': '.$rooms[$this->rid][topic];
}

?>