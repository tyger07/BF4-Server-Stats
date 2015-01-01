<?php
// BF4 Stats Page by Ty_ger07
// http://open-web-community.com/

// not at index page
if(!empty($page))
{
	echo '
	<div style="font-size: 10px; text-align: left; padding: 2px;">
	<a href="' . $_SERVER['PHP_SELF'] . '">Index</a>
	&bull; ';
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
	<div class="title">
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
	<div style="font-size: 10px; text-align: left; padding: 2px;">Select a Server Below</div>
	<div class="title">
	STATS INDEX
	</div>
	<div class="clear"></div>
	';
}
// at inherited page since this is the only server in db
elseif(empty($page) && !empty($ServerID))
{
	echo '
	<div style="font-size: 10px; text-align: left; padding: 2px;">
	<a href="' . $_SERVER['PHP_SELF'] . '">Index</a>
	&bull;
	' . $ServerName . '
	</div>
	<div class="title">
	HOME PAGE
	</div>
	<div class="clear"></div>
	';
}
?>
