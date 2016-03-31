<!DOCTYPE html>
<html><!-- Default Template -->
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo Config::$encoding; ?>" />
	<title>Redirect</title>
	<link href="<?php echo Config::$urlBase; ?>css/main.css" rel="stylesheet" type="text/css" />
</head>

<body>
<div id="container">

<div id="content">
<?php

$this->controller->show();

?>
</div>
</div>
</body>
</html>
