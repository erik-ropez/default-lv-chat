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

function cmd_help( $params )
{
	global $this, $cmdz, $cmdz_exc;
	static $cmd2;
	if ( !isset( $cmd2 ) ) $cmd2 = array( 'using' => ur2( 0, 2, 10 ), 'rules' => ur2( 0, 2, 4 ), 'faq' => ur2( 0, 2, 5 ) );
	if ( strlen( $params[1] ) )
	{
		$cmd = $params[1];
		if ( isset( $cmdz[$cmd] ) && ( !isset( $cmdz[$cmd][3] ) || $cmdz[$cmd][3] >= $this->ring || ( isset( $cmdz_exc[$cmd] ) && in_array( strtolower( $this->login ), $cmdz_exc[$cmd] ) ) ) )
			return '/'.$cmd.htmlspecialchars( $cmdz[$cmd][1] ).' - '.$cmdz[$cmd][2];
		else
		if ( isset( $cmd2[$cmd] ) )
		{
			$this->write( ':'.$cmd2[$cmd].NL );
			return 'Информация в новом окне';
		} else return 'Команда "'.$cmd.'" не определена или не доступна';
	} else
	{
		$a = array();
		foreach ( $cmdz as $cmd => $b ) if ( !isset( $b[3] ) || $b[3] >= $this->ring || ( isset( $cmdz_exc[$cmd] ) && in_array( strtolower( $this->login ), $cmdz_exc[$cmd] ) ) ) $a[] = '<a href="" onclick="top.s(\\\'/help '.$cmd.'\\\');return false;">'.$cmd.'</a>';
		sort( $a );
		return 'Список команд: '.implode( ', ', $a ).'<br>Для более подробной информации о команде используйте /help &lt;command&gt;<br>Дополнительная информация о чате:<br>/help using - руководство по изпользованию чата<br>/help rules - правила чата<br>/help faq - часто задаваемые вопросы<br>';
	}
} 

?>