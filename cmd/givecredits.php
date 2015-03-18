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

function cmd_givecredits( $params )
{
	global $this, $uid2sock, $conns;

	if ( !( $uid = getloginuid( $params[1] ) ) ) return 'Неизвестный акаунт';
	if ( $uid == $this->uid ) return 'Незаконные банковские операции';
	if ( $params[2] < 1 ) return 'Кол-во кредитов должно быть положительным';

	if ( $this->credit - $this->credit_out - 50 < $params[2] ) return 'У вас не хватает свободных кредитов';
	$a = getmydatak( 'credits', $this->uid, 0 );
	$c = $a[$uid][credits] + $params[2];
	$a[$uid] = array( 'login' => $params[1], 'credits' => $c );
	savemydatak( 'credits', $this->uid, 0, $a );
	$a = getmydatak( 'credits', $uid, 1 );
	$a[$this->uid] = array( 'login' => $this->login, 'credits' => $c );
	savemydatak( 'credits', $uid, 1, $a );
	$this->credit_out += $params[2];
	savemydatak( 'users2', $this->uid, 'credit_out', $this->credit_out );
	$credit_in = isset( $uid2sock[$uid] ) ? $conns[$uid2sock[$uid]]->credit_in : getmydatak( 'users2', $uid, 'credit_in' );
	$credit_in += $params[2];
	savemydatak( 'users2', $uid, 'credit_in', $credit_in );
	if ( isset( $uid2sock[$uid] ) ) $conns[$uid2sock[$uid]]->credit_in = $credit_in;
	
	$this->send( '['.$this->login.'] перечислил вам '.$params[2].' кредитов', $uid, 0 );

	$this->change_credits();
	$this->change_credits( $uid );

	return 'Кредиты перечислены';
}

?>