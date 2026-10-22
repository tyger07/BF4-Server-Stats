<?php
// BF4 Stats Page by Ty_ger07
// https://myrcon.net/topic/162-chat-guid-stats-and-mapstats-logger-1003/

// page case
// $page = ?p
if(!empty($_GET['p']))
{
	switch($_GET['p'])
	{
		case 'player':
			$page = 'player';
			break;
		case 'suspicious':
			$page = 'suspicious';
			break;
		case 'leaders':
			$page = 'leaders';
			break;
		case 'countries':
			$page = 'countries';
			break;
		case 'maps':
			$page = 'maps';
			break;
		case 'server':
			$page = 'server';
			break;
		case 'chat':
			$page = 'chat';
			break;
		case 'home':
			$page = 'home';
			break;
		case 'bans':
			$page = 'bans';
			break;
		default:
			$page = 'home';
	}
}
else
{
	$page = null;
}

// player name case
// $player = ?player
if(!empty($_GET['player']))
{
	$player = str_replace(array('\'', '"', '\\', '`'), '',mysqli_real_escape_string($BF4stats, strip_tags(preg_replace('/\s/','',$_GET['player']))));
}
else
{
	$player = null;
}

// player id case
// $pid = ?pid
if(!empty($_GET['pid']) && is_numeric($_GET['pid']))
{
	$pid = mysqli_real_escape_string($BF4stats, strip_tags($_GET['pid']));
}
else
{
	$pid = null;
}

// player search (jquery input) case
// $term = ?term
if(!empty($_GET['term']))
{
	$term = str_replace(array('\'', '"', '\\', '`'), '',mysqli_real_escape_string($BF4stats, strip_tags($_GET['term'])));
}
else
{
	$term = null;
}

// server id case
// $sid = ?sid
if(!empty($_GET['sid']) && is_numeric($_GET['sid']))
{
	$sid = mysqli_real_escape_string($BF4stats, strip_tags($_GET['sid']));
}
else
{
	$sid = null;
}

// game id case
// $gid = ?gid
if(!empty($_GET['gid']) && is_numeric($_GET['gid']))
{
	$gid = mysqli_real_escape_string($BF4stats, strip_tags($_GET['gid']));
}
else
{
	$gid = null;
}

// chat search input case
// $query = ?q
if(!empty($_GET['q']))
{
	$query = str_replace(array('\'', '"', '\\', '`'), '',mysqli_real_escape_string($BF4stats, strip_tags($_GET['q'])));
}
else
{
	$query = null;
}

// current page in pagination case
// $currentpage = ?cp
if(!empty($_GET['cp']) && is_numeric($_GET['cp']))
{
	$currentpage = mysqli_real_escape_string($BF4stats, strip_tags($_GET['cp']));
}
else
{
	$currentpage = null;
}

// page rank in pagination case
// $rank = ?r
if(!empty($_GET['r']) && !(is_numeric($_GET['r'])))
{
	$rank = mysqli_real_escape_string($BF4stats, strip_tags(preg_replace('/\s/','',$_GET['r'])));
}
else
{
	$rank = null;
}

// page order in pagination case
// $order = ?o
if(!empty($_GET['o']) && !(is_numeric($_GET['o'])))
{
	$order = mysqli_real_escape_string($BF4stats, strip_tags(preg_replace('/\s/','',$_GET['o'])));
}
else
{
	$order = null;
}

// scoreboard rank
// $scoreboard_rank = ?rank
if(!empty($_GET['rank']) && !(is_numeric($_GET['rank'])))
{
	$scoreboard_rank = mysqli_real_escape_string($BF4stats, strip_tags(preg_replace('/\s/','',$_GET['rank'])));
}
else
{
	$scoreboard_rank = null;
}

// scoreboard order
// $scoreboard_order = ?order
if(!empty($_GET['order']) && !(is_numeric($_GET['order'])))
{
	$scoreboard_order = mysqli_real_escape_string($BF4stats, strip_tags(preg_replace('/\s/','',$_GET['order'])));
}
else
{
	$scoreboard_order = null;
}

// cache refresh
// $cr = ?cr
if(!empty($_GET['cr']) AND is_numeric($_GET['cr']))
{
	$cr = mysqli_real_escape_string($BF4stats, strip_tags($_GET['cr']));
}
else
{
	$cr = null;
}
?>