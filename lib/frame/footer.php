<?php
$footer = array();
$footer[] = '<a href="http://www.literaturedb.com">literaturedb</a>';

if(trim(LibConfig::$footer) != ''){
	$footer[] = LibConfig::$footer;
}

if($sessionUser->isLoggedin()){
	if(LibGlobal::$numberOfMysqlQueries == 1){
		$footer[] = LibGlobal::$numberOfMysqlQueries .' DB Query';
	}
	else{
		$footer[] = LibGlobal::$numberOfMysqlQueries .' DB Queries';
	}
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