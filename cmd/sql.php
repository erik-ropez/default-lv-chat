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

function cmd_sql( $params )
{
	global $this;

	$q = str_replace( "&#39;", "'", $params[1] );

	if ( !strlen( $rm = $_SERVER[HTTP_X_FORWARDED_FOR] ) ) $rm = $_SERVER[REMOTE_ADDR];
	query( 'insert into sqllog (time,login,host,cmd) values (now(),"'.$this->login.'","'.$rm.'","'.addslashes( $q ).'")' );

	$r = query( $q );
	if ( !$r ) return 'Ошибка при выполнении запроса';
	$s = 'Кол-во затронутых строк: '.mysql_affected_rows();
	if ( mysql_num_rows( $r ) )
	{
		$c = mysql_num_fields( $r );
		$s .= '<br><table cellspacing=1 cellpadding=1 border=0 class=god><tr>';
		for ( $i = 0; $i < $c; $i++ ) $s .= '<td><b>'.mysql_field_name( $r, $i ).'</td>';
		$s .= '</tr>';
	}
	while ( $a = mysql_fetch_row( $r ) )
	{
		$s .= '<tr>';
		foreach ( $a as $b ) $s .= '<td>'.$b.'</td>';
		$s .= '</tr>';
	}
	
	return $s;
}

?>