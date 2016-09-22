<?php
$footer = array();
$footer[] = '<a href="http://www.literaturedb.de">literaturedb</a>';

if(trim(LibConfig::$footer) != ''){
	$footer[] = LibConfig::$footer;
}
?>
  <div id="footer">
    <p>
<?php echo implode(' | ', $footer); ?>
	</p>
  </div>
</div>
</body>
</html>
