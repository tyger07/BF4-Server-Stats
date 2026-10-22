<?php
// BF4 Stats Page by Ty_ger07
// https://myrcon.net/topic/162-chat-guid-stats-and-mapstats-logger-1003/

// not at index page
if(!empty($page))
{
	if(count($ServerIDs) > 1)
	{
		echo '
		<div class="title" style="font-size: 12px; margin-bottom: 6px;">
		<a href="' . $_SERVER['PHP_SELF'] . '">Index</a>
		» ';
		if(!empty($ServerID))
		{
			echo $ServerName;
		}
		else
		{
			echo 'Combined Stats';
		}
		echo '
		</div>
		<div class="clear"></div>
		';
	}
	echo '<div class="title">
	';
	if($page == 'player')
	{
		echo 'PLAYER STATS';
	}
	elseif($page == 'suspicious')
	{
		echo 'SUSPICIOUS PLAYERS';
	}
	elseif($page == 'countries')
	{
		echo 'COUNTRY STATS';
	}
	elseif($page == 'maps')
	{
		echo 'MAP STATS';
	}
	elseif($page == 'server')
	{
		echo 'SERVER INFORMATION';
	}
	elseif($page == 'chat')
	{
		echo 'CHAT LOG';
	}
	elseif($page == 'leaders')
	{
		echo 'LEADERBOARD';
	}
	elseif($page == 'bans')
	{
		echo 'BAN LIST';
	}
	elseif($page == 'home')
	{
		echo 'HOME PAGE';
	}
	echo '
	</div>
	<div class="clear"></div>
	';
}
// at index page
elseif(empty($page) && empty($ServerID))
{
	echo '
	<div class="title" style="font-size: 12px; margin-bottom: 6px;">Select a Server Below</div>
	<div class="clear"></div>
	<div class="title">
	STATS INDEX
	</div>
	<div class="clear"></div>
	';
}
// at inherited page since this is the only server in db
elseif(empty($page) && !empty($ServerID))
{
	if(count($ServerIDs) > 1)
	{
		echo '
		<div class="title" style="font-size: 12px; margin-bottom: 6px;">
		<a href="' . $_SERVER['PHP_SELF'] . '">Index</a>
		» 
		' . $ServerName . '
		</div>
		<div class="clear"></div>
		';
	}
	echo '<div class="title">
	HOME PAGE
	</div>
	<div class="clear"></div>
	';
}
?>