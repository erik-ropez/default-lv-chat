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

function cmd_undeny($params) {
	global $this, $uid2sock, $conns, $prisonroom;
	//-- get data from undeny file
	$a = getmydatak('undeny', 0, $params[1], true);
	if (empty($a)) return 'јппел€ции с таким кодом не найдено';
	$id = $a[id];
	$vuid = $a[uid];
	$duid = $a[puid];
	$time = $a[time];
	$credits = $a[credits];
	$login = $a[login];
	$high = $a[high];
	//-- multiplier
	if ($a[ring] == 1) {$mul = 5; $add = 10;}
	elseif ($a[ring] == 2) {$mul = 2; $add = 4;}
	else {$mul = 1; $add = 0;}
	$mcredits = $credits * $mul + $add;
	$mtime = $time * $mul;
	//--
	$GLOBALS[aeval][] = '$this->send(\'['.$this->login.'] понизил ['.$login.'] за превышение полномочий.\',0,0);';
	//-- set new value of credits/deny for denier
	list($ddeny,$dcredits) = isset($uid2sock[$duid]) ? array($conns[$uid2sock[$duid]]->deny, $conns[$uid2sock[$duid]]->credit) :
		getmydatak('users2', $duid, array('deny', 'credit'));
	if ($dcredits < $mcredits) {
		$t += ($mcredits - $dcredits) * 3600;
		$mcredits = $dcredits;
	}
	$dcredits -= $mcredits;
	$ddeny += $mtime;
	savemydatak('users2', $duid, array('credit', 'deny'), array($dcredits, $ddeny));
	dumpquery('insert into deny (uid,puid,type,level,date,time,credits,pring,id_deny_link,courtstate,high) values ('.$duid.','.$this->uid.',5,1,'.time().','.$mtime.','.$mcredits.',0,'.$id.','.($high ? 4 : 0).',1)');
	dumpquery('update users set deny='.$ddeny.' where id='.$duid);
	//-- victim
	list($vdeny,$vcredits,$vcredittime) = isset($uid2sock[$vuid]) ? array($conns[$uid2sock[$vuid]]->deny, $conns[$uid2sock[$vuid]]->credit, $conns[$uid2sock[$vuid]]->credittime) : getmydatak('users2', $vuid, array('deny', 'credit', 'credittime'));
	$vcredits += $credits;
	$vdeny -= $time;
	if ($vdeny < 0) {
		$vcredittime -= $vdeny;
		$vcredits += (int)($vcredittime / 3600);
		$vcredittime = $vcredittime % 3600;
		$vdeny = 0;
	}
	if (isset($uid2sock[$vuid])) {
		$conns[$uid2sock[$vuid]]->credit = $vcredits;
		$conns[$uid2sock[$vuid]]->deny = $vdeny;
		$conns[$uid2sock[$vuid]]->credittime = $vcredittime;
		if (!$vdeny) $GLOBALS[this]->prisoner = false;
	}
	savemydatak('users2', $vuid, array('credit', 'credittime', 'deny'), array($vcredits, $vcredittime, $vdeny));
	dumpquery('update users set deny='.$vdeny.' where id='.$vuid);
	dumpquery('update deny set courtstate=4,refereeid='.$this->uid.' where id='.$id);
	//--
	$this->change_credits($duid);
	$this->change_credits($vuid);
	//-- deny message
	if (isset($uid2sock[$duid])) {
		$conns[$uid2sock[$duid]]->credit = $dcredits;
		$conns[$uid2sock[$duid]]->deny = $ddeny;
		if ($conns[$uid2sock[$duid]]->rid != $prisonroom) {
			$conns[$uid2sock[$duid]]->prisoner = true;
			$GLOBALS[this] =& $conns[$uid2sock[$duid]];
			cmd_changeroom(array('', $prisonroom));
		}
		$this->send('['.$this->login.'] понизил ¬ас за превышение полномочий. <a href="http://www.default.lv/'.ur2(0, 11, 13).'" target=_blank>ѕодать аппел€цию</a>.', $duid, 0);
	}
	//--
	$a = getmydata('undeny', 0, true);
	unset($a[$params[1]]);
	savemydata('undeny', 0, $a, true);
}

?>