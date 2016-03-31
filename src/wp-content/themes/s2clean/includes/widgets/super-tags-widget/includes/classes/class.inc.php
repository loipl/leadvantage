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
if (!class_exists ("c_ws_widget__super_tags_class"))
	{
		class c_ws_widget__super_tags_class /* < Register this widget class. */
			extends WP_Widget /* See: /wp-includes/widgets.php for further details. */
			{
				public function c_ws_widget__super_tags_class () /* Builds the classname, id_base, description, etc. */
					{
						$widget_ops = array ("classname" => "super-tags widget_tag_cloud", "description" => $GLOBALS["WS_WIDGET__"]["super_tags"]["c"]["description"]);
						$control_ops = array ("width" => $GLOBALS["WS_WIDGET__"]["super_tags"]["c"]["control_w"], "id_base" => "ws_widget__super_tags");
						/**/
						eval ('foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;');
						do_action ("ws_widget__super_tags_class_before_widget_construction", get_defined_vars (), $this);
						unset ($__refs, $__v); /* Unset defined __refs, __v. */
						/**/
						$this->WP_Widget ($control_ops["id_base"], $GLOBALS["WS_WIDGET__"]["super_tags"]["c"]["name"], $widget_ops, $control_ops);
						/**/
						do_action ("ws_widget__super_tags_class_after_widget_construction", get_defined_vars (), $this);
						/**/
						return; /* Return for uniformity. */
					}
				/*
				Widget display function. This is where the widget actually does something.
				*/
				public function widget ($args = FALSE, $instance = FALSE)
					{
						$options = ws_widget__super_tags_configure_options_and_their_defaults (false, (array)$instance);
						/**/
						eval ('foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;');
						do_action ("ws_widget__super_tags_class_before_widget_display", get_defined_vars (), $this);
						unset ($__refs, $__v); /* Unset defined __refs, __v. */
						/**/
						echo $args["before_widget"]; /* Ok, here we go into this widget.
						/**/
						if (strlen ($options["title"])) /* If there is. */
							echo $args["before_title"] . apply_filters ("widget_title", $options["title"]) . $args["after_title"];
						/**/
						eval ('foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;');
						do_action ("ws_widget__super_tags_class_during_widget_display_before", get_defined_vars (), $this);
						unset ($__refs, $__v); /* Unset defined __refs, __v. */
						/**/
						echo wp_tag_cloud ($options); /* Pass options to the wp_tag_cloud function. */
						/**/
						eval ('foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;');
						do_action ("ws_widget__super_tags_class_during_widget_display_after", get_defined_vars (), $this);
						unset ($__refs, $__v); /* Unset defined __refs, __v. */
						/**/
						echo $args["after_widget"];
						/**/
						do_action ("ws_widget__super_tags_class_after_widget_display", get_defined_vars (), $this);
						/**/
						return; /* Return for uniformity. */
					}
				/*
				Widget form control function. This is where options are made configurable.
				*/
				public function form ($instance = FALSE)
					{
						$options = ws_widget__super_tags_configure_options_and_their_defaults (false, (array)$instance);
						/**/
						eval ('foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;');
						do_action ("ws_widget__super_tags_class_before_widget_form", get_defined_vars (), $this);
						unset ($__refs, $__v); /* Unset defined __refs, __v. */
						/*
						Ok, here is where we need to handle the widget control form. This allows a user to further customize the widget.
						*/
						echo '<label for="' . esc_attr ($this->get_field_id ("title")) . '">Title:</label><br />' . "\n";
						echo '<input class="widefat" id="' . esc_attr ($this->get_field_id ("title")) . '" name="' . esc_attr ($this->get_field_name ("title")) . '" type="text" value="' . format_to_edit ($options["title"]) . '" /><br /><br />' . "\n";
						/**/
						echo '<label for="' . esc_attr ($this->get_field_id ("smallest")) . '">Smallest Font Size ( optional / in pixels ):</label><br />' . "\n";
						echo '<input class="widefat" id="' . esc_attr ($this->get_field_id ("smallest")) . '" name="' . esc_attr ($this->get_field_name ("smallest")) . '" type="text" value="' . format_to_edit ($options["smallest"]) . '" /><br /><br />' . "\n";
						/**/
						echo '<label for="' . esc_attr ($this->get_field_id ("largest")) . '">Largest Font Size ( optional / in pixels ):</label><br />' . "\n";
						echo '<input class="widefat" id="' . esc_attr ($this->get_field_id ("largest")) . '" name="' . esc_attr ($this->get_field_name ("largest")) . '" type="text" value="' . format_to_edit ($options["largest"]) . '" /><br /><br />' . "\n";
						/**/
						echo '<label for="' . esc_attr ($this->get_field_id ("number")) . '">Max Tags To Display ( optional / number > 1 ):</label><br />' . "\n";
						echo '<input class="widefat" id="' . esc_attr ($this->get_field_id ("number")) . '" name="' . esc_attr ($this->get_field_name ("number")) . '" type="text" value="' . format_to_edit ($options["number"]) . '" /><br /><br />' . "\n";
						/**/
						echo '<label for="' . esc_attr ($this->get_field_id ("format")) . '">Format ( optional / flat or list ):</label><br />' . "\n";
						echo '<input class="widefat" id="' . esc_attr ($this->get_field_id ("format")) . '" name="' . esc_attr ($this->get_field_name ("format")) . '" type="text" value="' . format_to_edit ($options["format"]) . '" /><br /><br />' . "\n";
						/**/
						echo '<label for="' . esc_attr ($this->get_field_id ("orderby")) . '">Orderby ( optional / name or count ):</label><br />' . "\n";
						echo '<input class="widefat" id="' . esc_attr ($this->get_field_id ("orderby")) . '" name="' . esc_attr ($this->get_field_name ("orderby")) . '" type="text" value="' . format_to_edit ($options["orderby"]) . '" /><br /><br />' . "\n";
						/**/
						echo '<label for="' . esc_attr ($this->get_field_id ("order")) . '">Order ( optional / ASC, DESC, or RAND ):</label><br />' . "\n";
						echo '<input class="widefat" id="' . esc_attr ($this->get_field_id ("order")) . '" name="' . esc_attr ($this->get_field_name ("order")) . '" type="text" value="' . format_to_edit ($options["order"]) . '" /><br /><br />' . "\n";
						/**/
						echo '<label for="' . esc_attr ($this->get_field_id ("exclude")) . '">Exclude ( optional / comma separated term IDs ):</label><br />' . "\n";
						echo '<input class="widefat" id="' . esc_attr ($this->get_field_id ("exclude")) . '" name="' . esc_attr ($this->get_field_name ("exclude")) . '" type="text" value="' . format_to_edit ($options["exclude"]) . '" /><br /><br />' . "\n";
						/**/
						echo '<label for="' . esc_attr ($this->get_field_id ("include")) . '">Include ( optional / comma separated term IDs ):</label><br />' . "\n";
						echo '<input class="widefat" id="' . esc_attr ($this->get_field_id ("include")) . '" name="' . esc_attr ($this->get_field_name ("include")) . '" type="text" value="' . format_to_edit ($options["include"]) . '" /><br /><br />' . "\n";
						/**/
						echo '<label for="' . esc_attr ($this->get_field_id ("taxonomy")) . '">Taxonomy ( optional / post_tag, category, or link_category ):</label><br />' . "\n";
						echo '<input class="widefat" id="' . esc_attr ($this->get_field_id ("taxonomy")) . '" name="' . esc_attr ($this->get_field_name ("taxonomy")) . '" type="text" value="' . format_to_edit ($options["taxonomy"]) . '" /><br /><br />' . "\n";
						/**/
						echo 'For additional help with these parameters, please read the documentation for the <code><a href="http://codex.wordpress.org/Template_Tags/wp_tag_cloud" target="_blank">wp_tag_cloud() function</a></code>.';
						/**/
						do_action ("ws_widget__super_tags_class_after_widget_form", get_defined_vars (), $this);
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
						do_action ("ws_widget__super_tags_class_before_widget_update", get_defined_vars (), $this);
						unset ($__refs, $__v); /* Unset defined __refs, __v. */
						/**/
						$instance = (array)c_ws_widget__super_tags_utils_strings::trim_deep (stripslashes_deep ($instance));
						return ws_widget__super_tags_configure_options_and_their_defaults (false, $instance);
					}
			}
	}
?>