<?php
// BF4 Stats Page by Ty_ger07
// https://myrcon.net/topic/162-chat-guid-stats-and-mapstats-logger-1003/

// jquery auto refresh server list every 30 seconds
echo '
<script type="text/javascript">
$(function() {
	function callAjax(){
		$(\'#servers\').load("./common/home/index-display-servers-live.php");
	}
	setInterval(callAjax, 30000 );
});
</script>
';
// this will auto refresh every 30 seconds to ./common/home/index-display-servers-live.php
echo'
<div id="servers" style="position: relative;">
';
// updating text...
// hidden by default until time is reached
echo '
<div id="fadein" style="position: absolute; top: 11px; left: -150px; display: none;">
<div class="subsection" style="width: 100px;">
<center>Updating ...<span style="float:right;"><img class="update" src="./common/images/loading.gif" alt="loading" /></span></center>
</div>
</div>
';
// fadein javascript
echo '
<script type="text/javascript">
$("#fadein").delay(29000).fadeIn("slow");
</script>
';
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
$(\'#loaded\').load("./common/home/index-display-servers-live.php?gid=' . $GameID . '");
</script>
</div>
';
echo '
</div>
';
?>