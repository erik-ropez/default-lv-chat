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

function cmd_rightimg( $params )
{
	global $this;

	if (!($uid = getloginuid($params[1]))) return '����������� ������';

	if (!isset($params[3])) {
		$a = getmydata('rightimg', 2);
		if (!isset($a[$uid])) return '��� ������� ������� �������� �� �����������';
		unset($a[$uid]);
		savemydata('rightimg', 2, $a);
		return '�������� �����';
	} else {
		savemydatak('rightimg', 2, $uid, array($params[3], $params[4]));
		return '�������� �����������';
	}
}

?>