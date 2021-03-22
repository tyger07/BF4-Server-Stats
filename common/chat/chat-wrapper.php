<?php
// BF4 Stats Page by Ty_ger07
// https://myrcon.net/topic/162-chat-guid-stats-and-mapstats-logger-1003/

// include required files
require_once('../../config/config.php');
require_once('../connect.php');
require_once('../functions.php');
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
// jquery auto-find players in input box
// don't show to bots
if(!($isbot))
{
	echo '
	<script type="text/javascript">
	$(function()
	{
		$("#chat_search").autocomplete(
		{
			source: "./common/chat/chat-search.php?gid=' . $GameID;
			if(!empty($ServerID))
			{
				echo '&sid=' . $ServerID;
			}
			echo '",
			minLength: 3,
			delay: 500,
			select: function( event, ui )
			{
				if(ui.item)
				{
					var str = ui.item.value;
					if(!(str.includes("Message: ")) && !(str.includes("Date: ")))
					{
						$(\'#chat_search\').val(ui.item.value);
					}
				}
				$(\'#ajaxsearch_chat\').submit();
			}
		});
	});
	</script>
	';
}
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
<form id="ajaxsearch_chat" action="./index.php" method="get">
<span class="information">Search for Date, Message, or Player:</span>
<input type="hidden" name="p" value="chat" />
';
if(!empty($ServerID))
{
	echo '<input type="hidden" name="sid" value="' . $ServerID . '" />';
}
if(!empty($query))
{
	echo '<input id="chat_search" type="text" class="messagebox" name="q" value="' . $query . '" />';
}
else
{
	echo '<input id="chat_search" type="text" class="messagebox" name="q" />';
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