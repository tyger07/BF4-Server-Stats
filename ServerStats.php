<?php
// server stats page by Ty_ger07 at http://open-web-community.com/
// THIS FOLLOWING INFORMATION NEEDS TO BE FILLED IN

// DATABASE SERVER ID
$server_ID		= ''; // default server ID is 1.  If using more than one server in the same database, you can select a server other than the first one.

// SERVER NAME
$server_name	= ''; // server name to display

// BATTLE LOG LINK
$battlelog		= ''; // your server battlelog link

// DON'T EDIT ANYTHING BELOW UNLESS YOU KNOW WHAT YOU ARE DOING
//
//
// include common.php contents
require_once('./common/common.php');
// database connection details
$database_connect = $db_host . ':' . $db_port;
// start counting page load time
$mtime = microtime();
$mtime = explode(" ",$mtime);
$mtime = $mtime[1] + $mtime[0];
$starttime = $mtime;
// output the header
echo '
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en-gb" xml:lang="en-gb">
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<meta http-equiv="content-language" content="en-gb" />
<meta http-equiv="content-style-type" content="text/css" />
<meta http-equiv="imagetoolbar" content="no" />
<meta name="resource-type" content="document" />
<meta name="distribution" content="global" />
<meta name="copyright" content="2013 Open-Web-Community http://open-web-community.com/" />
';
// hide php notices
error_reporting(E_ALL ^ E_NOTICE);
// load stylesheet now because we have to (a bit early but ok)
echo '<link rel="stylesheet" href="./common/stats.css" type="text/css" />';
// use server name in database if user didn't enter one above
$database_connected = 0;
if($server_name == null)
{
	// connect to the database early because we have to
	@mysql_connect($database_connect, $db_uname, $db_pass);
	@mysql_select_db($db_name) or die ("<title>BF4 Player Stats - Error</title></head><body><br/><center><b>Unable to access stats database. Please notify this website's administrator.</b></center><br/><center>If you are the administrator, please seek assistance <a href='https://forum.myrcon.com/showthread.php?6854-Server-Stats-page-for-XpKiller-s-BF4-Chat-GUID-Stats-and-Mapstats-Logger' target='_blank'>here</a>.</center><br/></body></html>");
	$database_connected = 1;
	$server_query = @mysql_query("SELECT `ServerName` FROM `tbl_server` WHERE `ServerID` = '$server_ID'");
	if(@mysql_num_rows($server_query)!=0)
	{
		$server_row = @mysql_fetch_assoc($server_query);
		$server_name = $server_row['ServerName'];
	}
}
// change page title, meta description, and keywords depending on the page content
if($_GET['search_player'])
{
	$player_name = $_GET['player_name'];
	echo '
	<meta name="keywords" content="' . $player_name . ',' . $server_name . ',' . $clan_name . ',BF4,Player,Stats,Server" />
	<meta name="description" content="This is our ' . $clan_name . ' BF4 ' . $server_name . ' server player stats page for ' . $player_name . '." />
	<title>' . $clan_name . ' BF4 Player Stats - ' . $player_name . ' - ' . $server_name . '</title>
	';
}
elseif($_GET['suspicious_players'])
{
	echo '
	<meta name="keywords" content="Suspicious,Players,' . $server_name . ',' . $clan_name . ',BF4,Player,Stats,Server" />
	<meta name="description" content="This is our ' . $clan_name . ' BF4 ' . $server_name . ' server Suspicious Players page." />
	<title>' . $clan_name . ' BF4 Player Stats - Suspicious Players - ' . $server_name . '</title>
	';
}
elseif($_GET['top25_players'])
{
	echo '
	<meta name="keywords" content="Top,Players,' . $server_name . ',' . $clan_name . ',BF4,Player,Stats,Server" />
	<meta name="description" content="This is our ' . $clan_name . ' BF4 ' . $server_name . ' server player stats page of Top Players." />
	<title>' . $clan_name . ' BF4 Player Stats - Top Players - ' . $server_name . '</title>
	';
}
elseif($_GET['top25_countries'])
{
	echo '
	<meta name="keywords" content="Country,' . $server_name . ',' . $clan_name . ',BF4,Player,Stats,Server" />
	<meta name="description" content="This is our ' . $clan_name . ' BF4 ' . $server_name . ' server Country Stats page." />
	<title>' . $clan_name . ' BF4 Player Stats - Country Stats - ' . $server_name . '</title>
	';
}
elseif($_GET['mapstats'])
{
	echo '
	<meta name="keywords" content="Map,' . $server_name . ',' . $clan_name . ',BF4,Player,Stats,Server" />
	<meta name="description" content="This is our ' . $clan_name . ' BF4 ' . $server_name . ' server Map Stats page." />
	<title>' . $clan_name . ' BF4 Player Stats - Map Stats - ' . $server_name . '</title>
	';
}
elseif($_GET['serverstats'])
{
	echo '
	<meta name="keywords" content="Server,Scoreboard,' . $server_name . ',' . $clan_name . ',BF4,Player,Stats,Info" />
	<meta name="description" content="This is our ' . $clan_name . ' BF4 ' . $server_name . ' server Scoreboard and Info page." />
	<title>' . $clan_name . ' BF4 Player Stats - Server Info - ' . $server_name . '</title>
	<meta http-equiv="refresh" content="60" />
	';
}
elseif($_GET['chat'])
{
	echo '
	<meta name="keywords" content="Chat,' . $server_name . ',' . $clan_name . ',BF4,Player,Recent,Server" />
	<meta name="description" content="This is our ' . $clan_name . ' BF4 ' . $server_name . ' server Chat Content page." />
	<title>' . $clan_name . ' BF4 - Recent Chat - ' . $server_name . '</title>
	<meta http-equiv="refresh" content="60" />
	';
}
else
{
	echo '
	<meta name="keywords" content="Main,Top,25,Players,' . $server_name . ',' . $clan_name . ',BF4,Player,Stats,Server" />
	<meta name="description" content="This is our ' . $clan_name . ' BF4 ' . $server_name . ' server main player stats page." />
	<title>' . $clan_name . ' BF4 Player Stats - Main Page - ' . $server_name . '</title>
	<meta http-equiv="refresh" content="60" />
	';
}
echo '
</head>
<body>
<br/>
<div id="pagebody">
<br/>
<table width="100%" cellspacing="1">
<tr> 
<td>
<div>
<div class="topcontent">
<center><a href="' . $banner_url . '" target="_blank"><img alt="BF4 Stats Page Copyright 2013 Open-Web-Community" border="0" src="' . $banner_image . '" /></a></center>
</div>
';
// if index link is present, show this block
if($index_link != '')
{
	echo '
	<br/>
	<div class="topcontent">
	<table width="95%" align="center" border="0">
	<tr>
	<td width="90%">
	<table width="100%" border="0">
	<tr>
	<td>
	<br/><a href="' . $index_link . '"><font size="4">Return to ' . $clan_name . ' Stats Index Page</font></a><br/>
	</td>
	</tr>
	<tr>
	<td>
	<a href="' . $_SERVER['PHP_SELF'] . '"><b><font class="information">Currently viewing:</font> ' . $server_name . '</b></a><br/>
	</td>
	</tr>
	</table>
	</td>
	<td width="10%" style="text-align: right;">
	<br/><a href="' . $battlelog . '" target="_blank"><img src="./images/joinbtn.png" alt="join" class="joinbutton"/></a><br/>
	</td>
	</tr>
	</table>
	<br/>
	</div>
	';
}
else
{
	echo '
	<br/>
	<div class="topcontent">
	<table width="95%" align="center" border="0">
	<tr>
	<td width="75%">
	<br/><a href="' . $_SERVER['PHP_SELF'] . '"><b><font class="information">Currently viewing:</font> ' . $server_name . '</b></a><br/>
	</td>
	<td width="25%" style="float: right;">
	<br/><a href="' . $battlelog . '" target="_blank"><img src="./images/joinbtn.png" alt="join" class="joinbutton"/></a><br/>
	</td>
	</tr>
	</table>
	<br/>
	</div>
	';
}
echo '
<table border="0" width="100%" align="center">
<tr>
<td>
<center>
';
// connect to the database if not already done
if($database_connected == 0)
{
	@mysql_connect($database_connect, $db_uname, $db_pass);
	@mysql_select_db($db_name) or die ("<b><br/>Unable to access stats database. Please notify this website's administrator.</b><br/>If you are the administrator, please seek assistance <a href='https://forum.myrcon.com/showthread.php?6854-Server-Stats-page-for-XpKiller-s-BF4-Chat-GUID-Stats-and-Mapstats-Logger'>here</a>.<br/></center></td></tr></table></div></td></tr></table></div></body></html>");
	$database_connected = 1;
}
echo '
<table width="100%">
<tr>
<td width="1%">
</td>
<td>
<table width="100%">
<tr>
<td>
<div class="menucontent">
<table align="center" width="100%" border="0">
<tr>
<td width="30%" style="text-align: left">
<form action="' . $_SERVER['PHP_SELF'] . '" method="get">
&nbsp; &nbsp; <font class="information">Player:</font>&nbsp;
<input type="text" class="inputbox" name="player_name" />
<input type="submit" name="search_player" value="Search" title="Search" class="button" />
</form>
</td>
<td width="10%" style="text-align: center">
&nbsp;
</td>
<td width="10%" style="text-align: center">
<a href="' . $_SERVER['PHP_SELF'] . '">Home</a>
</td>
<td width="10%" style="text-align: center">
<a href="' . $_SERVER['PHP_SELF'] . '?suspicious_players=Search">Suspicious</a>
</td>
<td width="10%" style="text-align: center">
<a href="' . $_SERVER['PHP_SELF'] . '?chat=View">Chat</a>
</td>
<td width="10%" style="text-align: center">
<a href="' . $_SERVER['PHP_SELF'] . '?top25_countries=View">Country Stats</a>
</td>
<td width="10%" style="text-align: center">
<a href="' . $_SERVER['PHP_SELF'] . '?mapstats=View">Map Stats</a>
</td>
<td width="10%" style="text-align: center">
<a href="' . $_SERVER['PHP_SELF'] . '?serverstats=View">Server Info</a>
</td>
</tr>
</table>
</div>
<br/>
';
// these constants will be used later
// make an array of game modes
$mode_array = array('Air Superiority'=>'AirSuperiority0','Capture The Flag'=>'CaptureTheFlag0','Team Deathmatch'=>'TeamDeathMatch0','Domination'=>'Domination0','Defuse'=>'Elimination0','Obliteration'=>'Obliteration','Tank Superiority'=>'TankSuperiority0','Rush'=>'RushLarge0','Conquest Large'=>'ConquestLarge0','Conquest Small'=>'ConquestSmall0','Squad Deathmatch'=>'SquadDeathMatch0');
// make an array of map names
$map_array = array('Zavod 311'=>'MP_Abandoned','Lancang Dam'=>'MP_Damage','Flood Zone'=>'MP_Flooded','Golmud Railway'=>'MP_Journey','Paracel Storm'=>'MP_Naval','Operation Locker'=>'MP_Prison','Hainan Resort'=>'MP_Resort','Siege of Shanghai'=>'MP_Siege','Rogue Transmission'=>'MP_TheDish','Dawnbreaker'=>'MP_Tremors');
// make an array of squad names
$squad_array = array('None'=>'0','Alpha'=>'1','Bravo'=>'2','Charlie'=>'3','Delta'=>'4','Echo'=>'5','Foxtrot'=>'6','Golf'=>'7','Hotel'=>'8','India'=>'9','Juliet'=>'10','Kilo'=>'11','Lima'=>'12','Mike'=>'13','November'=>'14','Oscar'=>'15','Papa'=>'16','Quebec'=>'17','Romeo'=>'18','Sierra'=>'19','Tango'=>'20','Uniform'=>'21','Victor'=>'22','Whiskey'=>'23','X-Ray'=>'24','Yankee'=>'25','Zulu'=>'26');
// make an array of country names
$country_array = array('Null'=>'','Unknown'=>'--','Afghanistan'=>'AF','Albania'=>'AL','Algeria'=>'DZ','American Samoa'=>'AS','Andorra'=>'AD','Angola'=>'AO','Anguilla'=>'AI','Antarctica'=>'AQ','Antigua'=>'AG','Argentina'=>'AR','Armenia'=>'AM','Aruba'=>'AW','Australia'=>'AU','Austria'=>'AT','Azerbaijan'=>'AZ','Bahamas'=>'BS','Bahrain'=>'BH','Bangladesh'=>'BD','Barbados'=>'BB','Belarus'=>'BY','Belgium'=>'BE','Belize'=>'BZ','Benin'=>'BJ','Bermuda'=>'BM','Bhutan'=>'BT','Bolivia'=>'BO','Bosnia'=>'BA','Botswana'=>'BW','Bouvet Island'=>'BV','Brazil'=>'BR','Indian Ocean'=>'IO','Brunei Darussalum'=>'BN','Bulgaria'=>'BG','Burkina Faso'=>'BF','Burundi'=>'BI','Cambodia'=>'KH','Cameroon'=>'CM','Canada'=>'CA','Cape Verde'=>'CV','Cayman Islands'=>'KY','Central Africa'=>'CF','Chad'=>'TD','Chile'=>'CL','China'=>'CN','Christmas Island'=>'CX','Cocos Islands'=>'CC','Columbia'=>'CO','Comoros'=>'KM','Congo'=>'CG','Republic of Congo'=>'CD','Cook Islands'=>'CK','Costa Rica'=>'CR','Ivory Coast'=>'CI','Croatia'=>'HR','Cuba'=>'CU','Cyprus'=>'CY','Czech Repuplic'=>'CZ','Denmark'=>'DK','Djibouti'=>'DJ','Dominica'=>'DM','Dominican Republic'=>'DO','East Timor'=>'TP','Ecuador'=>'EC','Egypt'=>'EG','El Salvador'=>'SV','Equatorial Guinea'=>'GQ','Eritrea'=>'ER','Estonia'=>'EE','Ethiopia'=>'ET','Falkland Islands'=>'FK','Faroe Islands'=>'FO','Fiji'=>'FJ','Finland'=>'FI','France'=>'FR','Metropolitan France'=>'FX','French Guiana'=>'GF','French Polynesia'=>'PF','French Territories'=>'TF','Gabon'=>'GA','Gambia'=>'GM','Georgia'=>'GE','Germany'=>'DE','Ghana'=>'GH','Gibraltar'=>'GI','Greece'=>'GR','Greenland'=>'GL','Grenada'=>'GD','Guadeloupe'=>'GP','Guam'=>'GU','Guatemala'=>'GT','Guernsey'=>'GG','Guinea'=>'GN','Guinea-Bissau'=>'GW','Guyana'=>'GY','Haiti'=>'HT','McDonald Islands'=>'HM','Vatican City'=>'VA','Honduras'=>'HN','Hong Kong'=>'HK','Hungary'=>'HU','Iceland'=>'IS','India'=>'IN','Indonesia'=>'ID','Iran'=>'IR','Iraq'=>'IQ','Ireland'=>'IE','Israel'=>'IL','Italy'=>'IT','Jamaica'=>'JM','Japan'=>'JP','Jordan'=>'JO','Kazakstan'=>'KZ','Kenya'=>'KE','Kiribati'=>'KI','North Korea'=>'KP','South Korea'=>'KR','Kuwait'=>'KW','Kyrgyzstan'=>'KG','Lao'=>'LA','Latvia'=>'LV','Lebanon'=>'LB','Lesotho'=>'LS','Liberia'=>'LR','Libya'=>'LY','Liechtenstein'=>'LI','Lithuania'=>'LT','Luxembourg'=>'LU','Macau'=>'MO','Macedonia'=>'MK','Madagascar'=>'MG','Malawi'=>'MW','Malaysia'=>'MY','Maldives'=>'MV','Mali'=>'ML','Malta'=>'MT','Marshall Islands'=>'MH','Martinique'=>'MQ','Mauritania'=>'MR','Mauritius'=>'MU','Mayotte'=>'YT','Mexico'=>'MX','Micronesia'=>'FM','Moldova'=>'MD','Monaco'=>'MC','Mongolia'=>'MN','Montserrat'=>'MS','Morocco'=>'MA','Mozambique'=>'MZ','Myanmar'=>'MM','Namibia'=>'NA','Nauru'=>'NR','Nepal'=>'NP','Netherlands'=>'NL','Netherlands Antilles'=>'AN','New Caledonia'=>'NC','New Zealand'=>'NZ','Nicaragua'=>'NI','Niger'=>'NE','Nigeria'=>'NG','Niue'=>'NU','Norfolk Island'=>'NF','Mariana Islands'=>'MP','Norway'=>'NO','Oman'=>'OM','Pakistan'=>'PK','Palau'=>'PW','Palestine'=>'PS','Panama'=>'PA','Papua New Guinea'=>'PG','Paraguay'=>'PY','Peru'=>'PE','Philippines'=>'PH','Pitcairn'=>'PN','Poland'=>'PL','Portugal'=>'PT','Puerto Rico'=>'PR','Qatar'=>'QA','Reunion'=>'RE','Romania'=>'RO','Russia'=>'RU','Rwanda'=>'RW','Saint Helena'=>'SH','Saint Kitts'=>'KN','Saint Lucia'=>'LC','Saint Pierre'=>'PM','Saint Vincent'=>'VC','Samoa'=>'WS','San Marino'=>'SM','Sao Tome'=>'ST','Saudi Arabia'=>'SA','Senegal'=>'SN','Seychelles'=>'SC','Sierra Leone'=>'SL','Singapore'=>'SG','Slovakia'=>'SK','Slovenia'=>'SI','Solomon Islands'=>'SB','Somalia'=>'SO','South Africa'=>'ZA','Sandwich Islands'=>'GS','Spain'=>'ES','Sri Lanka'=>'LK','Sudan'=>'SD','Suriname'=>'SR','Svalbard'=>'SJ','Swaziland'=>'SZ','Sweden'=>'SE','Switzerland'=>'CH','Syria'=>'SY','Taiwan'=>'TW','Tajikistan'=>'TJ','Tanzania'=>'TZ','Thailand'=>'TH','Togo'=>'TG','Tokelau'=>'TK','Tonga'=>'TO','Trinidad'=>'TT','Tunisia'=>'TN','Turkey'=>'TR','Turkmenistan'=>'TM','Turks Islands'=>'TC','Tuvalu'=>'TV','Uganda'=>'UG','Ukraine'=>'UA','United Arab Emirates'=>'AE','United Kingdom'=>'GB','United States'=>'US','US Minor Outlying Islands'=>'UM','Uruguay'=>'UY','Uzbekistan'=>'UZ','Vanuatu'=>'VU','Venezuela'=>'VE','Vietnam'=>'VN','Virgin Islands (British)'=>'VG','Virgin Islands (US)'=>'VI','Wallis and Futuna'=>'WF','Western Sahara'=>'EH','Yemen'=>'YE','Yugoslavia'=>'YU','Zambia'=>'ZM','Zimbabwe'=>'ZW');
// functions
// function to find player's weapon stats
function Statsout($headingprint, $damagetype, $ThisPlayerName, $server_ID)
{
	$expand_id = preg_replace("/\s/","",$damagetype);
	// get current query details
	if(isset($_GET['rank']) AND !empty($_GET['rank']))
	{
		$rank = $_GET['rank'];
		if($rank == 'HSR')
		{
			$rankin = '(Headshots/Kills)';
		}
		else
		{
			$rankin = $rank;
		}
	}
	else
	{
		$rankin = 'Friendlyname';
		$rank = 'Friendlyname';
	}
	if(isset($_GET['order']) AND !empty($_GET['order']))
	{
		$order = $_GET['order'];
		if($order == 'DESC')
		{
			$nextorder = 'ASC';
		}
		else
		{
			$nextorder = 'DESC';
		}
	}
	else
	{
		$order = 'ASC';
		$nextorder = 'DESC';
	}
	// see if this player has used this category's weapons
	$weapon_result = @mysql_query("SELECT tws.Friendlyname, wa.Kills, wa.Deaths, wa.Headshots, wa.WeaponID FROM tbl_weapons_stats wa INNER JOIN tbl_server_player tsp ON tsp.StatsID = wa.StatsID INNER JOIN tbl_playerdata tpd ON tsp.PlayerID = tpd.PlayerID INNER JOIN tbl_weapons tws ON tws.WeaponID = wa.WeaponID WHERE tsp.ServerID = '$server_ID' AND tpd.SoldierName = '$ThisPlayerName' AND tws.Damagetype = '$damagetype' AND wa.Kills > '0' ORDER BY $rankin $order");
	// see if we have any records for this player for this category
	if(@mysql_num_rows($weapon_result)!=0)
	{
		echo '
		<div class="innercontent">
		<table width="98%" border="0">
		<tr>
		<th style="text-align: left;">' . $headingprint . '</th>
		</tr>
		</table>
		<table align="center" width="98%" border="0">
		<tr>
		<td width="3%" style="text-align:left">&nbsp;</td>
		';
		if($rank != 'Friendlyname')
		{
			echo'<th width="17%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?player_name=' . $ThisPlayerName . '&amp;search_player=Search&amp;rank=Friendlyname&amp;order=ASC"><span class="orderheader">Weapon Name</span></a></th>';
		}
		else
		{
			echo'<th width="17%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?player_name=' . $ThisPlayerName . '&amp;search_player=Search&amp;rank=Friendlyname&amp;order=' . $nextorder . '"><span class="orderheader">Weapon Name</span></a></th>';
		}
		echo '<th width="16%" style="text-align:left;">Rank</th>';
		if($rank != 'Kills')
		{
			echo'<th width="16%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?player_name=' . $ThisPlayerName . '&amp;search_player=Search&amp;rank=Kills&amp;order=DESC"><span class="orderheader">Kills</span></a></th>';
		}
		else
		{
			echo'<th width="16%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?player_name=' . $ThisPlayerName . '&amp;search_player=Search&amp;rank=Kills&amp;order=' . $nextorder . '"><span class="orderheader">Kills</span></a></th>';
		}
		if($rank != 'Deaths')
		{
			echo'<th width="16%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?player_name=' . $ThisPlayerName . '&amp;search_player=Search&amp;rank=Deaths&amp;order=DESC"><span class="orderheader">Deaths</span></a></th>';
		}
		else
		{
			echo'<th width="16%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?player_name=' . $ThisPlayerName . '&amp;search_player=Search&amp;rank=Deaths&amp;order=' . $nextorder . '"><span class="orderheader">Deaths</span></a></th>';
		}
		if($rank != 'Headshots')
		{
			echo'<th width="16%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?player_name=' . $ThisPlayerName . '&amp;search_player=Search&amp;rank=Headshots&amp;order=DESC"><span class="orderheader">Headshots</span></a></th>';
		}
		else
		{
			echo'<th width="16%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?player_name=' . $ThisPlayerName . '&amp;search_player=Search&amp;rank=Headshots&amp;order=' . $nextorder . '"><span class="orderheader">Headshots</span></a></th>';
		}
		if($rank != 'HSR')
		{
			echo'<th width="16%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?player_name=' . $ThisPlayerName . '&amp;search_player=Search&amp;rank=HSR&amp;order=DESC"><span class="orderheader">Headshot Ratio</span></a></th>';
		}
		else
		{
			echo'<th width="16%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?player_name=' . $ThisPlayerName . '&amp;search_player=Search&amp;rank=HSR&amp;order=' . $nextorder . '"><span class="orderheader">Headshot Ratio</span></a></th>';
		}
		echo '</tr>';
		while($weapon_row = @mysql_fetch_assoc($weapon_result))
		{
			$weapon_name_displayed = $weapon_row['Friendlyname'];
			$weapon_name_displayed = preg_replace("/_/"," ",$weapon_name_displayed);
			$kills = $weapon_row['Kills'];
			$deaths = $weapon_row['Deaths'];
			$headshots = $weapon_row['Headshots'];
			// avoid dividing by zero (this shouldn't happen though because we already filtered out zero kills)
			if($kills==0)
			{
				$kills = 0.0001;
			}
			$ratio = round((($headshots / $kills)*100),2);
			// return kills to their proper value
			if($kills==0.0001)
			{
				$kills = 0;
			}
			// find this player's weapon rank
			$weaponID = $weapon_row['WeaponID'];
			// initialize values
			$weapon_count = 0;
			$num_rows = 0;
			// rank weapon
			$weaponrank_result = @mysql_query("SELECT wa.Kills FROM tbl_weapons_stats wa INNER JOIN tbl_server_player tsp ON tsp.StatsID = wa.StatsID WHERE tsp.ServerID = '$server_ID' AND wa.WeaponID = '$weaponID' AND wa.Kills > '0' ORDER BY wa.Kills DESC");
			// count number of rows as total number of kills with this weapon in the database
			$num_rows = @mysql_num_rows($weaponrank_result);
			while($weaponrank_row = @mysql_fetch_assoc($weaponrank_result))
			{
				$weapon_count++;
				$this_kills = $weaponrank_row['Kills'];
				// if this player's number of kills matches the current kill row
				if($kills == $this_kills)
				{
					break;
				}
			}
			echo '
			<tr>
			<td width="3%" style="text-align:left">&nbsp;</td>
			<td width="17%" class="tablecontents"  style="text-align: left"><font class="information">' . $weapon_name_displayed . ':</font></td>
			<td width="16%" class="tablecontents" style="text-align: left">' . $weapon_count . '<font class="information"> / </font>' . $num_rows . '</td>
			<td width="16%" class="tablecontents" style="text-align: left">' . $kills . '</td>
			<td width="16%" class="tablecontents" style="text-align: left">' . $deaths . '</td>
			<td width="16%" class="tablecontents" style="text-align: left">' . $headshots . '</td>
			<td width="16%" class="tablecontents" style="text-align: left">' . $ratio . ' <font class="information">%</font></td>
			</tr>
			';
		}
		echo '
		</table></div>
		';
	}
}
// function to create and display scoreboard
function scoreboard($server_ID, $server_name, $mode_array, $map_array, $squad_array, $country_array)
{
	echo'
	<div class="middlecontent">
	<table width="100%" border="0">
	<tr>
	<th class="headline"><b>Scoreboard</b></th>
	</tr>
	<tr>
	<td>
	';
	// query for player in server and order them by team
	$scoreboard_players = @mysql_query("SELECT `TeamID` FROM tbl_currentplayers WHERE `ServerID` = '$server_ID' ORDER BY `TeamID` ASC");
	if(@mysql_num_rows($scoreboard_players)==0)
	{
		// initialize values
		$mode_name = 'Unknown';
		$map_name = 'Unknown';
		$mode = 'Unknown';
		// figure out current game mode and map name
		$mode_query = @mysql_query("SELECT `mapName`, `Gamemode`, `maxSlots`, `usedSlots`, `IP_Address` FROM tbl_server WHERE `ServerID` = '$server_ID'");
		if(@mysql_num_rows($mode_query)!=0)
		{
			$mode_row = @mysql_fetch_assoc($mode_query);
			$used_slots = $mode_row['usedSlots'];
			$available_slots = $mode_row['maxSlots'];
			$ip_address = $mode_row['IP_Address'];
			// convert mode to friendly name
			$mode = $mode_row['Gamemode'];
			$mode_name = array_search($mode,$mode_array);
			// convert map to friendly name
			$map = $mode_row['mapName'];
			$map_name = array_search($map,$map_array);
			$map_img = './images/maps/' . $map . '.png';
		}
		echo '
		<div class="innercontent">
		<table width="98%" align="center" border="0" class="prettytable">
		<tr>
		<td class="mapimage" style="background-image: url(' . $map_img . ');">
		<div class="simplecontent">
		<table width="80%" align="center" border="0">
		<tr>
		<td width="10%" style="text-align:left"><br/>&nbsp;<br/><br/></td>
		<td width="22%" style="text-align:left"><br/><font class="information">Current Game Mode:</font><br/><br/></td>
		<td width="22%" style="text-align:left"><br/>' . $mode_name . '<br/><br/></td>
		<td width="22%" style="text-align:left"><br/><font class="information">Current Map:</font><br/><br/></td>
		<td width="22%" style="text-align:left"><br/>' . $map_name . '<br/><br/></td>
		</tr>
		<tr>
		<td width="10%" style="text-align:left">&nbsp;<br/><br/></td>
		<td width="22%" style="text-align:left"><font class="information">Server IP Address:</font><br/><br/></td>
		<td width="22%" style="text-align:left">' . $ip_address . '<br/><br/></td>
		<td width="22%" style="text-align:left"><font class="information">Server Slots:</font><br/><br/></td>
		<td width="22%" style="text-align:left">' . $used_slots . ' <font class="information">/</font> ' . $available_slots . '<br/><br/></td>
		</tr>
		</table>
		</div>
		</td>
		</tr>
		</table>
		</div>
		';
	}
	else
	{
		echo '
		<div class="innercontent">
		<table width="98%" align="center" border="0">
		';
		// initialize values
		$mode_name = 'Unknown';
		$map_name = 'Unknown';
		$mode = 'Unknown';
		$count2 = 0;
		// figure out current game mode and map name
		$mode_query = @mysql_query("SELECT `mapName`, `Gamemode`, `maxSlots`, `usedSlots`, `IP_Address` FROM tbl_server WHERE `ServerID` = '$server_ID'");
		if(@mysql_num_rows($mode_query)!=0)
		{
			$mode_row = @mysql_fetch_assoc($mode_query);
			$used_slots = $mode_row['usedSlots'];
			$available_slots = $mode_row['maxSlots'];
			$ip_address = $mode_row['IP_Address'];
			// convert mode to friendly name
			$mode = $mode_row['Gamemode'];
			$mode_name = array_search($mode,$mode_array);
			// convert map to friendly name
			$map = $mode_row['mapName'];
			$map_name = array_search($map,$map_array);
			$map_img = './images/maps/' . $map . '.png';
			echo '
			<tr>
			<td colspan="2" class="mapimage" style="background-image: url(' . $map_img . ');">
			<div class="simplecontent">
			';
		}
		else
		{
			echo '<tr><td><div>';
		}
		// initialize values
		$mode_shown = 0;
		$last_team = -1;
		while($team_row = @mysql_fetch_assoc($scoreboard_players))
		{
			$this_team = $team_row['TeamID'];
			if($this_team != $last_team)
			{
				if($this_team == 3)
				{
					echo '</tr><tr><td colspan="2">&nbsp;</td></tr><tr>';
				}
				// only show the header information once
				if($mode_shown == 0)
				{
					echo '
					<table width="80%" align="center" border="0">
					<tr>
					<td width="10%" style="text-align:left"><br/>&nbsp;<br/><br/></td>
					<td width="22%" style="text-align:left"><br/><font class="information">Current Game Mode:</font><br/><br/></td>
					<td width="22%" style="text-align:left"><br/>' . $mode_name . '<br/><br/></td>
					<td width="22%" style="text-align:left"><br/><font class="information">Current Map:</font><br/><br/></td>
					<td width="22%" style="text-align:left"><br/>' . $map_name . '<br/><br/></td>
					</tr>
					<tr>
					<td width="10%" style="text-align:left">&nbsp;<br/><br/></td>
					<td width="22%" style="text-align:left"><font class="information">Server IP Address:</font><br/><br/></td>
					<td width="22%" style="text-align:left">' . $ip_address . '<br/><br/></td>
					<td width="22%" style="text-align:left"><font class="information">Server Slots:</font><br/><br/></td>
					<td width="22%" style="text-align:left">' . $used_slots . ' <font class="information">/</font> ' . $available_slots . '<br/><br/></td>
					</tr>
					</table>
					</div>
					</td>
					</tr>
					<tr>
					';
					$mode_shown = 1;
				}
				// change team name shown depending on team number
				if($this_team == 0)
				{
					$team_name = 'Loading In';
				}
				else
				{
					if($mode == 'RushLarge0')
					{
						if($this_team == 1)
						{
							if(($map == 'MP_Abandoned') OR ($map == 'MP_Damage') OR ($map == 'MP_Journey') OR ($map == 'MP_TheDish'))
							{
								$team_name = 'RU Attackers';
							}
							elseif(($map == 'MP_Flooded') OR ($map == 'MP_Naval') OR ($map == 'MP_Prison') OR ($map == 'MP_Resort') OR ($map == 'MP_Siege') OR ($map == 'MP_Tremors'))
							{
								$team_name = 'US Attackers';
							}
							else
							{
								$team_name = 'Attackers';
							}
						}
						elseif($this_team == 2)
						{
							if($map == 'MP_Abandoned')
							{
								$team_name = 'US Defenders';
							}
							elseif(($map == 'MP_Damage') OR ($map == 'MP_Flooded') OR ($map == 'MP_Journey') OR ($map == 'MP_Naval') OR ($map == 'MP_Resort') OR ($map == 'MP_Siege') OR ($map == 'MP_TheDish') OR ($map == 'MP_Tremors'))
							{
								$team_name = 'CN Defenders';
							}
							elseif($map == 'MP_Prison')
							{
								$team_name = 'RU Defenders';
							}
							else
							{
								$team_name = 'Defenders';
							}
						}
						else
						{
							$team_name = 'Team ' . $this_team;
						}
					}
					elseif(($mode == 'ConquestLarge0') OR ($mode == 'ConquestSmall0') OR ($mode == 'Domination0') OR ($mode == 'Elimination0') OR ($mode == 'Obliteration') OR ($mode == 'TeamDeathMatch0'))
					{
						if($this_team == 1)
						{
							if(($map == 'MP_Abandoned') OR ($map == 'MP_Damage') OR ($map == 'MP_Journey') OR ($map == 'MP_TheDish'))
							{
								$team_name = 'RU Army';
							}
							elseif(($map == 'MP_Flooded') OR ($map == 'MP_Naval') OR ($map == 'MP_Prison') OR ($map == 'MP_Resort') OR ($map == 'MP_Siege') OR ($map == 'MP_Tremors'))
							{
								$team_name = 'US Army';
							}
							else
							{
								$team_name = 'US Army';
							}
						}
						elseif($this_team == 2)
						{
							if($map == 'MP_Abandoned')
							{
								$team_name = 'US Army';
							}
							elseif(($map == 'MP_Damage') OR ($map == 'MP_Flooded') OR ($map == 'MP_Journey') OR ($map == 'MP_Naval') OR ($map == 'MP_Resort') OR ($map == 'MP_Siege') OR ($map == 'MP_TheDish') OR ($map == 'MP_Tremors'))
							{
								$team_name = 'CN Army';
							}
							elseif($map == 'MP_Prison')
							{
								$team_name = 'RU Army';
							}
							else
							{
								$team_name = 'RU Army';
							}
						}
						else
						{
							$team_name = 'Team ' . $this_team;
						}
					}
					elseif(($mode == 'SquadDeathMatch0'))
					{
						if($this_team == 1)
						{
							$team_name = 'Alpha';
						}
						elseif($this_team == 2)
						{
							$team_name = 'Bravo';
						}
						elseif($this_team == 3)
						{
							$team_name = 'Charlie';
						}
						elseif($this_team == 4)
						{
							$team_name = 'Delta';
						}
						else
						{
							$team_name = 'Team ' . $this_team;
						}
					}
					else
					{
						$team_name = 'Team ' . $this_team;
					}
				}
				if($this_team == 0)
				{
					echo '<td valign="top" colspan="2">';
				}
				else
				{
					echo '<td valign="top" class="prettytable">';
				}
				if($this_team != 0)
				{
					// query for scores
					$score_query = @mysql_query("SELECT `TeamID`, `Score`, `WinningScore` FROM `tbl_teamscores` WHERE `ServerID` = '$server_ID' AND `TeamID` = '$this_team'");
					if(@mysql_num_rows($score_query)!=0)
					{
						while($score_row = @mysql_fetch_assoc($score_query))
						{
							$Score = $score_row['Score'];
							$WinningScore = $score_row['WinningScore'];
							if($WinningScore == 0)
							{
								echo '<b><font class="teamname">' . $team_name . '</font></b> &nbsp; <font class="information">Tickets Remaining:</font> ' . $Score;
							}
							else
							{
								echo '<b><font class="teamname">' . $team_name . '</font></b> &nbsp; <font class="information">Tickets:</font> ' . $Score . '<font class="information">/</font>' . $WinningScore;
							}
						}
					}
				}
				echo '
				<table width="100%" align="center" border="0" class="prettytable">
				<tr>
				';
				// change team color depending...
				if($this_team == 0)
				{
					echo '
					<th width="15%" style="text-align:left">' . $team_name . '</th>
					<th width="40%" colspan="3" style="text-align:left">Player</th>
					';
				}
				else
				{
					echo '
					<th width="5%" style="text-align:left">#</th>
					<th width="51%" colspan="2" style="text-align:left">Player</th>
					';
				}
				// if player is loading in, don't show the score, kills, deaths, or squad name headers
				if($this_team != 0)
				{
					echo'
					<th width="10%" style="text-align:left">Score</th>
					<th width="10%" style="text-align:left">Kills</th>
					<th width="10%" style="text-align:left">Deaths</th>
					<th width="14%" style="text-align:left">Squad</th>
					';
				}
				echo'</tr>';
				// query all players on this team
				$scoreboard_query = @mysql_query("SELECT `ServerID`, `Soldiername`, `Score`, `Kills`, `Deaths`, `TeamID`, `SquadID`, `CountryCode` FROM tbl_currentplayers WHERE ServerID = '$server_ID' AND `TeamID` = '$this_team' ORDER BY `Score` Desc");
				if(@mysql_num_rows($scoreboard_query)!=0)
				{
					$count = 1;
					while($scoreboard_row = @mysql_fetch_assoc($scoreboard_query))
					{
						$player = $scoreboard_row['Soldiername'];
						$score = $scoreboard_row['Score'];
						$kills = $scoreboard_row['Kills'];
						$deaths = $scoreboard_row['Deaths'];
						$team = $scoreboard_row['TeamID'];
						$squad = $scoreboard_row['SquadID'];
						// convert squad name and country name to friendly names
						$squad_name = array_search($squad,$squad_array);
						$country = strtoupper($scoreboard_row['CountryCode']);
						$country_name = array_search($country,$country_array);
						if(($country == '') OR ($country == '--'))
						{
							$country_img = './images/flags/none.png';
						}
						else
						{
							$country_img = './images/flags/' . strtolower($country) . '.png';	
						}
						echo '
						<tr>
						<td class="tablecontents" width="5%" style="text-align:left">' . $count . '</td>
						<td class="tablecontents" width="26%" style="text-align:left"><a href="' . $_SERVER['PHP_SELF'] . '?player_name=' . $player . '&amp;search_player=Search"><font size="2">' . $player . '</font></a></td>
						<td class="tablecontents" width="26%" style="text-align:left"><img src="' . $country_img . '" alt="' . $country_name . '"/> ' . $country_name . '</td>
						';
						// if player is loading in, don't show the score, kills, deaths, or squad name
						if($this_team != 0)
						{
							echo '
							<td class="tablecontents" width="10%" style="text-align:left">' . $score . '</td>
							<td class="tablecontents" width="10%" style="text-align:left">' . $kills . '</td>
							<td class="tablecontents" width="10%" style="text-align:left">' . $deaths . '</td>
							<td class="tablecontents" width="14%" style="text-align:left">' . $squad_name . '</td>
							';
						}
						$count++;
						echo '</tr>';
					}
				}
				echo '</table></td>';
				if($this_team == 0)
				{
					echo '</tr><tr><td colspan="2">&nbsp;</td></tr><tr>';
				}
			}
			$last_team = $this_team;
		}
		echo '
		</tr>
		</table>
		<br/>
		</div>
		';
	}
	echo '
	</td></tr>
	</table>
	</div>
	';
}
// function to replace dangerous characters in content
function textcleaner($content)
{
	$content = preg_replace("/&/","&amp;",$content);
	$content = preg_replace("/'/","&#39;",$content);
	$content = preg_replace("/</","&lt;",$content);
	$content = preg_replace("/>/","&gt;",$content);
	return $content;
}
// function to reverse cleaning operation
function textuncleaner($content)
{
	
	$content = preg_replace("/&#39;/","'",$content);
	$content = preg_replace("/&lt;/","<",$content);
	$content = preg_replace("/&gt;/",">",$content);
	$content = preg_replace("/&amp;/","&",$content);
	return $content;
}
// function to search for and display all recent chat content
function recent_chat($server_ID, $server_name, $clan_name)
{
	echo'
	<div class="middlecontent">
	<table width="100%" border="0">
	<tr>
	<td class="headline">
	<br/>
	<center>
	<b>Recent Chat Content</b>
	</center>
	<br/>
	</td>
	</tr>
	</table>
	</div>
	<br/>
	<br/>
	<div class="middlecontent">
	<table width="100%" border="0">
	<tr>
	<th class="headline"><b>Chat Results</b></th>
	</tr>
	<tr>
	<td>
	<div class="innercontent">
	<table width="100%" border="0">
	<tr>
	<td>
	';
	// pagination code thanks to: http://www.phpfreaks.com/tutorial/basic-pagination
	// find out how many rows are in the table
	$sql = @mysql_query("SELECT count(`logDate`) AS count, `logDate` FROM tbl_chatlog WHERE `ServerID` = '$server_ID' ORDER BY `logDate` DESC");
	$r = @mysql_fetch_row($sql);
	$numrows = $r[0];
	// number of rows to show per page
	$rowsperpage = 25;
	// find out total pages
	$totalpages = ceil($numrows / $rowsperpage);
	// get the current page or set a default
	if (isset($_GET['currentpage']) && is_numeric($_GET['currentpage']))
	{
		// cast var as int
		$currentpage = (int) $_GET['currentpage'];
	}
	else
	{
		// default page num
		$currentpage = 1;
	}
	// if current page is greater than total pages...
	if ($currentpage > $totalpages)
	{
		// set current page to last page
		$currentpage = $totalpages;
	}
	// if current page is less than first page...
	if ($currentpage < 1)
	{
		// set current page to first page
		$currentpage = 1;
	}
	// get current query details
	if(isset($_GET['rank']) AND !empty($_GET['rank']))
	{
		$rank = $_GET['rank'];
		$rankin = $rank;
	}
	else
	{
		$rankin = 'logDate';
		$rank = 'logDate';
	}
	if(isset($_GET['order']) AND !empty($_GET['order']))
	{
		$order = $_GET['order'];
		if($order == 'DESC')
		{
			$nextorder = 'ASC';
		}
		else
		{
			$nextorder = 'DESC';
		}
	}
	else
	{
		$order = 'DESC';
		$nextorder = 'ASC';
	}
	// the offset of the list, based on current page 
	$offset = ($currentpage - 1) * $rowsperpage;
	// get the info from the db 
	$sql = @mysql_query("SELECT `logDate`, `logSoldierName`, TRIM(`logMessage`) AS logMessage, `logSubset` FROM tbl_chatlog WHERE `ServerID` = '$server_ID' ORDER BY $rankin $order, `logDate` DESC LIMIT $offset, $rowsperpage");
	// offset count
	$count = ($currentpage * 25) - 25;
	// check if chat rows were found
	if(@mysql_num_rows($sql)!=0)
	{
		echo '
		<table width="98%" align="center" border="0" class="prettytable">
		<tr>
		<th width="5%" style="text-align:left">#</th>
		';
		if($rank != 'logDate')
		{
			echo'<th width="13%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?chat=View&amp;currentpage=' . $currentpage . '&amp;rank=logDate&amp;order=DESC"><span class="orderheader">Date</span></a></th>';
		}
		else
		{
			echo'<th width="13%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?chat=View&amp;currentpage=' . $currentpage . '&amp;rank=logDate&amp;order=' . $nextorder . '"><span class="orderheader">Date</span></a></th>';
		}
		if($rank != 'logSoldierName')
		{
			echo'<th width="10%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?chat=View&amp;currentpage=' . $currentpage . '&amp;rank=logSoldierName&amp;order=ASC"><span class="orderheader">Player</span></a></th>';
		}
		else
		{
			echo'<th width="10%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?chat=View&amp;currentpage=' . $currentpage . '&amp;rank=logSoldierName&amp;order=' . $nextorder . '"><span class="orderheader">Player</span></a></th>';
		}
		if($rank != 'logSubset')
		{
			echo'<th width="7%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?chat=View&amp;currentpage=' . $currentpage . '&amp;rank=logSubset&amp;order=ASC"><span class="orderheader">Audience</span></a></th>';
		}
		else
		{
			echo'<th width="7%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?chat=View&amp;currentpage=' . $currentpage . '&amp;rank=logSubset&amp;order=' . $nextorder . '"><span class="orderheader">Audience</span></a></th>';
		}
		if($rank != 'logMessage')
		{
			echo'<th width="65%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?chat=View&amp;currentpage=' . $currentpage . '&amp;rank=logMessage&amp;order=ASC"><span class="orderheader">Message</span></a></th>';
		}
		else
		{
			echo'<th width="65%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?chat=View&amp;currentpage=' . $currentpage . '&amp;rank=logMessage&amp;order=' . $nextorder . '"><span class="orderheader">Message</span></a></th>';
		}
		echo '</tr>';
		// while there are rows to be fetched...
		while($chat_row = @mysql_fetch_assoc($sql))
		{
			// get data
			$logDate = $chat_row['logDate'];
			$logSoldierName = textcleaner($chat_row['logSoldierName']);
			$logMessage = textcleaner($chat_row['logMessage']);
			$logSubset = $chat_row['logSubset'];
			$count++;
			echo '
			<tr>
			<td width="5%" class="tablecontents" style="text-align: left;"><font size="2">' . $count . ':</font></td>
			<td width="13%" class="tablecontents" style="text-align: left;">' . $logDate . '</td>
			<td width="10%" class="tablecontents" style="text-align: left;"><a href="' . $_SERVER['PHP_SELF'] . '?player_name=' . $logSoldierName . '&amp;search_player=Search">' . $logSoldierName . '</a></td>
			<td width="7%" class="tablecontents" style="text-align: left;">' . $logSubset . '</td>
			<td width="65%" class="tablecontents" style="text-align: left;">' . $logMessage . '</td>
			</tr>
			';
		}
	}
	else
	{
		echo '
		<table width="98%" align="center" border="0" class="prettytable">
		<tr>
		<td style="text-align: left;" width="100%" colspan="5"><br/><center><font class="information">No chat content found for ' . $server_name . ' server.</font></center><br/></td>
		</tr>
		';
	}
	// build the pagination links
	echo '</table>';
	// if no chat was found, don't display pagination links
	if(@mysql_num_rows($sql)!=0)
	{
		echo '
		<div class="pagination">
		<center>
		';
		// range of num links to show
		$range = 3;
		// if on page 1, don't show back links
		if ($currentpage > 1)
		{
			// show << link to go back to first page
			echo '<a href="' . $_SERVER['PHP_SELF'] . '?chat=View&amp;currentpage=1&amp;rank=' . $rank . '&amp;order=' . $order . '">&lt;&lt;</a>';
			// get previous page num
			$prevpage = $currentpage - 1;
			// show < link to go back one page
			echo ' <a href="' . $_SERVER['PHP_SELF'] . '?chat=View&amp;currentpage=' . $prevpage . '&amp;rank=' . $rank . '&amp;order=' . $order . '">&lt;</a> ';
		}
		// loop to show links to range of pages around current page
		for($x = ($currentpage - $range); $x < (($currentpage + $range) + 1); $x++)
		{
			// if it's a valid page number...
			if (($x > 0) && ($x <= $totalpages))
			{
				// if we're on current page...
				if ($x == $currentpage)
				{
					// 'highlight' it but don't make a link
					echo ' [<font class="information">' . $x . '</font>] ';
				}
				else
				{
					// make it a link
					echo ' <a href="' . $_SERVER['PHP_SELF'] . '?chat=View&amp;currentpage=' . $x . '&amp;rank=' . $rank . '&amp;order=' . $order . '">' . $x . '</a> ';
				}
			}
		}
		// if not on last page, show forward links        
		if ($currentpage != $totalpages)
		{
			// get next page
			$nextpage = $currentpage + 1;
			// show > link to go forward one page
			echo ' <a href="' . $_SERVER['PHP_SELF'] . '?chat=View&amp;currentpage=' . $nextpage . '&amp;rank=' . $rank . '&amp;order=' . $order . '">&gt;</a> ';
			// show >> link to last page
			echo '<a href="' . $_SERVER['PHP_SELF'] . '?chat=View&amp;currentpage=' . $totalpages . '&amp;rank=' . $rank . '&amp;order=' . $order . '">&gt;&gt;</a>';
		}
		echo '
		</center>
		</div>
		';
	}
	// end build pagination links and end block
	echo '
	</td>
	</tr>
	</table>
	</div>
	</td>
	</tr>
	</table>
	</div>
	';
}
// page content depending on searches
// begin search player logic
if($_GET['search_player'])
{
	// GET player name and remove any spaces accidentally inserted into the search box
	$player_name = preg_replace('/\s/','',($_GET['player_name']));
	// check to see if any result was returned from GET from the URL
	// if no return from GET, display this section
	if ($player_name == null)
	{
		echo '
		<div class="middlecontent">
		<table width="100%" border="0">
		<tr>
		<td>
		<br/>
		<center>
		<font class="alert">Please enter a player name.</font>
		</center>
		<br/>
		</td>
		</tr>
		</table>
		</div>
		';
	}
	else
	{
		echo '
		<div class="middlecontent">
		<table width="100%" border="0">
		<tr>
		<td class="headline">
		<br/>
		<center>
		<b>Statistics Data for ' .$player_name . '</b>
		</center>
		<br/>
		</td>
		</tr>
		</table>
		</div>
		<br/>
		<br/>
		<div class="middlecontent">
		<table width="100%" border="0">
		<tr>
		';
		// get player stats
		$player_data_result = @mysql_query("SELECT tpd.CountryCode, tpd.SoldierName, tps.Suicide, tps.Score, tps.Kills, tps.Deaths, tps.TKs, tps.Headshots, tps.Rounds, tps.Killstreak, tps.Deathstreak FROM tbl_playerstats tps INNER JOIN tbl_server_player tsp ON tsp.StatsID = tps.StatsID INNER JOIN tbl_playerdata tpd ON tsp.PlayerID = tpd.PlayerID WHERE tsp.ServerID = '$server_ID' AND SoldierName = '$player_name'");
		// if no stats were found for player name, display this
		if(@mysql_num_rows($player_data_result)==0)
		{
			echo '
			<td>
			<br/>
			<center>
			<font class="alert">No player data found for "' . $player_name . '" in ' . $server_name . ' server.</font>
			</center><br/>
			</td></tr></table></div><br/>
			';
			// get current query details
			if(isset($_GET['rank']) AND !empty($_GET['rank']))
			{
				$rank = $_GET['rank'];
				if($rank == 'KDR')
				{
					$rankin = '(Kills/Deaths)';
				}
				else
				{
					$rankin = $rank;
				}
			}
			else
			{
				$rankin = 'SoldierName';
				$rank = 'SoldierName';
			}
			if(isset($_GET['order']) AND !empty($_GET['order']))
			{
				$order = $_GET['order'];
				if($order == 'DESC')
				{
					$nextorder = 'ASC';
				}
				else
				{
					$nextorder = 'DESC';
				}
			}
			else
			{
				$order = 'ASC';
				$nextorder = 'DESC';
			}
			// check to see if there are any players who match a similar name
			$player_match_result = @mysql_query("SELECT tpd.SoldierName, tps.Score, tps.Kills, tps.Deaths, tps.Rounds FROM tbl_playerstats tps INNER JOIN tbl_server_player tsp ON tsp.StatsID = tps.StatsID INNER JOIN tbl_playerdata tpd ON tsp.PlayerID = tpd.PlayerID WHERE tsp.ServerID = '$server_ID' AND SoldierName LIKE '%$player_name%' ORDER BY $rankin $order");
			// if a similar name was found, display this
			if(@mysql_num_rows($player_match_result)!=0)
			{
				echo '
				<br/>
				<div class="middlecontent">
				<table width="100%" border="0">
				<tr>
				<th class="headline"><b>Here are some players with names similar to "' . $player_name . '":</b></th>
				</tr>
				<tr>
				<td>
				<div class="innercontent">
				<table width="98%" align="center" border="0">
				<tr>
				<th width="5%" style="text-align:left">#</th>
				';
				if($rank != 'SoldierName')
				{
					echo'<th width="18%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?player_name=' . $player_name . '&amp;search_player=Search&amp;rank=SoldierName&amp;order=ASC"><span class="orderheader">Player</span></a></th>';
				}
				else
				{
					echo'<th width="18%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?player_name=' . $player_name . '&amp;search_player=Search&amp;rank=SoldierName&amp;order=' . $nextorder . '"><span class="orderheader">Player</span></a></th>';
				}
				if($rank != 'Score')
				{
					echo'<th width="15%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?player_name=' . $player_name . '&amp;search_player=Search&amp;rank=Score&amp;order=DESC"><span class="orderheader">Score</span></a></th>';
				}
				else
				{
					echo'<th width="15%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?player_name=' . $player_name . '&amp;search_player=Search&amp;rank=Score&amp;order=' . $nextorder . '"><span class="orderheader">Score</span></a></th>';
				}
				if($rank != 'Rounds')
				{
					echo'<th width="15%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?player_name=' . $player_name . '&amp;search_player=Search&amp;rank=Rounds&amp;order=DESC"><span class="orderheader">Rounds Played</span></a></th>';
				}
				else
				{
					echo'<th width="15%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?player_name=' . $player_name . '&amp;search_player=Search&amp;rank=Rounds&amp;order=' . $nextorder . '"><span class="orderheader">Rounds Played</span></a></th>';
				}
				if($rank != 'Kills')
				{
					echo'<th width="15%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?player_name=' . $player_name . '&amp;search_player=Search&amp;rank=Kills&amp;order=DESC"><span class="orderheader">Kills</span></a></th>';
				}
				else
				{
					echo'<th width="15%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?player_name=' . $player_name . '&amp;search_player=Search&amp;rank=Kills&amp;order=' . $nextorder . '"><span class="orderheader">Kills</span></a></th>';
				}
				if($rank != 'Deaths')
				{
					echo'<th width="15%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?player_name=' . $player_name . '&amp;search_player=Search&amp;rank=Deaths&amp;order=DESC"><span class="orderheader">Deaths</span></a></th>';
				}
				else
				{
					echo'<th width="15%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?player_name=' . $player_name . '&amp;search_player=Search&amp;rank=Deaths&amp;order=' . $nextorder . '"><span class="orderheader">Deaths</span></a></th>';
				}
				if($rank != 'KDR')
				{
					echo'<th width="17%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?player_name=' . $player_name . '&amp;search_player=Search&amp;rank=KDR&amp;order=DESC"><span class="orderheader">Kill/Death Ratio</span></a></th>';
				}
				else
				{
					echo'<th width="17%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?player_name=' . $player_name . '&amp;search_player=Search&amp;rank=KDR&amp;order=' . $nextorder . '"><span class="orderheader">Kill/Death Ratio</span></a></th>';
				}
				echo '</tr>';
				// initialize value
				$count = 0;
				while($player_match_row = @mysql_fetch_assoc($player_match_result))
				{
					$count++;
					$Soldier_Name = $player_match_row['SoldierName'];
					$Score = $player_match_row['Score'];
					$Kills = $player_match_row['Kills'];
					$Deaths = $player_match_row['Deaths'];
					// avoid dividing by 0
					if($Deaths == 0)
					{
						$Deaths = 0.0001;
					}
					// avoid dividing by 0
					if($Kills == 0)
					{
						$Kills = 0.0001;
					}
					$KDR = round(($Kills / $Deaths),2);
					// fix huge KDR division error
					if($Deaths == 0.0001)
					{
						$KDR = ($KDR / 10000);
					}
					// fix tiny KDR division error
					if($KDR == 0.0001)
					{
						$KDR = 0;
					}
					// fix 0
					if($Deaths == 0.0001)
					{
						$Deaths = 0;
					}
					// fix 0
					if($Kills == 0.0001)
					{
						$Kills = 0;
					}
					$Rounds = $player_match_row['Rounds'];
					echo '
					<tr>
					<td width="5%" class="tablecontents" style="text-align: left;"><font size="2">' . $count . ':</font></td>
					<td width="18%" class="tablecontents" style="text-align: left;"><a href="' . $_SERVER['PHP_SELF'] . '?player_name=' . $Soldier_Name . '&amp;search_player=Search"><font size="2">' . $Soldier_Name . '</font></a></td>
					<td width="15%" class="tablecontents" style="text-align: left;">' . $Score . '</td>
					<td width="15%" class="tablecontents" style="text-align: left;">' . $Rounds . '</td>
					<td width="15%" class="tablecontents" style="text-align: left;">' . $Kills . '</td>
					<td width="15%" class="tablecontents" style="text-align: left;">' . $Deaths . '</td>
					<td width="17%" class="tablecontents" style="text-align: left;">' . $KDR . '</td>
					</tr>
					';
				}
				echo '</table><br/></div>';
			}
		}
		else
		{
			// rank queries function
			function rank($server_ID, $player_name, $metric, $order, $width)
			{
				// initialize values
				$count = 0;
				$match = 0;
				// rank players
				$playerrank_result  = @mysql_query("SELECT tpd.SoldierName FROM tbl_playerstats tps INNER JOIN tbl_server_player tsp ON tsp.StatsID = tps.StatsID INNER JOIN tbl_playerdata tpd ON tsp.PlayerID = tpd.PlayerID WHERE tsp.ServerID = '$server_ID' ORDER BY $metric $order, SoldierName ASC");
				// count number of rows as total number of players in the database
				$num_rows = @mysql_num_rows($playerrank_result);
				while($playerrank_row = @mysql_fetch_assoc($playerrank_result))
				{
					// make sure case in player search doesn't matter
					$SoldierNameRank = strtolower($playerrank_row['SoldierName']);
					$SoldierMatch = strtolower($player_name);
					$count++;
					// if player name in rank list matches player of interest
					if($SoldierNameRank == $SoldierMatch)
					{
						$match = 1;
						echo '<td width="' . $width . '%" class="tablecontents" style="text-align:left">' . $count . '<font class="information"> / </font>' . $num_rows . '</td>';
						break;
					}
				}
				// in case no rank match was found, display error (this shouldn't happen)
				if($match == 0)
				{
					echo '<td width="' . $width . '%" class="tablecontents" style="text-align:left">error<font class="information"> / </font>' . $num_rows . '</td>';
				}
			}
			echo '
			<th class="headline"><b>Ranks</b></th>
			</tr>
			<tr>
			<td>
			<div class="innercontent">
			<table width="98%" align="center" border="0">
			<tr>
			<th width="16%" style="text-align:left">Score</th>
			<th width="16%" style="text-align:left">Kills</th>
			<th width="17%" style="text-align:left">Killstreak</th>
			<th width="17%" style="text-align:left">Kill/Death Ratio</th>
			<th width="17%" style="text-align:left">Headshots</th>
			<th width="17%" style="text-align:left">Headshot Ratio</th>
			</tr>
			<tr>
			';
			rank($server_ID, $player_name, 'Score', 'DESC', '16');
			rank($server_ID, $player_name, 'Kills', 'DESC', '16');
			rank($server_ID, $player_name, 'Killstreak', 'DESC', '17');
			rank($server_ID, $player_name, '(Kills/Deaths)', 'DESC', '17');
			rank($server_ID, $player_name, 'Headshots', 'DESC', '17');
			rank($server_ID, $player_name, '(Headshots/Kills)', 'DESC', '17');
			echo '
			</tr>
			</table>
			<br/>
			</div>
			';
			echo '
			</td>
			</tr>
			</table>
			</div>
			';
			echo '
			<br/><br/>
			<div class="middlecontent">
			<table width="100%" border="0">
			<tr>
			<th class="headline"><b>Overview</b></th>
			</tr>
			<tr>
			<td>
			';
			// get information
			$player_data_result_row = @mysql_fetch_assoc($player_data_result);
			$CountryCode = strtoupper($player_data_result_row['CountryCode']);
			if(($CountryCode == '') OR ($CountryCode == '--'))
			{
				$country_img = './images/flags/none.png';
			}
			else
			{
				$country_img = './images/flags/' . strtolower($CountryCode) . '.png';	
			}
			$Suicides = $player_data_result_row['Suicide'];
			$Score = $player_data_result_row['Score'];
			$Kills = $player_data_result_row['Kills'];
			$Deaths = $player_data_result_row['Deaths'];
			// avoid dividing by 0
			if($Deaths == 0)
			{
				$Deaths = 0.0001;
			}
			// avoid dividing by 0
			if($Kills == 0)
			{
				$Kills = 0.0001;
			}
			$Headshots = $player_data_result_row['Headshots'];
			$HSpercent = round((($Headshots / $Kills)*100),2);
			$Rounds = $player_data_result_row['Rounds'];
			$Killstreak = $player_data_result_row['Killstreak'];
			$Deathstreak = $player_data_result_row['Deathstreak'];
			$KDR = round(($Kills / $Deaths),2);
			// fix huge KDR division error
			if($Deaths == 0.0001)
			{
				$KDR = ($KDR / 10000);
			}
			// fix tiny KDR division error
			if($KDR == 0.0001)
			{
				$KDR = 0;
			}
			$TKs = $player_data_result_row['TKs'];
			// return deaths to 0 if necessary
			if($Deaths == 0.0001)
			{
				$Deaths = 0;
			}
			// return kills to 0 if necessary
			if($Kills == 0.0001)
			{
				$Kills = 0;
			}
			echo '
			<div class="innercontent">
			<br/>
			<table width="90%" align="center" border="0">
			<tr>
			<td style="text-align: left;" width="10%">&nbsp;<br/><br/></td>
			';
			// search the country array for the country code of interest and assign the full name to a variable
			$country = array_search($CountryCode,$country_array);
			echo '
			<td width="30%" style="text-align: left;"> <font class="information">Country: </font><img src="' . $country_img . '" alt="' . $country_name . '"/> ' . $country . '<font class="information"> (</font>' . $CountryCode . '<font class="information">)</font><br/><br/></td>
			<td width="30%" style="text-align: left;"> <font class="information">Score: </font>' . $Score . '<br/><br/></td>
			<td width="30%" style="text-align: left;"> <font class="information">Rounds Played: </font>' . $Rounds . '<br/><br/></td>
			</tr><tr>
			<td style="text-align: left;" width="10%">&nbsp;<br/><br/></td>
			<td width="30%" style="text-align: left;"> <font class="information">Kills: </font>' . $Kills . '<br/><br/></td>
			<td width="30%" style="text-align: left;"> <font class="information">Deaths: </font>' . $Deaths . '<br/><br/></td>
			<td width="30%" style="text-align: left;"> <font class="information">Kill / Death Ratio: </font>' . $KDR . '<br/><br/></td>
			</tr><tr>
			<td style="text-align: left;" width="10%">&nbsp;<br/><br/></td>
			<td width="30%" style="text-align: left;"> <font class="information">Killstreak: </font>' . $Killstreak . '<br/><br/></td>
			<td width="30%" style="text-align: left;"> <font class="information">Deathstreak: </font>' . $Deathstreak . '<br/><br/></td>
			<td width="30%" style="text-align: left;"> <font class="information">Team Kills: </font>' . $TKs . '<br/><br/></td>
			</tr><tr>
			<td style="text-align: left;" width="10%">&nbsp;<br/><br/></td>
			<td width="30%" style="text-align: left;"> <font class="information">Headshots: </font>' . $Headshots . '<br/><br/></td>
			<td width="30%" style="text-align: left;"> <font class="information">Headshot Ratio: </font>' . $HSpercent . '<font class="information"> %</font><br/><br/></td>
			<td width="30%" style="text-align: left;"> <font class="information">Suicides: </font>' . $Suicides . '<br/><br/></td>
			</tr>
			</table>
			</div>
			';
		}
		echo '
		</td>
		</tr>
		</table>
		</div>
		';
		// double check that a player was found
		if(@mysql_num_rows($player_data_result)!=0)
		{
			echo '<br/>';
		}
		// double check that a matching player was found
		if(@mysql_num_rows($player_data_result)!=0)
		{
			echo '
			<br/>
			<div class="middlecontent">
			<table width="100%" border="0">
			<tr>
			<th class="headline"><b>Weapons</b></th>
			</tr>
			<tr>
			<td>
			';
			// get assault rifle stats
			Statsout("Assault Rifle Stats","assaultrifle",$player_name, $server_ID);
			// get lmg stats
			Statsout("Light Machine Gun Stats","lmg",$player_name, $server_ID);
			// get shotgun stats
			Statsout("Shot Gun Stats","shotgun",$player_name, $server_ID);
			// get smg stats
			Statsout("Submachine Gun Stats","smg",$player_name, $server_ID);
			// get sniper stats
			Statsout("Sniper Rifle Stats","sniperrifle",$player_name, $server_ID);
			// get pistol stats
			Statsout("Hand Gun Stats","handgun",$player_name, $server_ID);
			// get rocket stats
			Statsout("Projectile Explosive Stats","projectileexplosive",$player_name, $server_ID);
			// get explosive stats
			Statsout("Explosive Stats","explosive",$player_name, $server_ID);
			// get other weapon stats
			Statsout("Other Weapons Stats","melee",$player_name, $server_ID);
			echo '
			<br/>
			</td>
			</tr>
			</table>
			</div>
			<br/><br/>
			';
		}
		// begin dog tag stats
		// double check that a matching player was found
		if(@mysql_num_rows($player_data_result)!=0)
		{
			$player_dogtag_result = @mysql_query("SELECT tpd.SoldierName AS Killer, dt.Count , tpd2.SoldierName AS Victim FROM tbl_dogtags dt INNER JOIN tbl_server_player tsp ON tsp.StatsID = dt.KillerID INNER JOIN tbl_server_player tsp2 ON tsp2.StatsID = dt.VictimID INNER JOIN tbl_playerdata tpd ON tsp.PlayerID = tpd.PlayerID INNER JOIN tbl_playerdata tpd2 ON tsp2.PlayerID = tpd2.PlayerID WHERE tpd.SoldierName = '$player_name' AND tsp.ServerID = '$server_ID'  ORDER BY Count DESC");
			// initialize value
			$count = 0;
			echo '
			<div class="middlecontent">
			<table width="100%" border="0">
			<tr>
			<th class="headline"><b>Dogtags</b></th>
			</tr>
			</table>
			<div class="innercontent">
			<table width="98%" align="center" border="0">
			<tr>
			<th width="100%" colspan="4" style="text-align:left">Dog tags collected by ' . $player_name . ':</th>
			</tr>
			<tr>
			<td width="3%" style="text-align: left">&nbsp;</td>
			<td width="5%" class="tablecontents" style="text-align: left">#</td>
			<td width="45%" class="tablecontents" style="text-align: left">Victim</td>
			<td width="47%" class="tablecontents" style="text-align: left">Count</td>
			</tr>
			';
			// check to see if the player has gotten anyone's tags
			if(@mysql_num_rows($player_dogtag_result)!=0)
			{
				while($player_dogtag_row = @mysql_fetch_assoc($player_dogtag_result))
				{
					$Victim = $player_dogtag_row['Victim'];
					$KillCount = $player_dogtag_row['Count'];
					$count++;
					$KillerID = $player_dogtag_row['KillerID'];
					echo '
					<tr>
					<td width="3%" style="text-align: left">&nbsp;</td>
					<td width="5%" class="tablecontents" style="text-align: left">' . $count . ':</td>
					<td width="45%" class="tablecontents" style="text-align: left"><a href="' . $_SERVER['PHP_SELF'] . '?player_name=' . $Victim . '&amp;search_player=Search"><font size="2">' . $Victim . '</font></a></td>
					<td width="47%" class="tablecontents" style="text-align: left">' . $KillCount . '</td>
					</tr>
					';
				}
			}
			else
			{
				echo '
				<tr>
				<td width="3%" style="text-align: left">&nbsp;</td>
				<td width="97%" class="tablecontents" colspan="3" style="text-align: left">' . $player_name . ' has not collected any dog tags.</td>
				</tr>
				';
			}
			echo '
			</table>
			</div>
			';
			// find who has killed this player
			$victim_dogtag_result = @mysql_query("SELECT tpd.SoldierName AS Killer, dt.Count , tpd2.SoldierName AS Victim FROM tbl_dogtags dt INNER JOIN tbl_server_player tsp ON tsp.StatsID = dt.KillerID INNER JOIN tbl_server_player tsp2 ON tsp2.StatsID = dt.VictimID INNER JOIN tbl_playerdata tpd ON tsp.PlayerID = tpd.PlayerID INNER JOIN tbl_playerdata tpd2 ON tsp2.PlayerID = tpd2.PlayerID WHERE tpd2.SoldierName = '$player_name' AND tsp.ServerID = '$server_ID' ORDER BY Count DESC");
			// initialize value
			$count = 0;
			echo '
			<div class="innercontent"><br/>
			<table width="98%" align="center" border="0">
			<tr>
			<th width="100%" colspan="4" style="text-align:left">Players who have collected ' . $player_name . '\'s dog tags:</th>
			</tr>
			<tr>
			<td width="3%" style="text-align: left">&nbsp;</td>
			<td width="5%" class="tablecontents" style="text-align: left">#</td>
			<td width="45%" class="tablecontents" style="text-align: left">Killer</td>
			<td width="47%" class="tablecontents" style="text-align: left">Count</td>
			</tr>
			';
			// check to see if anyone has got the player's tags
			if(@mysql_num_rows($victim_dogtag_result)!=0)
			{
				while($victim_dogtag_row = @mysql_fetch_assoc($victim_dogtag_result))
				{
					$Killer = $victim_dogtag_row['Killer'];
					$KillCount = $victim_dogtag_row['Count'];
					$count++;
					echo '
					<tr>
					<td width="3%" style="text-align: left">&nbsp;</td>
					<td width="5%" class="tablecontents" style="text-align: left">' . $count . ':</td>
					<td width="45%" class="tablecontents" style="text-align: left"><a href="' . $_SERVER['PHP_SELF'] . '?player_name=' . $Killer . '&amp;search_player=Search"><font size="2">' . $Killer . '</font></a></td>
					<td width="47%" class="tablecontents" style="text-align: left">' . $KillCount . '</td>
					</tr>
					';
				}
			}
			else
			{
				echo '
				<tr>
				<td width="3%" style="text-align: left">&nbsp;</td>
				<td width="97%" class="tablecontents" colspan="3" style="text-align: left">No one has gotten ' . $player_name . '\'s tags.</td>
				</tr>
				';
			}
			echo '
			</table>
			</div>
			<br/>
			</div>
			';
		}
	}
	// begin external stats block
	if($player_name != null)
	{
		echo '
		<br/><br/>
		<div class="middlecontent">
		<table width="100%" border="0">
		<tr>
		<th class="headline"><b>External Links for ' . $player_name . '</b></th>
		</tr>
		<tr><td>
		<div class="innercontent">
		<br/>
		<table align="center" width="95%" border="0">
		<tr>
		<td width="33%" style="text-align: center"><font class="information">Battlelog Stats: </font><a href="http://battlelog.battlefield.com/bf4/user/' . $player_name . '" target="_blank">www.Battlelog.Battlefield.com</a></td>
		<td width="33%" style="text-align: center"><font class="information">BF4 Stats: </font><a href="http://bf4stats.com/pc/' . $player_name . '" target="_blank">www.BF4stats.com</a></td>
		<td width="33%" style="text-align: center"><font class="information">Metabans: </font><a href="http://metabans.com/search/' . $player_name . '" target="_blank">www.Metabans.com</a></td>
		</tr>
		</table>
		<br/>
		</div>
		</td></tr>
		</table>
		</div>
		';
	}
}
// begin suspicious players logic
if($_GET['suspicious_players'])
{
	echo '
	<div class="middlecontent">
	<table width="100%" border="0">
	<tr><td  class="headline">
	<br/><center><b>Suspicious Players</b></center><br/>
	</td></tr>
	</table>
	<table width="100%" border="0">
	<tr><td>
	<br/>
	<center><b>Just because a player shows up on the list as being suspicious does not necessarily mean they are cheating.</b><br />
	<font class="information">The search algorithm makes sure that there is an appropriate sample size used before marking the player as suspicious.</font></center>
	<br/>
	</td></tr>
	</table>
	</div>
	<br/><br/>
	<div  class="middlecontent">
	<table width="100%" border="0">
	<tr>
	<th class="headline"><b>Suspicious Players</b></th>
	</tr>
	<tr>
	<td>
	';
	// get current query details
	if(isset($_GET['rank']) AND !empty($_GET['rank']))
	{
		$rank = $_GET['rank'];
		if($rank == 'KDR')
		{
			$rankin = '(Kills/Deaths)';
		}
		elseif($rank == 'HSR')
		{
			$rankin = '(Headshots/Kills)';
		}
		else
		{
			$rankin = $rank;
		}
	}
	else
	{
		$rankin = '(Kills/Deaths)';
		$rank = 'KDR';
	}
	if(isset($_GET['order']) AND !empty($_GET['order']))
	{
		$order = $_GET['order'];
		if($order == 'DESC')
		{
			$nextorder = 'ASC';
		}
		else
		{
			$nextorder = 'DESC';
		}
	}
	else
	{
		$order = 'DESC';
		$nextorder = 'ASC';
	}
	// check for suspicious players
	$suspicious_players_result = @mysql_query("SELECT tpd.SoldierName, tps.Kills, tps.Deaths, tps.Headshots, tps.Rounds FROM tbl_playerstats tps INNER JOIN tbl_server_player tsp ON tsp.StatsID = tps.StatsID INNER JOIN tbl_playerdata tpd ON tsp.PlayerID = tpd.PlayerID WHERE (tsp.ServerID = '$server_ID' AND (tps.Kills/(tps.Deaths+0.0001)) > 2.0 AND (tps.Headshots/(tps.Kills+0.0001)) > 0.65 AND tps.Kills > 20 AND tps.Rounds > 1) OR (tsp.ServerID = '$server_ID' AND (tps.Kills/(tps.Deaths+0.0001)) > 5.0 AND tps.Kills > 30 AND tps.Rounds > 1) OR (tsp.ServerID = '$server_ID' AND (tps.Kills/(tps.Deaths+0.0001)) > 1.0 AND (tps.Headshots/(tps.Kills+0.0001)) > 0.75 AND tps.Rounds > 1 AND tps.Kills > 15) ORDER BY $rankin $order");
	if(@mysql_num_rows($suspicious_players_result)==0)
	{
		echo '
		<div class="innercontent">
		<table width="98%" align="center" border="0">
		<tr><td>
		<center><font class="information">No suspicious players found in ' . $server_name . ' server.</font></center>
		</td></tr>
		</table>
		<br/>
		</div>
		';
	}
	else
	{
		echo '
		<div class="innercontent">
		<table width="98%" align="center" class="prettytable" border="0">
		<tr>
		<th width="5%" style="text-align:left">#</th>
		';
		if($rank != 'SoldierName')
		{
			echo'<th width="18%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?suspicious_players=Search&amp;rank=SoldierName&amp;order=ASC"><span class="orderheader">Player</span></a></th>';
		}
		else
		{
			echo'<th width="18%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?suspicious_players=Search&amp;rank=SoldierName&amp;order=' . $nextorder . '"><span class="orderheader">Player</span></a></th>';
		}
		if($rank != 'KDR')
		{
			echo'<th width="18%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?suspicious_players=Search&amp;rank=KDR&amp;order=DESC"><span class="orderheader">Kill/Death Ratio</span></a></th>';
		}
		else
		{
			echo'<th width="18%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?suspicious_players=Search&amp;rank=KDR&amp;order=' . $nextorder . '"><span class="orderheader">Kill/Death Ratio</span></a></th>';
		}
		if($rank != 'HSR')
		{
			echo'<th width="18%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?suspicious_players=Search&amp;rank=HSR&amp;order=DESC"><span class="orderheader">Headshot Ratio</span></a></th>';
		}
		else
		{
			echo'<th width="18%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?suspicious_players=Search&amp;rank=HSR&amp;order=' . $nextorder . '"><span class="orderheader">Headshot Ratio</span></a></th>';
		}
		if($rank != 'Rounds')
		{
			echo'<th width="18%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?suspicious_players=Search&amp;rank=Rounds&amp;order=DESC"><span class="orderheader">Rounds Played</span></a></th>';
		}
		else
		{
			echo'<th width="18%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?suspicious_players=Search&amp;rank=Rounds&amp;order=' . $nextorder . '"><span class="orderheader">Rounds Played</span></a></th>';
		}
		echo '</tr>';
		//initialize value
		$count = 0;
		while($suspicious_players_row = @mysql_fetch_assoc($suspicious_players_result))
		{
			$SoldierName = $suspicious_players_row['SoldierName'];
			$Kills = $suspicious_players_row['Kills'];
			$Deaths = $suspicious_players_row['Deaths'];
			// make sure we don't try to divide by zero
			if($Deaths == 0)
			{
				$Deaths = 0.0001;
			}
			$KDR = round(($Kills / $Deaths), 2);
			// fix huge KDR division error
			if($Deaths == 0.0001)
			{
				$KDR = ($KDR / 10000);
			}
			// fix tiny KDR division error
			if($KDR == 0.0001)
			{
				$KDR = 0;
			}
			$Headshots = $suspicious_players_row['Headshots'];
			// make sure we don't try to divide by zero
			if($Kills == 0)
			{
				$Kills = 0.0001;
			}
			$HSpercent = round((($Headshots / $Kills)*100), 2);
			// fix huge HSR division error
			if($Kills == 0.0001)
			{
				$HSpercent = ($HSpercent / 10000);
			}
			// fix tiny HSR division error
			if($HSpercent == 0.0001)
			{
				$HSpercent = 0;
			}
			$Rounds = $suspicious_players_row['Rounds'];
			// return deaths back to its proper value
			if($deaths == 0.0001)
			{
				$deaths = 0;
			}
			// return kills back to its proper value
			if($kills == 0.0001)
			{
				$kills = 0;
			}
			$count++;
			echo '
			<tr>
			<td width="5%" class="tablecontents" style="text-align: left;"><font size="2">' . $count . ':</font></td>
			<td width="25%" class="tablecontents" style="text-align: left;"><a href="' . $_SERVER['PHP_SELF'] . '?player_name=' . $SoldierName . '&amp;search_player=Search"><font size="2">' . $SoldierName . '</font></a></td>
			<td width="20%" class="tablecontents" style="text-align: left;">' . $KDR . '</td>
			<td width="25%" class="tablecontents" style="text-align: left;">' . $HSpercent . ' <font class="information">%</font></td>
			<td width="25%" class="tablecontents" style="text-align: left;">' . $Rounds . '</td>
			</tr>
			';
		}
		echo '
		</table>
		<br/>
		</div>
		';
	}
	echo '
	</td></tr>
	</table>
	</div>
	';
}
// begin top countries logic
if($_GET['top25_countries'])
{
	echo '
	<div class="middlecontent">
	<table width="100%" border="0">
	<tr><td  class="headline">
	<br/><center><b>Country Stats</b></center><br/>
	</td></tr>
	</table>
	<table width="100%" border="0">
	<tr><td>
	<br/>
	<center>These are the countries players in this server reside in and the stats for the top 10 most common countries.</center>
	<br/>
	</td></tr>
	</table>
	</div>
	<br/><br/>
	<table width="100%" border="0">
	<tr>
	<td valign="top" align="center">
	<div class="middlecontent">
	<table width="100%" border="0">
	<tr>
	<th class="headline"><b>Country Stats</b></th>
	</tr>
	<tr>
	<td><br/>
	<div class="innercontent">
	<table width="100%" border="0">
	<tr>
	<td>
	';
	// query for countries
	$country_result = @mysql_query("SELECT tpd.CountryCode, COUNT(tpd.CountryCode) AS PlayerCount, tps.Score FROM tbl_playerstats tps INNER JOIN tbl_server_player tsp ON tsp.StatsID = tps.StatsID INNER JOIN tbl_playerdata tpd ON tsp.PlayerID = tpd.PlayerID WHERE tsp.ServerID = '$server_ID' GROUP BY CountryCode ORDER BY PlayerCount DESC, Score DESC, CountryCode ASC LIMIT 10");
	$country_result_google = @mysql_query("SELECT tpd.CountryCode, COUNT(tpd.CountryCode) AS PlayerCount FROM tbl_playerstats tps INNER JOIN tbl_server_player tsp ON tsp.StatsID = tps.StatsID INNER JOIN tbl_playerdata tpd ON tsp.PlayerID = tpd.PlayerID WHERE tsp.ServerID = '$server_ID' AND CountryCode != '--' GROUP BY CountryCode ORDER BY PlayerCount DESC LIMIT 250");
	if(@mysql_num_rows($country_result)==0)
	{
		echo '
		<div class="innercontent"><br/>
		<table width="95%" align="center" border="0">
		<tr><td>
		<center><font class="information">No country stats found for ' . $server_name . ' server.</font></center>
		</td></tr>
		</table><br/>
		</div>
		';
	}
	else
	{
		echo '<div class="innerinnercontent">';
		// initialize values
		$count = 0;
		$googlecount = 0;
		echo '
		<script type="text/javascript" src="http://www.google.com/jsapi"></script>
		<script type="text/javascript">
			google.load(\'visualization\', \'1\', {packages: [\'geochart\']});
			function drawVisualization()
			{
				var data = new google.visualization.DataTable();
				data.addRows(250);
				data.addColumn(\'string\', \'Country\');
				data.addColumn(\'number\', \'Playercount\');
				';
				while($country_row2 = @mysql_fetch_array($country_result_google))
				{
					$CountryCode2 = strtoupper($country_row2['CountryCode']);
					$PlayerCount2 = $country_row2['PlayerCount'];
					$country_name2 = array_search($CountryCode2,$country_array);
					echo '
					data.setValue(' . $googlecount . ', 0, \'' . $country_name2 . '\');
					data.setValue(' . $googlecount . ', 1, ' . $PlayerCount2 . ');
					';
					$googlecount++;	
				}
				echo '
				var geomap = new google.visualization.GeoChart(
				document.getElementById(\'visualization\'));
				geomap.draw(data, null);
			}
			google.setOnLoadCallback(drawVisualization);
		</script>
		<div id="map" style="padding:3px;">
		<center>
		<div class="countrymap">
		<br/>
		<center>
		<div id="visualization" style="width: 800px; height: 400px;"></div>
		</center>
		</div>
		</center>
		</div>
		<br/>
		';
		// list out the countries
		while($country_row = @mysql_fetch_assoc($country_result))
		{
			$CountryCode = strtoupper($country_row['CountryCode']);
			if(($CountryCode == '') OR ($CountryCode == '--'))
			{
				$country_img = './images/flags/none.png';
			}
			else
			{
				$country_img = './images/flags/' . strtolower($CountryCode) . '.png';	
			}
			$id = $CountryCode;
			// change id for certain cases
			if($id == '--')
			{
				$id = 'Unknown';
			}
			elseif($id == '')
			{
				$id = 'Null';
			}
			$PlayerCount = $country_row['PlayerCount'];
			$country_name = array_search($CountryCode,$country_array);
			$count++;
			echo '
			<div class="innercontent">
			<table width="98%" align="center" border="0">
			<tr>
			<th width="5%" style="text-align: left;">' . $count . '</th>
			<th width="20%" style="text-align: left;"><font class="information"><img src="' . $country_img . '" alt="' . $country_name . '"/> ' . $country_name . '</font></th>
			<th width="15%" style="text-align: left;"><font class="information">Country Code: </font>' . $CountryCode . '</th>
			<th width="60%" style="text-align: left;"><font class="information">Player Count: </font>' . $PlayerCount . '</th>
			</tr>
			</table>
			<div class="innercontent">
			';
			// initialize value
			$country_count = 0;
			// get current query details
			if(isset($_GET['rank']) AND !empty($_GET['rank']))
			{
				$rank = $_GET['rank'];
				if($rank == 'KDR')
				{
					$rankin = '(Kills/Deaths)';
				}
				else
				{
					$rankin = $rank;
				}
			}
			else
			{
				$rankin = 'Score';
				$rank = 'Score';
			}
			if(isset($_GET['order']) AND !empty($_GET['order']))
			{
				$order = $_GET['order'];
				if($order == 'DESC')
				{
					$nextorder = 'ASC';
				}
				else
				{
					$nextorder = 'DESC';
				}
			}
			else
			{
				$order = 'DESC';
				$nextorder = 'ASC';
			}
			echo '
			<table width="98%" align="center" border="0">
			<tr>
			<td width="3%" style="text-align:left">&nbsp;</td>
			<th width="2%" style="text-align:left">#</th>
			';
			if($rank != 'SoldierName')
			{
				echo'<th width="20%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?top25_countries=View&amp;rank=SoldierName&amp;order=ASC"><span class="orderheader">Player</span></a></th>';
			}
			else
			{
				echo'<th width="20%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?top25_countries=View&amp;rank=SoldierName&amp;order=' . $nextorder . '"><span class="orderheader">Player</span></a></th>';
			}
			if($rank != 'Score')
			{
				echo'<th width="15%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?top25_countries=View&amp;rank=Score&amp;order=DESC"><span class="orderheader">Score</span></a></th>';
			}
			else
			{
				echo'<th width="15%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?top25_countries=View&amp;rank=Score&amp;order=' . $nextorder . '"><span class="orderheader">Score</span></a></th>';
			}
			if($rank != 'Rounds')
			{
				echo'<th width="15%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?top25_countries=View&amp;rank=Rounds&amp;order=DESC"><span class="orderheader">Rounds Played</span></a></th>';
			}
			else
			{
				echo'<th width="15%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?top25_countries=View&amp;rank=Rounds&amp;order=' . $nextorder . '"><span class="orderheader">Rounds Played</span></a></th>';
			}
			if($rank != 'Kills')
			{
				echo'<th width="15%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?top25_countries=View&amp;rank=Kills&amp;order=DESC"><span class="orderheader">Kills</span></a></th>';
			}
			else
			{
				echo'<th width="15%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?top25_countries=View&amp;rank=Kills&amp;order=' . $nextorder . '"><span class="orderheader">Kills</span></a></th>';
			}
			if($rank != 'Deaths')
			{
				echo'<th width="15%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?top25_countries=View&amp;rank=Deaths&amp;order=DESC"><span class="orderheader">Deaths</span></a></th>';
			}
			else
			{
				echo'<th width="15%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?top25_countries=View&amp;rank=Deaths&amp;order=' . $nextorder . '"><span class="orderheader">Deaths</span></a></th>';
			}
			if($rank != 'KDR')
			{
				echo'<th width="15%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?top25_countries=View&amp;rank=KDR&amp;order=DESC"><span class="orderheader">Kill/Death Ratio</span></a></th>';
			}
			else
			{
				echo'<th width="15%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?top25_countries=View&amp;rank=KDR&amp;order=' . $nextorder . '"><span class="orderheader">Kill/Death Ratio</span></a></th>';
			}
			echo '</tr>';
			//query top 10 players in this country
			$playerrank_result = @mysql_query("SELECT tpd.SoldierName, tpd.CountryCode, tps.Score, tps.Kills, tps.Deaths, tps.Rounds FROM tbl_playerstats tps INNER JOIN tbl_server_player tsp ON tsp.StatsID = tps.StatsID INNER JOIN tbl_playerdata tpd ON tsp.PlayerID = tpd.PlayerID WHERE tsp.ServerID = '$server_ID' AND tpd.CountryCode = '$CountryCode' ORDER BY $rankin $order LIMIT 10");
			while($playerrank_row = @mysql_fetch_assoc($playerrank_result))
			{
				$country_count++;
				$SoldierName = $playerrank_row['SoldierName'];
				$Score = $playerrank_row['Score'];
				$Rounds = $playerrank_row['Rounds'];
				$Kills = $playerrank_row['Kills'];
				$Deaths = $playerrank_row['Deaths'];
				// avoid dividing by zero
				if($Deaths==0)
				{
					$Deaths = 0.0001;
				}
				$KDR = round(($Kills / $Deaths),2);
				// fix huge KDR division error
				if($Deaths == 0.0001)
				{
					$KDR = ($KDR / 10000);
				}
				// fix tiny KDR division error
				if($KDR == 0.0001)
				{
					$KDR = 0;
				}
				// return value to zero if necessary
				if($Deaths == 0.0001)
				{
					$Deaths = 0;
				}
				echo '
				<tr>
				<td width="3%" style="text-align: left;">&nbsp;</td>
				<td width="2%" class="tablecontents" style="text-align: left;"><font size="2">' . $country_count . ':</font></td>
				<td width="20%" class="tablecontents" style="text-align: left;"><a href="' . $_SERVER['PHP_SELF'] . '?player_name=' . $SoldierName . '&amp;search_player=Search"><font size="2">' . $SoldierName . '</font></a></td>
				<td width="15%" class="tablecontents" style="text-align: left;">' . $Score . '</td>
				<td width="15%" class="tablecontents" style="text-align: left;">' . $Rounds . '</td>
				<td width="15%" class="tablecontents" style="text-align: left;">' . $Kills . '</td>
				<td width="15%" class="tablecontents" style="text-align: left;">' . $Deaths . '</td>
				<td width="15%" class="tablecontents" style="text-align: left;">' . $KDR . '</td>
				</tr>
				';
			}
			echo '
			</table>
			</div>
			</div>
			';
		}
		echo '</div>';
	}
	echo '
	</td></tr>
	</table>
	</div>
	</td></tr>
	</table>
	<br/>
	</div>
	</td></tr>
	</table>
	';
}
// begin map stats logic

if($_GET['mapstats'])
{
	echo '
	<div class="middlecontent">
	<table width="100%" border="0">
	<tr><td  class="headline">
	<br/><center><b>Map Stats</b></center><br/>
	</td></tr>
	</table>
	<table width="100%" border="0">
	<tr><td>
	<br/>
	<center>Average Polularity is calculated as average players divided by average players leaving for each map.  Higher is better.</center>
	<br/>
	</td></tr>
	</table>
	</div>
	<br/><br/>
	<table width="100%" border="0">
	<tr>
	<td valign="top" align="center">
	<div class="middlecontent">
	<table width="100%" border="0">
	<tr>
	<th class="headline"><b>Map Stats</b></th>
	</tr>
	<tr>
	<td>
	';
	// get current query details
	if(isset($_GET['rank']) AND !empty($_GET['rank']))
	{
		$rank = $_GET['rank'];
		if($rank == 'AVGPop')
		{
			$rankin = '(AVG(AvgPlayers)/AVG(PlayersLeftServer))';
		}
		else
		{
			$rankin = $rank;
		}
	}
	else
	{
		$rankin = '(AVG(AvgPlayers)/AVG(PlayersLeftServer))';
		$rank = 'AVGPop';
	}
	if(isset($_GET['order']) AND !empty($_GET['order']))
	{
		$order = $_GET['order'];
		if($order == 'DESC')
		{
			$nextorder = 'ASC';
		}
		else
		{
			$nextorder = 'DESC';
		}
	}
	else
	{
		$order = 'DESC';
		$nextorder = 'ASC';
	}
	// query for maps
	$map_result = @mysql_query("SELECT ServerID, MapName, SUM(NumberofRounds) AS NumberofRounds, AVG(AvgPlayers) AS AveragePlayers, AvgPlayers, AVG(PlayersLeftServer) AS AveragePlayersLeftServer, PlayersLeftServer FROM tbl_mapstats WHERE ServerID = '$server_ID' GROUP BY MapName ORDER BY $rankin $order");
	if(@mysql_num_rows($map_result)==0)
	{
		echo '
		<div class="innercontent"><br/>
		<table width="98%" align="center" border="0">
		<tr><td>
		<center><font class="information">No map stats found for ' . $server_name . '.</font></center>
		</td></tr>
		</table><br/>
		</div>
		';
	}
	else
	{
		echo'
		<div class="innercontent">
		<table width="98%" align="center" border="0">
		<tr>
		<th width="5%" style="text-align:left">#</th>
		<th width="25%" style="text-align:left">Map Name</th>
		';
		if($rank != 'MapName')
		{
			echo'<th width="24%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?mapstats=View&amp;rank=MapName&amp;order=ASC"><span class="orderheader">Map Code</span></a></th>';
		}
		else
		{
			echo'<th width="24%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?mapstats=View&amp;rank=MapName&amp;order=' . $nextorder . '"><span class="orderheader">Map Code</span></a></th>';
		}
		if($rank != 'NumberofRounds')
		{
			echo'<th width="23%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?mapstats=View&amp;rank=NumberofRounds&amp;order=DESC"><span class="orderheader">Rounds Played</span></a></th>';
		}
		else
		{
			echo'<th width="23%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?mapstats=View&amp;rank=NumberofRounds&amp;order=' . $nextorder . '"><span class="orderheader">Rounds Played</span></a></th>';
		}
		if($rank != 'AVGPop')
		{
			echo'<th width="23%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?mapstats=View&amp;rank=AVGPop&amp;order=DESC"><span class="orderheader">Average Popularity</span></a></th>';
		}
		else
		{
			echo'<th width="23%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?mapstats=View&amp;rank=AVGPop&amp;order=' . $nextorder . '"><span class="orderheader">Average Popularity</span></a></th>';
		}
		echo '</tr>';
		// initialize value
		$count = 0;
		while($map_row = @mysql_fetch_assoc($map_result))
		{
			$MapName = $map_row['MapName'];
			$NumberofRounds = $map_row['NumberofRounds'];
			$map_stats = array_search($MapName,$map_array);
			$AveragePlayers = $map_row['AveragePlayers'];
			$AveragePlayersLeftServer = $map_row['AveragePlayersLeftServer'];
			// avoid dividing by zero
			if(($AveragePlayers >= 1) AND ($AveragePlayersLeftServer >= 1))
			{
				$AveragePopularity = round(($AveragePlayers/$AveragePlayersLeftServer),2);
			}
			else
			{
				$AveragePopularity = 'Not enough data';
			}
			// only display the map in the list if it has been played
			if($MapName!=null)
			{
				$count++;
				echo '
				<tr>
				<td width="5%" class="tablecontents" style="text-align: left;"><font size="2">' . $count . ':</font></td>
				<td width="25%" class="tablecontents" style="text-align: left;">' . $map_stats . '</td>
				<td width="24%" class="tablecontents" style="text-align: left;">' . $MapName . '</td>
				<td width="23%" class="tablecontents" style="text-align: left;">' . $NumberofRounds . '</td>
				<td width="23%" class="tablecontents" style="text-align: left;">' . $AveragePopularity . '</td>
				</tr>
				';
			}
		}
		echo '
		</table>
		<br/>
		</div>
		';
	}
	echo '
	</td></tr>
	</table>
	</div>
	</td></tr>
	</table>
	';
}
// begin server stats logic
if($_GET['serverstats'])
{
	echo '
	<div class="middlecontent">
	<table width="100%" border="0">
	<tr><td  class="headline">
	<br/><center><b>Server Info</b></center><br/>
	</td></tr>
	</table>
	</div>
	<br/><br/>
	<div class="middlecontent">
	<table width="100%" border="0">
	<tr>
	<th class="headline"><b>Overall Server Stats</b></th>
	</tr>
	<tr>
	<td>
	<div class="innercontent"><br/>
	<table width="95%" align="center" border="0">
	<tr>
	<td>
	';
	// query server stats
	$server_stats = @mysql_query("SELECT `CountPlayers`, `SumKills`, `SumDeaths`, `AvgScore`, `AvgKills`, `AvgHeadshots`, `AvgDeaths`, `AvgSuicide`, `AvgTKs` FROM tbl_server_stats WHERE ServerID = '$server_ID'");
	if(@mysql_num_rows($server_stats) != 0)
	{
		$server_row = @mysql_fetch_assoc($server_stats);
		$players = round($server_row['CountPlayers'],2);
		$kills = round($server_row['SumKills'],2);
		$deaths = round($server_row['SumDeaths'],2);
		$avgscore = round($server_row['AvgScore'],2);
		$avgkills = round($server_row['AvgKills'],2);
		$avgheadshots = round($server_row['AvgHeadshots'],2);
		$avgdeaths = round($server_row['AvgDeaths'],2);
		$avgsuicide = round($server_row['AvgSuicide'],2);
		$avgtks = round($server_row['AvgTKs'],2);
		echo '
		<table width="90%" align="center" border="0"><tr>
		<td style="text-align: left;" width="10%">&nbsp;<br/><br/></td>
		<td style="text-align: left;" width="30%"><font class="information">Total Players: </font>' . $players . '<br/><br/></td>
		<td style="text-align: left;" width="30%"><font class="information">Total Kills: </font>' . $kills . '<br/><br/></td>
		<td style="text-align: left;" width="30%"><font class="information">Total Deaths: </font>' . $deaths . '<br/><br/></td>
		</tr><tr>
		<td style="text-align: left;" width="10%">&nbsp;<br/><br/></td>
		<td style="text-align: left;" width="30%"><font class="information">Average Score: </font>' . $avgscore . '<br/><br/></td>
		<td style="text-align: left;" width="30%"><font class="information">Average Kills: </font>' . $avgkills . '<br/><br/></td>
		<td style="text-align: left;" width="30%"><font class="information">Average Deaths: </font>' . $avgdeaths . '<br/><br/></td>
		</tr><tr>
		<td style="text-align: left;" width="10%">&nbsp;<br/><br/></td>
		<td style="text-align: left;" width="30%"><font class="information">Average Team Kills: </font>' . $avgtks . '<br/><br/></td>
		<td style="text-align: left;" width="30%"><font class="information">Average Headshots: </font>' . $avgheadshots . '<br/><br/></td>
		<td style="text-align: left;" width="30%"><font class="information">Average Suicides: </font>' . $avgsuicide . '<br/><br/></td>
		</tr></table>
		';
	}
	else
	{
		echo '<center><font class="information">No server stats found for ' . $server_name . ' server.</font></center><br/>';
	}
	echo '
	</td>
	</tr>
	</table>
	</div>
	</td>
	</tr>
	</table>
	</div>
	<br/><br/>
	';
	// show scoreboard
	scoreboard($server_ID, $server_name, $mode_array, $map_array, $squad_array, $country_array);
}
// begin chat logic
if($_GET['chat'])
{
	recent_chat($server_ID, $server_name, $clan_name);
}
// begin top 25 / welcome logic
// if user selects to see top 25 or doesn't select anything (welcome page), display this
if(($_GET['top25_players']) OR !(($_GET['search_player']) OR ($_GET['suspicious_players']) OR ($_GET['banned_players']) OR ($_GET['top25_countries']) OR ($_GET['mapstats']) OR ($_GET['serverstats']) OR ($_GET['chat'])))
{
	// change heading text based on if this is welcome page or top players page
	if(!($_GET['top25_players']))
	{
		echo '
		<div class="middlecontent">
		<table width="100%" border="0">
		<tr><td  class="headline">
		<br/><center><b>Home Page</b></center><br/>
		</td></tr>
		</table>
		';
	}
	else
	{
		echo '
		<div class="middlecontent">
		<table width="100%" border="0">
		<tr><td  class="headline">
		<br/><center><b>Top Players</b></center><br/>
		</td></tr>
		</table>
		';
	}
	echo '
	<table width="100%" border="0">
	<tr><td>
	<br/>
	<center>Statistics data presented is not from all BF4 servers.  These are the statistics of each player only in this server.</center>
	<br/>
	</td></tr>
	</table>
	</div>
	<br/>
	<br/>
	';
	// show scoreboard on welcome page
	if(!($_GET['top25_players']))
	{
		// show scoreboard
		scoreboard($server_ID, $server_name, $mode_array, $map_array, $squad_array, $country_array);
		echo '<br/><br/>';
	}
	echo '
	<table width="100%" border="0">
	<tr>
	<td valign="top" align="center">
	<div class="middlecontent">
	<table width="100%" border="0">
	<tr>
	<th class="headline"><b>Top Players</b></th>
	</tr>
	<tr>
	<td>
	<div class="innercontent">
	';
	// pagination code thanks to: http://www.phpfreaks.com/tutorial/basic-pagination
	// find out how many rows are in the table 
	$sql = @mysql_query("SELECT COUNT(tpd.SoldierName) FROM tbl_playerstats tps INNER JOIN tbl_server_player tsp ON tsp.StatsID = tps.StatsID INNER JOIN tbl_playerdata tpd ON tsp.PlayerID = tpd.PlayerID WHERE tsp.ServerID = '$server_ID' ORDER BY Score DESC, SoldierName ASC");
	$r = @mysql_fetch_row($sql);
	$numrows = $r[0];
	// number of rows to show per page
	$rowsperpage = 25;
	// find out total pages
	$totalpages = ceil($numrows / $rowsperpage);
	// get the current page or set a default
	if (isset($_GET['currentpage']) && is_numeric($_GET['currentpage']))
	{
		// cast var as int
		$currentpage = (int) $_GET['currentpage'];
	}
	else
	{
		// default page num
		$currentpage = 1;
	}
	// if current page is greater than total pages...
	if ($currentpage > $totalpages)
	{
		// set current page to last page
		$currentpage = $totalpages;
	}
	// if current page is less than first page...
	if ($currentpage < 1)
	{
		// set current page to first page
		$currentpage = 1;
	}
	// get current query details
	if(isset($_GET['rank']) AND !empty($_GET['rank']))
	{
		$rank = $_GET['rank'];
		if($rank == 'KDR')
		{
			$rankin = '(Kills/Deaths)';
		}
		elseif($rank == 'HSR')
		{
			$rankin = '(Headshots/Kills)';
		}
		else
		{
			$rankin = $rank;
		}
	}
	else
	{
		$rankin = 'Score';
		$rank = 'Score';
	}
	if(isset($_GET['order']) AND !empty($_GET['order']))
	{
		$order = $_GET['order'];
		if($order == 'DESC')
		{
			$nextorder = 'ASC';
		}
		else
		{
			$nextorder = 'DESC';
		}
	}
	else
	{
		$order = 'DESC';
		$nextorder = 'ASC';
	}
	// the offset of the list, based on current page 
	$offset = ($currentpage - 1) * $rowsperpage;
	// get the info from the db 
	$sql  = @mysql_query("SELECT tpd.SoldierName, tps.Score, tps.Kills, tps.Deaths, tps.Rounds, tps.Headshots FROM tbl_playerstats tps INNER JOIN tbl_server_player tsp ON tsp.StatsID = tps.StatsID INNER JOIN tbl_playerdata tpd ON tsp.PlayerID = tpd.PlayerID WHERE tsp.ServerID = '$server_ID' ORDER BY $rankin $order, SoldierName $nextorder LIMIT $offset, $rowsperpage");
	// offset of player rank count to show on scoreboard
	$count = ($currentpage * 25) - 25;
	// check if there are rows returned
	if(@mysql_num_rows($sql)!=0)
	{
		echo '
		<table width="98%" align="center" class="prettytable" border="0">
		<tr>
		<th width="5%" style="text-align:left">#</th>
		';
		if($rank != 'SoldierName')
		{
			echo'<th width="18%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?top25_players=View&amp;currentpage=' . $currentpage . '&amp;rank=SoldierName&amp;order=ASC"><span class="orderheader">Player</span></a></th>';
		}
		else
		{
			echo'<th width="18%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?top25_players=View&amp;currentpage=' . $currentpage . '&amp;rank=SoldierName&amp;order=' . $nextorder . '"><span class="orderheader">Player</span></a></th>';
		}
		if($rank != 'Score')
		{
			echo'<th width="11%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?top25_players=View&amp;currentpage=' . $currentpage . '&amp;rank=Score&amp;order=DESC"><span class="orderheader">Score</span></a></th>';
		}
		else
		{
			echo'<th width="11%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?top25_players=View&amp;currentpage=' . $currentpage . '&amp;rank=Score&amp;order=' . $nextorder . '"><span class="orderheader">Score</span></a></th>';
		}
		if($rank != 'Rounds')
		{
			echo'<th width="11%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?top25_players=View&amp;currentpage=' . $currentpage . '&amp;rank=Rounds&amp;order=DESC"><span class="orderheader">Rounds Played</span></a></th>';
		}
		else
		{
			echo'<th width="11%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?top25_players=View&amp;currentpage=' . $currentpage . '&amp;rank=Rounds&amp;order=' . $nextorder . '"><span class="orderheader">Rounds Played</span></a></th>';
		}
		if($rank != 'Kills')
		{
			echo'<th width="11%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?top25_players=View&amp;currentpage=' . $currentpage . '&amp;rank=Kills&amp;order=DESC"><span class="orderheader">Kills</span></a></th>';
		}
		else
		{
			echo'<th width="11%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?top25_players=View&amp;currentpage=' . $currentpage . '&amp;rank=Kills&amp;order=' . $nextorder . '"><span class="orderheader">Kills</span></a></th>';
		}
		if($rank != 'Deaths')
		{
			echo'<th width="11%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?top25_players=View&amp;currentpage=' . $currentpage . '&amp;rank=Deaths&amp;order=DESC"><span class="orderheader">Deaths</span></a></th>';
		}
		else
		{
			echo'<th width="11%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?top25_players=View&amp;currentpage=' . $currentpage . '&amp;rank=Deaths&amp;order=' . $nextorder . '"><span class="orderheader">Deaths</span></a></th>';
		}
		if($rank != 'KDR')
		{
			echo'<th width="11%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?top25_players=View&amp;currentpage=' . $currentpage . '&amp;rank=KDR&amp;order=DESC"><span class="orderheader">Kill/Death Ratio</span></a></th>';
		}
		else
		{
			echo'<th width="11%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?top25_players=View&amp;currentpage=' . $currentpage . '&amp;rank=KDR&amp;order=' . $nextorder . '"><span class="orderheader">Kill/Death Ratio</span></a></th>';
		}
		if($rank != 'Headshots')
		{
			echo'<th width="11%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?top25_players=View&amp;currentpage=' . $currentpage . '&amp;rank=Headshots&amp;order=DESC"><span class="orderheader">Headshots</span></a></th>';
		}
		else
		{
			echo'<th width="11%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?top25_players=View&amp;currentpage=' . $currentpage . '&amp;rank=Headshots&amp;order=' . $nextorder . '"><span class="orderheader">Headshots</span></a></th>';
		}
		if($rank != 'HSR')
		{
			echo'<th width="11%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?top25_players=View&amp;currentpage=' . $currentpage . '&amp;rank=HSR&amp;order=DESC"><span class="orderheader">Headshot Ratio</span></a></th>';
		}
		else
		{
			echo'<th width="11%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?top25_players=View&amp;currentpage=' . $currentpage . '&amp;rank=HSR&amp;order=' . $nextorder . '"><span class="orderheader">Headshot Ratio</span></a></th>';
		}
		echo '
		</tr>
		';
		// while there are rows to be fetched...
		while($playerstats_row = @mysql_fetch_assoc($sql))
		{
			$Score = $playerstats_row['Score'];
			$SoldierName = $playerstats_row['SoldierName'];
			$Kills = $playerstats_row['Kills'];
			$Deaths = $playerstats_row['Deaths'];
			$Headshots = $playerstats_row['Headshots'];
			// avoid dividing by zero
			if($Deaths == 0)
			{
				$Deaths = 0.0001;
			}
			$KDR = round(($Kills / $Deaths), 2);
			// avoid dividing by zero
			if($Kills == 0)
			{
				$Kills = 0.0001;
			}
			$HSR = round((($Headshots / $Kills)*100),2);
			// fix huge KDR division error
			if($Deaths == 0.0001)
			{
				$KDR = ($KDR / 10000);
			}
			// fix huge HSR division error
			if($Kills == 0.0001)
			{
				$HSR = ($HSR / 10000);
			}
			// fix tiny KDR division error
			if($KDR == 0.0001)
			{
				$KDR = 0;
			}
			// fix tiny KDR division error
			if($HSR == 0.0001)
			{
				$HSR = 0;
			}
			// return value to zero if necessary
			if($Deaths == 0.0001)
			{
				$Deaths = 0;
			}
			// return value to zero if necessary
			if($Kills == 0.0001)
			{
				$Kills = 0;
			}
			$Rounds = $playerstats_row['Rounds'];
			$rank_offset = (($_GET['currentpage']) * 25);
			$count++;

			echo '
			<tr>
			<td class="tablecontents" width="5%"><font size="2">' . $count . ':</font></td>
			<td width="18%" class="tablecontents"><a href="' . $_SERVER['PHP_SELF'] . '?player_name=' . $SoldierName . '&amp;search_player=Search"><font size="2">' . $SoldierName . '</font></a></td>
			<td width="11%" class="tablecontents">' . $Score . '</td>
			<td width="11%" class="tablecontents">' . $Rounds . '</td>
			<td width="11%" class="tablecontents">' . $Kills . '</td>
			<td width="11%" class="tablecontents">' . $Deaths . '</td>
			<td width="11%" class="tablecontents">' . $KDR . '</td>
			<td width="11%" class="tablecontents">' . $Headshots . '</td>
			<td width="11%" class="tablecontents">' . $HSR . '<font class="information"> %</font></td>
			</tr>	
			';
		}
		echo '</table>';
	}
	else
	{
		echo '
		<table width="95%" align="center" border="0">
		<tr>
		<td style="text-align: left;" width="100%"><center><font class="information">No player stats found for ' . $server_name . ' server.</font></center></td>
		</tr>
		</table>
		';
	}
	// build the pagination links
	// don't display pagination links if no players found
	if(@mysql_num_rows($sql)!=0)
	{
		echo '
		<div class="pagination">
		<center>
		';
		// range of num links to show
		$range = 3;
		// if on page 1, don't show back links
		if ($currentpage > 1)
		{
			// show << link to go back to first page
			echo '<a href="' . $_SERVER['PHP_SELF'] . '?top25_players=View&amp;currentpage=1&amp;rank=' . $rank . '&amp;order=' . $order . '">&lt;&lt;</a>';
			// get previous page num
			$prevpage = $currentpage - 1;
			// show < link to go back one page
			echo ' <a href="' . $_SERVER['PHP_SELF'] . '?top25_players=View&amp;currentpage=' . $prevpage . '&amp;rank=' . $rank . '&amp;order=' . $order . '">&lt;</a> ';
		}
		// loop to show links to range of pages around current page
		for($x = ($currentpage - $range); $x < (($currentpage + $range) + 1); $x++)
		{
			// if it's a valid page number...
			if (($x > 0) && ($x <= $totalpages))
			{
				// if we're on current page...
				if ($x == $currentpage)
				{
					// 'highlight' it but don't make a link
					echo ' [<font class="information">' . $x . '</font>] ';
				}
				else
				{
					// make it a link
					echo ' <a href="' . $_SERVER['PHP_SELF'] . '?top25_players=View&amp;currentpage=' . $x . '&amp;rank=' . $rank . '&amp;order=' . $order . '">' . $x . '</a> ';
				}
			}
		}
		// if not on last page, show forward links        
		if ($currentpage != $totalpages)
		{
			// get next page
			$nextpage = $currentpage + 1;
			// show > link to go forward one page
			echo ' <a href="' . $_SERVER['PHP_SELF'] . '?top25_players=View&amp;currentpage=' . $nextpage . '&amp;rank=' . $rank . '&amp;order=' . $order . '">&gt;</a> ';
			// show >> link to last page
			echo '<a href="' . $_SERVER['PHP_SELF'] . '?top25_players=View&amp;currentpage=' . $totalpages . '&amp;rank=' . $rank . '&amp;order=' . $order . '">&gt;&gt;</a>';
		}
		echo '
		</center>
		</div>
		';
	}
	// end build pagination links and end block
	echo '
	</div>
	</td>
	</tr>
	</table>
	</div>
	</td></tr>
	</table>
	';
}
echo '
<br/>
<br/>
<div class="middlecontent">
<table width="100%" border="0">
<tr><td>
<br/>
<center>[ <font class="information">Stats provided by <a href="https://forum.myrcon.com/showthread.php?6698-_BF4-PRoCon-Chat-GUID-Stats-and-Mapstats-Logger-1-0-0-1" target="_blank">XpKiller\'s PRoCon logging plugin</a></font> ]  &nbsp; [ <font class="information">Stats page provided by <a href="http://open-web-community.com/" target="_blank">Ty_ger07 at Open-Web-Community</a></font> ]</center>
<br/>
</td></tr>
</table>
</div>
';
// check to see if ses table exists
@mysql_query("CREATE TABLE IF NOT EXISTS `ses_{$server_ID}_tbl` (`IP` VARCHAR(45) NULL DEFAULT NULL, `timestamp` int(11) NOT NULL default '00000000000', PRIMARY KEY (`IP`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin");
// get user's IP address
$userip = $_SERVER["REMOTE_ADDR"];
// initialize values
$now_timestamp = time();
$old = $now_timestamp - 1800;
// check if user already has ses stored
$exist_query = @mysql_query("SELECT `IP` FROM `ses_{$server_ID}_tbl` WHERE `IP` = '$userip'");
if(@mysql_num_rows($exist_query)!=0)
{
	// user IP found, update timestamp
	@mysql_query("UPDATE `ses_{$server_ID}_tbl` SET `timestamp` = '$now_timestamp' WHERE `IP` = '$userip'");
}
else
{
	// user IP not found, add it to ses table
	@mysql_query("INSERT INTO `ses_{$server_ID}_tbl` (`IP`, `timestamp`) VALUES ('$userip', '$now_timestamp')");
}
// remove ses older than 30 minutes
@mysql_query("DELETE FROM `ses_{$server_ID}_tbl` WHERE `timestamp` <= '$old'");
@mysql_query("OPTIMIZE TABLE `ses_{$server_ID}_tbl`");
// count all ses
$ses_count = @mysql_query("SELECT count(`IP`) as ses FROM `ses_{$server_ID}_tbl` WHERE 1");
if(@mysql_num_rows($ses_count)!=0)
{
	$ses_row = @mysql_fetch_assoc($ses_count);
	$ses = $ses_row['ses'];
	echo '<br/><center><font class="footertext">' . $ses . ' users viewing these BF4 stats pages</font></center>';
}
// figure out total page load time
$mtime = microtime();
$mtime = explode(" ",$mtime);
$mtime = $mtime[1] + $mtime[0];
$endtime = $mtime;
$totaltime = round(($endtime - $starttime),3);
echo '<center><font class="footertext">data computed in ' . $totaltime . ' seconds</font></center>';
echo '
</td></tr>
</table>
</td>
<td width="1%"></td>
</tr>
</table>
</center>
</td>
</tr> 
</table>
</div>
</td>
</tr>
</table>
</div>
<br/>
</body>
</html>
';
// flush buffers in case it is necessary
flush();
ob_flush();
?>