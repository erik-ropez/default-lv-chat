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

function cmd_changeroom( $params )
{
	global $this, $rooms, $roomaop, $roomdeny, $rooms2sock, $uid2sock, $conns, $mess, $prisonroom;
	if ( !isset( $rooms[$params[1]] ) ) return 'Комнаты с данным номером не существует';
	if ( $this->rid == $params[1] ) return 'Вы находитесь в указанной комнате';
	$founder = $rooms[$params[1]][founderuid];
	if ( !$founder && $this->deny && $params[1] != $prisonroom ) return 'Заключенные не могут находится в публичных комнатах';
	if ( $founder && !empty( $roomdeny[$params[1]][$this->uid] ) ) return 'Вам запрещено заходить в указанную комнату';
	if ( $founder && $rooms[$params[1]][onlyop] && $this->ring && $this->uid != $founder && empty( $roomaop[$params[1]][$this->uid] ) ) return 'Комната закрытого типа и у Вас нет статуса для доступа';
	unset( $rooms2sock[$this->rid][$this->id] );
	$this->write_room(
		'#-'.$this->login.'>'.$rooms[$params[1]][caption].NL.
		'java_remuser('.$this->uid.',1);'.NL
		);
	// java_adduser
	$this->write_all(
		'java_room('.$this->rid.','.count( $rooms2sock[$this->rid] ).');'.NL.
		'java_room('.$params[1].','.( count( $rooms2sock[$params[1]] ) + 1 ).');'.NL
	);
	//--
	$this->rid = $params[1];
	if ( $founder ) $ra = $roomaop[$this->rid];
	if ( $founder ) $ring = ( $founder == $this->uid || $ra[$this->uid] || $this->roomop[$this->rid] ) ? 3 : 4;
	else $ring = $this->ring;
	$this->write_room(
		'#+'.$this->login.NL.
		'java_adduser('.$this->uid.',"'.$this->login.'",'.$this->img.','.$ring.',0,1,'.$this->rightimg_id.',"'.$this->rightimg_alt.'");'.NL
	);
	//--
	$rooms2sock[$this->rid][$this->id] = $this->id;
	// java_init
	$b = array();
	foreach ( $rooms as $i => $a ) $b[] = '['.$i.',"'.$a[caption].'",'.count( $rooms2sock[$i] ).']';
	$c = array();
	if ( $founder )
	{
		foreach ( $rooms2sock[$this->rid] as $i )
		{
			if ( $conns[$i]->uid == $founder || !empty( $ra[$conns[$i]->uid] ) || $conns[$i]->roomop[$this->rid] ) $ring = 3; else
				$ring = 4;
			$c[] = '['.$conns[$i]->uid.',"'.$conns[$i]->login.'",'.$conns[$i]->img.','.$ring.','.$conns[$i]->away.','.$conns[$i]->rightimg_id.',"'.$conns[$i]->rightimg_alt.'"]';
		}
	} else
	{
		foreach ( $rooms2sock[$this->rid] as $i ) $c[] = '['.$conns[$i]->uid.',"'.$conns[$i]->login.'",'.$conns[$i]->img.','.$conns[$i]->ring.','.$conns[$i]->away.','.$conns[$i]->rightimg_id.',"'.$conns[$i]->rightimg_alt.'"]';
	}
	$d = array();
	foreach ( $mess[$this->rid] as $a ) if ( !$a[0] || $a[0] == $this->uid ) $d[] = '[\''.$this->highlight( $a[1] ).'\','.$a[2].','.$a[3].']';
	$d = array_slice( $d, -MESSBUFLEN );
	$e = array();
	foreach ( $this->friends as $fuid => $l ) if ( isset( $uid2sock[$fuid] ) ) $e[] = '['.$conns[$uid2sock[$fuid]]->uid.',"'.$conns[$uid2sock[$fuid]]->login.'",'.$conns[$uid2sock[$fuid]]->img.','.$conns[$uid2sock[$fuid]]->ring.','.$conns[$uid2sock[$fuid]]->away.','.$conns[$uid2sock[$fuid]]->rightimg_id.',"'.$conns[$uid2sock[$fuid]]->rightimg_alt.'"]';
	$this->write(
		'java_init(['.implode( ',', $b ).'],['.implode( ',', $c ).'],['.implode( ',', $e ).'],'.$this->rid.',"'.addslashes( $rooms[$this->rid][topic] ).'");'.NL.
		'java_init_mess(['.implode( ',', $d ).']);'.NL
	);
	return false;
}

?>