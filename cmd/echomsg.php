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

function cmd_echomsg( $params )
{
	global $this;

	$a = getmydata( 'msgs2', $this->uid );
	$c = empty( $a ) ? 0 : count( $a );
	if ( !$c ) return 'В вашем ящике нет сообщений';

	if ( isset( $params[1] ) )
	{
		if ( $params[1] < 1 || $params[1] > $c ) return 'Неправильный номер сообщения';
		$ret = '<b>'.date( 'Y-m-d H:i:s', $a[$params[1] - 1][created] ).' '.$a[$params[1] - 1][login].'</b><br>'.$a[$params[1] - 1][text].'<br><a href="" onclick="top.s(\\\'/delmsg '.$params[1].'\\\');return false;">Удалить сообщение</a>';
		if ( !$a[$params[1] - 1][readed] )
		{
			$a[$params[1] - 1][readed] = 1;
			savemydata( 'msgs2', $this->uid, $a );
		}
	} else
	{
		$d = array();
		foreach ( $a as $i => $b )
		{
			if ( !$b[readed] )
			{
				$a[$i][readed] = 1;
				$d[] = '<b>'.date( 'Y-m-d H:i:s', $b[created] ).' '.$b[login].'</b><br>'.$b[text].'<br><a href="" onclick="top.s(\\\'/delmsg '.($i + 1).'\\\');return false;">Удалить сообщение</a>';
			}
		}
		if ( empty( $d ) ) return 'В вашем ящике нет новых сообщений';
		$ret = implode( '<br>', $d );
		savemydata( 'msgs2', $this->uid, $a );
	}
	return $ret;
}

?>