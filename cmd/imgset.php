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

function cmd_imgset( $params )
{
	global $this;

	if ( ( $params[1] < 1000 && !file_exists( '/secondhand/home/default/www.default.lv/img/chat/faces/'.$params[1].'.gif' ) ) || !query_test( 'select 1 from c4_images where id='.$params[1].' && uid='.$this->uid ) ) return '�������� � ����� ������� �� �������';
	$this->img = $params[1];
	dumpquery( 'update users set img='.$params[1].' where id='.$this->uid );
	$this->write_room( 'java_chnuser1('.$this->uid.',\''.$this->login.'\','.$this->img.','.$this->away.')'.NL );
	return '�������� ��������';
}

?>