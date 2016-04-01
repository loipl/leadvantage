<!DOCTYPE html>
<html><!-- Default Template -->
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo Config::$encoding; ?>" />
	<title><?php echo escapeHtml($this->controller->getPageTitle()); ?></title>
	<link href="<?php echo Config::$urlBase; ?>css/main.css" rel="stylesheet" type="text/css" />
	<script src="<?php echo Config::$urlBase; ?>js/jquery-1.7.min.js" type="text/javascript"></script>
	<link href="<?php echo Config::$urlBase; ?>css/ui-lightness/jquery-ui-1.8.16.custom.css" rel="stylesheet" type="text/css" />
	<script src="<?php echo Config::$urlBase; ?>js/jquery-ui-1.8.16.custom.min.js" type="text/javascript"></script>
	<script src="<?php echo Config::$urlBase; ?>js/jquery.qtip-1.0.0-rc3.min.js" type="text/javascript"></script>
</head>

<body>
<div id="container">
<div id="header-logo"><img src="/img/logo.png"><!-- PLACEHOLDER FOR HEADER TEXT &amp; LOGO (file is <?php echo __FILE__; ?>, styling is #header-logo in <a style="color: #fff;" href="/css/main.css">/css/main.css</a>) --></div>
<div id="header">
<?php

foreach ($this->sections as $url => $section) {
    if (is_numeric($url)) {
        $url = strtolower($section) . '/';
    }
    $s = ($this->activeSection == $section) ? ' active' : '';
    echo "<div class=\"tab$s\"><a href=\"" . Config::$urlBase . "$url\">$section</a></div>\n";
}

?>
<div class="tab"><a href="/forums/">Forums</a></div>
<!-- <div class="tab"><a href="/activity/">Activity</a></div> -->
<div class="tab"><a href="/groups/">Groups</a></div>
<!-- <div class="tab"><a href="/members/">Members</a></div> -->

<div style="float: right; padding: 6px 20px; white-space: nowrap; color: #bbb;"><table><tr><td style="padding-right: 10px;">
<?php if (Session::getSession()->impersonate) {
    echo "(Impersonating \"" . Session::getSession()->impersonate . '")</td><td style="padding-right: 20px;">';
    echo App::linkFor('Dashboard', 'Stop Impersonating', array('action' => 'stopImpersonate'));
}
?></td><td>
<a href="<?php echo App::getFrontController()->urlFor('Dashboard', array('action' => 'logout')); ?>">Sign Out</a></td></tr></table></div>
</div>
<div id="content">
<?php

//##############################################################################
//##############################################################################
//
// Top Notifications
$info = $warnings = $errors = array(); $count = 0;
$notifications =array();
$cids = $this->checkCampaignsWithNoPartners();
if ($cids) {
    $msg = '<div class="warn">Warning: The following campaigns have no delivery partners: ';
    $cc = false;
    foreach ($cids as $id => $cname) {
        if ($cc) {
            echo ', ';
        } else {
            $cc = true;
        }
        $links[] = App::linkFor('Campaigns', escapeHtml($cname), array('action' => 'delivery'), array('id' => $id));
    }
    $msg .= implode(', ', $links) . "</div>\n";
    $warnings[] = $msg;
    $count++;
}
foreach ($this->getNotifications() as $arr) {
    $info[] = '<div class="info">' . escapeHtml($arr[1]) . "</div>\n";
    $count++;
}
if ($count) {?>
<div id="user-notifications">
<?php if ($info) { ?>
    <table style="margin: 0; width: 100%; margin-bottom: 3px;" id="user-notifications-info">
    <tr>
    <td><?php foreach ($info as $s) {
        // echo "$arr[0]: ";
        echo $s;
    }?></td>
    <td class="user-notifications-td"><a style="text-decoration: none;" href="javascript:hideInfoNotifications();"><b>[X]</b> close</a></td>
    </tr>
    </table>
<?php } ?>
<?php if ($warnings) { ?>
    <table style="margin: 0; width: 100%; margin-bottom: 3px;" id="user-notifications-warning">
    <tr>
    <td><?php foreach ($warnings as $s) {
        // echo "$arr[0]: ";
        echo $s;
    }?></td>
    <td class="user-notifications-td"><a style="text-decoration: none;" href="javascript:hideWarningNotifications();"><b>[X]</b> close</a></td>
    </tr>
    </table>
<?php } ?>
<?php if ($errors) { ?>
    <table style="margin: 0; width: 100%; margin-bottom: 3px;" id="user-notifications-error">
    <tr>
    <td><?php foreach ($errors as $s) {
        // echo "$arr[0]: ";
        echo $s;
    }?></td>
    <td class="user-notifications-td">&nbsp;</td>
    </tr>
    </table>
<?php }

//##############################################################################

?>
</div>
<script type="text/javascript">
function hideInfoNotifications() {
	$('#user-notifications-info').fadeOut();
	$('#user-notifications-info').remove();
	hideUserNotificationsDivIfEmpty();
};
function hideWarningNotifications() {
	$('#user-notifications-warning').fadeOut();
	$('#user-notifications-warning').remove();
	hideUserNotificationsDivIfEmpty();
};
function hideUserNotificationsDivIfEmpty() {
	if ($('#user-notifications-info').size() > 0) {
		return;
	};
	if ($('#user-notifications-warning').size() > 0) {
		return;
	};
	if ($('#user-notifications-error').size() > 0) {
		return;
	};
	$('#user-notifications').hide();
};

$(document).ready(function() {
	setTimeout('hideInfoNotifications()', 7000);
});
</script>
<?php }

$this->controller->show();

?>
</div>
<div class="clear"></div>
<div id="page-footer">&copy; Copyright <?php echo date('Y'); ?> by <a href="<?php echo 'http://' . $_SERVER['HTTP_HOST'] . '/'; ?>"><?php echo $_SERVER['HTTP_HOST']; ?></a><br>
<!-- Footer message -->

<div class="r">Page generated in <?php  echo number_format(microtime(true) - FWLITE_START_TIME, 4); ?>s</div>
<?php
if (App::$sqlLog) {
    echo '</div>
<div class="grid_12"><h3>SQL Log:<br /></h3>';
    echo App::$sqlLog;
    list($count, $time) = DB::getGlobalStats();
    echo "Total of $count queries in {$time}s";
}
?>
</div>
</div>
</body>
</html>
