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
if (!class_exists ("c_ws_widget__ad_squares_class"))
	{
		class c_ws_widget__ad_squares_class /* < Register this widget class. */
			extends WP_Widget /* See: /wp-includes/widgets.php for further details. */
			{
				public function c_ws_widget__ad_squares_class () /* Builds the classname, id_base, description, etc. */
					{
						$widget_ops = array ("classname" => "ad-squares", "description" => $GLOBALS["WS_WIDGET__"]["ad_squares"]["c"]["description"]);
						$control_ops = array ("width" => $GLOBALS["WS_WIDGET__"]["ad_squares"]["c"]["control_w"], "id_base" => "ws_widget__ad_squares");
						/**/
						eval ('foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;');
						do_action ("ws_widget__ad_squares_class_before_widget_construction", get_defined_vars (), $this);
						unset ($__refs, $__v); /* Unset defined __refs, __v. */
						/**/
						$this->WP_Widget ($control_ops["id_base"], $GLOBALS["WS_WIDGET__"]["ad_squares"]["c"]["name"], $widget_ops, $control_ops);
						/**/
						do_action ("ws_widget__ad_squares_class_after_widget_construction", get_defined_vars (), $this);
						/**/
						return; /* Return for uniformity. */
					}
				/*
				Widget display function. This is where the widget actually does something.
				*/
				public function widget ($args = FALSE, $instance = FALSE)
					{
						$options = ws_widget__ad_squares_configure_options_and_their_defaults (false, (array)$instance);
						/**/
						eval ('foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;');
						do_action ("ws_widget__ad_squares_class_before_widget_display", get_defined_vars (), $this);
						unset ($__refs, $__v); /* Unset defined __refs, __v. */
						/**/
						echo $args["before_widget"]; /* Ok, here we go into this widget.
						/**/
						if (strlen ($options["title"])) /* If there is. */
							echo $args["before_title"] . apply_filters ("widget_title", $options["title"]) . $args["after_title"];
						/**/
						$options["code_1"] = preg_split ("/\<\!--rotate--\>/", $options["code_1"]);
						$options["code_2"] = preg_split ("/\<\!--rotate--\>/", $options["code_2"]);
						$options["code_3"] = preg_split ("/\<\!--rotate--\>/", $options["code_3"]);
						$options["code_4"] = preg_split ("/\<\!--rotate--\>/", $options["code_4"]);
						$options["code_5"] = preg_split ("/\<\!--rotate--\>/", $options["code_5"]);
						$options["code_6"] = preg_split ("/\<\!--rotate--\>/", $options["code_6"]);
						$options["code_7"] = preg_split ("/\<\!--rotate--\>/", $options["code_7"]);
						$options["code_8"] = preg_split ("/\<\!--rotate--\>/", $options["code_8"]);
						/**/
						shuffle ($options["code_1"]) . shuffle ($options["code_2"]) . shuffle ($options["code_3"]) . shuffle ($options["code_4"]);
						shuffle ($options["code_5"]) . shuffle ($options["code_6"]) . shuffle ($options["code_7"]) . shuffle ($options["code_8"]);
						/**/
						$codes = array (); /* Initialize the array of codes so we can push. */
						/**/
						if ($options["squares"] >= 2) /* Push codes 1 and 2 onto array. */
							array_push ($codes, $options["code_1"][0], $options["code_2"][0]);
						/**/
						if ($options["squares"] >= 4) /* Push codes 3 and 4 onto array. */
							array_push ($codes, $options["code_3"][0], $options["code_4"][0]);
						/**/
						if ($options["squares"] >= 6) /* Push codes 5 and 6 onto array. */
							array_push ($codes, $options["code_5"][0], $options["code_6"][0]);
						/**/
						if ($options["squares"] >= 8) /* Push codes 7 and 8 onto array. */
							array_push ($codes, $options["code_7"][0], $options["code_8"][0]);
						/**/
						if ($options["shuffle"]) /* Shuffle? ( problems w/ cache engines. ) */
							shuffle ($codes); /* This way they show up differently each time.
							/**/
						eval ('foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;');
						do_action ("ws_widget__ad_squares_class_during_widget_display_before", get_defined_vars (), $this);
						unset ($__refs, $__v); /* Unset defined __refs, __v. */
						/**/
						echo '<div style="margin:0 auto 0 auto; text-align:center;">' . "\n";
						echo '<table cellpadding="0" cellspacing="0" style="border:0; margin:0 auto 0 auto;">' . "\n";
						echo '<tbody>' . "\n";
						/**/
						if ($options["squares"] >= 2)
							echo '<tr>' . "\n" . /* If they have chosen to show 2 or more ad squares here. */
							'<td style="padding:0 ' . esc_attr ($options["padding"]) . 'px 0 0;">', ( (c_ws_widget__ad_squares_utils_conds::is_multisite_farm ()) ? do_shortcode (trim ($codes[0])) : do_shortcode (c_ws_widget__ad_squares_utilities::evl (trim ($codes[0])))), '</td>' . "\n" ./**/
							'<td style="padding:0 0 0 0;">', ( (c_ws_widget__ad_squares_utils_conds::is_multisite_farm ()) ? do_shortcode (trim ($codes[1])) : do_shortcode (c_ws_widget__ad_squares_utilities::evl (trim ($codes[1])))), '</td>' . "\n" ./**/
							'</tr>' . "\n";
						/**/
						if ($options["squares"] >= 4)
							echo '<tr>' . "\n" . /* If they have chosen to show 4 or more ad squares here. */
							'<td style="padding:' . esc_attr ($options["padding"]) . 'px ' . esc_attr ($options["padding"]) . 'px 0 0;">', ( (c_ws_widget__ad_squares_utils_conds::is_multisite_farm ()) ? do_shortcode (trim ($codes[2])) : do_shortcode (c_ws_widget__ad_squares_utilities::evl (trim ($codes[2])))), '</td>' . "\n" ./**/
							'<td style="padding:' . esc_attr ($options["padding"]) . 'px 0 0 0;">', ( (c_ws_widget__ad_squares_utils_conds::is_multisite_farm ()) ? do_shortcode (trim ($codes[3])) : do_shortcode (c_ws_widget__ad_squares_utilities::evl (trim ($codes[3])))), '</td>' . "\n" ./**/
							'</tr>' . "\n";
						/**/
						if ($options["squares"] >= 6)
							echo '<tr>' . "\n" . /* If they have chosen to show 6 or more ad squares here. */
							'<td style="padding:' . esc_attr ($options["padding"]) . 'px ' . esc_attr ($options["padding"]) . 'px 0 0;">', ( (c_ws_widget__ad_squares_utils_conds::is_multisite_farm ()) ? do_shortcode (trim ($codes[4])) : do_shortcode (c_ws_widget__ad_squares_utilities::evl (trim ($codes[4])))), '</td>' . "\n" ./**/
							'<td style="padding:' . esc_attr ($options["padding"]) . 'px 0 0 0;">', ( (c_ws_widget__ad_squares_utils_conds::is_multisite_farm ()) ? do_shortcode (trim ($codes[5])) : do_shortcode (c_ws_widget__ad_squares_utilities::evl (trim ($codes[5])))), '</td>' . "\n" ./**/
							'</tr>' . "\n";
						/**/
						if ($options["squares"] >= 8)
							echo '<tr>' . "\n" . /* If they have chosen to show 8 or more ad squares here. */
							'<td style="padding:' . esc_attr ($options["padding"]) . 'px ' . esc_attr ($options["padding"]) . 'px 0 0;">', ( (c_ws_widget__ad_squares_utils_conds::is_multisite_farm ()) ? do_shortcode (trim ($codes[6])) : do_shortcode (c_ws_widget__ad_squares_utilities::evl (trim ($codes[6])))), '</td>' . "\n" ./**/
							'<td style="padding:' . esc_attr ($options["padding"]) . 'px 0 0 0;">', ( (c_ws_widget__ad_squares_utils_conds::is_multisite_farm ()) ? do_shortcode (trim ($codes[7])) : do_shortcode (c_ws_widget__ad_squares_utilities::evl (trim ($codes[7])))), '</td>' . "\n" ./**/
							'</tr>' . "\n";
						/**/
						echo '</tbody>' . "\n";
						echo '</table>' . "\n";
						echo '</div>' . "\n";
						/**/
						eval ('foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;');
						do_action ("ws_widget__ad_squares_class_during_widget_display_after", get_defined_vars (), $this);
						unset ($__refs, $__v); /* Unset defined __refs, __v. */
						/**/
						echo $args["after_widget"];
						/**/
						do_action ("ws_widget__ad_squares_class_after_widget_display", get_defined_vars (), $this);
						/**/
						return; /* Return for uniformity. */
					}
				/*
				Widget form control function. This is where options are made configurable.
				*/
				public function form ($instance = FALSE)
					{
						$options = ws_widget__ad_squares_configure_options_and_their_defaults (false, (array)$instance);
						/**/
						eval ('foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;');
						do_action ("ws_widget__ad_squares_class_before_widget_form", get_defined_vars (), $this);
						unset ($__refs, $__v); /* Unset defined __refs, __v. */
						/*
						Ok, here is where we need to handle the widget control form. This allows a user to further customize the widget.
						*/
						echo '<label for="' . esc_attr ($this->get_field_id ("title")) . '">Title:</label><br />' . "\n";
						echo '<input class="widefat" id="' . esc_attr ($this->get_field_id ("title")) . '" name="' . esc_attr ($this->get_field_name ("title")) . '" type="text" value="' . format_to_edit ($options["title"]) . '" /><br /><br />' . "\n";
						/**/
						echo '<label for="' . esc_attr ($this->get_field_id ("squares")) . '">Squares:</label>' . "\n";
						echo '<select id="' . esc_attr ($this->get_field_id ("squares")) . '" name="' . esc_attr ($this->get_field_name ("squares")) . '" onchange="this.v = jQuery(this).val(); this.trs = new Object(); this.trs[\'2\'] = jQuery(\'table#ws-widget--ad-squares-table-' . esc_attr ($this->number) . ' tr.ws-widget--ad-squares-tr2\'); this.trs[\'4\'] = jQuery(\'table#ws-widget--ad-squares-table-' . esc_attr ($this->number) . ' tr.ws-widget--ad-squares-tr4\'); this.trs[\'6\'] = jQuery(\'table#ws-widget--ad-squares-table-' . esc_attr ($this->number) . ' tr.ws-widget--ad-squares-tr6\'); this.trs[\'8\'] = jQuery(\'table#ws-widget--ad-squares-table-' . esc_attr ($this->number) . ' tr.ws-widget--ad-squares-tr8\'); if(this.v >= 2){ this.trs[\'2\'].css(\'display\', \'\'); } else { this.trs[\'2\'].hide(); } if(this.v >= 4){ this.trs[\'4\'].css(\'display\', \'\'); } else { this.trs[\'4\'].hide(); } if(this.v >= 6){ this.trs[\'6\'].css(\'display\', \'\'); } else { this.trs[\'6\'].hide(); } if(this.v >= 8){ this.trs[\'8\'].css(\'display\', \'\'); } else { this.trs[\'8\'].hide(); }">' . "\n";
						echo '<option value="2"' . ( ($options["squares"] == 2) ? ' selected="selected"' : '') . '>2</option>' . "\n";
						echo '<option value="4"' . ( ($options["squares"] == 4) ? ' selected="selected"' : '') . '>4</option>' . "\n";
						echo '<option value="6"' . ( ($options["squares"] == 6) ? ' selected="selected"' : '') . '>6</option>' . "\n";
						echo '<option value="8"' . ( ($options["squares"] == 8) ? ' selected="selected"' : '') . '>8</option>' . "\n";
						echo '</select>' . "\n";
						/**/
						echo '<label for="' . esc_attr ($this->get_field_id ("padding")) . '">Padding:</label>' . "\n";
						echo '<select id="' . esc_attr ($this->get_field_id ("padding")) . '" name="' . esc_attr ($this->get_field_name ("padding")) . '">' . "\n";
						echo '<option value="5"' . ( ($options["padding"] == 5) ? ' selected="selected"' : '') . '>5px</option>' . "\n";
						echo '<option value="10"' . ( ($options["padding"] == 10) ? ' selected="selected"' : '') . '>10px</option>' . "\n";
						echo '<option value="15"' . ( ($options["padding"] == 15) ? ' selected="selected"' : '') . '>15px</option>' . "\n";
						echo '<option value="20"' . ( ($options["padding"] == 20) ? ' selected="selected"' : '') . '>20px</option>' . "\n";
						echo '<option value="25"' . ( ($options["padding"] == 25) ? ' selected="selected"' : '') . '>25px</option>' . "\n";
						echo '<option value="30"' . ( ($options["padding"] == 30) ? ' selected="selected"' : '') . '>30px</option>' . "\n";
						echo '<option value="35"' . ( ($options["padding"] == 35) ? ' selected="selected"' : '') . '>35px</option>' . "\n";
						echo '<option value="40"' . ( ($options["padding"] == 40) ? ' selected="selected"' : '') . '>40px</option>' . "\n";
						echo '<option value="45"' . ( ($options["padding"] == 45) ? ' selected="selected"' : '') . '>45px</option>' . "\n";
						echo '<option value="50"' . ( ($options["padding"] == 50) ? ' selected="selected"' : '') . '>50px</option>' . "\n";
						echo '</select><br /><br />' . "\n";
						/**/
						echo '<strong>Paste Code Into These Squares:</strong><br />' . "\n";
						echo '<small style="display:block; text-align:justify;">' . "\n";
						echo 'Feel free to paste any type of ad code into the squares. AdSense®, XHTML' . ( (c_ws_widget__ad_squares_utils_conds::is_multisite_farm ()) ? '' : ', PHP') . ', IFrame, JavaScript, whatever. The recommended size is 125x125, but it does not have to be that exact size. Just be sure to stick with the same size in each square so things remain symmetrical.' . "\n";
						echo '</small><br />' . "\n"; /* Try to provide labels for each form element. */
						/**/
						echo '<table cellpadding="0" cellspacing="0" border="0" id="ws-widget--ad-squares-table-' . esc_attr ($this->number) . '">' . "\n";
						echo '<tbody>' . "\n";
						/**/
						echo '<tr class="ws-widget--ad-squares-tr2" style="display:' . ( ($options["squares"] >= 2) ? '' : 'none') . ';">' . "\n";
						echo '<td style="padding:0 15px 0 0;">';
						echo '<textarea class="widefat" style="width:125px; height:125px;" rows="1" cols="1" id="' . esc_attr ($this->get_field_id ("code_1")) . '" name="' . esc_attr ($this->get_field_name ("code_1")) . '">' . format_to_edit ($options["code_1"]) . '</textarea>' . "\n";
						echo '</td>' . "\n";
						echo '<td style="padding:0 0 0 0;">';
						echo '<textarea class="widefat" style="width:125px; height:125px;" rows="1" cols="1" id="' . esc_attr ($this->get_field_id ("code_2")) . '" name="' . esc_attr ($this->get_field_name ("code_2")) . '">' . format_to_edit ($options["code_2"]) . '</textarea>' . "\n";
						echo '</td>' . "\n";
						echo '</tr>' . "\n";
						/**/
						echo '<tr class="ws-widget--ad-squares-tr4" style="display:' . ( ($options["squares"] >= 4) ? '' : 'none') . ';">' . "\n";
						echo '<td style="padding:15px 15px 0 0;">' . "\n";
						echo '<textarea class="widefat" style="width:125px; height:125px;" rows="1" cols="1" id="' . esc_attr ($this->get_field_id ("code_3")) . '" name="' . esc_attr ($this->get_field_name ("code_3")) . '">' . format_to_edit ($options["code_3"]) . '</textarea>' . "\n";
						echo '</td>' . "\n";
						echo '<td style="padding:15px 0 0 0;">' . "\n";
						echo '<textarea class="widefat" style="width:125px; height:125px;" rows="1" cols="1" id="' . esc_attr ($this->get_field_id ("code_4")) . '" name="' . esc_attr ($this->get_field_name ("code_4")) . '">' . format_to_edit ($options["code_4"]) . '</textarea>' . "\n";
						echo '</td>' . "\n";
						echo '</tr>' . "\n";
						/**/
						echo '<tr class="ws-widget--ad-squares-tr6" style="display:' . ( ($options["squares"] >= 6) ? '' : 'none') . ';">' . "\n";
						echo '<td style="padding:15px 15px 0 0;">' . "\n";
						echo '<textarea class="widefat" style="width:125px; height:125px;" rows="1" cols="1" id="' . esc_attr ($this->get_field_id ("code_5")) . '" name="' . esc_attr ($this->get_field_name ("code_5")) . '">' . format_to_edit ($options["code_5"]) . '</textarea>' . "\n";
						echo '</td>' . "\n";
						echo '<td style="padding:15px 0 0 0;">' . "\n";
						echo '<textarea class="widefat" style="width:125px; height:125px;" rows="1" cols="1" id="' . esc_attr ($this->get_field_id ("code_6")) . '" name="' . esc_attr ($this->get_field_name ("code_6")) . '">' . format_to_edit ($options["code_6"]) . '</textarea>' . "\n";
						echo '</td>' . "\n";
						echo '</tr>' . "\n";
						/**/
						echo '<tr class="ws-widget--ad-squares-tr8" style="display:' . ( ($options["squares"] >= 8) ? '' : 'none') . ';">' . "\n";
						echo '<td style="padding:15px 15px 0 0;">' . "\n";
						echo '<textarea class="widefat" style="width:125px; height:125px;" rows="1" cols="1" id="' . esc_attr ($this->get_field_id ("code_7")) . '" name="' . esc_attr ($this->get_field_name ("code_7")) . '">' . format_to_edit ($options["code_7"]) . '</textarea>' . "\n";
						echo '</td>' . "\n";
						echo '<td style="padding:15px 0 0 0;">' . "\n";
						echo '<textarea class="widefat" style="width:125px; height:125px;" rows="1" cols="1" id="' . esc_attr ($this->get_field_id ("code_8")) . '" name="' . esc_attr ($this->get_field_name ("code_8")) . '">' . format_to_edit ($options["code_8"]) . '</textarea>' . "\n";
						echo '</td>' . "\n";
						echo '</tr>' . "\n";
						/**/
						echo '</tbody>' . "\n";
						echo '</table><br />' . "\n";
						/**/
						echo '<label for="' . esc_attr ($this->get_field_id ("shuffle")) . '">Randomly Shuffle Positions:</label>' . "\n";
						echo '<select id="' . esc_attr ($this->get_field_id ("shuffle")) . '" name="' . esc_attr ($this->get_field_name ("shuffle")) . '">' . "\n";
						echo '<option value="0"' . ( (!$options["shuffle"]) ? ' selected="selected"' : '') . '>No</option>' . "\n";
						echo '<option value="1"' . ( ($options["shuffle"]) ? ' selected="selected"' : '') . '>Yes</option>' . "\n";
						echo '</select><br />' . "\n";
						/**/
						echo '<small style="display:block; text-align:justify;">' . "\n";
						echo 'OR: If you would like to rotate a few different ad codes ( inside a particular square ), just insert this special tag: ' . esc_html ("<!--rotate-->") . ' between every ad code. In other words, one or all of your squares can contain multiple ad codes separated by this rotation tag.' . "\n";
						echo '</small>' . "\n";
						/**/
						do_action ("ws_widget__ad_squares_class_after_widget_form", get_defined_vars (), $this);
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
						do_action ("ws_widget__ad_squares_class_before_widget_update", get_defined_vars (), $this);
						unset ($__refs, $__v); /* Unset defined __refs, __v. */
						/**/
						$instance = (array)c_ws_widget__ad_squares_utils_strings::trim_deep (stripslashes_deep ($instance));
						return ws_widget__ad_squares_configure_options_and_their_defaults (false, $instance);
					}
			}
	}
?>