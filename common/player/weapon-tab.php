<?php
// BF4 Stats Page by Ty_ger07
// http://open-web-community.com/

// include required files
require_once('../../config/config.php');
require_once('../connect.php');
require_once('../functions.php');
require_once('../constants.php');
require_once('../case.php');
// default variable to null
$ServerID = null;
$Code = null;
// get values
if(!empty($sid))
{
	$ServerID = $sid;
}
if(!empty($pid))
{
	$PlayerID = $pid;
}
// get query search string
if(!empty($_GET['c']))
{
	$Code = mysqli_real_escape_string($BF4stats, $_GET['c']);
}
echo '
<script type="text/javascript">
$(document).ready(function()
{
	$(".expanded4").hide();
	$(".collapsed4, .expanded4").click(function()
	{
		$(this).parent().children(".expanded4, .collapsed4").toggle();
	});
});
</script>
';				
Statsout($Code, $weapon_array, $PlayerID, $ServerID, $valid_ids, $GameID, $BF4stats, '4');
?>