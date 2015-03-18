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

function cmd_friend( $params )
{
	global $this, $uid2sock, $conns;

	if ( isset( $params[1] ) )
	{
		if ( !( $fuid = getloginuid( $params[1] ) ) ) return 'Неизвестный акаунт';
		if ( $this->uid == $fuid ) return 'Дружите с собой не сдесь';
		if ( isset( $this->friends[$fuid] ) )
		{
			unset( $this->friends[$fuid] );
			savemydatak( 'fuid', $this->uid, 0, $this->friends );
			$a = getmydatak(  'fuid', $fuid, 1 );
			unset( $a[$this->uid] );
			savemydatak( 'fuid', $fuid, 1, $a );
			$ret = '['.$params[1].'] больше не находится в списке ваших друзей';
			$this->write( 'java_remfriend('.$fuid.');'.NL );
			$this->write( 'java_remuser('.$fuid.',0);'.NL );
		} else
		{
			$this->friends[$fuid] = $params[1];
			savemydatak( 'fuid', $this->uid, 0, $this->friends );
			$a = getmydatak(  'fuid', $fuid, 1 );
			$a[$this->uid] = $this->login;
			savemydatak( 'fuid', $fuid, 1, $a );
			$ret = '['.$params[1].'] занесен в список ваших друзей';
			$this->write( 'java_addfriend('.$fuid.');'.NL );
			if ( isset( $uid2sock[$fuid] ) )
				$this->write( 'java_adduser('.$fuid.',"'.$conns[$uid2sock[$fuid]]->login.'",'.$conns[$uid2sock[$fuid]]->img.','.$conns[$uid2sock[$fuid]]->ring.','.$conns[$uid2sock[$fuid]]->away.',0,'.$conns[$uid2sock[$fuid]]->rightimg_id.',"'.$conns[$uid2sock[$fuid]]->rightimg_alt.'");'.NL );
		}
		if ( isset( $uid2sock[$fuid] ) ) $conns[$uid2sock[$fuid]]->friend2 = $a;
		$this->friendstr = implode( '|', $this->friends );
	} else
	{
		$a = $this->friends;
		sort( $a );
		$b = count( $a ) ? implode( ', ', $a ) : 'нет';
		$a = getmydatak(  'fuid', $this->uid, 1 );
		if ( empty( $a ) ) $a = array();
		sort( $a );
		$c = count( $a ) ? implode( ', ', $a ) : 'нет';
		$ret = 'Ваши друзья: '.$b.'<br>Вы являетесь другом: '.$c;
	}
	return $ret;
}

?>