<?php
/*
Copyright: © 2009 WebSharks, Inc. ( coded in the USA )
<mailto:support@websharks-inc.com> <http://www.websharks-inc.com/>

Released under the terms of the GNU General Public License.
You should have received a copy of the GNU General Public License,
along with this software. In the main directory, see: /licensing/
If not, see: <http://www.gnu.org/licenses/>.
*/
/*
Get the e variable ( extensions ).
*/
$e = htmlspecialchars ($_GET["e"]);
/*
Get the l variable ( load ).
*/
$l = htmlspecialchars ($_GET["l"]);
/*
Get the c variable ( color ).
*/
$c = htmlspecialchars ($_GET["c"]);
/*
Get the u variable ( template_url ).
*/
$u = base64_decode (htmlspecialchars ($_GET["u"]));
/*
Build the i variable ( images url ).
*/
$i = $u . "/colors/" . $c . "/images";
/*
Send headers and clean buffers.
*/
header ("HTTP/1.0 200 OK");
header ("Content-Type: text/javascript; charset=utf-8");
eval ('while (@ob_end_clean ());'); /* Clean buffers. */
/*
Send no-cache headers upon special request via `no-cache`.
*/
if ($_GET["no-cache"]) /* ONLY when requested. */
	{
		header ("Expires: " . gmdate ("D, d M Y H:i:s", strtotime ("-1 week")) . " GMT");
		header ("Last-Modified: " . gmdate ("D, d M Y H:i:s") . " GMT");
		header ("Cache-Control: no-cache, must-revalidate, max-age=0");
		header ("Pragma: no-cache");
	}
else /* Else allow caching for up to 1 week. */
	{
		header ("Expires: " . gmdate ("D, d M Y H:i:s", strtotime ("+1 week")) . " GMT");
		header ("Last-Modified: " . gmdate ("D, d M Y H:i:s") . " GMT");
		header ("Cache-Control: max-age=604800");
		header ("Pragma: public");
	}
/*
Load up specific extensions?
Use the priority/order specified.
*/
if ($e && $c && $u) /* If parameters allow. */
	{
		if (is_dir ("includes/jquery") && is_array ($extensions = preg_split ("/[,; ]+/", $e)))
			{
				foreach ($extensions as $extension) /* Now include each extension. */
					{
						if (preg_match ("/^jquery\./", $extension)) /* Must start with jquery dot. */
							{
								if (file_exists ("includes/jquery/" . $extension . "/" . $extension . "-min.js"))
									{
										include_once "includes/jquery/" . $extension . "/" . $extension . "-min.js";
										echo "\n"; /* Add line breaks. */
									}
							}
					}
			}
		/**/
		if (is_dir ("includes/cc-incs/jquery") && is_array ($extensions = preg_split ("/[,; ]+/", $e)))
			{
				foreach ($extensions as $extension) /* Now include each extension. */
					{
						if (preg_match ("/^jquery\./", $extension)) /* Must start with jquery dot. */
							{
								if (file_exists ("includes/cc-incs/jquery/" . $extension . "/" . $extension . "-min.js"))
									{
										include_once "includes/cc-incs/jquery/" . $extension . "/" . $extension . "-min.js";
										echo "\n"; /* Add line breaks. */
									}
							}
					}
			}
	}
/*
Else, collectively load all extensions.
Some with priority/order when they exist.
*/
else if (!$e && !$l && $c && $u) /* If parameters allow. */
	{
		if (is_dir ("includes/jquery") && is_array ($extensions = scandir ("includes/jquery")))
			{
				@include_once "includes/jquery/jquery.sprintf/jquery.sprintf.js";
				echo "\n"; /* Add line breaks between em. */
				/**/
				@include_once "includes/jquery/jquery.userags/jquery.userags-min.js";
				echo "\n"; /* Add line breaks between em. */
				/**/
				@include_once "includes/jquery/jquery.json-ps/jquery.json-ps-min.js";
				echo "\n"; /* Add line breaks between em. */
				/**/
				@include_once "includes/jquery/jquery.hoverintent/jquery.hoverintent-min.js";
				echo "\n"; /* Add line breaks between em. */
				/**/
				@include_once "includes/jquery/jquery.colorbox/jquery.colorbox-min.js";
				echo "\n"; /* Add line breaks between em. */
				/**/
				foreach ($extensions as $extension) /* Now include each extension. */
					{
						if (preg_match ("/^jquery\./", $extension) && !in_array ($extension, array ("jquery.ui-effects")))
							{
								include_once "includes/jquery/" . $extension . "/" . $extension . "-min.js";
								echo "\n"; /* Add line breaks. */
							}
					}
			}
		/**/
		if (is_dir ("includes/cc-incs/jquery") && is_array ($extensions = scandir ("includes/cc-incs/jquery")))
			{
				foreach ($extensions as $extension) /* Now include each extension. */
					{
						if (preg_match ("/^jquery\./", $extension)) /* Must start with jquery dot. */
							{
								include_once "includes/cc-incs/jquery/" . $extension . "/" . $extension . "-min.js";
								echo "\n"; /* Add line breaks. */
							}
					}
			}
	}
/*
Load up specific JS files?
Use the priority/order specified.
*/
if ($l && $c && $u) /* If parameters allow. */
	{
		if (is_dir ("colors/" . $c) && is_array ($js = preg_split ("/[,; ]+/", $l)))
			{
				foreach ($js as $file) /* Now include each JS file. */
					{
						if (preg_match ("/\.js$/", $file)) /* Must end with JS. */
							{
								if (file_exists ("colors/" . $c . "/" . $file))
									{
										include_once "colors/" . $c . "/" . $file;
										echo "\n"; /* Add line breaks. */
									}
							}
					}
			}
		/**/
		if (is_dir ("includes/custom") && is_array ($js = preg_split ("/[,; ]+/", $l)))
			{
				foreach ($js as $file) /* Now include each JS file. */
					{
						if (preg_match ("/\.js$/", $file)) /* Must end with JS. */
							{
								if (file_exists ("includes/custom/" . $file))
									{
										include_once "includes/custom/" . $file;
										echo "\n"; /* Add line breaks. */
									}
							}
					}
			}
		/**/
		if (is_dir ("colors/" . $c . "/custom") && is_array ($js = preg_split ("/[,; ]+/", $l)))
			{
				foreach ($js as $file) /* Now include each JS file. */
					{
						if (preg_match ("/\.js$/", $file)) /* Must end with JS. */
							{
								if (file_exists ("colors/" . $c . "/custom/" . $file))
									{
										include_once "colors/" . $c . "/custom/" . $file;
										echo "\n"; /* Add line breaks. */
									}
							}
					}
			}
	}
/*
Else, collectively load all JS files.
Some with priority/order when they exist.
Last, load up any custom files that exist.
*/
else if (!$e && !$l && $c && $u) /* If parameters allow. */
	{
		if (is_dir ("colors/" . $c) && is_array ($js = scandir ("colors/" . $c)))
			{
				@include_once "colors/" . $c . "/inline.js";
				echo "\n"; /* Add line breaks between em. */
				/**/
				@include_once "colors/" . $c . "/onready.js";
				echo "\n"; /* Add line breaks between em. */
				/**/
				@include_once "colors/" . $c . "/onload.js";
				echo "\n"; /* Add line breaks between em. */
				/**/
				foreach ($js as $file) /* Now include each JS file. */
					{
						if (preg_match ("/\.js$/", $file)) /* Must end with JS. */
							{
								include_once "colors/" . $c . "/" . $file;
								echo "\n"; /* Add line breaks. */
							}
					}
			}
		/**/
		if (is_dir ("includes/custom") && is_array ($js = scandir ("includes/custom")))
			{
				foreach ($js as $file) /* Include each custom JS file, excluding samples. */
					{
						if (preg_match ("/\.js$/", $file) && !preg_match ("/sample/i", $file))
							{
								include_once "includes/custom/" . $file;
								echo "\n"; /* Add line breaks. */
							}
					}
			}
		/**/
		if (is_dir ("colors/" . $c . "/custom") && is_array ($js = scandir ("colors/" . $c . "/custom")))
			{
				foreach ($js as $file) /* Include each custom JS file, excluding samples. */
					{
						if (preg_match ("/\.js$/", $file) && !preg_match ("/sample/i", $file))
							{
								include_once "colors/" . $c . "/custom/" . $file;
								echo "\n"; /* Add line breaks. */
							}
					}
			}
	}
?>