<?php

include_once("class/pData.class.php");
include_once("class/pDraw.class.php");
include_once("class/pPie.class.php");
include_once("class/pImage.class.php");

// include common.php contents
include_once('../config/config.php');
include_once('../common/constants.php');
$BF4stats = mysqli_connect(HOST, USER, PASS, NAME, PORT);
 
// check if a server was provided
// if so, this is a server stats page
if(!empty($_GET['server']))
{
	$id = mysqli_real_escape_string($BF4stats, $_GET['server']);
	$query = "
		SELECT `MapName`, SUM(`NumberofRounds`) AS number
		FROM `tbl_mapstats`
		WHERE `ServerID` = {$id}
		AND `Gamemode` != ''
		GROUP BY `MapName`
		ORDER BY number DESC
	"; 
	$result = mysqli_query($BF4stats, $query);
}
// this must be a global stats page
else
{
	$query = "
		SELECT `MapName`, SUM(`NumberofRounds`) AS number
		FROM `tbl_mapstats`
		WHERE `Gamemode` != ''
		GROUP BY `MapName`
		ORDER BY number DESC
	"; 
	$result = mysqli_query($BF4stats, $query);
} 

if($result)
{
	while($row = mysqli_fetch_assoc($result))
	{
		$key = $row['MapName'];
		// convert map to friendly name
		// first find if this map name is even in the map array
		if(in_array($key,$map_array))
		{
			$MapName = array_search($key,$map_array);
		}
		// this map is missing!
		else
		{
			$MapName = $key;
		}
		$maps[] = $MapName;
		$number[] = $row['number'];
	}
			
	/* Create and populate the pData object */
	$MyData = new pData();   
	$MyData->addPoints($number,"ScoreA");  
	$MyData->setSerieDescription("ScoreA","Application A");
	
	/* Define the absissa serie */
	$MyData->addPoints($maps,"Labels");
	$MyData->setAbscissa("Labels");
	
	/* Create the pChart object */
	$myPicture = new pImage(300,248,$MyData,TRUE);
	
	/* Write the picture title */ 
	$myPicture->setFontProperties(array("FontName"=>"fonts/Forgotte.ttf","FontSize"=>12));
	$myPicture->drawText(10,13,"Maps played in percent from greatest to least.",array("R"=>255,"G"=>255,"B"=>255));
	
	/* Set the default font properties */ 
	$myPicture->setFontProperties(array("FontName"=>"fonts/Forgotte.ttf","FontSize"=>10,"R"=>255,"G"=>255,"B"=>255));
		
	/* Create the pPie object */ 
	$PieChart = new pPie($myPicture,$MyData);
	
	/* Draw an AA pie chart */ 
	$PieChart->draw2DRing(210,140,array("WriteValues"=>TRUE,"ValueR"=>255,"ValueG"=>255,"ValueB"=>255,"Border"=>TRUE));
	
	/* Write the legend box */ 
	$myPicture->setShadow(FALSE);
	$PieChart->drawPieLegend(15,40,array("Alpha"=>0));
	
	/* Render the picture (choose the best way) */
	$myPicture->autoOutput("pictures/example.draw2DRingValue.png"); 
}
?>