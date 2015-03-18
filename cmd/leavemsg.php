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

function cmd_leavemsg($params) {
	global $this, $uid2sock, $conn;

	if (!($uid = getloginuid($params[1]))) return 'Неизвестный акаунт';

	$t = time();

	dumpquery('insert into mail (i_uid,i_uid2,b_direction,i_time,s_message) values ('.$this->uid.','.$uid.',1,'.$t.',"'.$params[2].'")');
	dumpquery('insert into mail (i_uid,i_uid2,b_direction,i_time,s_message) values ('.$uid.','.$this->uid.',0,'.$t.',"'.$params[2].'")');

	if ($uid != $this->uid && isset($uid2sock[$uid])) $this->send('Вам пришло сообщение от ['.$this->login.']. <a href="http://www.default.lv/'.ur2(0, 11, 16, 0).'" target=_blank>Показать сообщение.</a>', $uid, 0);

	return 'Сообщение отправлено';
}

?>