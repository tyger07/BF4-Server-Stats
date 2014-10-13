<?php
// case variables for server stats page by Ty_ger07 at http://open-web-community.com/

// DON'T EDIT ANYTHING BELOW UNLESS YOU KNOW WHAT YOU ARE DOING

// page case
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
if(!empty($_GET['pid']) && is_numeric($_GET['pid']))
{
	$pid = mysqli_real_escape_string($BF4stats, $_GET['pid']);
}
else
{
	$pid = null;
}

// player query case
if(!empty($_GET['term']))
{
	$term = mysqli_real_escape_string($BF4stats, $_GET['term']);
}
else
{
	$term = null;
}

// server id case
if(!empty($_GET['sid']) && is_numeric($_GET['sid']))
{
	$sid = mysqli_real_escape_string($BF4stats, $_GET['sid']);
}
else
{
	$sid = null;
}

// game id case
if(!empty($_GET['gid']) && is_numeric($_GET['gid']))
{
	$gid = mysqli_real_escape_string($BF4stats, $_GET['gid']);
}
else
{
	$gid = null;
}

// query input case
if(!empty($_GET['q']))
{
	$query = mysqli_real_escape_string($BF4stats, $_GET['q']);
}
else
{
	$query = null;
}

// current page pagination case
if(!empty($_GET['cp']) && is_numeric($_GET['cp']))
{
	$currentpage = mysqli_real_escape_string($BF4stats, $_GET['cp']);
}
else
{
	$currentpage = null;
}

// page rank case
if(!empty($_GET['r']))
{
	$rank = mysqli_real_escape_string($BF4stats, $_GET['r']);
}
else
{
	$rank = null;
}

// page order case
if(!empty($_GET['o']))
{
	$order = mysqli_real_escape_string($BF4stats, $_GET['o']);
}
else
{
	$order = null;
}

?>