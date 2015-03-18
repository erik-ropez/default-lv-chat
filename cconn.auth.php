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

function cconn_auth( &$this, $str )
{
	global $socks, $uid2sock, $rooms2sock, $rooms, $conns, $mess, $defaultroom, $prisonroom, $http_header, $http_header_failed, $http_header, $clubs, $clubs_cnt;

	writestep( 'auth' );

	$auth = 0;

	debug( 'authentification, '.$this->id.', '.$str.', ' );
	if ( preg_match( '/^([\wа-яА-Я]{1,16})\s+(\w{32})\s+(\d+)$/', $str, $a ) )
	{
		$login = $a[1];
		$paswd = $a[2];
		$rid = $defaultroom;//$a[3];

		if ( !isset( $rooms2sock[$rid] ) ) $rid = $GLOBALS[defaultroom];
		$uid = getloginuid( $login );
		if ( $uid ) list($p,$img,$mail) = getmydatak( 'users', $uid, array( 'paswd', 'img', 'mail' ), true );

		if ( $uid && !strcmp( $paswd, $p ) )
		{
			if ( $uid2sock[$uid] )
			{
				$conns[$uid2sock[$uid]]->send( 'Попытка входа в чат под вашим акаунтом с адреса '.$conns[$uid2sock[$uid]]->host, $uid, 0 );
				$auth = 2;
			} else
			{
				// Basic data
				$this->uid = $uid;
				$this->login = $login;
				$this->img = $img;
				$this->mail = $mail;
				list($this->credit,$this->credittime,$this->ring,$this->deny,$this->lastdenyduid,$this->lastdenytime) = getmydatak( 'users2', $uid, array( 'credit', 'credittime', 'ring', 'deny', 'lastdenyduid', 'lastdenytime' ) );
				if ( !isset( $this->ring ) ) $this->ring = 4;
				// Start Club Test
				/*
				if ( $this->ring )
				{
					$h = $this->hostlong;
					$this->club = $club = 0;
					foreach ( $clubs as $i => $c )
					{
						foreach ( $c[masks] as $m )
						{
							if ( ( $h & $m[1] ) == $m[0] )
							{
								$club = $i;
								break;
							}
						}
						if ( $club ) break;
					}
					if ( $club )
					{
						$this->club = $club;
						$c = $clubs[$club][acc][date( 'Y' )][date( 'n' )];
						if ( $clubs_cnt[$club] > $c ) $auth = 3;
					}
				}
				*/
				// End Club Test
				if ( !$auth )
				{
					$this->ready = true;
					$this->lastmess = time();
					// Credits
					$a = getmydata( 'credits', $uid );
					$this->credit_out = 0;
					if ( is_array( $a[0] ) ) foreach ( $a[0] as $b ) $this->credit_out += $b[credits];
					$this->credit_in = 0;
					if ( is_array( $a[1] ) ) foreach ( $a[1] as $b ) $this->credit_in += $b[credits];
					savemydatak( 'users2', $uid, array( 'login', 'host', 'credit_out', 'credit_in' ), array( $login, $this->hostlong, $this->credit_out, $this->credit_in ) );
					$this->change_credits();
					// Prisoner
					$fugitive = false;
					if ( $this->deny )
					{
						if ( $rid != $prisonroom )
						{
							$rid = $prisonroom;
							$fugitive = true;
						}
					}
					$this->rid = $rid;
					// Friends
					$a = getmydata( 'fuid', $uid );
					$this->friends = $a[0];
					if ( empty( $this->friends ) ) $this->friends = array();
					$this->friendstr = implode( '|', $this->friends );
					$this->friend2 = $a[1];
					// Ignores
					$this->ignores = getmydatak( 'iuid', $uid, 1 );
					if ( empty( $this->ignores ) ) $this->ignores = array();
					foreach ( $this->ignores as $i => $l ) $this->ignores[$i] = true;
					// away
					$this->away = 1;
					// filter
					$this->filter = false;
					// rightimg
					//$this->rightimg_id = 0;
					for ($i = 1; $i <= 2; $i++) {
						$a = getmydata('rightimg', $i);
						if (isset($a[$uid])) {
							$this->rightimg_id = $a[$uid][0];
							$this->rightimg_alt = $a[$uid][1];
							break;
						}
					}
					// adduser
					$this->write_room(
						'#+'.$login.NL.
						'java_adduser('.$uid.',"'.$login.'",'.$this->img.','.$this->ring.','.$this->away.',1,'.$this->rightimg_id.',"'.$this->rightimg_alt.'");'.NL
					);
					$this->write_friends( 'java_adduser('.$uid.',"'.$login.'",'.$this->img.','.$this->ring.','.$this->away.',0,'.$this->rightimg_id.',"'.$this->rightimg_alt.'");'.NL );
					$this->write_all( 'java_room('.$this->rid.','.( count( $rooms2sock[$this->rid] ) + 1 ).');'.NL );
					//--
					$uid2sock[$uid] = $this->id;
					$rooms2sock[$rid][$this->id] = $this->id;
					// java_init
					$b = array();
					foreach ( $rooms as $i => $a ) $b[] = '['.$i.',"'.$a[caption].'",'.count( $rooms2sock[$i] ).']';
					$c = array();
					foreach ( $rooms2sock[$rid] as $i ) $c[] = '['.$conns[$i]->uid.',"'.$conns[$i]->login.'",'.$conns[$i]->img.','.$conns[$i]->ring.','.$conns[$i]->away.','.$conns[$i]->rightimg_id.',"'.$conns[$i]->rightimg_alt.'"]';
					$d = array();
					foreach ( $mess[$this->rid] as $a ) if ( !$a[0] || $a[0] == $this->uid ) $d[] = '[\''.$this->highlight( $a[1] ).'\','.$a[2].','.$a[3].']';
					$d = array_slice( $d, -MESSBUFLEN );
					$e = array();
					foreach ( $this->friends as $fuid => $l ) if ( isset( $uid2sock[$fuid] ) ) $e[] = '['.$conns[$uid2sock[$fuid]]->uid.',"'.$conns[$uid2sock[$fuid]]->login.'",'.$conns[$uid2sock[$fuid]]->img.','.$conns[$uid2sock[$fuid]]->ring.','.$conns[$uid2sock[$fuid]]->away.','.$conns[$uid2sock[$fuid]]->rightimg_id.',"'.$conns[$uid2sock[$fuid]]->rightimg_alt.'"]';
					$str = $this->http ? $http_header : 'OK'.NL;
					$this->write( $str, false, true );
					$this->sendtag = md5( uniqid( rand() ) );
					$this->write(
						'java_init_base('.$uid.',['.implode( ',', array_keys( $this->friends ) ).'],\''.$this->sendtag.'\');'.NL.
						'java_init(['.implode( ',', $b ).'],['.implode( ',', $c ).'],['.implode( ',', $e ).'],'.$rid.',\''.$rooms[$rid][topic].'\');'.NL.
						'java_init_mess(['.implode( ',', $d ).']);'.NL
//						'java_topic(\''.$rooms[$rid][topic].'\');'.NL
					);
					if ( $fugitive ) $this->send( 'Заключенным запрещается выходить из тюрьмы', $uid, 0, false, false );
					// Msgs
					$c = getmydata( 'msgs2', $uid );
					$a = array();
					foreach ($c as $b) if (isset($b[login])) $a[] = $b;
					savemydata( 'msgs2', $uid, $a );
					if ( !empty( $a ) )
					{
						$c = 0;
						foreach ( $a as $b ) if ( !$b[readed] ) $c++;
						if ( $c ) $this->send( 'В вашем ящике '.$c.' непрочитанных сообщений. <a href="" onclick="top.s(\\\'/echomsg\\\');return false;">Показать все сообщения.</a>', $uid, 0, false, false );
					}
					dumpquery( '^online '.$uid );
			
					debug( 'OK'.NL );
				}
			}
		} else $auth = 1;
	} else $auth = 1;

	switch ( $auth )
	{
		case 1:
			debug( 'failed'.NL );
			$str = 'Неправильний логин/пароль';
			if ( !$this->http ) $str = '~'.$str.NL;
			break;
		case 2:
			debug( 'connected now'.NL );
			$str = 'Этот ник уже находится в чате';
			if ( !$this->http ) $str = '~'.$str.NL;
			break;
		case 3:
			debug( 'club limit'.NL );
			$str = 'Не хватает свободных акаунтов для входа из Вашего клуба ('.$clubs[$this->club][caption].')';
			if ( !$this->http ) $str = '~'.$str.NL;
			break;
	}

	if ( $auth )
	{
		if ( $this->http ) $str = $http_header_failed.'<script language=javascript>document.domain="default.lv";parent.document.all.status.innerHTML="'.$str.'";</script>';
		@socket_write( $this->sock, $str );
		@socket_close( $this->sock );
		$socks[$this->id] = null;
		unset( $socks[$this->id] );
		unset( $conns[$this->id] );
		return false;
	} else
	if ( $this->club ) $clubs_cnt[$this->club]++;

	writestate();

	return true;
}

?>