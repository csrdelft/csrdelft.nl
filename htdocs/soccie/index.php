<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

<title>SocCie</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<script type="text/javascript" src="../layout/js/jquery.js"></script>
<script type="text/javascript" src="../layout/js/countdown.js"></script>
	<?php
	
		$now = mktime(23, 0, 0, 5, 3, 2012) - 7200 - date(time());
		if($now > 0) {
		
			$time = date("00:H:i:s", $now);
		
		} else {
		
			$time = '00:00:00:00';
		
		}
	
	?>
    <script type="text/javascript">
      $(function(){
        $('#counter').countdown({
          image: 'count.png',
          startTime: '<?php echo $time; ?>'
        });
		
		$(window).resize(function() {
		
		$("#container").each(function() {
		
			$(this).css({paddingTop: ($(document).height() - $(this).height()) / 2});
		
		});
		
		}).resize();
		
      });
    </script>
    <style type="text/css">
	body{
		width: 551px;
		margin: 0 auto;
	}
      .cntSeparator {
        font-size: 54px;
        margin: 10px 7px;
        color: #000;
      }
      .desc { margin: 7px 3px; }
      .desc div {
        float: left;
        font-family: Arial;
        width: 70px;
        margin-right: 65px;
        font-size: 13px;
        font-weight: bold;
        color: #000;
      }
    </style>
  </head>
<body>
	<div id="container">
  <div id="counter"></div>
  <div class="desc">
    <div>Dagen</div>
    <div>Uren</div>
    <div>Minuten</div>
    <div>Seconden</div>
  </div>
  </div>
</body>
</html>