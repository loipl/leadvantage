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
if (!class_exists ("c_ws_theme__featurettes"))
	{
		class c_ws_theme__featurettes
			{
				/*
				Function that builds the featurette image list items.
				If some slots are empty, they'll be skipped silently.
				
				Also, if the redirection URL for a specific image ends
				in svg|png|gif|jpg|jpe|jpeg|bmp; $.boxOpen is called upon.
				*/
				public static function featurette_items ($type = FALSE, $max = 10)
					{
						global $post; /* So we can check for feature_1st. */
						/**/
						eval ('foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;');
						do_action ("ws_theme__before_featurette_items", get_defined_vars ());
						unset ($__refs, $__v); /* Unset defined __refs, __v. */
						/**/
						$type = ($type) ? rtrim ($type, "_") . "_" : "";
						/**/
						for ($i = 1, $items = array (); $i <= $max; $i++) /* Go through items, in order. */
							{
								if ($GLOBALS["WS_THEME__"]["o"][$type . "featurette_img_" . $i . "_src"])
									{
										if ($GLOBALS["WS_THEME__"]["o"][$type . "featurette_img_" . $i . "_href"])
											{
												$click = (preg_match ("/\.(svg|png|gif|jpg|jpe|jpeg|bmp)$/i", $GLOBALS["WS_THEME__"]["o"][$type . "featurette_img_" . $i . "_href"])) ? ' onclick="jQuery(this).boxOpen(); return false;"' : '';
												$items[$i] = '<li><a href="' . esc_attr ($GLOBALS["WS_THEME__"]["o"][$type . "featurette_img_" . $i . "_href"]) . '"' . $click . '><img src="' . esc_attr ($GLOBALS["WS_THEME__"]["o"][$type . "featurette_img_" . $i . "_src"]) . '" alt="" /></a></li>';
											}
										else /* Otherwise, this image has no click redirection. */
											{
												unset ($click); /* There is no click value. */
												$items[$i] = '<li><img src="' . esc_attr ($GLOBALS["WS_THEME__"]["o"][$type . "featurette_img_" . $i . "_src"]) . '" alt="" /></li>';
											}
										/**/
										eval ('foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;');
										do_action ("ws_theme__during_featurette_items", get_defined_vars ());
										unset ($__refs, $__v); /* Unset defined __refs, __v. */
									}
							}
						/**/
						if ($GLOBALS["WS_THEME__"]["o"][$type . "featurette_shuffle"])
							c_ws_theme__utils_arrays::shuffle_pre_keys ($items);
						/**/
						if (is_singular () && is_object ($post) && $post->ID)
							if ($_1st = (int)get_post_meta ($post->ID, "feature_1st", true))
								{
									$__1st = $items[$_1st];
									unset ($items[$_1st]);
									array_unshift ($items, $__1st);
								}
						/**/
						return implode ("\n", apply_filters ("ws_theme__featurette_items", $items, get_defined_vars ()));
					}
			}
	}
?>