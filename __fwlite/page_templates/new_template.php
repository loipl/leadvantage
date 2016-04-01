<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title><?php echo escapeHtml($this->controller->getPageTitle()); ?></title>
	<meta http-equiv="content-type" content="application/xhtml+xml; charset=<?php echo Config::$encoding; ?>" />

	<meta name="robots" content="index, follow" />

	<meta name="description" content="" />
	<meta name="keywords" content="" />
	<meta name="author" content="" />

	<link rel="stylesheet" href="<?php echo Config::$urlBase; ?>style/main.css" media="screen,projection" type="text/css" />
<?php

foreach ($this->extraCss as $css) {
    echo "\t<link rel=\"stylesheet\" href=\"" . Config::$urlBase . "style/$css\" media=\"screen,projection\" type=\"text/css\" />\n";
}

?>

	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.0/jquery.min.js" type="text/javascript"></script>
	<link href="<?php  echo Config::$urlBase; ?>css/ui-lightness/jquery-ui-1.8.16.custom.css" rel="stylesheet" type="text/css" />
	<link href="<?php  echo Config::$urlBase; ?>css/jquery.timepicker.css" rel="stylesheet" type="text/css" />
        <script src="<?php echo Config::$urlBase; ?>js/jquery.form.js"></script> 
        <script src="<?php echo Config::$urlBase; ?>js/jquery.timepicker.min.js"></script> 
        
	<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/jquery-ui.min.js"></script>
	<script src="<?php echo Config::$urlBase; ?>js/jquery.qtip-1.0.0-rc3.min.js" type="text/javascript"></script>
        <?php 
        $jsPrepends = $this->controller->getJsPrepend();
        if (!empty($jsPrepends)) {
            foreach ($jsPrepends as $js) {?>
        
        <script src="<?php echo Config::$urlBase; ?>js/<?php echo $js;?>.js" type="text/javascript"></script>
        
        <?php }}?>
</head>

<body>

	<div id="wrapper">

		<div id="container">


			<div id="header">

				<div class="logo">
					<!--<a href="/dashboard/"><img src="/images/logo-.jpg" alt=""/></a>-->
				</div><!--logo-->

				<div class="account">

					<ul>
						<li class="first"><?php
echo '<span class="x">';
if (Session::getSession()->impersonate) {
    echo "Impersonating <span class=\"name\">" . Session::getSession()->impersonate . '</span></span></li>';
    echo '<li>' . App::linkFor('Dashboard', 'Stop', array('action' => 'stopImpersonate'), array(), ' style="color: red;"');
} else {
    echo 'Logged in as <span class="name" onclick="window.location.href=\'/account/\'">';
    $u = get_user_by('id', Auth::getInstance()->getUserId());
    echo escapeHtml($u->data->user_nicename) . '<span></a>' ;
}

?></li>
						<!-- <li><a href="/wp-admin/profile.php">My Account</a></li> -->
						<li class="help"><a href="mailto:support@leadwrench.com">Help</a></li>
						<li><a href="<?php echo App::getFrontController()->urlFor('Dashboard', array('action' => 'logout')); ?>">Logout</a></li>
					</ul>

				</div><!--account-->

				<div class="clear"></div>
			</div><!-- header -->

			<div id="navigation">

				<ul>
				    <?php

foreach ($this->sections as $url => $section) {
    if (is_numeric($url)) {
        $url = strtolower($section) . '/';
        if ($url == 'reporting/') {
            $url .= '?muid=' . Auth::getInstance()->getUserId();
        }
    }
    $s = ($this->activeSection == $section) ? ' class="current"' : '';
    echo "<li$s><a href=\"" . Config::$urlBase . "$url\">$section</a></li>\n";
}

?>
					<!-- <li><a href="#">Community</a>
						<ul>
							<li><a href="/forums/">Forums</a></li>
							<li><a href="/activity/">Activity</a></li>
							<li><a href="/members/">Members</a></li>
						</ul>
					</li> -->
				</ul>

				<div class="clear"></div>
			</div><!-- navigation -->


			<div id="content">

<?php
$this->getMessagesToArray();
$success = $errors = array();
$warnings = $this->getOverQuotaNotifications();
$messages = $this->messages;
$count = sizeof($messages) + sizeof($warnings);

$notifications =array();
$cids = $this->checkCampaignsWithNoPartners();
if ($cids) {
    $msg = 'The following campaigns have no delivery partners: ';
    foreach ($cids as $id => $cname) {
        $links[] = App::linkFor('Campaigns', escapeHtml($cname), array('action' => 'delivery'), array('id' => $id));
    }
    $msg .= implode(', ', $links);
    $warnings[] = $msg;
    $count++;
}

$pids = $this->checkPartnersWithInvalidTemplate();
if ($pids) {
    $msg = 'The following partners have invalid templates: ';
    $links = array();
    foreach ($pids as $partner) {
        $links[] = App::linkFor('Partners', escapeHtml($partner['name']), array('action' => 'template'), array('id' => $partner['id']));
    }
    $msg .= implode(', ', $links);
    $warnings[] = $msg;
    $count++;
}

foreach ($this->getNotifications() as $arr) {
    $success[] = $arr[2] ? escapeHtml($arr[1]) : $arr[1];
    $count++;
}

$noPartner = FrontController::checkIfUserHasNoPartner();
if ($noPartner) {
    $count++;
}

if ($count) {
?>

            <div class="status" id="user-notifications">
<?php if ($warnings) { ?>
            	<div class="warning" id="user-notifications-warning">
            		<a class="close" href="javascript:hideWarningNotifications();"><img src="/images/close.jpg"/></a>
<?php foreach ($warnings as $s) echo '
            		<h4><b>Warning:</b> ' . $s . '</h4>
';
?>
            	</div><!--warning-->
<?php
}

$userNotificationIDs = array();
if ($messages) {
    $countAll = $countUser = 0;
    foreach ($messages as $arr) {
        if ($arr['user_id']) {
            $countUser++;
        } else {
            $countAll++;
        }
    }
    if ($countAll) {
?>
            	<div class="info" id="user-notifications-info">
            		<a class="close" href="javascript:hideInfoNotifications();"><img src="/images/close.jpg"/></a>
<?php foreach ($messages as $arr) if (!$arr['user_id']) echo '
            		<h4><b>Info:</b> ' . ($arr['should_escape'] ? escapeHtml($arr['text']) : $arr['text']) . '.</h4>
';
?>
            	</div><!--info-->               
<?php
    };
    if($countUser) {
        foreach ($messages as $arr) {
            if ($arr['user_id']) {
                $userNotificationIDs[] = (int)$arr['id']; ?>
            	<div class="info" id="user-notifications-info-<?php echo $arr['id']; ?>">
            		<a class="close" href="javascript:removeInfoNotificationById(<?php echo $arr['id']; ?>);"><img src="/images/close.jpg"/></a>
                <?php
                echo '
                <h4><b>Info:</b> ' . ($arr['should_escape'] ? escapeHtml($arr['text']) : $arr['text']) . '.</h4>
            </div><!-- #info ' . $arr['id'] . ' -->
';
            }
        }
    }
}

if($noPartner) { ?>
        <div class="info" id="no-partner-notifications">
            <a class="close" href="javascript:hideNoPartnerNotifications();"><img src="/images/close.jpg"/></a>
            <h4><b>Info:</b>No Partners Available - <a href="/partners/add.html">Add a New Partner</a></h4>
        </div>
<?php }

if ($errors) {
?>
            	<div class="error" id="user-notifications-error">
<?php foreach ($errors as $s) echo '
            		<h4><b>Error:</b> ' . escapeHtml($s) . '</h4>
'
?>
            	</div><!--error-->
<?php
}

if ($success) {
?>
            	<div class="success" id="user-notifications-success">
            		<a class="close" href="javascript:hideSuccessNotifications();"><img src="/images/close.jpg"/></a>
<?php foreach ($success as $s) echo '
            		<h4><b>Success:</b> ' . $s . '</h4>
';
?>
            	</div><!--success-->
<?php
}
?>
            </div><!--status-->
<script type="text/javascript">
function hideInfoNotifications() {

	// e.preventDefault();
	var lastId = <?php echo $this->lastMessageId; ?>;
	if (lastId) {
		$.post(<?php echo "'" . App::getFrontController()->urlFor('Dashboard', array('action' => 'ajaxDismissMessage')) . "'"; ?>, {'__nonce' : <?php echo "'" . Session::getSession()->getNonce() . "'"; ?>, 'id' : lastId},
			function(response) {
				if (response != 'OK') {
					alert('There was an error');
					return;
				}
				$('#user-notifications-info').fadeOut();
				$('#user-notifications-info').remove();
				hideUserNotificationsDivIfEmpty();
			}
		);
	};
};

function removeInfoNotificationById(id) {
		$.post(<?php echo "'" . App::getFrontController()->urlFor('Dashboard', array('action' => 'ajaxDeletePerUserMessage')) . "'"; ?>, {'__nonce' : <?php echo "'" . Session::getSession()->getNonce() . "'"; ?>, 'id' : id},
			function(response) {
				if (response != 'OK') {
					alert('There was an error');
					return;
				}
				$('#user-notifications-info-' + id).fadeOut();
				$('#user-notifications-info-' + id).remove();
				hideUserNotificationsDivIfEmpty();
			}
		);
};
function hideSuccessNotifications() {
	$('#user-notifications-success').fadeOut();
	$('#user-notifications-success').remove();
	hideUserNotificationsDivIfEmpty();
};
function hideWarningNotifications() {
	$('#user-notifications-warning').fadeOut();
	$('#user-notifications-warning').remove();
	hideUserNotificationsDivIfEmpty();
};

function hideNoPartnerNotifications() {
    $('#no-partner-notifications').hide();
}

function hideUserNotificationsDivIfEmpty() {
	if ($('#user-notifications-info').size() > 0) {
		return;
	};
	if ($('#user-notifications-warning').size() > 0) {
		return;
	};
	if ($('#user-notifications-success').size() > 0) {
		return;
	};
	if ($('#user-notifications-error').size() > 0) {
		return;
	};
	<?php foreach ($userNotificationIDs as $id) { ?>
	if ($('#user-notifications-info-<?php echo $id;?>').size() > 0) {
		return;
	};
	<?php } ?>
	$('#user-notifications').hide();
};

$(document).ready(function() {
	setTimeout('hideSuccessNotifications()', 7000);
});
</script>
<?php
}

$this->controller->show();

?>
			</div><!-- content -->

		</div><!-- container -->

	</div><!-- wrapper -->

	<div id="footer">

			<!--<p class="copy">Copyright &copy; 2011 - <?php echo date('Y'); ?>  by LeadWrench.com</p>-->

		<div class="clear"></div>
<?php
if (App::$sqlLog) {
    echo '
<div class="sql-log" style="background-color: #fff; padding: 10px;"><h3>SQL Log:<br /></h3>';
    echo App::$sqlLog;
    list($count, $time) = DB::getGlobalStats();
    echo "<br>Total of $count queries in {$time}s</div>";
}
?>
	</div><!-- footer -->

</body>
</html>