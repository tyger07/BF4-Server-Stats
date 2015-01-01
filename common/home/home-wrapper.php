<?php
// BF4 Stats Page by Ty_ger07
// http://open-web-community.com/

// show loading...
echo '
<div id="loading">
<br/><br/>
<center><img src="./common/images/loading.gif" alt="loading" style="width: 24px; height: 24px;" /></center>
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
echo '");
</script>
</div>
';
?>