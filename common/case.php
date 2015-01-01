<?php
// BF4 Stats Page by Ty_ger07
// http://open-web-community.com/

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
	// remove accidental spaces from name input
	$player = mysqli_real_escape_string($BF4stats, preg_replace('/\s/','',($_GET['player'])));
}
else
{
	$player = null;
}

// player id case
// $pid = ?pid
if(!empty($_GET['pid']) && is_numeric($_GET['pid']))
{
	$pid = mysqli_real_escape_string($BF4stats, $_GET['pid']);
}
else
{
	$pid = null;
}

// player search (jquery input) case
// $term = ?term
if(!empty($_GET['term']))
{
	$term = mysqli_real_escape_string($BF4stats, $_GET['term']);
}
else
{
	$term = null;
}

// server id case
// $sid = ?sid
if(!empty($_GET['sid']) && is_numeric($_GET['sid']))
{
	$sid = mysqli_real_escape_string($BF4stats, $_GET['sid']);
}
else
{
	$sid = null;
}

// game id case
// $gid = ?gid
if(!empty($_GET['gid']) && is_numeric($_GET['gid']))
{
	$gid = mysqli_real_escape_string($BF4stats, $_GET['gid']);
}
else
{
	$gid = null;
}

// chat search input case
// $query = ?q
if(!empty($_GET['q']))
{
	$query = mysqli_real_escape_string($BF4stats, $_GET['q']);
}
else
{
	$query = null;
}

// current page in pagination case
// $currentpage = ?cp
if(!empty($_GET['cp']) && is_numeric($_GET['cp']))
{
	$currentpage = mysqli_real_escape_string($BF4stats, $_GET['cp']);
}
else
{
	$currentpage = null;
}

// page rank in pagination case
// $rank = ?r
if(!empty($_GET['r']))
{
	$rank = mysqli_real_escape_string($BF4stats, $_GET['r']);
}
else
{
	$rank = null;
}

// page order in pagination case
// $order = ?o
if(!empty($_GET['o']))
{
	$order = mysqli_real_escape_string($BF4stats, $_GET['o']);
}
else
{
	$order = null;
}

// scoreboard rank
// $scoreboard_rank = ?rank
if(!empty($_GET['rank']))
{
	$scoreboard_rank = mysqli_real_escape_string($BF4stats, $_GET['rank']);
}
else
{
	$scoreboard_rank = null;
}

// scoreboard order
// $scoreboard_order = ?order
if(!empty($_GET['order']))
{
	$scoreboard_order = mysqli_real_escape_string($BF4stats, $_GET['order']);
}
else
{
	$scoreboard_order = null;
}

// country code (flag image in banner override) case
// $cc = ?cc
if(!empty($_GET['cc']) && strlen($_GET['cc']) == 2 && !(is_numeric($_GET['cc'])))
{
	$cc = strtoupper(mysqli_real_escape_string($BF4stats, $_GET['cc']));
}
else
{
	$cc = null;
}
?>