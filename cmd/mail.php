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

function cmd_mail( $params )
{
	global $this;

	$m = $this->mail;
	if ( preg_match( '/[\w\.-]+@[\w\.-]+/', $params[1] ) )
	{
		dumpquery( 'insert into sendmail(from_mail,from_name,to_mail,to_name,subj,text) values ("'.$m.'","'.$this->login.'","'.$params[1].'","","DeFault.Chat","'.$params[2].'")' );
	} else
	{
		if ( !( $uid = getloginuid( $params[1] ) ) ) return '����������� ������';
		$m2 = getmydatak( 'users', $uid, 'mail', true );
		dumpquery( 'insert into sendmail(from_mail,from_name,to_mail,to_name,subj,text) values ("'.$m.'","'.$this->login.'","'.$m2.'","'.$params[1].'","DeFault.Chat","'.$params[2].'")' );
	}
	return '��������� �����������';
}

?>