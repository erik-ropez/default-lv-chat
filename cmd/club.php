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

//clubs	[id_club][caption,masks[host,mask],acc[year][month]count]

function cmd_club( $params )
{
	global $this, $clubs, $clubs_cnt;

	if ( !empty( $params[2] ) && !strcasecmp( $params[2], 'list' ) )
	{
		$s = 'Список клубов:';
		foreach ( $clubs as $i => $c ) $s .= '<br>'.$i.'. '.$c[caption].' - '.(int)$clubs_cnt[$i];
		return $s;
	}
	if ( !empty( $params[3] ) && !strcasecmp( $params[3], 'add' ) )
	{
		$i = mymax( $clubs );
		$clubs[$i] = array( 'caption' => $params[4] );
		_cmd_club_save();
		return 'Клуб добавлен в список под номером '.$i;
	}
	if ( !empty( $params[5] ) )
	{
		$nclub = (int)$params[5];
		if ( !isset( $clubs[$nclub] ) ) return 'Клуб под номером '.$nclub.' не существует';
		if ( !empty( $params[7] ) && !strcasecmp( $params[7], 'rem' ) )
		{
			unset( $clubs[$nclub] );
			_cmd_club_save();
			return 'Клуб под номером '.$nclub.' удален';
		}
		if ( !empty( $params[8] ) && !strcasecmp( $params[8], 'mask' ) )
		{
			if ( !empty( $params[10] ) && !strcasecmp( $params[10], 'list' ) )
			{
				$s = 'Список маск для клуба "'.$clubs[$nclub][caption].'":';
				foreach ( $clubs[$nclub][masks] as $i => $m ) $s .= '<br>'.$i.'. '.long2ip( $m[0] ).':'.long2ip( $m[1] );
				return $s;
			}
			if ( !empty( $params[11] ) && !strcasecmp( $params[11], 'rem' ) )
			{
				unset( $clubs[$nclub][masks][$params[12]] );
				_cmd_club_save();
				return 'Маска под номером '.$params[12].' для клуба "'.$clubs[$nclub][caption].'" удалена';
			}
			if ( !empty( $params[13] ) && !strcasecmp( $params[13], 'add' ) )
			{
				$i = mymax( $clubs[$nclub][masks] );
				if ( is_numeric( $params[15] ) ) $m = 0xffffffff << $params[15];
				else $m = ip2long( $params[15] );
				$h = ip2long( $params[14] ) & $m;
				$clubs[$nclub][masks][$i] = array( $h, $m );
				_cmd_club_save();
				return 'Маска для клуба "'.$clubs[$nclub][caption].'" добавлена под номером '.$i;
			}
		}
		if ( !empty( $params[16] ) && !strcasecmp( $params[16], 'acc' ) )
		{
			if ( !empty( $params[18] ) && !strcasecmp( $params[18], 'list' ) )
			{
				$s = 'Количество акаунтов для клуба "'.$clubs[$nclub][caption].'":';
				foreach ( $clubs[$nclub][acc] as $y => $a )
					foreach ( $a as $m => $c )
						$s .= '<br>'.$y.'.'.$m.' - '.$c;
				return $s;
			}
			if ( !empty( $params[19] ) && !strcasecmp( $params[19], 'set' ) )
			{
				if ( $params[22] ) $clubs[$nclub][acc][$params[20]][$params[21]] = $params[22];
				else unset( $clubs[$nclub][acc][$params[20]][$params[21]] );
				_cmd_club_save();
				return 'Количество акаутнов для клуба "'.$clubs[$nclub][caption].'" изменено';
			}
		}
	}
}

function _cmd_club_save()
{
	$f = fopen( $GLOBALS[data_dir].'/clubs', 'w' );
	fwrite( $f, serialize( $GLOBALS[clubs] ) );
	fclose( $f );
}

?>