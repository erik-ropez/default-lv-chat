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

$sock = array();

foreach ( $bind as $a )
{
	echo 'starting server at '.$a[host].':'.$a[port].'...';
	if ( ( $s = @socket_create( AF_INET, SOCK_STREAM, 0) ) < 0 ) exit( 'socket_create() failed: '.socket_strerror( socket_last_error() ).NL );
	if ( !@socket_bind( $s, $a[host], $a[port] ) ) exit( 'socket_bind() failed: '.socket_strerror( socket_last_error() ).NL );
	if ( !@socket_listen( $s, 5 ) ) exit( 'socket_listen() failed: '.socket_strerror( socket_last_error() ).NL );
	$sock[] = $s;
	echo 'OK'.NL;
}

if ( file_exists( 'log/error' ) ) rename( 'log/error', 'log/error.'.date( 'YmdHis' ) );

if ( !DEBUG )
{
	$pid = @pcntl_fork();
	if ( $pid == -1 ) exit( 'pcntl_fork() failed' );
	if ( $pid )
	{
		$f = fopen( 'chat3.pid', 'w' );
		fwrite( $f, $pid );
		fclose( $f );
		exit;
	}
	if ( !( $pid = @posix_setsid() ) ) exit( 'posix_setsid() failed' );
	error_reporting( 0 );
}

$sig_term = false;

function cleanup() {
	foreach ($GLOBALS[socks] as $s) {
		socket_shutdown($s);
		socket_close($s);
	}
	foreach ($GLOBALS[sock] as $s) {
		socket_shutdown($s);
		socket_close($s);
	}
	debug('exit'.NL);
	exit;
}

function sig_handler($signo) {
	switch ($signo) {
		case SIGTERM:
			debug('SIGTERM'.NL);
			cleanup();
			break;
		default:
	}
}

pcntl_signal(SIGTERM, "sig_handler");

?>