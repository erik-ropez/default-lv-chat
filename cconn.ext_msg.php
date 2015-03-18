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

function cconn_ext_msg(&$this) {
	global $socks, $conns, $http_header_failed, $uid2sock;

	if (!strcmp($this->host, '127.0.0.1')) {
		if (isset($uid2sock[$this->i_ext_msg_to]))
			$conns[$uid2sock[$this->i_ext_msg_to]]->send($this->s_ext_msg_text, $this->i_ext_msg_to, 0);
		$result = true;
	} $result = false;

	@socket_write($this->sock, $http_header_failed.$result);
	$this->closeme = true;
	@socket_close($this->sock);
	$socks[$this->id] = null;
	unset($socks[$this->id]);
	unset($conns[$this->id]);
}

?>