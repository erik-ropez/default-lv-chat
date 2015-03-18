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

class cconn
{
	var	$id;
	var	$sock;
	var	$host;
	var	$hostlong;
	var	$port;
	var	$ready;
	var	$uid;
	var	$rid;
	var	$login;
	var	$img;
	var	$ring;
	var	$lastmess;
	var	$credit;
	var	$credittime;
	var	$away;
	var	$friends;
	var	$friend2;
	var	$ignores;
	var	$friendstr;
	var	$input;
	var	$output;
	var	$http;
	var	$http_get;
	var	$flood;
	var	$antiflood;
	var	$credit_in;
	var	$credit_out;
	var	$deny;
	var	$club;
	var	$roomop;
	var	$sendtag;
	var	$post;
	var	$postdata;
	var	$mydata;
	var	$mydata_func;
	var	$mydata_param;
	var	$filter;
	var	$lastdenyduid;
	var	$lastdenytime;
	var	$getsend;
	var	$getcred;
	var	$getcred_messages;
	var	$getcred_expire;
	var	$getcred_code;
	var	$closeme;
	var $rightimg_id;
	var $rightimg_alt;
	var $b_ext_msg;
	var $i_ext_msg_from;
	var $i_ext_msg_to;
	var $s_ext_msg_text;

	function cconn( $id, $s )
	{
		socket_set_nonblock( $s );
		$this->sock = $s;
		debug( date( 'H:i:s' ).' new connection, '.$id.', ' );
		socket_getpeername( $s, $a, $b );
		$this->id = $id;
		$this->host = $a;
		$this->hostlong = ip2long( $a );
		$this->port = $b;
		$this->ready = false;
		$this->getcred = false;
		$this->flood = array();
		$this->antiflood = array();
		$this->rightimg_id = 0;
		debug( $a.':'.$b.', '.$s.NL );
	}

	function close()
	{
		mytrace( 'cconn->close()' );
		global $socks, $conns, $uid2sock, $rooms2sock, $clubs_cnt;
		debug( date( 'H:i:s' ).' socket closed by peer, '.$this->id.', '.$this->host.':'.$thid->port.NL );
		@socket_close( $this->sock );
		$socks[$this->id] = null;
		unset( $socks[$this->id] );
		unset( $conns[$this->id] );
		if ( $this->ready )
		{
			unset( $uid2sock[$this->uid] );
			unset( $rooms2sock[$this->rid][$this->id] );
			if ( $this->club ) $clubs_cnt[$this->club]--;
			$this->write_room(
				'#-'.$this->login.NL.
				'java_remuser('.$this->uid.',1);'.NL
			);
			$this->write_friends( 'java_remuser('.$this->uid.',0);'.NL );
			$this->write_all( 'java_room('.$this->rid.','.count( $rooms2sock[$this->rid] ).');'.NL );
			dumpquery('^offline '.$this->uid );
		}
		$this->closeme = true;
		writestate();
	}

	function read()
	{
		mytrace( 'cconn->read()' );
		$c = @socket_read( $this->sock, 1024 );
		if ( !$c || $c === false )
		{
			debug( 'read failed'.NL );
			$this->close();
			return false;
		}
		$this->input .= $c;
		while ( ( $p = strpos( $this->input, "\n" ) ) !== false )
		{
			$c = trim( substr( $this->input, 0, $p ) );
			$this->input = preg_replace( '/^\r+/', '', substr( $this->input, $p + 1 ) );
			if ( $this->ready ) cconn_parse( $this, $c );
			else $this->parse_header( $c );
		}
		if ( $this->postdata && !empty( $this->input ) ) cconn_post( $this, $this->input );
		return true;
	}

	function parse_header( $str )
	{
		if ($this->post && empty($str)) {
			$this->postdata = true;
			return;
		}

		if (!$this->http)
		{
			if (preg_match('/^GET\s+\/((get|save)my(value|data)k?)\?([\w\d\=\&\%\.\-]+)\s+HTTP\/1\.\d+/i', $str, $a)) {
				$this->http = true;
				$this->mydata = true;
				$this->mydata_func = $a[1];
				$this->mydata_param = $a[4];
				return;
			}
			if (preg_match( '/^POST\s+\/\s+HTTP\/1\.\d+/i', $str)) {
				$this->http = true;
				$this->post = true;
				return;
			}
			if (preg_match('/^GET\s+\/msg\?(\d+)\&(\d+)\&([^\s]+)\s+HTTP\/1\.\d+/i', $str, $a)) {
				$this->http = true;
				$this->b_ext_msg = true;
				$this->i_ext_msg_from = (int)$a[1];
				$this->i_ext_msg_to = (int)$a[2];
				$this->s_ext_msg_text = trim(urldecode($a[3]));
				return;
			}
			if (preg_match('/^GET\s+\/\?([\w\d\=\&\%]+)\s+HTTP\/1\.\d+/i', $str, $a)) {
				$this->http = true;
				$a = explode( '&', $a[1] );
				foreach ( $a as $b ) {
					$c = explode( '=', $b );
					$this->http_get[$c[0]] = $c[1];
				}
				return;
			}
			if (preg_match('/^GET\s+\/send\s+HTTP\/1\.\d+/i', $str)) {
				$this->http = true;
				$this->getsend = true;
				return;
			}
		} else

		if ($this->http) {
			if (!empty($str)) return;
			if ($this->mydata) {
				cconn_mydata($this);
				return;
			} elseif ($this->getsend) {
				debug('GET /send'.NL);
				@socket_write( $this->sock, $GLOBALS[http_header_post] );
				$this->closeme = true;
				return;
			} elseif ($this->b_ext_msg) {
				cconn_ext_msg($this);
				return;
			}
			$str = $this->http_get[l].' '.$this->http_get[p].' '.$this->http_get[r];
		}

		cconn_auth( $this, $str );
	}

	function write( $str, $hl = false, $ignorehttp = false )
	{
		mytrace( 'cconn->write()' );
		if ( $hl ) $str = $this->highlight( $str );
		debug( '['.$this->uid.'] write: '.$str );
		if ( !( $l = strlen( $str ) ) ) return true;
		if ( $this->http && !$ignorehttp )
		{
			$a = explode( "\n", $str );
			$str = '';
			foreach ( $a as $b )
			{
				if ( $b{0} == '#' ) $b = 'parent.setstatus("'.substr(trim($b), 1).'");';
				$b = trim( $b );
				if ( $b{0} == ':' ) $b = 'window.open("http://www.default.lv/'.substr( $b, 1 ).'");';
				$str .= $b.NL;
			}
			$str = '<script language=javascript>'.trim( $str ).'</script>'.NL;
			$l = strlen( $str );
		}
		//else {$b = preg_replace('/href=\\\"([^"]+)\\\"/', 'href="\\1"', $b);}
		$this->output .= $str;
		return true;
	}

	function writep( $str, $uid, $hl = false )
	{
		global $conns, $uid2sock;
		if ( isset( $uid2sock[$uid] ) ) $conns[$uid2sock[$uid]]->write( $str, $hl );
	}

	function write_room( $str, $hl = false )
	{
		global $rooms2sock, $conns;
		debug( '['.$this->uid.'] write_room: '.$str );
		foreach ( $rooms2sock[$this->rid] as $id ) $conns[$id]->write( $str, $hl );
	}

	function write_room_filter( $str, $hl = false )
	{
		global $rooms2sock, $conns;
		debug( '['.$this->uid.'] write_room_filter: '.$str );
		foreach ( $rooms2sock[$this->rid] as $id )
			if ((!$conns[$id]->filter || (preg_match('/'.$conns[$id]->login.'/i', $str))) &&
				!$this->ignores[$conns[$id]->uid]) $conns[$id]->write( $str, $hl );
	}

	function write_all( $str, $hl = false )
	{
		global $uid2sock, $conns;
		debug( 'write_all: '.$str );
		foreach ( $uid2sock as $id ) $conns[$id]->write( $str, $hl );
	}

	function write_friends( $str )
	{
		global $uid2sock, $conns;
		debug( 'write_friends: '.$str );
		foreach ( $this->friend2 as $fuid => $l ) if ( isset( $uid2sock[$fuid] ) ) $conns[$uid2sock[$fuid]]->write( $str, false );
	}

	function insert_mess3($text, $uid = 0, $i_img_obj_id = -1, $i_img_id = 0)
	{
		mytrace( 'cconn->insert_mess3' );
		global $mess, $mess_public;
		$id = mymax( $mess[$this->rid] );
		$mess[$this->rid][$id] = array( $uid, $text, $i_img_obj_id, $i_img_id );
		if ( !$uid )
		{
			$mess_public[$this->rid][] = $id;
			if ( count( $mess_public[$this->rid] ) > MESSBUFLEN )
			{
				reset( $mess_public[$this->rid] );
				list($id2,$id3) = each( $mess_public[$this->rid] );
				unset( $mess_public[$this->rid][$id2] );
				foreach ( $mess[$this->rid] as $id => $a )
				{
					if ( $id >= $id3 ) break;
					unset( $mess[$this->rid][$id] );
				}
			}
		}
	}

	function send( $text, $puid = 0, $uid = false, $action = false, $remember = true )
	{
		global $conns, $uid2sock, $rooms, $roomaop, $i_img_counter;

		$originaltext = $text;

		if ( $uid === false ) $uid = $this->uid;

		dumpquery( '^mess3 '.date( 'Ymd' ).' '.time().',"'.addslashes( $text ).'",'.$puid.','.$uid.','.( $action ? 1 : 0 ).','.( $uid ? $this->img : 0 ).','.$this->rid);
		
		if (!$rooms[$this->rid][bardak]) {
			$text = preg_replace( '/\&lt\;img(\d+)\&gt\;/', '<img align=absmiddle src="http://static.default.lv/img/chat/symbols/\\1.gif" class=sym>', $text, 5 );
			$text = preg_replace( '/\&lt\;img\d+\&gt\;/', '', $text );
		} else 	$text = preg_replace( '/\&lt\;img(\d+)\&gt\;/', '<img align=absmiddle src="http://static.default.lv/img/chat/symbols/\\1.gif" class=sym>', $text );


		if ( $action )
		{
			$t = '<font class=god>'.$this->login.' '.$text.'</font>';
			$this->write_room_filter( 'java_mess(\''.$t.'\');'.NL, true );
			if ( $remember ) $this->insert_mess3( $t );
			return;
		}

		if ( !$uid ) $ring = 4; else
		if ( $rooms[$this->rid][founderuid] )
		{
		    if ( $uid == $rooms[$this->rid][founderuid] || !empty( $roomaop[$this->rid][$uid] ) || $this->roomop[$this->rid] ) $ring = 3;
		    else $ring = 4;
		} else $ring = isset( $uid2sock[$uid] ) ? $conns[$uid2sock[$uid]]->ring : getmydatak( 'users2', $uid, 'ring' );

//		$t = '<img align=absmiddle src="http://static.default.lv/img/chat/faces/'.( $uid ? ( $this->img % 100 ).'/'.$this->img : 0 ).'.gif" vspace=1 class=r'.$ring;
		$t = '<img align=absmiddle name=i'.$i_img_counter.' vspace=1 class=r'.$ring;
		$i_img_id = $uid ? $this->img : 0;
		$s_append = $i_img_counter.','.$i_img_id;
		if ( !$uid )
		{
			$t .= '> <font class=god>¬севышний</font>';//&laquo;&raquo;
			if ( !$puid )
			{
				$t .= ': '.$text;
				$this->write_room( 'java_mess(\''.$t.'\','.$s_append.');'.NL, true );
			} else
			{
				$t .= ' <font class=priv>лично</font>: '.$text;
				$this->writep( 'java_mess(\''.$t.'\','.$s_append.');'.NL, $puid, true );
			}
			if ( $remember ) $this->insert_mess3( $t, $puid, $i_img_counter, $i_img_id);
		} else
		{
			$t .= ' onclick="top.z(\\\''.$this->login.'\\\',event)"> <a href="javascript:top.y(\\\''.$this->login.'\\\','.$this->uid.')" class=login>';//&laquo;
			if ( $ring < 4 ) $t .= '@';
			$t .= $this->login.'</a>';//&raquo;

			if ( !$puid )
			{
				$t .= ': '.$text;
				if ( $remember ) $this->insert_mess3( $t, 0, $i_img_counter, $i_img_id );
				$this->write_room_filter( 'java_mess(\''.$t.'\','.$s_append.');'.NL, true );
			} else
			{
				$priv = isset( $uid2sock[$puid] ) ? $conns[$uid2sock[$puid]]->login : getmydatak( 'users2', $puid, 'login' );
				if (!$this->ignores[$puid])
				{
					$t .= ' <font class=priv>лично';
					if ( $uid != $puid ) {
						$t1 = $t.' ('.$priv.')</font>: '.$text;
						if ( $remember ) $this->insert_mess3( $t1, $uid, $i_img_counter, $i_img_id );
						$this->write( 'java_mess(\''.$t1.'\','.$s_append.');'.NL, true );
					}
					$t2 = $t.'</font>: '.$text;
					if ( $remember ) $this->insert_mess3( $t2, $puid, $i_img_counter, $i_img_id );
					if ( isset( $uid2sock[$puid] ) ) $this->writep( 'java_mess(\''.$t2.'\','.$s_append.');'.NL, $puid, true );
					else $this->send($priv.' уже не находитс€ в чате. <a href="" onclick="top.m(\\\'/leavemsg '.$priv.' '.$originaltext.'\\\');return false;">ќставить сообщение</a>', $this->uid, 0);
				} else $this->send($priv.' ¬ас игнорирует', $this->uid, 0);
			}
		}
		$i_img_counter++;
	}

	function antimat( &$str )
	{
		static $a = array('$','/','@','!','%','^','*',',','\\','(',')','-','+','_','=','|');
		$s = str_replace( $a, '', $str );
		// "/([^\wа-€]|^)([z3з]+[aа]+|[pп]+[oо]+|[nн]+[eеaаи]+)*[xх]+[uу]+[eijийе€]+|([^\wа-€]|^)([z3з]+[aа]+|[dдpп]+[oо]+)*[pп]+[iи»]+[z3з]+[dд]+|([^\wа-€]|^)[bб]+[lл]+([`'][aа]|ja|[€])/i"
		if ( preg_match( "/[xх’]+[uyу”]+[eijийе€»…≈я]+|[pпѕ]+[iи»]+[z3з«]+[dдƒ]+|([^a-zа-€ј-я]|^)[bбЅ]+[lлЋ]+(ja|[€я])/i", $s ) )
		{
			$str = '<font class=mat>'.$str.'</font>';
			dumpquery( 'update users set matcnt=matcnt+1 where id='.$this->uid );
			return true;
		}
		return false;
	}

	function highlight($str) {
		// Old '/((^|>)[^<]*)('.$this->friendstr.')/i', '\\1<font class=friend>\\3</font>'
		//$str = preg_replace('/(^|(>([^<]*?[^\w]|)))('.$this->login.')([^\w]|$)/i', '\\1<font class=priv>\\4</font>\\5', $str);
		//if ($this->friendstr) $str = preg_replace('/(^|(>([^<]*?[^\w]|)))('.$this->friendstr.')([^\w]|$)/i', '\\1<font class=friend>\\4</font>\\5', $str);
		$str = preg_replace('/((^|>)[^<]*)('.$this->login.')/i', '\\1<font class=priv>\\3</font>', $str);
		if ($this->friendstr) $str = preg_replace('/((^|>)[^<]*)('.$this->friendstr.')/i', '\\1<font class=friend>\\3</font>', $str);
		return $str;
	}

	function change_credits( $uid = 0 )
	{
		global $uid2sock, $conns;

		if ( !$uid )
		{
			$uid = $this->uid;
			$r2 = $this->ring;
			$c1 = $this->credit - $this->credit_out;
			$c2 = $this->credit_in;
		} else
		{
			list($r2,$c,$c1,$c2) = isset( $uid2sock[$uid] ) ? array( $conns[$uid2sock[$uid]]->ring, $conns[$uid2sock[$uid]]->credit, $conns[$uid2sock[$uid]]->credit_out, $conns[$uid2sock[$uid]]->credit_in ) : getmydatak( 'users2', $uid, array( 'ring', 'credit', 'credit_out', 'credit_in' ) );
			$c1 = $c - $c1;
		}
		if ( !$r2 ) return;
		if ( $c1 >= 1000 && $c2 >= 1000 ) $ring = 1; else
		if ( $c1 >= 400 && $c2 >= 400 ) $ring = 2; else
		if ( $c1 >= 50 ) $ring = 3; else $ring = 4;
		if ( $r2 != $ring )
		{
			dumpquery( 'update users set ring='.$ring.' where id='.$uid );
			savemydatak( 'users2', $uid, 'ring', $ring );
			if ( isset( $uid2sock[$uid] ) )
			{
				$conns[$uid2sock[$uid]]->ring = $ring;
				$conns[$uid2sock[$uid]]->write_room( 'java_chnuser2('.$uid.','.$ring.',1);'.NL );
				$conns[$uid2sock[$uid]]->write_friends( 'java_chnuser2('.$uid.','.$ring.',0);'.NL );
				$GLOBALS[aeval][] = '$this->send(\'” вас '.( $ring < $r2 ? 'повысилс€' : 'понизилс€' ).' статус\','.$uid.',0);';
			}
		}
	}

}

include'cconn.auth.php';
include'cconn.post.php';
include'cconn.mydata.php';
include'cconn.parse.php';
include'cconn.ext_msg.php';

?>