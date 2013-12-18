<?php
include_once("class/pData.class.php");
include_once("class/pDraw.class.php");
include_once("class/pImage.class.php");

// include common.php contents
include_once('../common/common.php');
$BF4stats = mysqli_connect($db_host, $db_uname, $db_pass, $db_name, $db_port);

$limit = 50;

// check if a server was provided
// if so, this is a server stats page
if(isset($_GET['server']) AND !empty($_GET['server']))
{
	$id = mysqli_real_escape_string($BF4stats, $_GET['server']);
	$query  = "SELECT `PlayersJoinedServer`, `PlayersLeftServer` FROM `tbl_mapstats` WHERE ServerID = ". $id ." LIMIT {$limit}";
	$result = mysqli_query($BF4stats, $query);
}
// this must be a global stats page
else
{
	$query  = "SELECT `PlayersJoinedServer`, `PlayersLeftServer` FROM `tbl_mapstats` WHERE 1 LIMIT {$limit}";
	$result = mysqli_query($BF4stats, $query);
}

if($result)
{
	$i = 1;
	while($row = mysqli_fetch_assoc($result))
	{
		$rounds[$i] = $i;
		$joins[]  = $row['PlayersJoinedServer'];
		$leaves[] = $row['PlayersLeftServer'];
		$i++;
	}
}

$myData = new pData();
$myData->addPoints($joins,"Serie1");
$myData->setSerieDescription("Serie1","Joins");
$myData->setSerieOnAxis("Serie1",0);

$myData->addPoints($leaves,"Serie2");
$myData->setSerieDescription("Serie2","Leaves");
$myData->setSerieOnAxis("Serie2",0);

$myData->addPoints($rounds,"Absissa");
$myData->setAbscissa("Absissa");

$myData->setAxisPosition(0,AXIS_POSITION_LEFT);
$myData->setAxisName(0,"Players");
$myData->setAxisUnit(0,"");

$myPicture = new pImage(600,300,$myData,TRUE);

$myPicture->setFontProperties(array("FontName"=>"fonts/Forgotte.ttf","FontSize"=>12));
$TextSettings = array("Align"=>TEXT_ALIGN_MIDDLEMIDDLE
, "R"=>150, "G"=>150, "B"=>150);
// if so, this is a server stats page
if(isset($_GET['server']) AND !empty($_GET['server']))
{
	$myPicture->drawText(297,18,"Joins and leaves of this server in last ". $limit ." rounds.",$TextSettings);
}
// this must be a global stats page
else
{
	$myPicture->drawText(297,18,"Joins and leaves of these servers in last ". $limit ." rounds.",$TextSettings);
}

$myPicture->setShadow(FALSE);
$myPicture->setGraphArea(50,50,576,270);
$myPicture->setFontProperties(array("R"=>150,"G"=>150,"B"=>150,"FontName"=>"fonts/pf_arma_five.ttf","FontSize"=>6));

$Settings = array("Pos"=>SCALE_POS_LEFTRIGHT
, "Mode"=>SCALE_MODE_FLOATING
, "LabelingMethod"=>LABELING_ALL
, "GridR"=>150, "GridG"=>150, "GridB"=>150, "GridAlpha"=>50, "TickR"=>150, "TickG"=>150, "TickB"=>150, "TickAlpha"=>50, "LabelRotation"=>0, "CycleBackground"=>1, "DrawXLines"=>0, "DrawSubTicks"=>1, "SubTickR"=>150, "SubTickG"=>150, "SubTickB"=>150, "SubTickAlpha"=>50, "DrawYLines"=>NONE, "AxisR"=>150, "AxisG"=>150,"AxisB"=>150);
$myPicture->drawScale($Settings);

$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>50,"G"=>50,"B"=>50,"Alpha"=>10));

$Config = "";
$myPicture->drawSplineChart($Config);

$Config = array("FontR"=>150, "FontG"=>150, "FontB"=>150, "FontName"=>"fonts/pf_arma_five.ttf", "FontSize"=>6, "Margin"=>6, "Alpha"=>30, "BoxSize"=>5, "Style"=>LEGEND_NOBORDER
, "Mode"=>LEGEND_HORIZONTAL
);
$myPicture->drawLegend(529,12,$Config);

$myPicture->stroke();
?>