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

function cmd_sms( $params )
{
	global $this;

	$text = trim( $params[2] );
	$c = (int)( ( strlen( $text ) - 1 ) / 120 + 1 );
	$op = preg_match( '/^(95|96|97|98|67|68|60|59)/', $params[1] ) ? 1 : 0;
	$sndr = $this->login.'@'.$this->host;
	$rcvr = $op ? $params[1].'@sms.tele2.lv' : '371'.$params[1].'@smsmail.lmt.lv';

	for ( $i = 0; $i < $c; $i++ )
		dumpquery( 'insert into sendmail(from_mail,from_name,to_mail,to_name,subj,text) values ("'.$sndr.'","","'.$rcvr.'","","'.( $i + 1 ).'/'.$c.'","'.substr( $text, $i * 120, 120 ).'")' );

	return 'Сообщение отправленно';
}

?>