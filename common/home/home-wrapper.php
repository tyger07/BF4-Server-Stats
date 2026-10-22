<?php
// BF4 Stats Page by Ty_ger07
// https://myrcon.net/topic/162-chat-guid-stats-and-mapstats-logger-1003/

// jquery auto refresh scoreboard every 30 seconds
// don't show to bots
if(!empty($ServerID) && !($isbot))
{
	echo '
	<script type="text/javascript">
	$(function() {
		function callAjax(){
			$(\'#scoreboard\').load("./common/home/scoreboard-live.php?p=home&sid=' . $ServerID . '&gid=' . $GameID;
			if(!empty($scoreboard_rank))
			{
				echo '&rank=' . $scoreboard_rank;
			}
			if(!empty($scoreboard_order))
			{
				echo '&order=' . $scoreboard_order;
			}
			echo '");
		}
		setInterval(callAjax, 30000 );
	});
	</script>
	';
}
// show loading...
echo '
<div id="loading">
<br/><br/>
<center><img class="load" src="./common/images/loading.gif" alt="loading" /></center>
<br/><br/>
</div>
';
// then ajax load content
echo '
<div id="loaded" style="display: none;">
<script type="text/javascript">
$(\'#loaded\').load("./common/home/home.php?p=home&gid=' . $GameID;
if(!empty($ServerID))
{
	echo '&sid=' . $ServerID;
}
if(!empty($player))
{
	echo '&player=' . $player;
}
if(!empty($currentpage))
{
	echo '&cp=' . $currentpage;
}
if(!empty($rank))
{
	echo '&r=' . $rank;
}
if(!empty($order))
{
	echo '&o=' . $order;
}
if(!empty($scoreboard_rank))
{
	echo '&rank=' . $scoreboard_rank;
}
if(!empty($scoreboard_order))
{
	echo '&order=' . $scoreboard_order;
}
if(!empty($cr))
{
	echo '&cr=' . $cr;
}
echo '");
</script>
</div>
';
?>