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
header ("Content-Type: text/css; charset=utf-8");
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
Open compression buffer.
*/
ob_start ("compression");
/*
Load up specific CSS files?
Use the priority/order specified.
*/
if ($l && $c && $u) /* If parameters allow. */
	{
		if (is_dir ("colors/" . $c) && is_array ($css = preg_split ("/[,; ]+/", $l)))
			{
				foreach ($css as $file) /* Now include each CSS file. */
					{
						if (preg_match ("/\.css$/", $file)) /* Must end with CSS. */
							{
								if (file_exists ("colors/" . $c . "/" . $file))
									include_once "colors/" . $c . "/" . $file;
							}
					}
			}
		/**/
		if (is_dir ("includes/custom") && is_array ($css = preg_split ("/[,; ]+/", $l)))
			{
				foreach ($css as $file) /* Now include each CSS file. */
					{
						if (preg_match ("/\.css$/", $file)) /* Must end with CSS. */
							{
								if (file_exists ("includes/custom/" . $file))
									include_once "includes/custom/" . $file;
							}
					}
			}
		/**/
		if (is_dir ("colors/" . $c . "/custom") && is_array ($css = preg_split ("/[,; ]+/", $l)))
			{
				foreach ($css as $file) /* Now include each CSS file. */
					{
						if (preg_match ("/\.css$/", $file)) /* Must end with CSS. */
							{
								if (file_exists ("colors/" . $c . "/custom/" . $file))
									include_once "colors/" . $c . "/custom/" . $file;
							}
					}
			}
	}
/*
Else, collectively load all CSS files.
Some with priority/order when they exist.
Last, load up any custom files that exist.
*/
else if (!$l && $c && $u) /* If parameters allow. */
	{
		if (is_dir ("colors/" . $c) && is_array ($css = scandir ("colors/" . $c)))
			{
				@include_once "colors/" . $c . "/elements.css";
				@include_once "colors/" . $c . "/wordpress.css";
				@include_once "colors/" . $c . "/utilities.css";
				@include_once "colors/" . $c . "/megafish.css";
				@include_once "colors/" . $c . "/colorbox.css";
				@include_once "colors/" . $c . "/common.css";
				@include_once "colors/" . $c . "/layout.css";
				/**/
				foreach ($css as $file) /* Now include each CSS file. */
					{
						if (preg_match ("/\.css$/", $file)) /* Must end with CSS. */
							{
								include_once "colors/" . $c . "/" . $file;
							}
					}
			}
		/**/
		if (is_dir ("includes/custom") && is_array ($css = scandir ("includes/custom")))
			{
				foreach ($css as $file) /* Include each custom CSS file, excluding samples. */
					{
						if (preg_match ("/\.css$/", $file) && !preg_match ("/sample/i", $file))
							{
								include_once "includes/custom/" . $file;
							}
					}
			}
		/**/
		if (is_dir ("colors/" . $c . "/custom") && is_array ($css = scandir ("colors/" . $c . "/custom")))
			{
				foreach ($css as $file) /* Include each custom CSS file, excluding samples. */
					{
						if (preg_match ("/\.css$/", $file) && !preg_match ("/sample/i", $file))
							{
								include_once "colors/" . $c . "/custom/" . $file;
							}
					}
			}
	}
/*
Function that handles compression.
*/
function compression ($css = FALSE)
	{
		$c6 = "/(\:#| #)([A-Z0-9]{6})/i";
		$css = preg_replace ("/\/\*(.*?)\*\//s", "", $css);
		$css = preg_replace ("/[\r\n\t]+/", "", $css);
		$css = preg_replace ("/ {2,}/", " ", $css);
		$css = preg_replace ("/ , | ,|, /", ",", $css);
		$css = preg_replace ("/ \> | \>|\> /", ">", $css);
		$css = preg_replace ("/\[ /", "[", $css);
		$css = preg_replace ("/ \]/", "]", $css);
		$css = preg_replace ("/ \!\= | \!\=|\!\= /", "!=", $css);
		$css = preg_replace ("/ \|\= | \|\=|\|\= /", "|=", $css);
		$css = preg_replace ("/ \^\= | \^\=|\^\= /", "^=", $css);
		$css = preg_replace ("/ \$\= | \$\=|\$\= /", "$=", $css);
		$css = preg_replace ("/ \*\= | \*\=|\*\= /", "*=", $css);
		$css = preg_replace ("/ ~\= | ~\=|~\= /", "~=", $css);
		$css = preg_replace ("/ \= | \=|\= /", "=", $css);
		$css = preg_replace ("/ \+ | \+|\+ /", "+", $css);
		$css = preg_replace ("/ ~ | ~|~ /", "~", $css);
		$css = preg_replace ("/ \{ | \{|\{ /", "{", $css);
		$css = preg_replace ("/ \} | \}|\} /", "}", $css);
		$css = preg_replace ("/ \: | \:|\: /", ":", $css);
		$css = preg_replace ("/ ; | ;|; /", ";", $css);
		$css = preg_replace ("/;\}/", "}", $css);
		/**/
		return preg_replace_callback ($c6, "_c3", $css);
	}
function _c3 ($m = FALSE)
	{
		if ($m[2][0] === $m[2][1] && $m[2][2] === $m[2][3] && $m[2][4] === $m[2][5])
			return $m[1] . $m[2][0] . $m[2][2] . $m[2][4];
		return $m[0];
	}
/*
Function for building the @font-face src: value.
*/
function font_face ($font = FALSE, $embed = TRUE)
	{
		global $u; /* Need this for IE noFOUT. */
		/**/
		$src = "src: "; /* Initialize src value. */
		$svg = $font . ".svg"; /* SVG: Chrome[0-3]. */
		$eot = $font . ".eot"; /* EOT: Internet Explorer 4+. */
		$ttf = $font . ".ttf"; /* TTF: Firefox 3.5+, Safari 3.1+, Chrome 4+, Opera 10+. */
		$ttfe = $ttf . "e"; /* Embeds font in a data URI ( and helps to mitigate FOUT ). */
		$otf = $font . ".otf"; /* OTF: Firefox 3.5+, Safari 3.1+, Chrome 4+, Opera 10+. */
		$otfe = $otf . "e"; /* Embeds font in a data URI ( and helps to mitigate FOUT ). */
		$woff = $font . ".woff"; /* WOFF: Firefox 3.6+ ( may also be supported by IE 9+ ). */
		/**/
		if (preg_match ("/MSIE/i", $_SERVER["HTTP_USER_AGENT"])) /* Check for IE browsers. */
			{
				if (file_exists ($eot)) /* IE must have an EOT file, that is all it supports. */
					$src .= "url('" . $u . "/" . $eot . "')"; /* IE will not understand format(). */
			/* We use the full URL here to satisfy the jQuery.noFOUT() extension requirements. */
			}
		else /* Otherwise, we can build the standards compliant src: with each format available. */
			{
				if ($embed && preg_match ("/(Gecko)(\/)([0-9]+)/i", $_SERVER["HTTP_USER_AGENT"]) && file_exists ($ttf) && (file_exists ($ttfe) || @file_put_contents ($ttfe, base64_encode (file_get_contents ($ttf)))))
					{
						$src .= "url('data:font/truetype;charset=utf-8;base64," . file_get_contents ($ttfe) . "') format('truetype'), ";
					}
				else if ($embed && preg_match ("/(Gecko)(\/)([0-9]+)/i", $_SERVER["HTTP_USER_AGENT"]) && file_exists ($otf) && (file_exists ($otfe) || @file_put_contents ($otfe, base64_encode (file_get_contents ($otf)))))
					{
						$src .= "url('data:font/opentype;charset=utf-8;base64," . file_get_contents ($otfe) . "') format('opentype'), ";
					}
				/**/
				if (file_exists ($woff)) /* Include if available. */
					{
						$src .= "url('" . $woff . "') format('woff'), ";
					}
				if (file_exists ($ttf)) /* Include if available. */
					{
						$src .= "url('" . $ttf . "') format('truetype'), ";
					}
				if (file_exists ($otf)) /* Default if available. */
					{
						$src .= "url('" . $otf . "') format('opentype'), ";
					}
				if (file_exists ($svg)) /* Default if available. */
					{
						$src .= "url('" . $svg . "#" . basename ($font) . "') format('svg'), ";
					}
			}
		/**/
		return rtrim ($src, " ,") . ";";
	}
?>