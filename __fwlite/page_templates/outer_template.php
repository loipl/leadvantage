<!DOCTYPE html>
<html><!-- Outer Template -->
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo Config::$encoding; ?>" />
	<title><?php echo escapeHtml($this->controller->getPageTitle()); ?></title>
	<link href="<?php echo Config::$urlBase; ?>css/main.css" rel="stylesheet" type="text/css" />
	<script src="<?php echo Config::$urlBase; ?>js/jquery-1.7.min.js" type="text/javascript"></script>
	<link href="<?php echo Config::$urlBase; ?>css/ui-lightness/jquery-ui-1.8.16.custom.css" rel="stylesheet" type="text/css" />
	<script src="<?php echo Config::$urlBase; ?>js/jquery-ui-1.8.16.custom.min.js" type="text/javascript"></script>
</head>

<body>
<div style="width: 960px; margin: 10px auto;">
<?php

$this->controller->show();

?>
</div>
<div id="page-footer">&copy; Copyright <?php echo date('Y'); ?> by <a href="<?php echo 'http://' . $_SERVER['HTTP_HOST'] . '/'; ?>"><?php echo $_SERVER['HTTP_HOST']; ?></a>


<div class="r">Page generated in <?php  echo number_format(microtime(true) - FWLITE_START_TIME, 4); ?>s</div>
<?php 
if (App::$sqlLog) {
    echo '</div>
<div class="grid_12"><h3>SQL Log:<br /></h3>';
    echo App::$sqlLog;
    list($count, $time) = DB::getGlobalStats();
    echo "Total of $count queries in {$time}s";
}
?></div>
</body>
</html>