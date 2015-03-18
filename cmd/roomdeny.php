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

function cmd_roomdeny( $params )
{
	global $this, $rooms, $roomaop, $roomdeny, $conns, $uid2sock, $prisonroom, $defaultroom;
	$rid = $this->rid;
	if ( $this->ring && $this->uid != $rooms[$rid][founderuid] && empty( $roomaop[$rid][$this->uid] ) && !$this->roomop[$rid] ) return '� ��� ��� ����� ����';
	if ( !$rooms[$rid][founderuid] ) return '� ������ ������� ������ ��������� ��������� ����';
	if ( isset( $params[1] ) )
	{
		$uid = getloginuid( $params[1] );
		if ( !$uid ) return '����������� ���';
		if ( $uid == $rooms[$rid][founderuid] ) return '��������� ������ �������� ���������� �������';
		if ( !empty( $roomaop[$rid][$uid] ) ) return '��������� ������ �������� �����';
		if ( isset( $uid2sock[$uid] ) && $conns[$uid2sock[$uid]]->roomop[$rid] ) return '��������� ������ �������� ����';
		if ( !empty( $roomdeny[$rid][$uid] ) )
		{
			unset( $roomdeny[$rid][$uid] );
			$o = false;
		} else
		{
			$roomdeny[$rid][$uid] = $params[1];
			$o = true;
			if ( isset( $uid2sock[$uid] ) && $conns[$uid2sock[$uid]]->rid == $rid )
			{
				$GLOBALS[this] =& $conns[$uid2sock[$uid]];
				$r = $GLOBALS[this]->deny ? $prisonroom : $defaultroom;
				$a = array( '', $r );
				cmd_changeroom( $a );
			}
		}
		rd_save();
		$this->send( '['.$this->login.'] '.( $o ? '���' : '����' ).' ��� ������ deny � ������� '.$rooms[$rid][caption], $uid, 0 );
		$GLOBALS[aeval][] = '$this->send(\'['.$this->login.'] '.( $o ? '���' : '����' ).' ������ deny ��� ['.$params[1].']\',0,0);';
	} else
	{
		$a = $roomdeny[$rid];
		if ( !count( $a ) ) return '� ������ ������� ������ ��� � ������ ������';
		sort( $a );
		return '������ ������: '.implode( ', ', $a );
	}
}

?>