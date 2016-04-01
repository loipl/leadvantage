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
if (!class_exists ("c_ws_theme__twitter"))
	{
		class c_ws_theme__twitter
			{
				/*
				Pull tweets from the specified Twitter account.
				*/
				public static function tweets ($username = FALSE, $max = 10)
					{
						include_once (ABSPATH . WPINC . "/feed.php");
						/**/
						eval ('foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;');
						do_action ("ws_theme__before_tweets", get_defined_vars ());
						unset ($__refs, $__v); /* Unset defined __refs, __v. */
						/**/
						add_filter (($cache = "wp_feed_cache_transient_lifetime"), ($lifetime = "c_ws_theme__twitter::_tweets_cache_lifetime"));
						/**/
						if ($username && $max && !is_wp_error ($feed = fetch_feed ("http://search.twitter.com/search.atom?q=from:" . urlencode ($username) . "&rpp=" . urlencode ($max))))
							{
								remove_filter ($cache, $lifetime);
								/**/
								return apply_filters ("ws_theme__tweets", $feed->get_items (0, $max), get_defined_vars ());
							}
						else /* Else remove filter & return false. */
							{
								remove_filter ($cache, $lifetime);
								/**/
								return apply_filters ("ws_theme__tweets", false, get_defined_vars ());
							}
					}
				/*
				Pull the lastest tweet from the specified Twitter account.
				*/
				public static function latest_tweet ($username = FALSE)
					{
						eval ('foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;');
						do_action ("ws_theme__before_latest_tweet", get_defined_vars ());
						unset ($__refs, $__v); /* Unset defined __refs, __v. */
						/**/
						if ($tweets = c_ws_theme__twitter::tweets ($username, 1))
							{
								return apply_filters ("ws_theme__latest_tweet", $tweets[0]->get_content (), get_defined_vars ());
							}
						/**/
						return apply_filters ("ws_theme__latest_tweet", false, get_defined_vars ());
					}
				/*
				A sort of callback function that sets the cache liftime.
				Attach to: add_filter("wp_feed_cache_transient_lifetime");
					300 seconds = 5 minutes.
				*/
				public static function _tweets_cache_lifetime ($seconds = FALSE)
					{
						return apply_filters ("_ws_theme__tweets_cache_lifetime", 300, get_defined_vars ());
					}
			}
	}
?>