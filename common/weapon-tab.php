<?php

// first connect to the database
// include config.php contents
require_once('../config/config.php');
require_once('../common/functions.php');
require_once('../common/constants.php');
$BF4stats = mysqli_connect(HOST, USER, PASS, NAME, PORT);

// default variable to null
$ServerID = null;
$GameID = 1;
$Code = null;
// get query search string
if(!empty($_GET['sid']))
{
	$ServerID = mysqli_real_escape_string($BF4stats, $_GET['sid']);
}
// get query search string
if(!empty($_GET['gid']))
{
	$GameID = mysqli_real_escape_string($BF4stats, $_GET['gid']);
}
// get query search string
if(!empty($_GET['pid']))
{
	$PlayerID = mysqli_real_escape_string($BF4stats, $_GET['pid']);
}
// get query search string
if(!empty($_GET['c']))
{
	$Code = mysqli_real_escape_string($BF4stats, $_GET['c']);
}

// clean up name
// find clean name in array
if(in_array($Code,$cat_array))
{
	$code_Displayed = array_search($Code,$cat_array);
}
// not found in array.  use ugly version :(
else
{
	$code_Displayed = $Code;
}
						
Statsout($code_Displayed . " Stats", $Code, $weapon_array, $PlayerID, $ServerID, $BF4stats);
?>