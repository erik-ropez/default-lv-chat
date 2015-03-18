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

function cmd_createroom( $params )
{
	global $this, $rooms;
	$c = trim( $params[1] );
	if ( !preg_match( '/^[ 0-9a-zа-яА-Я]{2,20}$/i', $c ) ) return 'Название не соответствует условиям';
	if ( $this->credit - $this->credit_out < 10 ) return 'У Вас нет 10-и свободных кредитов';
	$this->credit -= 10;
	savemydatak( 'users2', $this->uid, 'credit', $this->credit );
	$this->change_credits();
	$rid = mymax( $rooms );
	$t = time();
	$r = array(
		'caption' => $c,
		'topic' => $c,
		'topicwhouid' => $this->uid,
		'topicwhologin' => $this->login,
		'topicwhen' => $t,
		'founderuid' => $this->uid,
		'founderlogin' => $this->login,
		'created' => $t,
		'credits' => 3
	);
	$rooms[$rid] = $r;
	rooms_save();
	$this->write_all(
		'#'.$this->login.' создал комнату '.$c.NL.
		'java_addroom('.$rid.',\''.$c.'\');'.NL
	);
	return 'Комната создана';
}

?>