<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Forms Model File
 *
 * @package			DevDemon_Forms
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2010 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com/forms/
 */
class Forms_model
{
	/**
	 * Constructor
	 *
	 * @access public
	 * @return void
	 */
	public function __construct()
	{
		$this->EE =& get_instance();
		$this->EE->load->library('forms_helper');
		$this->site_id = $this->EE->forms_helper->get_current_site_id();

		if (isset($this->EE->forms) == FALSE)
		{
			$this->EE->forms = new stdClass();
		}

		$this->EE->load->helper('url');
		$this->EE->load->helper('directory');
		$this->load_fieldtypes();
	}

	// ********************************************************************************* //

	public function create_update_form($data, $form_id=0)
	{
		// -----------------------------------------
		// Check for fields
		// -----------------------------------------
		if (isset($data['site_id'])) $this->EE->db->set('site_id', $data['site_id']);
		if (isset($data['entry_id'])) $this->EE->db->set('entry_id', $data['entry_id']);
		if (isset($data['channel_id'])) $this->EE->db->set('channel_id', $data['channel_id']);
		if (isset($data['ee_field_id'])) $this->EE->db->set('ee_field_id', $data['ee_field_id']);
		if (isset($data['member_id'])) $this->EE->db->set('member_id', $data['member_id']);
		if (isset($data['form_title'])) $this->EE->db->set('form_title', $data['form_title']);
		if (isset($data['form_url_title'])) $this->EE->db->set('form_url_title', strtolower(url_title($data['form_url_title'])));
		if (isset($data['date_created'])) $this->EE->db->set('date_created', $data['date_created']);
		if (isset($data['admin_template'])) $this->EE->db->set('admin_template', $data['admin_template']);
		if (isset($data['user_template'])) $this->EE->db->set('user_template', $data['user_template']);
		if (isset($data['form_type'])) $this->EE->db->set('form_type', $data['form_type']);
		if (isset($data['form_settings'])) $this->EE->db->set('form_settings', $data['form_settings']);

		if (isset($data['form_id']) == TRUE && $data['form_id'] > 0 && $form_id = 0) $form_id = $data['form_id'];

		// -----------------------------------------
		// Update Or Insert
		// -----------------------------------------
		if ($form_id > 0)
		{
			$this->EE->db->where('form_id', $form_id);
			$this->EE->db->update('exp_forms');
		}
		else
		{
			$this->EE->db->insert('exp_forms');
			$form_id = $this->EE->db->insert_id();
		}

		return $form_id;
	}

	// ********************************************************************************* //

	public function delete_form($form_id)
	{
		// -----------------------------------------
		// Grab all fields
		// -----------------------------------------
		$this->EE->db->select('*');
		$this->EE->db->from('exp_forms_fields');
		$this->EE->db->where('form_id', $form_id);
		$query = $this->EE->db->get();

		// Delete them all
		$this->delete_fields($query->result());

		// -----------------------------------------
		// Delete the form
		// -----------------------------------------
		$this->EE->db->where('form_id', $form_id);
		$this->EE->db->delete('exp_forms');

		// -----------------------------------------
		// Delete all form entries associated with this form
		// -----------------------------------------
		$this->EE->db->where('form_id', $form_id);
		$this->EE->db->delete('exp_forms_entries');
	}

	// ********************************************************************************* //

	private function load_fieldtypes()
	{
		if (class_exists('CF_Field') == FALSE) include(PATH_THIRD.'forms/fields/cf_field.php');

		if (isset($this->formsfields) == TRUE && empty($this->EE->formsfields) == FALSE) return;

		$this->EE->formsfields = array();
		//$fields = $this->config->item('cf_formfields_cats');

		// Make the map
		if (($temp = directory_map(PATH_THIRD.'forms/fields/', 2)) !== FALSE)
		{
			// Loop over all fields
			foreach ($temp as $classname => $files)
			{
				// Check for empty array and such
				if (is_array($files) == FALSE OR empty($files) == TRUE)
				{
					continue;
				}

				// Search for the file we need, not there? continue
				if (array_search($classname.'.php', $files) === FALSE) continue;

				$final_class = 'CF_Field_'.$classname;

				// Do a simple check, we don't want fatal errors
				if (class_exists($final_class) == FALSE)
				{
					// Include it of course! and get the class vars
					require PATH_THIRD.'forms/fields/' .$classname.'/'. $classname.'.php';
				}

				//$obj = new $final_class();

				/*
				// Store it!
				$fields[ $obj->info['category'] ][$classname] = $obj;

				// We need to be sure it's formatted correctly
				if (isset($obj->info['name']) == FALSE) unset($fields[ $obj->info['category'] ][$classname]);
				if (isset($fields[ $obj->info['category'] ]) == FALSE) unset($fields[ $obj->info['category'] ][$classname]);
				*/

				//$fields[$classname] = $obj;
				$this->EE->formsfields[$classname] = new $final_class();

				// Final check
				if (isset($this->EE->formsfields[$classname]->info) == FALSE) unset($this->EE->formsfields[$classname]);
				if (isset($this->EE->formsfields[$classname]->info['disabled']) == TRUE && $this->EE->formsfields[$classname]->info['disabled'] == TRUE) unset($this->EE->formsfields[$classname]);
			}
		}

		//$this->fieldtypes  = $fields;
	}

	// ********************************************************************************* //

	public function create_update_field($data=array(), $field_id=0)
	{	// -----------------------------------------
		// Check required!
		// -----------------------------------------
		if (isset($data['title']) == FALSE) return false;
		if (isset($data['field_type']) == FALSE) return false;

		// -----------------------------------------
		// Check for fields
		// -----------------------------------------
		$this->EE->db->set('title', $data['title']);
		$this->EE->db->set('field_type', $data['field_type']);
		if (isset($data['form_id'])) $this->EE->db->set('form_id', $data['form_id']);
		if (isset($data['entry_id'])) $this->EE->db->set('entry_id', $data['entry_id']);
		if (isset($data['ee_field_id'])) $this->EE->db->set('ee_field_id', $data['ee_field_id']);
		if (isset($data['url_title'])) $this->EE->db->set('url_title', strtolower(url_title($data['url_title'])));
		if (isset($data['description'])) $this->EE->db->set('description', $data['description']);
		if (isset($data['field_order'])) $this->EE->db->set('field_order', $data['field_order']);
		if (isset($data['required'])) $this->EE->db->set('required', $data['required']);
		if (isset($data['no_dupes'])) $this->EE->db->set('no_dupes', $data['no_dupes']);
		if (isset($data['field_settings'])) $this->EE->db->set('field_settings', serialize($this->EE->formsfields[$data['field_type']]->save_settings($data['field_settings'], TRUE)));

		if (isset($data['field_id']) == TRUE && $data['field_id'] > 0 && $field_id == 0) $field_id = $data['field_id'];



		// -----------------------------------------
		// Update Or Insert
		// -----------------------------------------
		if ($field_id > 0)
		{
			$this->EE->db->where('field_id', $field_id);
			$this->EE->db->update('exp_forms_fields');
		}
		else
		{
			$this->EE->db->insert('exp_forms_fields');
			$field_id = $this->EE->db->insert_id();

			// Load dbforge
			$this->EE->load->dbforge();

			// Create the column
			$fields = array('fid_'.$field_id => array('type' => 'TEXT', 'null' => FALSE, 'default' => ''));
			$this->EE->dbforge->add_column('forms_entries', $fields);
		}

		return $field_id;
	}


	// ********************************************************************************* //

	/**
	 * Delete Fields
	 * @param array $fields Array of fields (DB result!)
	 */
	public function delete_fields($fields=array())
	{
		// We can always pass a single field
		if (is_array($fields) == FALSE)
		{
			// Convert it to array
			$fields = array($fields);
		}

		// Loop over them all
		foreach($fields as $field)
		{
			$this->EE->db->where('field_id', $field->field_id);
			$this->EE->db->delete('exp_forms_fields');

			// Load dbforge
			$this->EE->load->dbforge();

			$this->EE->formsfields[$field->field_type]->delete_field($field);
			if ($this->EE->db->field_exists('fid_'.$field->field_id, 'forms_entries'))
			{
				$this->EE->dbforge->drop_column('forms_entries', 'fid_'.$field->field_id);
			}
		}
	}

	// ********************************************************************************* //

	public function store_form_data($form)
	{
		// -----------------------------------------
		// Store Form
		// -----------------------------------------
		$this->EE->db->set('ip_address', $this->EE->input->ip_address());
		$this->EE->db->set('date', $this->EE->localize->now);
		$this->EE->db->set('form_data', serialize($form));
		$this->EE->db->insert('exp_security_hashes');
		$FPID = $this->EE->db->insert_id();

		return $FPID;
	}

	// ********************************************************************************* //

	public function delete_form_data($FPID)
	{
		$this->EE->db->query("DELETE FROM exp_security_hashes WHERE hash_id = '".$FPID."'");
		$this->EE->db->query("DELETE FROM exp_security_hashes WHERE date < UNIX_TIMESTAMP()-7200"); // helps garbage collection for old hashes
	}

	// ********************************************************************************* //

	public function create_update_template($data, $template_id=0)
	{
		// -----------------------------------------
		// Check for fields
		// -----------------------------------------
		$this->EE->db->set('site_id', $this->site_id);
		if (isset($data['form_id'])) $this->EE->db->set('form_id', $data['form_id']);
		if (isset($data['template_label'])) $this->EE->db->set('template_label', $data['template_label']);
		if (isset($data['template_name'])) $this->EE->db->set('template_name', $data['template_name']);
		if (isset($data['template_type'])) $this->EE->db->set('template_type', $data['template_type']);
		if (isset($data['template_desc'])) $this->EE->db->set('template_desc', $data['template_desc']);
		if (isset($data['email_type'])) $this->EE->db->set('email_type', $data['email_type']);
		if (isset($data['email_wordwrap'])) $this->EE->db->set('email_wordwrap', $data['email_wordwrap']);
		if (isset($data['email_to'])) $this->EE->db->set('email_to', $data['email_to']);
		if (isset($data['email_from'])) $this->EE->db->set('email_from', $data['email_from']);
		if (isset($data['email_from_email'])) $this->EE->db->set('email_from_email', $data['email_from_email']);
		if (isset($data['email_reply_to'])) $this->EE->db->set('email_reply_to', $data['email_reply_to']);
		if (isset($data['email_reply_to_email'])) $this->EE->db->set('email_reply_to_email', $data['email_reply_to_email']);
		if (isset($data['reply_to_author'])) $this->EE->db->set('reply_to_author', $data['reply_to_author']);
		if (isset($data['email_subject'])) $this->EE->db->set('email_subject', $data['email_subject']);
		if (isset($data['email_cc'])) $this->EE->db->set('email_cc', $data['email_cc']);
		if (isset($data['email_bcc'])) $this->EE->db->set('email_bcc', $data['email_bcc']);
		if (isset($data['email_attachments'])) $this->EE->db->set('email_attachments', $data['email_attachments']);
		if (isset($data['template'])) $this->EE->db->set('template', $data['template']);

		if (isset($data['template_id']) == TRUE && $data['template_id'] > 0 && $template_id = 0) $template_id = $data['template_id'];

		// -----------------------------------------
		// Linked to a form?
		// -----------------------------------------
		if (isset($data['form_id']))
		{
			$query = $this->EE->db->select('template_id')->from('exp_forms_email_templates')->where('form_id', $data['form_id'])->where('template_type', $data['template_type'])->limit(1)->get();
			if ($query->num_rows() > 0)
			{
				$template_id = $query->row('template_id');
			}
		}

		// -----------------------------------------
		// Update Or Insert
		// -----------------------------------------
		if ($template_id > 0)
		{
			$this->EE->db->where('template_id', $template_id);
			$this->EE->db->update('exp_forms_email_templates');
		}
		else
		{
			$this->EE->db->insert('exp_forms_email_templates');
			$template_id = $this->EE->db->insert_id();
		}

		return $template_id;
	}

	// ********************************************************************************* //

	public function delete_template($template_id)
	{
		$this->EE->db->set('admin_template', 0);
		$this->EE->db->where('admin_template', $template_id);
		$this->EE->db->update('exp_forms');

		$this->EE->db->set('user_template', 0);
		$this->EE->db->where('user_template', $template_id);
		$this->EE->db->update('exp_forms');

		$this->EE->db->where('template_id', $template_id);
		$this->EE->db->delete('exp_forms_email_templates');
	}

	// ********************************************************************************* //

	public function create_update_list($data, $list_id=0)
	{
		// -----------------------------------------
		// Check for fields
		// -----------------------------------------
		if (isset($data['list_label'])) $this->EE->db->set('list_label', $data['list_label']);
		if (isset($data['list_data'])) $this->EE->db->set('list_data', $data['list_data']);

		if (isset($data['list_id']) == TRUE && $data['list_id'] > 0 && $list_id = 0) $list_id = $data['list_id'];

		// -----------------------------------------
		// Update Or Insert
		// -----------------------------------------
		if ($list_id > 0)
		{
			$this->EE->db->where('list_id', $list_id);
			$this->EE->db->update('exp_forms_lists');
		}
		else
		{
			$this->EE->db->insert('exp_forms_lists');
			$list_id = $this->EE->db->insert_id();
		}

		return $list_id;
	}

	// ********************************************************************************* //

	public function delete_list($list_id)
	{
		$this->EE->db->where('list_id', $list_id);
		$this->EE->db->delete('exp_forms_lists');
	}

	// ********************************************************************************* //

} // END CLASS

/* End of file forms_model.php  */
/* Location: ./system/expressionengine/third_party/forms/models/forms_model.php */