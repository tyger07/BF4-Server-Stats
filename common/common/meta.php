<?php
// BF4 Stats Page by Ty_ger07
// https://myrcon.net/topic/162-chat-guid-stats-and-mapstats-logger-1003/

// if valid ?p is specified in the URL
if(!empty($page))
{
	if($page == 'player')
	{
		// if there is a ServerID, this is a server stats page
		if(!empty($ServerID))
		{
			echo '
			<meta name="keywords" content="' . $SoldierName . ',' . $ServerName . ',' . $clan_name . ',BF4,Player,Stats" />
			<meta name="description" content="This is our ' . $clan_name . ' BF4 ' . $ServerName . ' player stats page for ' . $SoldierName . '." />
			<title>' . $clan_name . ' BF4 Stats - ' . $SoldierName . ' - ' . $ServerName . '</title>
			';
		}
		// or else this is a combined stats page
		else
		{
			echo '
			<meta name="keywords" content="' . $SoldierName . ',' . $clan_name . ',BF4,Player,Stats" />
			<meta name="description" content="This is our ' . $clan_name . ' BF4 global player stats page for ' . $SoldierName . '." />
			<title>' . $clan_name . ' BF4 Stats - ' . $SoldierName . ' - Global Stats</title>
			';
		}
	}
	elseif($page == 'suspicious')
	{
		// if there is a ServerID, this is a server stats page
		if(!empty($ServerID))
		{
			echo '
			<meta name="keywords" content="Suspicious,Players,' . $ServerName . ',' . $clan_name . ',BF4,Stats" />
			<meta name="description" content="This is our ' . $clan_name . ' BF4 ' . $ServerName . ' Suspicious Players page." />
			<title>' . $clan_name . ' BF4 Stats - Suspicious Players - ' . $ServerName . '</title>
			';
		}
		// or else this is a combined stats page
		else
		{
			echo '
			<meta name="keywords" content="Suspicious,Players,' . $clan_name . ',BF4,Stats" />
			<meta name="description" content="This is our ' . $clan_name . ' BF4 global Suspicious Players page." />
			<title>' . $clan_name . ' BF4 Stats - Suspicious Players - Global Stats</title>
			';
		}
	}
	elseif($page == 'bans')
	{
		// if there is a ServerID, this is a server stats page
		if(!empty($ServerID))
		{
			echo '
			<meta name="keywords" content="Ban,List,' . $ServerName . ',' . $clan_name . ',BF4,Stats" />
			<meta name="description" content="This is our ' . $clan_name . ' BF4 ' . $ServerName . ' Banned Players page." />
			<title>' . $clan_name . ' BF4 Stats - Banned Players - ' . $ServerName . '</title>
			';
		}
		// or else this is a combined stats page
		else
		{
			echo '
			<meta name="keywords" content="Ban,List,' . $clan_name . ',BF4,Stats" />
			<meta name="description" content="This is our ' . $clan_name . ' BF4 global Banned Players page." />
			<title>' . $clan_name . ' BF4 Stats - Banned Players - Global Stats</title>
			';
		}
	}
	elseif($page == 'leaders')
	{
		// if there is a ServerID, this is a server stats page
		if(!empty($ServerID))
		{
			echo '
			<meta name="keywords" content="Top,Players,' . $ServerName . ',' . $clan_name . ',BF4,Stats" />
			<meta name="description" content="This is our ' . $clan_name . ' BF4 ' . $ServerName . ' Top Players page." />
			<title>' . $clan_name . ' BF4 Stats - Top Players - ' . $ServerName . '</title>
			';
		}
		// or else this is a combined stats page
		else
		{
			echo '
			<meta name="keywords" content="Top,Players,' . $clan_name . ',BF4,Stats" />
			<meta name="description" content="This is our ' . $clan_name . ' BF4 global Top Players page." />
			<title>' . $clan_name . ' BF4 Stats - Top Players - Global Stats</title>
			';
		}
	}
	elseif($page == 'countries')
	{
		// if there is a ServerID, this is a server stats page
		if(!empty($ServerID))
		{
			echo '
			<meta name="keywords" content="Country,Stats,' . $ServerName . ',' . $clan_name . ',BF4" />
			<meta name="description" content="This is our ' . $clan_name . ' BF4 ' . $ServerName . ' Country Stats page." />
			<title>' . $clan_name . ' BF4 Stats - Country Stats - ' . $ServerName . '</title>
			';
		}
		// or else this is a combined stats page
		else
		{
			echo '
			<meta name="keywords" content="Country,Stats,' . $clan_name . ',BF4" />
			<meta name="description" content="This is our ' . $clan_name . ' BF4 global Country Stats page." />
			<title>' . $clan_name . ' BF4 Stats - Country Stats - Global Stats</title>
			';
		}
	}
	elseif($page == 'maps')
	{
		// if there is a ServerID, this is a server stats page
		if(!empty($ServerID))
		{
			echo '
			<meta name="keywords" content="Map,Stats,' . $ServerName . ',' . $clan_name . ',BF4" />
			<meta name="description" content="This is our ' . $clan_name . ' BF4 ' . $ServerName . ' Map Stats page." />
			<title>' . $clan_name . ' BF4 Stats - Map Stats - ' . $ServerName . '</title>
			';
		}
		// or else this is a combined stats page
		else
		{
			echo '
			<meta name="keywords" content="Map,Stats,' . $clan_name . ',BF4" />
			<meta name="description" content="This is our ' . $clan_name . ' BF4 global Map Stats page." />
			<title>' . $clan_name . ' BF4 Stats - Map Stats - Global Stats</title>
			';
		}
	}
	elseif($page == 'server')
	{
		// if there is a ServerID, this is a server stats page
		if(!empty($ServerID))
		{
			echo '
			<meta name="keywords" content="Server,Stats,' . $ServerName . ',' . $clan_name . ',BF4,Info" />
			<meta name="description" content="This is our ' . $clan_name . ' BF4 ' . $ServerName . ' Server Info page." />
			<title>' . $clan_name . ' BF4 Stats - Server Info - ' . $ServerName . '</title>
			';
		}
		// or else this is a combined stats page
		else
		{
			echo '
			<meta name="keywords" content="Server,Stats,' . $clan_name . ',BF4,Info" />
			<meta name="description" content="This is our ' . $clan_name . ' BF4 global Server Info page." />
			<title>' . $clan_name . ' BF4 Stats - Server Info - Global Stats</title>
			';
		}
	}
	elseif($page == 'chat')
	{
		// if there is a ServerID, this is a server stats page
		if(!empty($ServerID))
		{
			echo '
			<meta name="keywords" content="Chat,Recent,' . $ServerName . ',' . $clan_name . ',BF4" />
			<meta name="description" content="This is our ' . $clan_name . ' BF4 ' . $ServerName . ' Chat Content page." />
			<title>' . $clan_name . ' BF4 Stats - Chat Log - ' . $ServerName . '</title>
			';
		}
		// or else this is a combined stats page
		else
		{
			echo '
			<meta name="keywords" content="Chat,Recent,' . $clan_name . ',BF4" />
			<meta name="description" content="This is our ' . $clan_name . ' BF4 global Chat Content page." />
			<title>' . $clan_name . ' BF4 Stats - Chat Log - Global Stats</title>
			';
		}
	}
	elseif($page == 'home')
	{
		// if there is a ServerID, this is a server stats page
		if(!empty($ServerID))
		{
			echo '
			<meta name="keywords" content="Home,' . $ServerName . ',' . $clan_name . ',BF4,Server,Stats" />
			<meta name="description" content="This is our ' . $clan_name . ' BF4 ' . $ServerName . ' stats Home Page." />
			<title>' . $clan_name . ' BF4 Stats - Home Page - ' . $ServerName . '</title>
			';
		}
		// or else this is a combined stats page
		else
		{
			echo '
			<meta name="keywords" content="Home,' . $clan_name . ',BF4,Server,Stats" />
			<meta name="description" content="This is our ' . $clan_name . ' BF4 global stats Home Page." />
			<title>' . $clan_name . ' BF4 Stats - Home Page - Global Stats</title>
			';
		}
	}
}
else
{
	// if there is a ServerID, this is a server stats page
	if(!empty($ServerID))
	{
		echo '
		<meta name="keywords" content="Home,' . $ServerName . ',' . $clan_name . ',BF4,Server,Stats" />
		<meta name="description" content="This is our ' . $clan_name . ' BF4 ' . $ServerName . ' stats Home Page." />
		<title>' . $clan_name . ' BF4 Stats - Home Page - ' . $ServerName . '</title>
		';
	}
	// or else this is a combined stats page
	else
	{
		echo '
		<meta name="keywords" content="Index,' . $clan_name . ',BF4,Server,Stats" />
		<meta name="description" content="This is our ' . $clan_name . ' BF4 stats Index Page." />
		<title>' . $clan_name . ' BF4 Stats - Index Page</title>
		';
	}
}
?>