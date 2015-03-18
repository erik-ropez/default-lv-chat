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

function cmd_op( $params )
{
	global $this, $rooms, $roomaop, $roomdeny, $conns, $uid2sock;
	$uid = getloginuid( $params[1] );
	if ( !$uid ) return '����������� ���';
	$rid = $this->rid;
	if ( $uid == $rooms[$rid][founderuid] ) return '��������� ������� ������ ������ ����';
	if ( $this->uid != $rooms[$rid][founderuid] && empty( $roomaop[$rid][$this->uid] ) && !$this->roomop[$rid] ) return '� ��� ��� ����� ����';
	if ( !empty( $roomaop[$rid][$uid] ) ) return '['.$params[1].'] �������� �����';
	if ( !isset( $uid2sock[$uid] ) ) return '['.$params[1].'] �� ������ ������ �� � ����';
	$o = !$conns[$uid2sock[$uid]]->roomop[$rid];
	$conns[$uid2sock[$uid]]->roomop[$rid] = $o;
	if ( $conns[$uid2sock[$uid]]->rid == $rid )
		$conns[$uid2sock[$uid]]->write_room( 'java_chnuser2('.$uid.','.( $o ? 3 : 4 ).');'.NL, true );
	else
		$this->send( '['.$this->login.'] '.( $o ? '���' : '����' ).' ��� ������ op � ������� '.$rooms[$rid][caption], $uid, 0 );
	$GLOBALS[aeval][] = '$this->send(\'['.$this->login.'] '.( $o ? '���' : '����' ).' ������ op ��� ['.$params[1].']\',0,0);';
}

?>