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

function cmd_statmsg( $params )
{
	global $this;

	$mpp = 10;

	$a = getmydata( 'msgs2', $this->uid );
	$c = empty( $a ) ? 0 : count( $a );
	if ( !$c ) return 'В вашем ящике нет сообщений';

	$page = isset($params[1]) ? $params[1] : 1;
	$p = floor( ( $c - 1 ) / 10 ) + 1;
	if ( $page < 1 || $page > $p ) $ret = 'Неправильный номер страницы'; else
	{
		$ret = 'Страница '.$page.' из '.$p;
		for ( $i = ($page - 1) * $mpp + 1; $i <= $page * $mpp && $i <= $c; $i++ )
		{
			$j = $c - $i;
			$ret .= '<br><a href="" onclick="top.s(\\\'/echomsg '.($j + 1).'\\\');return false;">'.($j + 1).'</a>. '.date( 'Y-m-d H:i:s', $a[$j][created] ).' '.$a[$j][login];
			if ( !$a[$j][readed] ) $ret .= '*';
		}
		$ret .= '<br>Страницы:';
		for ($i = 1; $i <= $p; $i++)
			if ($i == $page) $ret .= ' <b>'.$i.'</b>';
			else $ret .= ' <a href="" onclick="top.s(\\\'/statmsg '.$i.'\\\');return false;">'.$i.'</a>';
	}
	$ret .= '<br>Всего сообщений: '.$c;
	return $ret;
}

?>