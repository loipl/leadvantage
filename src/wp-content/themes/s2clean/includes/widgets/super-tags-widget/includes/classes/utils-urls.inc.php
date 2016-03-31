<?php
/*
Copyright: © 2009 WebSharks, Inc. ( coded in the USA )
<mailto:support@websharks-inc.com> <http://www.websharks-inc.com/>

Released under the terms of the GNU General Public License.
You should have received a copy of the GNU General Public License,
along with this software. In the main directory, see: /licensing/
If not, see: <http://www.gnu.org/licenses/>.
*/
if (realpath (__FILE__) === realpath ($_SERVER["SCRIPT_FILENAME"]))
	exit ("Do not access this file directly.");
/**/
if (!class_exists ("c_ws_widget__super_tags_utils_urls"))
	{
		class c_ws_widget__super_tags_utils_urls
			{
				/*
				Responsible for remote communications processed by this plugin.
					`wp_remote_request()` through the `WP_Http` class.
				*/
				public static function remote ($url = FALSE, $post_vars = FALSE, $args = FALSE, $return = FALSE)
					{
						static $http_response_filtered = false; /* Apply GZ filters only once. */
						/**/
						$args = (!is_array ($args)) ? array (): $args; /* Disable SSL verifications. */
						$args["sslverify"] = (!isset ($args["sslverify"])) ? false : $args["sslverify"];
						/**/
						if (!$http_response_filtered && ($http_response_filtered = true))
							add_filter ("http_response", "c_ws_widget__super_tags_utils_urls::_remote_gz_variations");
						/**/
						if ($url) /* Obviously, we must have a valid URL before we do anything at all here. */
							{
								if (preg_match ("/^https/i", $url) && stripos (PHP_OS, "win") === 0)
									add_filter ("use_curl_transport", "__return_false", ($curl_disabled = 1352));
								/**/
								if ((is_array ($post_vars) || is_string ($post_vars)) && !empty ($post_vars))
									$args = array_merge ($args, array ("method" => "POST", "body" => $post_vars));
								/**/
								$response = wp_remote_request ($url, $args); /* Get remote request response. */
								/**/
								if ($return === "array" && !is_wp_error ($response) && is_array ($response))
									{
										$r["code"] = (int)wp_remote_retrieve_response_code ($response);
										$r["message"] = wp_remote_retrieve_response_message ($response);
										/**/
										$r["headers"] = array (); /* Creates an array of lowercase headers. */
										foreach (array_keys ($r["o_headers"] = wp_remote_retrieve_headers ($response)) as $header)
											$r["headers"][strtolower ($header)] = $r["o_headers"][$header];
										/**/
										$r["body"] = wp_remote_retrieve_body ($response);
										$r["response"] = $response;
									}
								/**/
								else if (!is_wp_error ($response) && is_array ($response))
									$r = wp_remote_retrieve_body ($response);
								/**/
								else /* Else the request has failed. */
									$r = false; /* Request failed. */
								/**/
								if (!empty ($curl_disabled) && $curl_disabled === 1352)
									remove_filter ("use_curl_transport", "__return_false", 1352);
								/**/
								return $r; /* The return value. */
							}
						/**/
						return false; /* Else return false. */
					}
				/*
				Filters the WP_Http response for additional gzinflate variations.
					Attach to: add_filter("http_response");
				*/
				public static function _remote_gz_variations ($response = array ())
					{
						if (!isset ($response["ws__gz_variations"]) && ($response["ws__gz_variations"] = 1))
							{
								if ($response["headers"]["content-encoding"])
									if (substr ($response["body"], 0, 2) === "\x78\x9c")
										if (($gz = @gzinflate (substr ($response["body"], 2))))
											$response["body"] = $gz;
							}
						/**/
						return $response;
					}
			}
	}
?>