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

function cmd_ignore( $params )
{
	global $this, $uid2sock, $conns;

	if ( isset( $params[1] ) )
	{
		if ( !( $iuid = getloginuid( $params[1] ) ) ) return 'Неизвестный акаунт';
		if ( $this->uid == $iuid ) return 'Вы не можете игнорировать себя';
		$a = getmydatak( 'iuid', $iuid, 1 );
		if ( isset( $uid2sock[$iuid] ) ? $conns[$uid2sock[$iuid]]->ignores[$this->uid] : !empty( $a[$this->uid] ) )
		{
			if ( isset( $uid2sock[$iuid] ) ) unset( $conns[$uid2sock[$iuid]]->ignores[$this->uid] );
			unset( $a[$this->uid] );
			savemydatak( 'iuid', $iuid, 1, $a );
			$a = getmydatak( 'iuid', $this->uid, 0 );
			unset( $a[$iuid] );
			savemydatak( 'iuid', $this->uid, 0, $a );
			$ret = 'Вы больше неигнорируете ['.$params[1].']';
			$this->send( '['.$this->login.'] больше вас не игнорирует', $iuid, 0 );
		} else
		{
//			if ( $this->ring > ( isset( $uid2sock[$iuid] ) ? $conns[$uid2sock[$iuid]]->ring : getmydatak( 'users2', $iuid, 'ring' ) ) )
//				$ret = 'Вы не можете игнорировать по статусу выше стоящего человека';
			$ring = isset($uid2sock[$iuid]) ? $conns[$uid2sock[$iuid]]->ring : getmydatak('users2', $iuid, 'ring');
			if (!$ring && !in_array(strtolower($params[1]), array())) $ring = 4;
			if ($ring <= 0)
				$ret = 'Вы не можете игнорировать рута';
			else
			{
				if ( isset( $uid2sock[$iuid] ) ) $conns[$uid2sock[$iuid]]->ignores[$this->uid] = true;
				$a[$this->uid] = $this->login;
				savemydatak( 'iuid', $iuid, 1, $a );
				$a = getmydatak( 'iuid', $this->uid, 0 );
				$a[$iuid] = $params[1];
				savemydatak( 'iuid', $this->uid, 0, $a );
				$ret = 'Вы игнорируете ['.$params[1].']';
				$this->send( '['.$this->login.'] вас игнорирует', $iuid, 0 );
			}
		}
	} else
	{
		$a = getmydatak( 'iuid', $this->uid, 0 );
		if ( empty( $a ) ) $a = array();
		sort( $a );
		$b = count( $a ) ? implode( ', ', $a ) : 'никого';
		$a = getmydatak( 'iuid', $this->uid, 1 );
		if ( empty( $a ) ) $a = array();
		sort( $a );
		$c = count( $a ) ? implode( ', ', $a ) : 'никто';
		$ret = 'Вы игнорируете: '.$b.'<br>Вас игнорируют: '.$c;
	}
	return $ret;
}

?>