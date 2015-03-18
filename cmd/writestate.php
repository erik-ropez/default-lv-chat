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

function cmd_writestate($params)
{
	global $socks, $conns, $uid2sock, $iteration, $server_started;
	$t = time() - $server_started;
	$f = fopen('/home/chat/log/state', 'w');
	fwrite( $f,
		'uptime: '.(int)( $t / 60 ).':'.( $t % 60 ).NL.
		'iteration: '.$iteration.NL.
		'socks/conns/uid2sock: '.count( $socks ).'/'.count( $conns ).'/'.count( $uid2sock ).NL.
		'socks = '.var_export( $socks, true ).NL.
		'conns = '.var_export( $conns, true ).NL.
		'uid2sock = '.var_export( $uid2sock, true ).NL.
		''
	);
	fclose($f);
	return 'Дамп записан в log/state ('.$f.')';
}

?>