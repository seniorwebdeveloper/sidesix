<?php if (!defined('BASEPATH')) die('No direct script access allowed');

/**
 * Forms Module Tags
 *
 * @package			DevDemon_Forms
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2011 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com
 * @see				http://expressionengine.com/user_guide/development/module_tutorial.html#core_module_file
 */
class Forms
{

	/**
	 * Constructor
	 *
	 * @access public
	 *
	 * Calls the parent constructor
	 */
	public function __construct()
	{
		$this->EE =& get_instance();
		$this->EE->load->library('forms_helper');
		$this->EE->load->model('forms_model');
		$this->site_id = $this->EE->forms_helper->get_current_site_id();
		$this->EE->forms_helper->define_theme_url();
	}

	// ********************************************************************************* //

	public function form()
	{
		$this->EE->load->helper('form');

		// Some Standard Vars
		$form_errors = (isset($_POST['forms_global_errors']) == TRUE) ? $_POST['forms_global_errors'] : array();
		$field_errors = (isset($_POST['forms_errors']) == TRUE) ? $_POST['forms_errors'] : array();

		// Variable prefix
		$prefix = $this->EE->TMPL->fetch_param('prefix', 'forms') . ':';

		// -----------------------------------------
		// Form Name="" or ID?
		// -----------------------------------------
		if ($this->EE->TMPL->fetch_param('form_name') == TRUE OR $this->EE->TMPL->fetch_param('form_id') > 0)
		{
			if ($this->EE->TMPL->fetch_param('form_name') != FALSE)
			{
				$this->EE->db->where('form_url_title', $this->EE->TMPL->fetch_param('form_name'));
			}
			else
			{
				$this->EE->db->where('form_id', $this->EE->TMPL->fetch_param('form_id'));
			}
		}
		else
		{
			// -----------------------------------------
			// Do we have entry_id ?
			// -----------------------------------------
			$entry_id = $this->EE->forms_helper->get_entry_id_from_param();

			if (! $entry_id)
			{
				$this->EE->TMPL->log_item('FORMS: Entry ID could not be resolved (form_name=""/form_id="" either)');
				return $this->EE->forms_helper->custom_no_results_conditional($prefix.'no_form', $this->EE->TMPL->tagdata);
			}

			$this->EE->db->where('entry_id', $entry_id);
		}

		// -----------------------------------------
		// Grab the form
		// -----------------------------------------
		$this->EE->db->select('*');
		$this->EE->db->from('exp_forms');
		$this->EE->db->limit(1);
		$query = $this->EE->db->get();

		// Did we find anything?
		if ($query->num_rows() == 0)
		{
			$this->EE->TMPL->log_item('FORMS: No form has been found!');
			return $this->EE->forms_helper->custom_no_results_conditional($prefix.'no_form', $this->EE->TMPL->tagdata);
		}

		$form = $query->row_array();
		$form['form_settings'] = unserialize($form['form_settings']);

		// -----------------------------------------
		// Form Open?
		// -----------------------------------------
		if (isset($form['form_settings']['form_enabled']) == TRUE && $form['form_settings']['form_enabled'] != 'yes')
		{
			$this->EE->TMPL->log_item('FORMS: The form is closed! (Forms Settings)');
			return $this->EE->forms_helper->custom_no_results_conditional($prefix.'closed', $this->EE->TMPL->tagdata);
		}

		// Open FROM
		if (isset($form['form_settings']['open_fromto']['from']) == TRUE && $form['form_settings']['open_fromto']['from'] != FALSE)
		{
			$time = strtotime($form['form_settings']['open_fromto']['from'] . ' 01:01 AM');

			if ($time > $this->EE->localize->now)
			{
				$this->EE->TMPL->log_item('FORMS: The form is closed! (Forms Settings: Open FROM)');
				return $this->EE->forms_helper->custom_no_results_conditional($prefix.'closed', $this->EE->TMPL->tagdata);
			}
		}

		// Open TO
		if (isset($form['form_settings']['open_fromto']['to']) == TRUE && $form['form_settings']['open_fromto']['to'] != FALSE)
		{
			$time = strtotime($form['form_settings']['open_fromto']['to'] . ' 11:59 PM');

			if ($time < $this->EE->localize->now)
			{
				$this->EE->TMPL->log_item('FORMS: Form cannot be displayed (Member Group Restriction)');
				return $this->EE->forms_helper->custom_no_results_conditional($prefix.'no_form', $this->EE->TMPL->tagdata);
			}
		}

		// -----------------------------------------
		// Member Group Restriction?
		// -----------------------------------------
		if (isset($form['form_settings']['member_groups']) == TRUE && is_array($form['form_settings']['member_groups']) == TRUE && empty($form['form_settings']['member_groups']) == FALSE)
		{
			if ($this->EE->session->userdata('group_id') != 1 && in_array($this->EE->session->userdata('group_id'), $form['form_settings']['member_groups']) == FALSE)
			{
				$this->EE->TMPL->log_item('FORMS: The form is closed! (Forms Settings)');
				return $this->EE->forms_helper->custom_no_results_conditional($prefix.'closed', $this->EE->TMPL->tagdata);
			}
		}

		// -----------------------------------------
		// Return URL
		// -----------------------------------------
		$form['return'] = $this->EE->uri->uri_string();

		// Return Param?
		if ($this->EE->TMPL->fetch_param('return') != FALSE)
		{
			$form['return'] = $this->EE->TMPL->fetch_param('return');
		}

		// Form Settings Override?
		if (isset($form['form_settings']['return_url']) == TRUE && $form['form_settings']['return_url'] != FALSE)
		{
			$form['return'] = $form['form_settings']['return_url'];
		}

		// -----------------------------------------
		// Display Error
		// -----------------------------------------
		$form['display_error'] = 'default';

		if ($this->EE->TMPL->fetch_param('display_error') == 'inline')
		{
			$form['display_error'] = 'inline';
		}

		// -----------------------------------------
		// Grab the all fields
		// -----------------------------------------
		$this->EE->db->select('*');
		$this->EE->db->from('exp_forms_fields');
		$this->EE->db->where('form_id', $form['form_id']);
		$this->EE->db->order_by('field_order', 'ASC');
		$query = $this->EE->db->get();

		// Did we find anything?
		if ($query->num_rows() == 0)
		{
			$this->EE->TMPL->log_item('FORMS: No form fields has been associated.');
			return $this->EE->forms_helper->custom_no_results_conditional($prefix.'no_form', $this->EE->TMPL->tagdata);
		}

		// Store the DB fields
		$dbfields = $query->result_array();

		// -----------------------------------------
		// Find Pagebreaks
		// -----------------------------------------
		$pagebreaks = array();
		$page_count = 2;
		foreach ($dbfields as $key => $field)
		{
			// Is it a pagebreak?
			if ($field['field_type'] == 'pagebreak')
			{
				// Are they any other fields after it?
				if (isset($dbfields[ ($key+1) ]) == TRUE && $dbfields[ ($key+1) ]['field_type'] != 'pagebreak')
				{
					$pagebreaks[$page_count] = $field['field_id'];
					$page_count++;
					continue;
				}
			}
		}

		// -----------------------------------------
		// What Page are we on?
		// -----------------------------------------
		$form['paging'] = FALSE;
		$form['current_page']	= 1;
		$form['total_pages']	= count($pagebreaks);
		$form['fields_shown']	= array();
		$form['pages_left'] = $form['total_pages'] - $form['current_page'];

		if (isset($_POST['page']) == TRUE)
		{
			$form['current_page']	= $_POST['page'];
		}

		// How many pages left?
		$form['pages_left'] = ($form['total_pages']+1) - $form['current_page'];

		// -----------------------------------------
		// Remove all fields that don't belong to this page
		// -----------------------------------------
		if ($form['total_pages'] > 0)
		{
			$form['paging'] = TRUE;
			$this->EE->TMPL->log_item("FORMS: Pagebreaks Found. Total: {$form['total_pages']}");
			$this->EE->TMPL->log_item("FORMS: (Paging) Current Page: {$form['current_page']}");

			// There are two pathways,
			// 1. First page, loop over all fields until you find a pagebreak
			// 2. Delete all fields untill you find your id

			if ($form['current_page'] == 1)
			{
				$pagebreak_found = FALSE;
				$this->EE->TMPL->log_item("FORMS: (Paging) Looping over all fields. (START) | Pathway 1");

				foreach ($dbfields as $key => $field)
				{
					if ($pagebreak_found == TRUE OR $field['field_type'] == 'pagebreak')
					{
						if ($field['field_type'] == 'pagebreak') $this->EE->TMPL->log_item("FORMS: (Paging) Found pagebreak! Removing all future fields!");
						$pagebreak_found = TRUE;
						unset ($dbfields[$key]);
						continue;
					}

					$this->EE->TMPL->log_item("FORMS: (Paging) Adding Field to output (ID: {$field['field_id']}, Name: {$field['title']})");
					$form['fields_shown'][] = $field['field_id'];
				}

				$this->EE->TMPL->log_item("FORMS: (Paging) Looping over all fields. (END) | Pathway 1");
			}
			else
			{
				$pagebreak_id = $pagebreaks[$form['current_page']];
				$pagebreak_found = FALSE;
				$this->EE->TMPL->log_item("FORMS: (Paging) Looping over all fields. (START) | Pathway 2");

				foreach ($dbfields as $key => $field)
				{
					// Is this our pagebreak field_id?
					if ($field['field_id'] == $pagebreak_id)
					{
						// Mark it and remove it
						$this->EE->TMPL->log_item("FORMS: (Paging) Pagebreak of current page FOUND!");
						$pagebreak_found = TRUE;
						unset ($dbfields[$key]);
						continue;
					}

					// We didn't find our pagebreak yet, delete it
					if ($pagebreak_found == FALSE)
					{
						$this->EE->TMPL->log_item("FORMS: (Paging) Pagebreak not found, removing field. (ID: {$field['field_id']}, Name: {$field['title']})");
						unset ($dbfields[$key]);
						continue;
					}

					// Did we find our pagebreak? And is this a pagebreak?
					if ($pagebreak_found == TRUE && $field['field_type'] == 'pagebreak')
					{
						// Mark it, and delete all future ones!
						$this->EE->TMPL->log_item("FORMS: (Paging) Found next pagebreak! Removing all future fields!");
						$pagebreak_found = FALSe;
						unset ($dbfields[$key]);
						continue;
					}

					$this->EE->TMPL->log_item("FORMS: (Paging) Adding Field to output (ID: {$field['field_id']}, Name: {$field['title']})");
					$fields_shown[] = $field['field_id'];
				}

				$this->EE->TMPL->log_item("FORMS: (Paging) Looping over all fields. (END) | Pathway 2");
			}
		}
		else
		{
			$this->EE->TMPL->log_item("FORMS: (Paging) Field Added to output (ID: {$field['field_id']}, Name: {$field['title']})");
		}


		// -----------------------------------------
		// Parse Fields
		// -----------------------------------------
		$fields = array();

		foreach ($dbfields as $field)
		{
			$this->EE->TMPL->log_item('FORMS: Start Render: ' . $field['field_type']);

			// Grab our field settings
			$field['settings'] = @unserialize($field['field_settings']);

			// Our Form Name
			$field['form_name'] = 'fields[' . $field['field_id'] . ']';

			// Then Finally our form settings!
			$field['form_settings'] = $form['form_settings'];

			$field['html'] = $this->EE->formsfields[ $field['field_type'] ]->display_field($field, TRUE);

			// Add it to the array
			$fields[] = $field;
		}

		// -----------------------------------------
		// Submit Button
		// -----------------------------------------
		if ($this->EE->TMPL->fetch_param('output_submit') != 'no')
		{
			$submit_btn = '';

			// Default Button?
			if ($form['form_settings']['submit_button']['type'] == 'default')
			{
				if ($form['pages_left'] > 0)
				{
					$form['form_settings']['submit_button']['text'] = $form['form_settings']['submit_button']['text_next_page'];
				}

				$submit_btn	.= '<div class="dform_element submit_button"> <div class="dform_container"><div class="dfinput_full">';
				$submit_btn	.= '<input type="submit" class="submit" name="submit" value="'.$form['form_settings']['submit_button']['text'].'"/>';
				$submit_btn	.= '</div></div></div>';
			}

			// Image Button (also adds class="submit_button_image")
			else
			{
				if ($form['pages_left'] > 0)
				{
					$form['form_settings']['submit_button']['img_url'] = $form['form_settings']['submit_button']['img_url_next_page'];
				}

				$submit_btn	.= '<div class="dform_element submit_button submit_button_image"> <div class="dform_container"><div class="dfinput_full">';
				$submit_btn	.= '<input type="image" class="submit" name="submit" src="'.$form['form_settings']['submit_button']['img_url'].'"/>';
				$submit_btn	.= '</div></div></div>';
			}

			// Add it to the array
			$fields[] = array(
				'field_type' => 'submit_button',
				'field_id' => 0,
				'html' => $submit_btn
			);
		}

		// -----------------------------------------
		// Output CSS & JS?
		// -----------------------------------------
		$css_js = '';

		if ($this->EE->TMPL->fetch_param('output_css') != 'no' && isset($this->EE->session->cache['Forms']['CSS']) == FALSE)
		{
			$css_js .= '<link rel="stylesheet" href="' . FORMS_THEME_URL . 'forms_base.css" type="text/css" media="print, projection, screen" />';
			$this->EE->session->cache['Forms']['CSS'] = TRUE;
		}

		if ($this->EE->TMPL->fetch_param('output_js') != 'no' && isset($this->EE->session->cache['Forms']['JS']) == FALSE)
		{
			//$css_js .= '<link rel="stylesheet" href="' . FORMS_THEME_URL . 'forms_base.css" type="text/css" media="print, projection, screen" />';
			$this->EE->session->cache['Forms']['JS'] = TRUE;
		}

		// -----------------------------------------
		// Do we need to show the confirmation message?
		// -----------------------------------------
		if ($this->EE->session->flashdata('forms:show_confirm') == 'yes')
		{
			$css_js .= '<p class="dform_confirmation">' . $form['form_settings']['confirmation']['text'] . '</p>';
		}

		// -----------------------------------------
		// Form Action URL
		// -----------------------------------------
		$action_url = $this->EE->functions->fetch_current_uri();

		// Last Slash?
		if (substr($_SERVER['REQUEST_URI'], -1, 1) == '/') $action_url .= '/';

		// Store the formdata
		$FPID = $this->EE->forms_model->store_form_data($form);

		// -----------------------------------------
		// Hidden Fields
		// -----------------------------------------
		$hidden_fields = array();
		$hidden_fields['ACT'] = $this->EE->forms_helper->get_router_url('act_id', 'ACT_form_submission');
		$hidden_fields['FPID'] = $FPID;
		$hidden_fields['XID'] = (isset($_POST['XID']) == TRUE) ? $_POST['XID'] : '';

		// -----------------------------------------
		// Store Paging?
		// -----------------------------------------
		if (isset($form['paging']) && $form['paging'] == TRUE && isset($_POST['fields']))
		{
			foreach ($_POST['fields'] as $field_id => $val)
			{
				// Is it an array?
				if (is_array($val) === TRUE)
				{
					// Loop
					foreach ($val as $key => $val_sub)
					{
						// store it
						$hidden_fields["fields[{$field_id}][{$key}]"] = $val_sub;
					}

					continue;
				}

				// Simle field, just store it
				$hidden_fields["fields[{$field_id}]"] = $val;
			}
		}

		//----------------------------------------
		// <form> Data!
		//----------------------------------------
		$formdata = array();
		$formdata['enctype'] = 'multi';
		$formdata['hidden_fields'] = $hidden_fields;
		$formdata['action']	= $action_url;
		$formdata['name']	= '';
		$formdata['id']		= ($this->EE->TMPL->fetch_param('attr:id') != FALSE) ? $this->EE->TMPL->fetch_param('attr:id'): 'new_submission';
		$formdata['class']	= ($this->EE->TMPL->fetch_param('attr:class') != FALSE) ? $this->EE->TMPL->fetch_param('attr:class'): '';
		$formdata['onsubmit'] = ($this->EE->TMPL->fetch_param('attr:onsubmit') != FALSE) ? $this->EE->TMPL->fetch_param('attr:onsubmit'): '';


		$OUT = '';
		$OUT_FORM_PREPEND = '';

		//----------------------------------------
		// Snaptcha
		//----------------------------------------
		if ( isset($form['form_settings']['snaptcha']) == TRUE && $form['form_settings']['snaptcha'] == 'yes')
		{
			// Does the file exist?
			if (isset($this->EE->extensions->version_numbers['Snaptcha_ext']) == TRUE)
			{
				require_once(PATH_THIRD.'snaptcha/ext.snaptcha.php');
				$SNAP = new Snaptcha_ext();

				// We need to find the settings
				foreach ($this->EE->extensions->extensions['insert_comment_start'] as $priority => $exts)
				{
					// Loop over all extension
					foreach ($exts as $name => $ext)
					{
						if ($name == 'Snaptcha_ext')
						{
							// Store the Snaptcha field settings
							$SNAP->settings = unserialize($ext[1]);
						}
					}
				}

				// Append the field!
				$OUT_FORM_PREPEND .= $SNAP->comment_field($OUT_FORM_PREPEND);
			}
		}

		// -----------------------------------------
		// Single Tag Pair?
		// -----------------------------------------
		if ($this->EE->TMPL->tagdata == FALSE)
		{
			$fdata = '';
			foreach ($fields as $field) $fdata .= $field['html'];

			// -----------------------------------------
			// Contruct Final Output
			// -----------------------------------------
			$OUT = $css_js;
			$OUT .= $this->EE->functions->form_declaration($formdata);
			$OUT .= $OUT_FORM_PREPEND;
			$OUT .= '<div class="dform">' . $fdata . '</div>';
			$OUT .= '</form>';

			$this->return_data = $OUT;
			return $OUT;
		}

		// -----------------------------------------
		// {forms:fields} tag pair exists?
		// -----------------------------------------
		if (strpos($this->EE->TMPL->tagdata, LD.'/'.$prefix.'fields'.RD) === FALSE)
		{
			$this->EE->TMPL->log_item('FORMS: The fields variable pair was not found! ');
			return $this->EE->forms_helper->custom_no_results_conditional($prefix.'no_form', $this->EE->TMPL->tagdata);
		}

		$pair_data = $this->EE->forms_helper->fetch_data_between_var_pairs($prefix.'fields', $this->EE->TMPL->tagdata);

		// -----------------------------------------
		// Loop over all fields!
		// -----------------------------------------
		$final = '<div class="dform">';

		foreach ($fields as $count => $field)
		{
			$temp = '';
			$vars = array();
			$vars[$prefix.'field'] = $field['html'];
			$vars[$prefix.'field_type'] = $field['field_type'];

			$temp = $this->EE->TMPL->parse_variables_row($pair_data, $vars);
			$final .= $temp;
		}

		$final .= '</div> <!-- /dform -->';

		// Swap it back!
		$this->EE->TMPL->tagdata = $this->EE->forms_helper->swap_var_pairs($prefix.'fields', $final, $this->EE->TMPL->tagdata);

		// -----------------------------------------
		// Parse {forms:form_errors}
		// -----------------------------------------
		if (strpos($this->EE->TMPL->tagdata, LD.'/'.$prefix.'form_errors'.RD) !== FALSE)
		{
			$pair_data = $this->EE->forms_helper->fetch_data_between_var_pairs($prefix.'form_errors', $this->EE->TMPL->tagdata);
			$final = '';

			foreach ($form_errors as $count => $error)
			{
				$temp = '';
				$vars = array();
				$vars[$prefix.'error'] = $error['msg'];
				$vars[$prefix.'error_type'] = $error['type'];
				$vars[$prefix.'error_count'] = $count + 1;

				$temp = $this->EE->TMPL->parse_variables_row($pair_data, $vars);
				$final .= $temp;
			}

			// Swap it back!
			$this->EE->TMPL->tagdata = $this->EE->forms_helper->swap_var_pairs($prefix.'form_errors', $final, $this->EE->TMPL->tagdata);
		}

		// -----------------------------------------
		// Parse Form Variables
		// -----------------------------------------
		$vars = array();
		$vars[$prefix.'form_id']         = $form['form_id'];
		$vars[$prefix.'label']           = $form['form_title'];
		$vars[$prefix.'short_name']      = $form['form_url_title'];
		$vars[$prefix.'entry_id']        = $form['entry_id'];
		$vars[$prefix.'channel_id']      = $form['channel_id'];
		$vars[$prefix.'ee_field_id']     = $form['ee_field_id'];
		$vars[$prefix.'member_id']       = $form['member_id'];
		$vars[$prefix.'date_created']    = $form['date_created'];
		$vars[$prefix.'date_last_entry'] = $form['date_last_entry'];
		$vars[$prefix.'total_entries']   = $form['total_submissions'];
		$vars[$prefix.'current_page']    = $form['current_page'];
		$vars[$prefix.'total_pages']     = $form['total_pages']+1;

		$vars[$prefix.'paged']		     = ($form['total_pages'] > 0) ? 'yes' : '';
		$vars[$prefix.'total_form_errors'] = count($form_errors);
		$vars[$prefix.'total_field_errors'] = count($field_errors);

		$this->EE->TMPL->tagdata = $this->EE->TMPL->parse_variables_row($this->EE->TMPL->tagdata, $vars);

		// -----------------------------------------
		// Contruct Final Output
		// -----------------------------------------
		$OUT = $css_js;
		$OUT .= $this->EE->functions->form_declaration($formdata);
		$OUT .= $OUT_FORM_PREPEND;
		$OUT .= $this->EE->TMPL->tagdata;
		$OUT .= '</form>';


		return $OUT;
	}

	// ********************************************************************************* //

	public function ACT_general_router()
	{
		// -----------------------------------------
		// Ajax Request?
		// -----------------------------------------
		if ( $this->EE->input->get_post('ajax_method') != FALSE OR (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') )
		{
			// Load Library
			if (class_exists('forms_AJAX') != TRUE) include 'ajax.forms.php';

			$AJAX = new forms_AJAX();

			// Shoot the requested method
			$method = $this->EE->input->get_post('ajax_method');
			echo $AJAX->$method();
			exit();
		}

		exit('CHANNEL FORMS ACT!');

	}

	// ********************************************************************************* //

	public function ACT_form_submission()
	{
		// Load Library
		if (class_exists('Forms_ACT') != TRUE) include 'act.forms.php';

		$ACT = new Forms_ACT();

		$ACT->form_submission();
	}

	// ********************************************************************************* //


} // END CLASS

/* End of file mod.forms.php */
/* Location: ./system/expressionengine/third_party/forms/mod.forms.php */