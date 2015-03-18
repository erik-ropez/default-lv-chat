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

//-------------------------------------

function cconn_parse( &$this, $str )
{
	global $cmdz, $cmdz_exc, $uid2sock, $conns;
	
	writestep( 'parse' );

	debug( 'received from '.$this->id.', '.$str.NL );
	if ( !preg_match( '/^(\d+) (.+)$/', $str, $a ) ) return true;
	$puid = $a[1];
	if ( $puid == $this->uid ) $puid = 0;
	$mess = stripmess( $this, $a[2] );

	if ( preg_match( '/^\/(\w+)( |$)/', $mess, $a ) ) $cmd = $a[1];
	else $cmd = '';

	$GLOBALS[aeval] = array();

	if ($this->getcred) {
		$this->getcred_messages--;
		if (!$this->getcred_messages || time() > $this->getcred_expire) $this->getcred = false;
	}

	if ( $cmd )
	{
		unset( $str );
		if ( isset( $cmdz[$cmd] ) )
		{
			if ( isset( $cmdz[$cmd][3] ) && $cmdz[$cmd][3] < $this->ring && ( !isset( $cmdz_exc[$cmd] ) || ( isset( $cmdz_exc[$cmd] ) && !in_array( strtolower( $this->login ), $cmdz_exc[$cmd] ) ) ) ) $str = 'Данная команда не определена или не доступна';
			else if ( preg_match( '/^\/'.$cmd.'('.$cmdz[$cmd][0].')$/i', $mess, $a ) )
			{
				$b = array_slice( $a, 1 );
				$b[0] = $a[0];
				$GLOBALS[this] =& $this;
				$GLOBALS[puid] =& $puid;
				$str = call_user_func( 'cmd_'.$cmd, $b );
			}
			else $str = 'Неправильный синтаксис команды, используйте /help';
		} else $str = 'Данная команда не определена или не доступна';
		if ( $str !== false )
		{
			if ( strlen( $str ) ) $str = '<br>'.$str;
			$this->send( $mess.$str, $this->uid );
		}
	} else
	{
		if ( !antiflood( $this ) ) return true;

		$t = time();

		$mat = (!$puid && $this->antimat($mess));

		if ($mat) {
			$puid = $this->uid;
			$this->send("Сообщение не может быть отображено публично", $puid, 0);
			$tl = 0;
		} else $tl = $t - $this->lastmess;

		$this->lastmess = $t;
		
		if (!$mat && $tl > 0 && $tl < 180) addtime( $this, $tl );
		dumpquery( '^users '.$this->uid.' '.$t.' '.$tl );

		$this->send( $mess, $puid );

		if (!$mat && $this->away )
		{
			$this->away = 0;
			$this->write_room( 'java_chnuser1('.$this->uid.',\''.$this->login.'\','.$this->img.',0,1)'.NL );
			$this->write_friends( 'java_chnuser1('.$this->uid.',\''.$this->login.'\','.$this->img.',0,0)'.NL );
		}
	}
	
	foreach ( $GLOBALS[aeval] as $e ) eval( $e );

	return true;
}

//-------------------------------------

function antiflood( &$this )
{
	static $af = array(
		3 => 3,
		6 => 9,
		12 => 27,
		24 => 81
	);
	$t = time();
	$ret = true;
	$this->flood[] = $t;
	$a = array();
	foreach ( $this->antiflood as $i => $b )
	{
		list($t2,$t3) = $b;
		if ( $t3 >= $t )
		{
			$a[$t2] = true;
			$this->send( 'Неизтек штраф за флуд в '.$t2.' секунд', $this->uid, 0 );
			$this->antiflood[$i][1] = $t + $t2;
			$ret = false;
		} else unset( $this->antiflood[$i] );
	}
	$c = mymax( $this->flood ) - 1;
	foreach ( $af as $n => $s )
	{
		if ( $a[$s] ) continue;
		$t2 = $this->flood[$c - $n + 1];
		if ( $t - $t2 < $s )
		{
			$this->send( 'Подозрение на флуд ('.$n.' сообщений за '.$s.' секунд), штраф на '.$s.' секунд', $this->uid, 0 );
			$this->antiflood[] = array( $s, $t + $s );
			$ret = false;
		}
	}
	unset( $this->flood[$c - $n] );
	return $ret;
}

//-------------------------------------

function stripmess( &$this, $mess )
{
	global $rooms;

	if ( $this->ring ) $mess = substr( $mess, 0, 1024 );
	$a = array(
		'&' => '&amp;',
		'"' => '&quot;',
		'\'' => '&#39;',
		'<' => '&lt;',
		'>' => '&gt;',
		'\\' => '&#92;'
	);
	$mess = strtr( $mess, $a );
	$mess = str_replace('\\', '$#92;', $mess);
	$mess = preg_replace( '/&lt;(\/?[bui])&gt;/i', '<\\1>', $mess );

	if ($rooms[$this->rid][bardak] ||
		$this->ring <= 1 ||
		in_array(strtolower($this->login), array()))
		$mess = preg_replace( '/\&lt\;(red|yellow|blue|black|green)\&gt\;/i', '<font color=\\1>', $mess );
	else
		$mess = preg_replace( '/\&lt\;(red|yellow|blue|black|green)\&gt\;/i', '', $mess );

	if ($this->ring <= 1 ||
		in_array(strtolower($this->login), array()))
		$mess = preg_replace( '/\&lt\;private\&gt\;/i', '<font class=priv>', $mess );

	$mess = preg_replace( '/\&lt\;normal\&gt\;/i', '<font class=tbl1>', $mess );	

	$mess = preg_replace( '/\&lt\;url\s+(.*?)\&gt\;\s*(.*?)\&lt\;\/url\&gt\;/i', '<a href=\\"\\1\\" target=_blank>\\2</a>', $mess );

	$mess = preg_replace( '/\&lt\;mail\s+(.*?)\&gt\;\s*(.*?)\&lt\;\/mail\&gt\;/i', '<a href=\\"mailto:\\1\\" target=_blank>\\2</a>', $mess );

	$mess = preg_replace( '/(^|\s)(http|ftp):\/\/([^\s]+)/i', '\\1<a href=\\"\\2\://\\3\\" target=_blank>\\2://\\3</a>', $mess );

	return $mess;
}

//-------------------------------------

function addtime( &$this, &$tl )
{
	dumpquery( '^iptime '.date( 'Ymd' ).' '.$this->hostlong.' '.$tl );
	
	if ( $this->deny )
	{
		if ( $this->deny <= $tl )
		{
			$tl -= $this->deny;
			$this->deny = 0;
			$this->send( 'Срок закончен, вы свободны', $this->uid, 0 );
		} else
		{
			$this->deny -= $tl;
			$tl = 0;
		}
		savemydatak( 'users2', $this->uid, 'deny', $this->deny, true );
		dumpquery( 'update users set deny='.$this->deny.' where id='.$this->uid );
	}

	if ( !$this->deny && $tl )
	{
		$this->credittime += $tl;
		if ( $this->credittime > 3600 )
		{
			$this->credittime -= 3600;
			$this->getcred = true;
			$this->getcred_messages = 5;
			$this->getcred_expire = time() + 600;
			$this->getcred = true;
			$i = md5(rand());
			$this->getcred_code = substr(strtr(md5($i.$this->host), array('a' => 0, 'b' => 1, 'c' => 2, 'd' => 3, 'e' => 4, 'f' => 5)), 3, 6);
			$this->send( 'Вы заработали новый кредит. Получить кредит Вы можете в течении пяти сообщений не позже чем через 10 минут. Для его получения введите <a href="" onclick="top.m(\\\'/getcred \\\');return false;">/getcred</a> <img align=absmiddle src="http://www.default.lv/safecredit.php?i='.$i.'" width=44 height=16>', $this->uid, 0 );
		}
	}

	savemydatak( 'users2', $this->uid, array( 'credittime' ), array( $this->credittime ) );
}

//-------------------------------------

?>