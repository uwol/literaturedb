<?php
$footer = array();
$footer[] = '<a href="http://www.literaturedb.com">literaturedb</a>';
if(!isset(LibConfig::$footer))
	$footer[] = '<a href="http://validator.w3.org/check?uri=referer" title="W3C HTML Validation">XHTML</a> | <a href="http://www.raykonline.com/">Design</a> <a href="http://creativecommons.org/licenses/by/1.0/">CC</a>';
elseif(trim(LibConfig::$footer) != '')
	$footer[] = LibConfig::$footer;

if($sessionUser->isLoggedin()){
	if(LibGlobal::$numberOfMysqlQueries == 1)
		$footer[] = LibGlobal::$numberOfMysqlQueries .' DB Query';
	else
		$footer[] = LibGlobal::$numberOfMysqlQueries .' DB Queries';
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