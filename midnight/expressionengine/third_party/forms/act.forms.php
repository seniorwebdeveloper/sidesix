<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Forms ACT File
 *
 * @package			DevDemon_Forms
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2010 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com/forms/
 */
class Forms_ACT
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
		$this->site_id = $this->EE->config->item('site_id');
		$this->EE->load->library('forms_helper');
		$this->EE->load->model('forms_model');
		$this->EE->lang->loadfile('forms');

		$this->EE->forms_helper->is_ajax();
	}

	// ********************************************************************************* //

	public function form_submission()
	{
		//----------------------------------------
		// Check POST
		//----------------------------------------
		foreach ($_POST as $key => $val)
		{
			$_POST[$key] = $this->EE->security->xss_clean($val);
		}

		//----------------------------------------
		// Standard Fields
		//----------------------------------------
		$this->EE->forms->data = array();
		$this->EE->forms->fields = array();
		$this->EE->forms->dbfields = array();
		$this->EE->forms->ip_address = $this->EE->input->ip_address();
		$this->EE->forms->ip_int = sprintf("%u", ip2long($this->EE->forms->ip_address));
		$this->EE->forms->finaldata = array();

		//----------------------------------------
		// Check Fields
		//----------------------------------------
		if (isset($_POST['fields']) == FALSE OR empty($_POST['fields']) == TRUE)
		{
			return $this->return_error('missing_data', $this->EE->lang->line('form:error:missing_data') . '(MISSING_FIELDS)' );
		}

		//----------------------------------------
		// Form Data
		//----------------------------------------
		$FPID = $this->EE->input->post('FPID');

		if ($this->EE->forms_helper->is_natural_number($FPID) != FALSE)
		{
			// Get the data
			$query = $this->EE->db->select('*')->from('exp_security_hashes')->where('hash_id', $FPID)->limit(1)->get();

			if ($query->num_rows() == 0)
			{
				return $this->return_error('missing_data', $this->EE->lang->line('form:error:missing_data') . '(MISSING_FORM_DATA)' );
			}

			$this->EE->forms->data = $query->row_array();

			// Same IP?
			if ($this->EE->forms->data['ip_address'] != $this->EE->forms->ip_address)
			{
				return $this->return_error('missing_data', $this->EE->lang->line('form:error:missing_data') . '(DIFFERENT_IP)' );
			}

			// Parse form data
			$this->EE->forms->data = unserialize($this->EE->forms->data['form_data']);
		}
		else
		{
			return $this->return_error('missing_data', $this->EE->lang->line('form:error:missing_data') . '(FORM_DATA_NOT_SUBMITTED)' );
		}

		//----------------------------------------
		// Is the user banned?
		//----------------------------------------
		if ($this->EE->session->userdata['is_banned'] == TRUE)
		{
			return $this->return_error('not_authorized', $this->EE->lang->line('form:error:not_authorized') . ' (BANNED)');
		}

		//----------------------------------------
		// Is the IP address and User Agent required?
		//----------------------------------------
		if ($this->EE->config->item('require_ip_for_posting') == 'y')
		{
			if ($this->EE->forms->ip_address == '0.0.0.0' OR $this->EE->session->userdata['user_agent'] == '')
			{
				return $this->return_error('not_authorized', $this->EE->lang->line('form:error:not_authorized') . ' (NO_IP)');
			}
		}

		//----------------------------------------
		// Is the nation of the user banend?
		//----------------------------------------
		if ( $this->EE->session->nation_ban_check(FALSE) === FALSE && $this->EE->config->item('ip2nation') == 'y')
		{
			return $this->return_error('not_authorized', $this->EE->lang->line('form:error:not_authorized') . ' (NATION)');
		}

		//----------------------------------------
		// Blacklist/Whitelist Check
		//----------------------------------------
		if ($this->EE->blacklist->blacklisted == 'y' && $this->EE->blacklist->whitelisted == 'n')
		{
			return $this->return_error('not_authorized', $this->EE->lang->line('form:error:not_authorized') . ' (BLACKLIST)');
		}

		//----------------------------------------
		// Get Form Fields
		//----------------------------------------
		$this->EE->db->select('*');
		$this->EE->db->from('exp_forms_fields');
		$this->EE->db->where('form_id', $this->EE->forms->data['form_id']);
		$this->EE->db->where('field_type !=', 'pagebreak');
		$this->EE->db->order_by('field_order', 'ASC');

		// Limit to spefic fields?
		if (isset($this->EE->forms->data['fields_shown']) && empty($this->EE->forms->data['fields_shown']) == FALSE)
		{
			$this->EE->db->where_in('field_id', $this->EE->forms->data['fields_shown']);
		}

		$query = $this->EE->db->get();
		foreach ($query->result_array() as $field)
		{
			$field['settings'] = @unserialize($field['field_settings']);
			$this->EE->forms->dbfields[] = $field;
		}

		//----------------------------------------
		// Run field validations!
		//----------------------------------------
		$errors = array();
		foreach ($this->EE->forms->dbfields as $field)
		{
			// Is our Data there?
			$data = (isset($_POST['fields'][$field['field_id']]) == TRUE) ? $_POST['fields'][$field['field_id']] : '';

			$is_empty = FALSE;
			if (is_string($data) == TRUE)
			{
				$data = trim($data);
				if (strlen($data) == 0) $is_empty = TRUE;
			}
			else
			{
				$is_empty = empty($data);
			}

			// Is this field required?
			if ($field['required'] == 1 && $is_empty == TRUE)
			{
				$errors[] = array('type' => 'required', 'msg' => $this->EE->lang->line('form:error:required_field'), 'field_id' => $field['field_id']);
				continue;
			}

			// Validate!
			$result = $this->EE->formsfields[$field['field_type']]->validate($field, $data);

			// Validation Error?
			if ($result !== TRUE && is_array($result) == TRUE)
			{
				// Pass our error along!
				$result['field_id'] = $field['field_id'];
				$errors[] = $result;
			}
			elseif ($result !== TRUE && is_array($result) == FALSE)
			{
				// General Validation Errror then
				$errors[] = array('type' => 'general', 'msg' => $result, 'field_id' => $field['field_id']);
			}
		}

		//----------------------------------------
		// Snaptcha
		//----------------------------------------
		if ( isset($this->EE->forms->data['form_settings']['snaptcha']) == TRUE && $this->EE->forms->data['form_settings']['snaptcha'] == 'yes')
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

				// Validate! (using the freeform name. How akward is that)
				$fake_error = array();
				$fake_error = $SNAP->freeform_validate($fake_error);

				if (empty($fake_error) == FALSE)
				{
					$errors[] = array('type' => 'captcha', 'msg' => $fake_error[0], 'field_id' => 0);
				}
			}
		}

		//----------------------------------------
		// Handle Errors
		//----------------------------------------
		if (empty($errors) == FALSE)
		{
			//----------------------------------------
			// AJAX Request? (always comes first!)
			//----------------------------------------
			if (IS_AJAX == TRUE)
			{
				$out = array();
				$out['success'] = 'no';
				$out['type'] = 'validation';
				$out['errors'] = $errors;
				exit( $this->EE->forms_helper->generate_json($out) );
			}

			//----------------------------------------
			// Normal Errror Message?
			//----------------------------------------
			if ($this->EE->forms->data['display_error'] == 'default')
			{
				$error = array();
				foreach ($errors as $err) $error[] = $err['msg'];
				$this->EE->output->show_user_error('submission', $error);
			}
			else
			{
				//----------------------------------------
				// Are we using Pages or Structure?
				//----------------------------------------
				$template = (string)$this->EE->config->item('template');
				$template_group = (string) $this->EE->config->item('template_group');

				// Look for a page in the pages module
				if ($template_group == '' && $template == '')
				{
					$pages		= $this->EE->config->item('site_pages');
					$site_id	= $this->EE->config->item('site_id');
					$entry_id	= FALSE;
					
					// If we have pages, we'll look for an entry id
					if ($pages && isset($pages[$site_id]['uris']))
					{
						$match_uri = '/'.trim($this->EE->uri->uri_string, '/');	// will result in '/' if uri_string is blank
						$page_uris = $pages[$site_id]['uris'];
						
						$entry_id = array_search($match_uri, $page_uris);
						
						if ( ! $entry_id AND $match_uri != '/')
						{
							$entry_id = array_search($match_uri.'/', $page_uris);
						}
					}

					// Found an entry - grab related template
					if ($entry_id)
					{
						$qry = $this->EE->db->select('t.template_name, tg.group_name')
											->from(array('templates t', 'template_groups tg'))
											->where('t.group_id', 'tg.group_id', FALSE)
											->where('t.template_id',
												$pages[$site_id]['templates'][$entry_id])
											->get();

						if ($qry->num_rows() > 0)
						{
							/* 
								We do it this way so that we are not messing with 
								any of the segment variables, which should reflect 
								the actual URL and not our Pages redirect. We also
								set a new QSTR variable so that we are not 
								interfering with other module's besides the Channel 
								module (which will use the new Pages_QSTR when available).
							*/
							$template = $qry->row('template_name');
							$template_group = $qry->row('group_name');
							$this->EE->uri->page_query_string = $entry_id;

							// DOes the structure exist?
							if (isset($this->EE->extensions->OBJ['Structure_ext']) == TRUE)
							{
								$this->EE->extensions->OBJ['Structure_ext']->sessions_start(null);
							}
						}
					}
				}


				// Format the Errors Array
				$_POST['forms_errors'] = array();
				$_POST['forms_global_errors'] = array();

				foreach ($errors as $err)
				{
					if ($err['field_id'] == 0) $_POST['forms_global_errors'][] = $err;
					else $_POST['forms_errors'][ $err['field_id'] ] = $err;
				}

				// Are we paging?
				if ($this->EE->forms->data['paging'] == TRUE && $this->EE->forms->data['current_page'] < ($this->EE->forms->data['total_pages']+1))
				{
					$_POST['page'] = $this->EE->forms->data['current_page'];
				}

				// Remove unwanted crap
				unset($_POST['ACT'], $this->EE->forms->dbfields, $this->EE->forms->data);

				require_once APPPATH.'libraries/Template.php';
				$this->EE->TMPL = new EE_Template();
				//$this->EE->TMPL->parse_template_uri();
				$this->EE->TMPL->run_template_engine($template_group, $template);
				$this->EE->output->_display();
				exit();
			}
		}

		//----------------------------------------
		// Are we paging?
		//----------------------------------------
		if ($this->EE->forms->data['paging'] == TRUE && $this->EE->forms->data['current_page'] < ($this->EE->forms->data['total_pages']+1))
		{
			$_POST['page'] = $this->EE->forms->data['current_page'] +1;

			require_once APPPATH.'libraries/Template.php';
			$this->EE->TMPL = new EE_Template();
			$this->EE->TMPL->parse_template_uri();
			$this->EE->TMPL->run_template_engine();
			$this->EE->output->_display();
			exit();
		}

		//----------------------------------------
		// Run Presave Routines
		//----------------------------------------
		foreach ($this->EE->forms->dbfields as $field)
		{
			// Is our Data there?
			$data = (isset($_POST['fields'][ $field['field_id'] ]) == TRUE) ? $_POST['fields'][ $field['field_id'] ] : '';
			if (is_string($data) == TRUE) trim($data);

			// Run each fields save() method
			$data = $this->EE->formsfields[$field['field_type']]->save($field, $data);

			$this->EE->forms->finaldata[ $field['field_id'] ] = $data;
			unset($_POST['fields'][ $field['field_id'] ]);
		}

		//----------------------------------------
		// Figure Out the users Country!
		//----------------------------------------
		$country = '';
		if ($this->EE->config->item('ip2nation') == 'y')
		{
			$query = $this->EE->db->query("SELECT country FROM exp_ip2nation WHERE ip < INET_ATON('".$this->EE->db->escape_str($this->EE->forms->ip_address)."') ORDER BY ip DESC LIMIT 0,1");
			$country = $query->row('country');
		}
		else
		{
			if (function_exists('dns_get_record') == TRUE)
			{
				$reverse_ip = implode('.',array_reverse(explode('.',$this->EE->forms->ip_address)));
				$DNS_resolver = '.lookup.ip2.cc';
				$lookup = @dns_get_record($reverse_ip.$DNS_resolver, DNS_TXT);
				$country = isset($lookup[0]['txt']) ? strtolower($lookup[0]['txt']) : FALSE;

				if ($country == FALSE)
				{
					$content = $this->EE->forms_helper->fetch_url_file('http://www.geoplugin.net/php.gp?ip='.$this->EE->forms->ip_address);
					$geoip = @unserialize($content);
					$country = strtolower($geoip['geoplugin_countryCode']);
				}
			}
			else
			{
				$content = $this->EE->forms_helper->fetch_url_file('http://www.geoplugin.net/php.gp?ip='.$this->EE->forms->ip_address);
				$geoip = @unserialize($content);
				$country = strtolower($geoip['geoplugin_countryCode']);
			}
		}

		if ($country == FALSE) $country = 'xx';

		//----------------------------------------
		// Send Emails!
		//----------------------------------------
		$this->process_emails();

		//----------------------------------------
		// Save the submission
		//----------------------------------------
		$this->EE->db->set('form_id', $this->EE->forms->data['form_id']);
		$this->EE->db->set('site_id', $this->site_id);
		$this->EE->db->set('member_id', $this->EE->session->userdata('member_id'));
		$this->EE->db->set('ip_address', $this->EE->forms->ip_int);
		$this->EE->db->set('date', $this->EE->localize->now);
		$this->EE->db->set('country', $country);

		//----------------------------------------
		// Save all field data
		//----------------------------------------
		foreach ($this->EE->forms->finaldata as $field_id => $data)
		{
			$this->EE->db->set('fid_'.$field_id, $data);
		}

		$this->EE->db->insert('exp_forms_entries');
		$fentry_id = $this->EE->db->insert_id();

		//----------------------------------------
		// Update Form Data
		//----------------------------------------
		$this->EE->db->set('total_submissions', '(total_submissions+1)', FALSE);
		$this->EE->db->set('date_last_entry', $this->EE->localize->now);
		$this->EE->db->where('form_id', $this->EE->forms->data['form_id']);
		$this->EE->db->update('exp_forms');

		// Delete the FORM_DATA
		$this->EE->forms_model->delete_form_data($FPID);

		//----------------------------------------
		// Return the USER
		//----------------------------------------
		$RET = $this->EE->forms->data['return'];

		// Parse Fentry ID
		$RET = str_replace('%ENTRY_ID%', $fentry_id, $RET);

		if (IS_AJAX == TRUE)
		{
			$out = '{"success":"yes", "body": ""}';
		}
		else
		{
			$this->EE->load->helper('string');

			// Do we need to create an URL?
			if (strpos($RET, 'http://') === FALSE && strpos($RET, 'https://') === FALSE)
			{
				$RET = $this->EE->functions->remove_double_slashes($this->EE->functions->create_url(trim_slashes($RET)));
			}

			//----------------------------------------
			// Confirmation Message?
			//----------------------------------------
			if (isset($this->EE->forms->data['form_settings']['confirmation']['when']) == TRUE)
			{
				$when = $this->EE->forms->data['form_settings']['confirmation']['when'];

				// Show Before Redirect?
				if ($when == 'before_redirect')
				{
					// Build success message
					$data = array(	'title' 	=> lang('thank_you'),
									'heading'	=> lang('thank_you'),
									'content'	=> $this->EE->forms->data['form_settings']['confirmation']['text'],
									'redirect'	=> $RET,
									//'link'		=> array($RET, $site_name)
								 );
					$this->EE->output->show_message($data);
				}

				// Show Only?
				if ($when == 'show_only')
				{
					// Build success message
					$data = array(	'title' 	=> lang('thank_you'),
									'heading'	=> lang('thank_you'),
									'content'	=> $this->EE->forms->data['form_settings']['confirmation']['text']
								 );

					$this->EE->output->show_message($data);
				}

				// Just Redirect?
				if ($when == 'after_redirect' OR $when == 'disabled')
				{
					if ($when == 'after_redirect') $this->EE->session->set_flashdata('forms:show_confirm', 'yes');
					$this->EE->functions->redirect($RET);
				}
			}
		}
	}

	// ********************************************************************************* //

	protected function return_error($type, $msg)
	{
		// Ajax Response?
		if (IS_AJAX == TRUE)
		{
			$out = '{"success":"no", "type": "'.$type.'", "body": "'.$msg.'"}';
		}
		else
		{
			return $this->EE->output->show_user_error('submission', $msg);
		}

		return $out;
	}

	// ********************************************************************************* //

	private function process_emails()
	{
		// Load Email Library
		$this->EE->load->library('email');

		//----------------------------------------
		// Send Admin?
		//----------------------------------------
		if ((int)$this->EE->forms->data['admin_template'] !== 0)
		{
			// Grab our template!
			$this->EE->db->select('*');
			$this->EE->db->from('exp_forms_email_templates');
			if ($this->EE->forms->data['admin_template'] > 0) $this->EE->db->where('template_id', $this->EE->forms->data['admin_template']);
			else $this->EE->db->where('form_id', $this->EE->forms->data['form_id']);
			$this->EE->db->where('template_type', 'admin');
			$query = $this->EE->db->get();

			// Store it for easy
			$email = $query->row();

			// Kill the db object
			$query->free_result();
			unset($query);

			//----------------------------------------
			// Send Email!
			//----------------------------------------
			$this->EE->email->EE_initialize();

			$to = $email->email_to;
			if (isset($this->EE->session->cache['Forms']['EmailAdminOverride']) == TRUE)
			{
				$to = $this->EE->session->cache['Forms']['EmailAdminOverride'];
			}

			//----------------------------------------
			// Custom Reply To?
			//----------------------------------------
			if ($email->reply_to_author == 'yes')
			{
				$this->EE->email->reply_to($this->EE->session->userdata['email']);
			}
			else
			{
				$this->EE->email->reply_to($email->email_reply_to_email, $email->email_reply_to);
			}

			$this->EE->email->wordwrap = ($email->email_wordwrap == 0) ? FALSE : TRUE;
			$this->EE->email->mailtype = $email->email_type;
			$this->EE->email->from($email->email_from_email, $email->email_from);
			$this->EE->email->to( $to );
			$this->EE->email->subject( $this->parse_forms_vars($email->email_subject) );
			$this->EE->email->cc($email->email_cc);
			$this->EE->email->bcc($email->email_bcc);
			$this->EE->email->message( $this->parse_email_template($email) );

			if ($email->email_type == 'html') $this->EE->email->set_alt_message( $this->parse_email_template($email, TRUE) );

			// Handle Attachtments!
			if ($email->email_attachments == 'yes')
			{
				if (isset($this->EE->session->cache['Forms']['UploadedFiles']) == TRUE && is_array($this->EE->session->cache['Forms']['UploadedFiles']) == TRUE)
				{
					foreach($this->EE->session->cache['Forms']['UploadedFiles'] as $file)
					{
						$this->EE->email->attach($file);
					}
				}
			}


			// Send the Email!
			$this->EE->email->send();

			//echo $this->EE->email->print_debugger();

			// Clear all email vars (incl. attachments)
			$this->EE->email->clear(TRUE);
		}

		//----------------------------------------
		// Send User!
		//----------------------------------------
		if ((int)$this->EE->forms->data['user_template'] !== 0 && $this->EE->session->userdata['email'] != FALSE)
		{
			// Grab our template!
			$this->EE->db->select('*');
			$this->EE->db->from('exp_forms_email_templates');
			if ($this->EE->forms->data['user_template'] > 0) $this->EE->db->where('template_id', $this->EE->forms->data['user_template']);
			else $this->EE->db->where('form_id', $this->EE->forms->data['form_id']);
			$this->EE->db->where('template_type', 'user');
			$query = $this->EE->db->get();

			// Store it for easy
			$email = $query->row();

			// Kill the db object
			$query->free_result();
			unset($query);

			//----------------------------------------
			// Send Email!
			//----------------------------------------
			$this->EE->email->EE_initialize();

			$this->EE->email->wordwrap = ($email->email_wordwrap == 0) ? FALSE : TRUE;
			$this->EE->email->mailtype = $email->email_type;
			$this->EE->email->from($email->email_from_email, $email->email_from);
			$this->EE->email->reply_to($email->email_reply_to_email, $email->email_reply_to);
			$this->EE->email->to($this->EE->session->userdata['email']);
			$this->EE->email->subject( $this->parse_forms_vars($email->email_subject) );
			$this->EE->email->cc($email->email_cc);
			$this->EE->email->bcc($email->email_bcc);
			$this->EE->email->message( $this->parse_email_template($email) );

			if ($email->email_type == 'html') $this->EE->email->set_alt_message( $this->parse_email_template($email, TRUE) );

			// Handle Attachtments!
			if ($email->email_attachments == 'yes')
			{
				if (isset($this->EE->session->cache['Forms']['UploadedFiles']) == TRUE && is_array($this->EE->session->cache['Forms']['UploadedFiles']) == TRUE)
				{
					foreach($this->EE->session->cache['Forms']['UploadedFiles'] as $file)
					{
						$this->EE->email->attach($file);
					}
				}
			}

			// Send the Email!
			$this->EE->email->send();

			//echo $this->EE->email->print_debugger();

			// Clear all email vars (incl. attachments)
			$this->EE->email->clear(TRUE);
		}
	}

	// ********************************************************************************* //

	private function parse_email_template($email_template, $alt_body=FALSE)
	{
		$out = '';

		// What Email Type? (for form fields display method)
		$email_type = 'html';
		if ($alt_body == TRUE) $email_type = 'text';

		//----------------------------------------
		// Get the template body
		//----------------------------------------
		if ($alt_body == TRUE)
		{
			$out = $email_template->alt_template;
		}
		elseif ($email_template->ee_template_id > 0)
		{
			$query = $this->EE->db->select('template_data')->from('exp_templates')->where('template_id', $email_template->ee_template_id)->get();
			$out = $query->row('template_data');
		}
		else
		{
			$out = $email_template->template;
		}

		// Empty? Nothing to do then!
		if ($out == FALSE) return '';

		//----------------------------------------
		// Parse available variables!
		//----------------------------------------
		$out = $this->parse_forms_vars($out, $email_type);

		//----------------------------------------
		// Loop over all fields?
		//----------------------------------------
		if (strpos($out, '{form:fields}') !== FALSE)
		{
			// Grab the data between the pairs
			$tagdata = $this->EE->forms_helper->fetch_data_between_var_pairs('form:fields', $out);

			$final = '';
			$count = 0;

			// Loop over all fields
			foreach ($this->EE->forms->dbfields as $field)
			{
				$row = '';
				$count++;

				// Create the VARS
				$vars = array();
				$vars['{field:label}'] = $field['title'];
				$vars['{field:short_name}'] = $field['url_title'];
				$vars['{field:value}'] = $this->EE->formsfields[ $field['field_type'] ]->output_data($field, $this->EE->forms->finaldata[ $field['field_id'] ], $email_type);
				$vars['{field:count}'] = $count;

				// Parse them
				$row = str_replace(array_keys($vars), array_values($vars), $tagdata);


				$final .= $row;
			}

			// Replace the var pair!
			$out = $this->EE->forms_helper->swap_var_pairs('form:fields', $final, $out);
		}

		//----------------------------------------
		// Allows template parsing!
		//----------------------------------------
		if (class_exists('EE_Template') == FALSE) require_once APPPATH.'libraries/Template.php';
		$this->EE->TMPL = new EE_Template();
		$this->EE->TMPL->parse($out, FALSE, $this->site_id);
		$out = $this->EE->TMPL->final_template;

		return $out;
	}

	// ********************************************************************************* //

	private function parse_forms_vars($string, $format='text')
	{
		$vars = array();
		$vars['{form:label}'] = $this->EE->forms->data['form_title'];
		$vars['{form:short_name}'] = $this->EE->forms->data['form_url_title'];
		$vars['{form:id}'] = $this->EE->forms->data['form_id'];
		$vars['{user:referrer}'] = (isset($_SERVER['HTTP_REFERER']) == TRUE) ? $_SERVER['HTTP_REFERER'] : '';
		$vars['{date:usa}'] = $this->EE->localize->decode_date('%m/%d/%Y', $this->EE->localize->now);
		$vars['{date:eu}'] = $this->EE->localize->decode_date('%d/%m/%Y', $this->EE->localize->now);
		$vars['{datetime:usa}'] = $this->EE->localize->decode_date('%m/%d/%Y %h:%i %A', $this->EE->localize->now);
		$vars['{datetime:eu}'] =  $this->EE->localize->decode_date('%d/%m/%Y %H:%i', $this->EE->localize->now);

		// Parse it!
		$string = str_replace(array_keys($vars), array_values($vars), $string);

		// Parse all user session data too
		foreach($this->EE->session->userdata as $var => $val)
		{
			// Val has arrays? Ignore them!
			if (is_array($val) == TRUE) continue;

			$string = str_replace('{user:'.$var.'}', $val, $string);
		}

		foreach($this->EE->forms->data as $var => $val)
		{
			// Val has arrays? Ignore them!
			if (is_array($val) == TRUE) continue;

			$string = str_replace('{form:'.$var.'}', $val, $string);
		}

		foreach ($this->EE->forms->dbfields as $field)
		{
			$string = str_replace('{field:'.$field['url_title'].'}', $this->EE->formsfields[ $field['field_type'] ]->output_data($field, $this->EE->forms->finaldata[ $field['field_id'] ], $format), $string);
		}

		return $string;
	}

	// ********************************************************************************* //


} // END CLASS

/* End of file act.forms.php  */
/* Location: ./system/expressionengine/third_party/forms/act.forms.php */