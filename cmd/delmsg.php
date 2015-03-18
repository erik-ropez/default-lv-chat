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

function cmd_delmsg( $params )
{
	global $this;

	$a = getmydata( 'msgs2', $this->uid );
	$c = empty( $a ) ? 0 : count( $a );
	if ( !$c ) return '� ����� ����� ��� ���������';

	if ( $params[1] < 1 || $params[1] > $c ) return '������������ ����� ���������';

	$d = array();
	$j = 1;
	if ( isset( $params[3] ) )
	{
		if ( $params[3] <= $params[1] || $params[3] < 1 || $params[3] > $c ) return '������������ ����� ���������';
		foreach ( $a as $i => $b ) if ( $i < $params[1] - 1 && $i > $params[3] - 1 ) $d[] = $b;
		$ret = '��������� ������';
	} else
	{
		foreach ( $a as $i => $b ) if ( $i != $params[1] - 1 ) $d[] = $b;
		$ret = '��������� ������';
	}
	savemydata( 'msgs2', $this->uid, $d );
	return $ret;
}

?>