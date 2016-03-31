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
Template Name: Contact Form ( displays contact form )
*/
if (realpath (__FILE__) === realpath ($_SERVER["SCRIPT_FILENAME"]))
	exit ("Do not access this file directly.");
/*
Check theme compatibility.
*/
if ($GLOBALS["WS_THEME__"]["compatible"])
	{
		if (ob_start ()) /* Get parent template. */
			{
				include_once TEMPLATEPATH . "/" . apply_filters ("ws_theme__contact_form_parent_template", "fullpage.x.php");
				/*
				Store the contents of parent template.
				*/
				$ws_theme__temp_s_parent_template = ob_get_contents ();
				/*
				Close the output buffer.
				*/
				ob_end_clean ();
			}
		/*
		Now lets build the custom page content.
		*/
		if (! ($ws_theme__temp_s_custom = "") && apply_filters ("ws_theme__display_contact_form", true) && ob_start ())
			{
				$ws_theme__temp_a_p = c_ws_theme__utils_strings::trim_deep (stripslashes_deep ($_POST));
				/**/
				do_action ("ws_theme__before_contact_form");
				/*
				Build the contact form.
				*/
				echo '<form id="contact-form" class="contact-form" method="post">' . "\n";
				/*
				Hidden input field that triggers form processing.
				*/
				echo '<input type="hidden" name="ws_theme__contact_form" value="1" />' . "\n";
				/*
				Hidden input field that dicates who the form will go to. If not specificed, this will default to the administrative email for WordPress®.
				*/
				echo '<input type="hidden" name="ws_theme__contact_form_to" value="' . esc_attr (c_ws_theme__utils_encryption::encrypt (get_post_meta (get_the_ID (), "contact_form_to", true))) . '" />' . "\n";
				/*
				This is where we process form.
				*/
				if ($ws_theme__temp_a_p["ws_theme__contact_form"]) /* Process?. */
					{ /* Any custom fields injected by Hooks will be included automatically.
						Custom field names should always start with `ws_theme__contact_form_`. */
						echo '<div id="contact-form-response" class="contact-form-response">' . "\n";
						echo c_ws_theme__contact_forms::process_contact_form ($ws_theme__temp_a_p) . "\n";
						echo '</div>' . "\n";
						/**/
						if (did_action ("ws_theme__during_process_contact_form_success"))
							$_POST = $ws_theme__temp_a_p = array ();
					}
				/*
				Hook before form fields.
				Custom fields should start with `ws_theme__contact_form_`.
				*/
				do_action ("ws_theme__during_contact_form_before_fields");
				/*
				Ask visitor for their full name.
				*/
				if (apply_filters ("ws_theme__during_contact_form_during_fields_display_name", true))
					{
						do_action ("ws_theme__during_contact_form_during_fields_before_name");
						/**/
						echo '<div id="contact-form-name" class="contact-form-name">' . "\n";
						echo '<input type="text" name="ws_theme__contact_form_name" value="' . format_to_edit ($ws_theme__temp_a_p["ws_theme__contact_form_name"]) . '" title="Your Full Name" tabindex="10" />' . "\n";
						echo '</div>' . "\n";
						/**/
						do_action ("ws_theme__during_contact_form_during_fields_after_name");
					}
				/*
				Ask visitor for their email address.
				*/
				if (apply_filters ("ws_theme__during_contact_form_during_fields_display_email", true))
					{
						do_action ("ws_theme__during_contact_form_during_fields_before_email");
						/**/
						echo '<div id="contact-form-email" class="contact-form-email">' . "\n";
						echo '<input type="text" name="ws_theme__contact_form_email" value="' . format_to_edit ($ws_theme__temp_a_p["ws_theme__contact_form_email"]) . '" title="Your EMail Address" tabindex="20" />' . "\n";
						echo '</div>' . "\n";
						/**/
						do_action ("ws_theme__during_contact_form_during_fields_after_email");
					}
				/*
				Ask visitor to supply an email subject line.
				*/
				if (apply_filters ("ws_theme__during_contact_form_during_fields_display_subject", true))
					{
						do_action ("ws_theme__during_contact_form_during_fields_before_subject");
						/**/
						echo '<div id="contact-form-subject" class="contact-form-subject">' . "\n";
						echo '<input type="text" name="ws_theme__contact_form_subject" value="' . format_to_edit ($ws_theme__temp_a_p["ws_theme__contact_form_subject"]) . '" title="EMail Subject Line" tabindex="30" />' . "\n";
						echo '</div>' . "\n";
						/**/
						do_action ("ws_theme__during_contact_form_during_fields_after_subject");
					}
				/*
				Ask visitor to write a message.
				*/
				if (apply_filters ("ws_theme__during_contact_form_during_fields_display_message", true))
					{
						do_action ("ws_theme__during_contact_form_during_fields_before_message");
						/**/
						echo '<div id="contact-form-message" class="contact-form-message">' . "\n";
						echo '<textarea name="ws_theme__contact_form_message" cols="100%" rows="10" title="Please type your message here..." tabindex="40">' . format_to_edit ($ws_theme__temp_a_p["ws_theme__contact_form_message"]) . '</textarea>' . "\n";
						echo '</div>' . "\n";
						/**/
						do_action ("ws_theme__during_contact_form_during_fields_after_message");
					}
				/*
				Hook after form fields.
				Custom fields should start with `ws_theme__contact_form_`.
				*/
				do_action ("ws_theme__during_contact_form_after_fields");
				/*
				Contact form submit button.
				*/
				echo '<div id="contact-form-submit" class="contact-form-submit">' . "\n";
				echo '<input type="submit" value="Submit Form" tabindex="50" />' . "\n";
				echo '</div>' . "\n";
				/*
				Close form tag.
				*/
				echo '</form>' . "\n";
				/*
				Hook after contact form.
				*/
				do_action ("ws_theme__after_contact_form");
				/*
				Store custom content and close buffer.
				*/
				$ws_theme__temp_s_custom = ob_get_contents ();
				/*
				Close the output buffer.
				*/
				ob_end_clean ();
			}
		/*
		Now lets put it all together with custom content.
		*/
		echo preg_replace ("/\<\!-- custom-content-after --\>/", $ws_theme__temp_s_custom, $ws_theme__temp_s_parent_template);
		/**/
		unset ($ws_theme__temp_s_custom, $ws_theme__temp_s_parent_template, $ws_theme__temp_a_p); /* Conserve memory; dump these now. */
	}
?>