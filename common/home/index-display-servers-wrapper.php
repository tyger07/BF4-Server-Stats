<?php
// BF4 Stats Page by Ty_ger07
// http://open-web-community.com/

// this will auto refresh every 30 seconds to ./common/home/index-display-servers-live.php
echo'
<div id="servers" style="position: relative;">
';
// updating text...
// hidden by default until time is reached
echo '
<div id="fadein" style="position: absolute; top: 11px; left: -150px; display: none;">
<div class="subsection" style="width: 100px;">
<center>Updating ...<span style="float:right;"><img src="./common/images/loading.gif" alt="loading" style="width: 16px; height: 16px;" /></span></center>
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
<center><img src="./common/images/loading.gif" alt="loading" style="width: 24px; height: 24px;" /></center>
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