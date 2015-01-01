<?php
// BF4 Stats Page by Ty_ger07
// http://open-web-community.com/

// ajax load content
echo'
<div id="scoreboard">
<br/><br/>
<center><img src="./common/images/loading.gif" alt="loading" style="width: 24px; height: 24px;" /></center>
<br/><br/>
<script type="text/javascript">
$(\'#scoreboard\').load("./common/home/scoreboard-live.php?gid=' . $GameID;
if(!empty($ServerID))
{
	echo '&sid=' . $ServerID;
}
if(!empty($scoreboard_rank))
{
	echo '&rank=' . $scoreboard_rank;
}
if(!empty($scoreboard_order))
{
	echo '&order=' . $scoreboard_order;
}
echo '");
</script>
</div>
';
?>