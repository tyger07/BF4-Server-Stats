<?php
// BF4 Stats Page by Ty_ger07
// https://myrcon.net/topic/162-chat-guid-stats-and-mapstats-logger-1003/

// if there is a server id or a page value, this is not the index page
// show menu
if(!empty($ServerID) || !empty($page))
{
	// jquery auto-find players in input box
	// don't show to bots
	if(!($isbot))
	{
		echo '
		<script type="text/javascript">
		$(function()
		{
			$("#soldiers").autocomplete(
			{
				source: "./common/player/player-search.php?gid=' . $GameID;
				if(!empty($ServerID))
				{
					echo '&sid=' . $ServerID;
				}
				echo '",
				minLength: 3,
				delay: 500,
				select: function( event, ui )
				{
					if(ui.item)
					{
						$(\'#soldiers\').val(ui.item.value);
					}
					$(\'#ajaxsearch\').submit();
				}
			});
		});
		</script>
		';
	}
	// continue HTML
	echo '
	<div id="menucontent">
	';
	// player column
	echo '
	<div class="menuitems';
	if($page == 'player')
	{
		echo 'elected';
	}
	echo '" style="width: 19%">
	<form id="ajaxsearch" action="' . $_SERVER['PHP_SELF'] . '" method="get">
	&nbsp; <span class="information">Player:</span>
	<input type="hidden" name="p" value="player" />
	';
	if(!empty($ServerID))
	{
		echo '<input type="hidden" name="sid" value="' . $ServerID . '" />';
	}
	echo '
	<input id="soldiers" type="text" class="inputbox" ';
	// try to fill in search box
	if(!empty($SoldierName))
	{
		echo 'value="' . $SoldierName . '" ';
	}
	echo 'name="player" style="font-size: 12px;"/>
	</form>
	</div>
	';
	// home column
	echo '
	<div class="menuitems';
	if(($page == 'home') || empty($page))
	{
		echo 'elected';
	}
	if(!($isbot) && $adkats_available)
	{
		echo '" style="width: 9%">';
	}
	else
	{
		echo '" style="width: 11%">';
	}
	echo '
	<a class="fill-div" href="' . $_SERVER['PHP_SELF'] . '?p=home';
	if(!empty($ServerID))
	{
		echo '&amp;sid=' . $ServerID;
	}
	echo '">Home</a>
	</div>
	';
	// leaders column
	echo '
	<div class="menuitems';
	if($page == 'leaders')
	{
		echo 'elected';
	}
	if(!($isbot) && $adkats_available)
	{
		echo '" style="width: 12%">';
	}
	else
	{
		echo '" style="width: 13%">';
	}
	echo '
	<a class="fill-div" href="' . $_SERVER['PHP_SELF'] . '?p=leaders';
	if(!empty($ServerID))
	{
		echo '&amp;sid=' . $ServerID;
	}
	echo '">Leaderboard</a>
	</div>
	';
	// suspicious column
	echo '
	<div class="menuitems';
	if($page == 'suspicious')
	{
		echo 'elected';
	}
	echo '" style="width: 11%">
	<a class="fill-div" href="' . $_SERVER['PHP_SELF'] . '?p=suspicious';
	if(!empty($ServerID))
	{
		echo '&amp;sid=' . $ServerID;
	}
	echo '">Suspicious</a>
	</div>
	';
	// bans column
	if(!($isbot) && $adkats_available)
	{
		echo '
		<div class="menuitems';
		if($page == 'bans')
		{
			echo 'elected';
		}
		echo '" style="width: 9%">
		<a class="fill-div" href="' . $_SERVER['PHP_SELF'] . '?p=bans';
		if(!empty($ServerID))
		{
			echo '&amp;sid=' . $ServerID;
		}
		echo '">Bans</a>
		</div>
		';
	}
	// chat column
	echo '
	<div class="menuitems';
	if($page == 'chat')
	{
		echo 'elected';
	}
	if(!($isbot) && $adkats_available)
	{
		echo '" style="width: 9%">';
	}
	else
	{
		echo '" style="width: 11%">';
	}
	echo '
	<a class="fill-div" href="' . $_SERVER['PHP_SELF'] . '?p=chat';
	if(!empty($ServerID))
	{
		echo '&amp;sid=' . $ServerID;
	}
	echo '">Chat</a>
	</div>
	';
	// countries column
	echo '
	<div class="menuitems';
	if($page == 'countries')
	{
		echo 'elected';
	}
	echo '" style="width: 11%">
	<a class="fill-div" href="' . $_SERVER['PHP_SELF'] . '?p=countries';
	if(!empty($ServerID))
	{
		echo '&amp;sid=' . $ServerID;
	}
	echo '">Countries</a>
	</div>
	';
	// maps column
	echo '
	<div class="menuitems';
	if($page == 'maps')
	{
		echo 'elected';
	}
	if(!($isbot) && $adkats_available)
	{
		echo '" style="width: 9%">';
	}
	else
	{
		echo '" style="width: 11%">';
	}
	echo '
	<a class="fill-div" href="' . $_SERVER['PHP_SELF'] . '?p=maps';
	if(!empty($ServerID))
	{
		echo '&amp;sid=' . $ServerID;
	}
	echo '">Maps</a>
	</div>
	';
	// server info column
	echo '
	<div class="menuitems';
	if($page == 'server')
	{
		echo 'elected';
	}
	if(!($isbot) && $adkats_available)
	{
		echo '" style="width: 11%">';
	}
	else
	{
		echo '" style="width: 13%">';
	}
	echo '
	<a class="fill-div" href="' . $_SERVER['PHP_SELF'] . '?p=server';
	if(!empty($ServerID))
	{
		echo '&amp;sid=' . $ServerID;
	}
	echo '">Server Info</a>
	</div>
	</div>
	';
}
?>