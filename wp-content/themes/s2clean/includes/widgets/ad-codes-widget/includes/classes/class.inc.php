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
if (!class_exists ("c_ws_widget__ad_codes_class"))
	{
		class c_ws_widget__ad_codes_class /* < Register this widget class. */
			extends WP_Widget /* See: /wp-includes/widgets.php for further details. */
			{
				public function c_ws_widget__ad_codes_class () /* Builds the classname, id_base, description, etc. */
					{
						$widget_ops = array ("classname" => "ad-codes", "description" => $GLOBALS["WS_WIDGET__"]["ad_codes"]["c"]["description"]);
						$control_ops = array ("width" => $GLOBALS["WS_WIDGET__"]["ad_codes"]["c"]["control_w"], "id_base" => "ws_widget__ad_codes");
						/**/
						eval ('foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;');
						do_action ("ws_widget__ad_codes_class_before_widget_construction", get_defined_vars (), $this);
						unset ($__refs, $__v); /* Unset defined __refs, __v. */
						/**/
						$this->WP_Widget ($control_ops["id_base"], $GLOBALS["WS_WIDGET__"]["ad_codes"]["c"]["name"], $widget_ops, $control_ops);
						/**/
						do_action ("ws_widget__ad_codes_class_after_widget_construction", get_defined_vars (), $this);
						/**/
						return; /* Return for uniformity. */
					}
				/*
				Widget display function. This is where the widget actually does something.
				*/
				public function widget ($args = FALSE, $instance = FALSE)
					{
						$options = ws_widget__ad_codes_configure_options_and_their_defaults (false, (array)$instance);
						/**/
						eval ('foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;');
						do_action ("ws_widget__ad_codes_class_before_widget_display", get_defined_vars (), $this);
						unset ($__refs, $__v); /* Unset defined __refs, __v. */
						/**/
						echo $args["before_widget"]; /* Ok, here we go into this widget.
						/**/
						if (strlen ($options["title"])) /* If there is. */
							echo $args["before_title"] . apply_filters ("widget_title", $options["title"]) . $args["after_title"];
						/**/
						$options["code"] = preg_split ("/\<\!--rotate--\>/", $options["code"]);
						shuffle ($options["code"]); /* Support multiple random rotations. */
						/**/
						eval ('foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;');
						do_action ("ws_widget__ad_codes_class_during_widget_display_before", get_defined_vars (), $this);
						unset ($__refs, $__v); /* Unset defined __refs, __v. */
						/**/
						echo '<div style="margin: 0 auto 0 auto; text-align:center;">' . "\n";
						/**/
						if (c_ws_widget__ad_codes_utils_conds::is_multisite_farm ())
							echo do_shortcode (trim ($options["code"][0]));
						/**/
						else /* Otherwise, it's OK to execute PHP code. */
							echo do_shortcode (c_ws_widget__ad_codes_utilities::evl (trim ($options["code"][0])));
						/**/
						echo "\n" . '</div>' . "\n";
						/**/
						eval ('foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;');
						do_action ("ws_widget__ad_codes_class_during_widget_display_after", get_defined_vars (), $this);
						unset ($__refs, $__v); /* Unset defined __refs, __v. */
						/**/
						echo $args["after_widget"];
						/**/
						do_action ("ws_widget__ad_codes_class_after_widget_display", get_defined_vars (), $this);
						/**/
						return; /* Return for uniformity. */
					}
				/*
				Widget form control function. This is where options are made configurable.
				*/
				public function form ($instance = FALSE)
					{
						$options = ws_widget__ad_codes_configure_options_and_their_defaults (false, (array)$instance);
						/**/
						eval ('foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;');
						do_action ("ws_widget__ad_codes_class_before_widget_form", get_defined_vars (), $this);
						unset ($__refs, $__v); /* Unset defined __refs, __v. */
						/*
						Ok, here is where we need to handle the widget control form. This allows a user to further customize the widget.
						*/
						echo '<label for="' . esc_attr ($this->get_field_id ("title")) . '">Title:</label><br />' . "\n";
						echo '<input class="widefat" id="' . esc_attr ($this->get_field_id ("title")) . '" name="' . esc_attr ($this->get_field_name ("title")) . '" type="text" value="' . format_to_edit ($options["title"]) . '" /><br /><br />' . "\n";
						/**/
						echo '<label for="' . esc_attr ($this->get_field_id ("code")) . '">' . "\n";
						echo '<strong>Feel free to paste any type of ad code here.</strong><br />' . "\n";
						echo 'AdSense®, XHTML' . ( (c_ws_widget__ad_codes_utils_conds::is_multisite_farm ()) ? '' : ', PHP') . ', IFrame, JavaScript, whatever:' . "\n";
						echo '</label><br />' . "\n";
						echo '<textarea class="widefat" style="height:200px;" rows="1" cols="1" id="' . esc_attr ($this->get_field_id ("code")) . '" name="' . esc_attr ($this->get_field_name ("code")) . '">' . format_to_edit ($options["code"]) . '</textarea><br />' . "\n";
						echo '<small style="display:block; text-align:justify;">Quick Tip: If you\'d like to rotate a few different ads, just insert this tag:  ' . esc_html ("<!--rotate-->") . ' between every ad code that you want to rotate through.</small>' . "\n";
						/**/
						do_action ("ws_widget__ad_codes_class_after_widget_form", get_defined_vars (), $this);
						/**/
						echo '<br />' . "\n";
						/**/
						return; /* Return for uniformity. */
					}
				/*
				Widget update function. This is where an updated instance is configured/stored.
				*/
				public function update ($instance = FALSE, $old = FALSE)
					{
						eval ('foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;');
						do_action ("ws_widget__ad_codes_class_before_widget_update", get_defined_vars (), $this);
						unset ($__refs, $__v); /* Unset defined __refs, __v. */
						/**/
						$instance = (array)c_ws_widget__ad_codes_utils_strings::trim_deep (stripslashes_deep ($instance));
						return ws_widget__ad_codes_configure_options_and_their_defaults (false, $instance);
					}
			}
	}
?>