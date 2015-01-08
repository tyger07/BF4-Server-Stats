<?php
// BF4 Stats Page by Ty_ger07
// http://open-web-community.com/

// include required files
require_once('../../config/config.php');
require_once('../functions.php');
require_once('../connect.php');
require_once('../case.php');
// default variable to null
$ServerID = null;
// get value
if(!empty($sid))
{
	$ServerID = $sid;
}
// jquery auto refresh chat every 60 seconds
echo '
<script type="text/javascript">
$(function() {
	function callAjax(){
		$(\'#chat\').load("./common/chat/chat-live.php?p=chat&gid=' . $GameID;
		if(!empty($ServerID))
		{
			echo '&sid=' . $ServerID;
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
		if(!empty($query))
		{
			echo '&q=' . urlencode($query);
		}
		echo '");
	}
	setInterval(callAjax, 60000 );
});
</script>
';
// javascript transition wrapper between loading and loaded
echo '
<script type="text/javascript">
$(\'#loading\').hide(0);
$(\'#loaded\').fadeIn("slow");
</script>
';
// continue html output
echo'
<div class="subsection" style="margin-bottom: 4px;">
<form action="./index.php" method="get">
<span class="information">Search for Player, Message, or Date:</span>
<input type="hidden" name="p" value="chat" />
';
if(!empty($ServerID))
{
	echo '<input type="hidden" name="sid" value="' . $ServerID . '" />';
}
if(!empty($query))
{
	echo '<input type="text" class="messagebox" name="q" value="' . $query . '" />';
}
else
{
	echo '<input type="text" class="messagebox" name="q" />';
}
echo '
</form>
</div>
<div id="chat" style="position: relative;">
<br/><br/>
<center><img class="load" src="./common/images/loading.gif" alt="loading" /></center>
<br/>
<script type="text/javascript">
$(\'#chat\').load("./common/chat/chat-live.php?gid=' . $GameID;
	if(!empty($ServerID))
	{
		echo '&sid=' . $ServerID;
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
	if(!empty($query))
	{
		echo '&q=' . urlencode($query);
	}
	if(!empty($page))
	{
		echo '&p=' . $page;
	}
	echo '");
</script>
</div>
';
?>