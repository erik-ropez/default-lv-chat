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

function cmd_aop( $params )
{
	global $this, $rooms, $roomaop, $conns, $uid2sock, $prisonroom, $defaultroom;
	$rid = $this->rid;
	if ( $this->ring && $this->uid != $rooms[$rid][founderuid] ) return 'У вас нет таких прав';
	if ( !$rooms[$rid][founderuid] ) return 'В данной комнате нельзя создавать локальных аопов';
	if ( isset( $params[1] ) )
	{
		$uid = getloginuid( $params[1] );
		if ( !$uid ) return 'Неизвестный ник';
		if ( $uid == $rooms[$rid][founderuid] ) return 'Создателя комнаты нельзя делать опом';
		if ( !empty( $roomaop[$rid][$uid] ) )
		{
			unset( $roomaop[$rid][$uid] );
			$o = false;
			if ( $rooms[$rid][onlyop] && isset( $uid2sock[$uid] ) && $conns[$uid2sock[$uid]]->rid == $rid )
			{
				$GLOBALS[this] =& $conns[$uid2sock[$uid]];
				$r = $GLOBALS[this]->deny ? $prisonroom : $defaultroom;
				$a = array( '', $r );
				cmd_changeroom( $a );
			}
		} else
		{
			$roomaop[$rid][$uid] = $params[1];
			if ( $uid2sock[$uid] ) unset( $conns[$uid2sock[$uid]]->roomop[$rid] );
			$o = true;
		}
		ra_save();
		if ( isset( $uid2sock[$uid] ) && $conns[$uid2sock[$uid]]->rid == $rid )
			$this->write_room( 'java_chnuser2('.$uid.','.( $o ? 3 : 4 ).');'.NL, true );
		else
			$this->send( '['.$this->login.'] '.( $o ? 'дал' : 'снял' ).' Вам статус aop в комнате '.$rooms[$rid][caption], $uid, 0 );
		$GLOBALS[aeval][] = '$this->send(\'['.$this->login.'] '.( $o ? 'дал' : 'снял' ).' статус aop для ['.$params[1].']\',0,0);';
	} else
	{
		$a = $roomaop[$rid];
		if ( !count( $a ) ) return 'В данной комнате нет аопов';
		sort( $a );
		return 'Список апов: '.implode( ', ', $a );
	}
}

?>