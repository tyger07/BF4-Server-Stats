<?php
// database queries page by Ty_ger07 at http://open-web-community.com/

// DON'T EDIT ANYTHING BELOW UNLESS YOU KNOW WHAT YOU ARE DOING

// GameID query
$q1 = @mysqli_query($BF4stats,"
	SELECT `GameID`
	FROM `tbl_games`
	WHERE `Name` = 'BF4'
");
if(@mysqli_num_rows($q1) == 1)
{
	$r1 = @mysqli_fetch_assoc($q1);
}
else
{
	$r1 = null;
}
?>