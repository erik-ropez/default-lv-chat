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

function cmd_changeimg( $params )
{
	global $this, $uid2sock, $conns, $prisonroom;

	if ( !( $uid = getloginuid( $params[1] ) ) ) return 'Неизвестный акаунт';
	$i = isset( $uid2sock[$uid] ) ? $conns[$uid2sock[$uid]]->img : getmydataimg( 'users', $uid, 'img', true );
	if ( $i < 10000 ) return 'У указанного акаунта стандартная картинка';
	if ( isset( $uid2sock[$uid] ) )
	{
		$conns[$uid2sock[$uid]]->img = 0;
 		$conns[$uid2sock[$uid]]->write_room( 'java_chnuser1('.$uid.',\''.$conns[$uid2sock[$uid]]->login.'\',0,'.$conns[$uid2sock[$uid]]->away.',1)'.NL );
 		$conns[$uid2sock[$uid]]->write_friends( 'java_chnuser1('.$uid.',\''.$conns[$uid2sock[$uid]]->login.'\',0,'.$conns[$uid2sock[$uid]]->away.',0)'.NL );
	}
	savemydatak( 'users', $uid, 'img', 0, true );
	dumpquery( 'update users set img=0 where id='.$uid );
	dumpquery( 'update c4_images set uid=0 where id='.$i );
//	unlink( '/secondhand/default/www.default.lv/img/chat/faces/'.$i.'.gif' );
	$this->send( '['.$this->login.'] изменил удалил вашу картинку', $uid, 0 );
	return 'Картинка изменена';
}

?>