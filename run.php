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

$lastaway = time();
$lastroomtest = date( 'Ymd' );
$lastministate = 0;
$iteration = 0;
$server_started = time();

do
{
	$iteration++;
	do
	{
		$t = time();
		if ($t - $lastministate >= 60) {
			$lastministate = $t;
			writeministate();
		}
		if ($t - $lastaway >= 10) {
			$lastaway = $t;
			testaway();
			parseinput();
		}
		$lrt = date('Ymd', $t);
		if (strcmp($lastroomtest, $lrt)) {
			$lastroomtest = $lrt;
			roomtest();
		}
		debug( '.' );
		writestep( 'socket_select' );
		$read = $socks;
		foreach ( $sock as $s ) $read[] = $s;
		$write = array();
		foreach ( $conns as $id => $c )
		{
			if ( !empty( $conns[$id]->output ) )
				$write[] = $conns[$id]->sock;
		}
		if ( false === ( $n = @socket_select( $read, $write, $except = NULL, 1 ) ) ) {
			debug('socket_select() failed: '.socket_strerror(socket_last_error()).NL);
			$sig_term = true;
		}
	} while ( !$n && !$sig_term );
	if ($sig_term) break;

	$failed = false;
	if ( count( $read ) ) writestep( 'test $read' );
	foreach ( $read as $s )
	{
		$id = array_search( $s, $socks );
		if ( !$id )
		{
			writestep( 'new connection' );
			if ( ( $s = socket_accept( $s ) ) < 0 ) exit( 'socket_accept() failed: '.socket_strerror( socket_last_error() ) );
			for ($id = 1; isset($socks[$id]) && $socks[$id] != null; $id++);
			//$id = mymax( $socks );
			//if ( !$id ) $id = 1;
			$socks[$id] = $s;
			$conns[$id] = new cconn( $id, $s );
		} else
		{
			if ( !$conns[$id]->read() ) {
				$failed = true;
				debug( 'read failed'.$id.NL );
			}
			if ($conns[$id]->closeme) {
				@socket_close($socks[$id]);
				$socks[$id] = null;
				unset($socks[$id]);
				unset($conns[$id]);
			}
		}
	}

	if ( $failed ) debug( 'failed'.NL );

	writestep( 'test $write' );
	foreach ( $write as $s )
	{

		$id = array_search( $s, $socks );
		if ( !$id ) continue;
		$c = @socket_write( $s, $conns[$id]->output );
		if ( $c > 0 )
		{
			$conns[$id]->output = substr( $conns[$id]->output, $c );
			writestep( 'write ['.$id.'] '.strlen( $conns[$id]->output ) );
		}
		else
		{
			writestep( '['.$id.']->close' );
			debug( 'write failed'.NL );
			$conns[$id]->close();
			$socks[$id] = null;
			unset($socks[$id]);
		}
	}

	if ( $failed ) debug( 'failed 2'.NL );

	declare(ticks = 1);
	declare(ticks = 0);

} while (!$sig_term);

cleanup();

function roomtest()
{
	global $rooms, $rooms2sock, $conns, $prisonroom, $defaultroom, $roomsaop, $roomsdeny, $rooms2sock, $mess, $mess_public;
	writestep( 'test rooms' );
	foreach ( $rooms as $rid => $room )
	{
		if ( !$room[founderuid] ) continue;
		$rooms[$rid][credits]--;
		if ( $rooms[$rid][credits] <= 0 )
		{
			foreach( $rooms2sock[$rid] as $id )
			{
				$r = $conns[$id]->deny ? $prisonroom : $defaultroom;
				$GLOBALS[this] =& $conns[$id];
				$a = array( '', $r );
				cmd_changeroom( $a );
			}
			unset( $rooms[$rid] );
			unset( $roomsaop[$rid] );
			unset( $roomsdeny[$rid] );
			unset( $rooms2sock[$rid] );
			unset( $mess[$rid] );
			unset( $mess_public[$rid] );
			rooms_save();
			ra_save();
			rd_save();
			$id = $uid2sock[min( array_keys( $uid2sock ) )];
			if ( $id ) $conns[$id]->write_all( 'java_remroom('.$rid.');'.NL );
		}
	}
}

function testaway()
{
	global $conns;
	writestep( 'test away' );
	foreach ( $conns as $id => $c )
	{
		if ( $c->ready && !$c->away && $t - $c->lastmess > 600 )
		{
			$conns[$id]->away = 1;
			$conns[$id]->write_room( 'java_chnuser1('.$c->uid.',\''.$c->login.'\','.$c->img.',1,1)'.NL );
			$conns[$id]->write_friends( 'java_chnuser1('.$c->uid.',\''.$c->login.'\','.$c->img.',1,0)'.NL );
		}
	}
}

function parseinput() {
	global $f_input, $uid2sock, $conns;
	
	flock($f_input, LOCK_EX);
	fseek($f_input, 0, SEEK_SET);
	$a_input = array();
	while (strlen($s = trim(fgets($f_input)))) $a_input[] = $s;
	ftruncate($f_input, 0);
	flock($f_input, LOCK_UN);

//	msg <uid> <puid> <msg>

	foreach ($a_input as $s) {
		if (preg_match('/^msg\s+(\d+)\s+(\d+)\s+(.+)$/', $s, $a)) {
			$i_uid = (int)$a[1];
			$i_puid = (int)$a[2];
			$s_msg = $a[3];
			if (isset($uid2sock[$i_puid]))
				$conns[$uid2sock[$i_puid]]->send($s_msg, $i_puid, $i_uid, false, false);
		}
	}
}

?>