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

function cmd_deny( $params )
{
	global $this, $uid2sock, $conns, $prisonroom, $denyz, $referee;

	$ring2 = in_array(strtolower($this->login), array()) ? 0 : $this->ring;

	if ( isset( $params[1] ) ) {
		if ( !( $duid = getloginuid( $params[1] ) ) ) return 'Неизвестный акаунт';
		$login = $params[1];
		if ( $this->uid == $duid ) return 'Вы не можете себя понизить';
		list($ring,$deny,$c3,$lastdenyduid,$lastdenytime) = isset( $uid2sock[$duid] ) ? array( $conns[$uid2sock[$duid]]->ring, $conns[$uid2sock[$duid]]->deny, $conns[$uid2sock[$duid]]->credit, $conns[$uid2sock[$duid]]->lastdenyduid, $conns[$uid2sock[$duid]]->lastdenytime ) : getmydatak( 'users2', $duid, array( 'ring', 'deny', 'credit', 'lastdenyduid', 'lastdenytime' ) );
		$ring = (int)$ring;
		if (!$ring && !in_array(strtolower($params[1]), array())) $ring = 4;
		if ( $ring2 >= $ring ) return 'Вы не можете понизить человека со статусом выше или таким же как у вас';
		if ( !isset( $denyz[$params[2]] ) ) return 'Неправильный номер статьи';
		if ( !isset( $denyz[$params[2]][$params[3]] ) ) return 'Неправильный номер подстатьи';
		if ($denyz[$params[2]][$params[3]][ring] < $ring2) return 'Данная статья Вам не доступна';

		if (time() < ($lastdenytime + 60) && $lastdenyduid != $this->uid) return 'Понижаемый захвачен другим человеком';
		$c1 = $denyz[$params[2]][caption];
		$c2 = $denyz[$params[2]][$params[3]][caption];
		$t = $denyz[$params[2]][$params[3]][time];
		$c = $denyz[$params[2]][$params[3]][credits];
		if ( $c3 < $c )
		{
			$t += ( $c - $c3 ) * 3600;
			$c = $c3;
		}
		$c3 -= $c;
		$deny += $t;
		$GLOBALS[aeval][] = '$this->send(\'['.$this->login.'] понизил ['.$login.'] за '.$c1.' - '.$c2.'\',0,0);';
		savemydatak( 'users2', $duid, array( 'credit', 'deny', 'lastdenyduid', 'lastdenytime' ), array( $c3, $deny, $this->uid, time() ) );
		//-- high level if root or referee is denying or referee is denied
		$high = (!$ring2 || isset($referee[strtolower($login)]) || isset($referee[strtolower($this->login)])) ? 1 : 0;
		//--
		dumpquery( 'insert into deny (uid,puid,type,level,date,time,credits,pring,high) values ('.$duid.','.$this->uid.','.$params[2].','.$params[3].','.time().','.$t.','.$c.','.$ring2.','.$high.')' );
		dumpquery( 'update users set deny='.$deny.' where id='.$duid );
		if ( isset( $uid2sock[$duid] ) )
		{
			$conns[$uid2sock[$duid]]->credit = $c3;
			$conns[$uid2sock[$duid]]->deny = $deny;
			$conns[$uid2sock[$duid]]->lastdenytime = time();
			$conns[$uid2sock[$duid]]->lastdenyduid = $this->uid;
			$GLOBALS[this] =& $conns[$uid2sock[$duid]];
			if ( $GLOBALS[this]->rid != $prisonroom )
			{
				$a = array( '', $prisonroom );
				cmd_changeroom( $a );
			}
			$GLOBALS[this]->prisoner = true;
			$GLOBALS[this]->send( '['.$this->login.'] понизил Вас за '.$c1.' - '.$c2.'. <a href="http://www.default.lv/'.ur2(0, 11, 13).'" target=_blank>Подать аппеляцию</a>.', $duid, 0 );
		}
		$this->change_credits( $duid );
	} else
	{
		$s = '';
		foreach ( $denyz as $t => $a )
		{
			$s .= '<b>'.$t.'. '.$a[caption].'</b><br>'.$a[descr].'<br>';
			foreach ( $a as $l => $b )
			{
				if (!is_numeric($l) || $b[ring] < $ring2) continue;
				$s .= '&nbsp;&nbsp;'.$l.'. '.$b[caption].' - '.$b[credits].' кредитов, '.( $b[time] / 60 ).' минут<br>&nbsp;&nbsp;&nbsp;&nbsp;'.$b[descr].'<br>';
			}
		}
		return $s;
	}
}

?>