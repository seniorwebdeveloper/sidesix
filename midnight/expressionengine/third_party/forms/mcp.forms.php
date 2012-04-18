<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Channel Forms Module Control Panel Class
 *
 * @package			DevDemon_Forms
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2010 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com/forms/
 * @see				http://expressionengine.com/user_guide/development/module_tutorial.html#control_panel_file
 */
class Forms_mcp
{
	/**
	 * Views Data
	 * @var array
	 * @access private
	 */
	private $vData = array();

	/**
	 * Constructor
	 *
	 * @access public
	 * @return void
	 */
	public function __construct()
	{
		// Creat EE Instance
		$this->EE =& get_instance();

		// Load Models & Libraries & Helpers
		$this->EE->load->library('forms_helper');
		$this->EE->load->model('forms_model');
		$this->site_id = $this->EE->forms_helper->get_current_site_id();

		// Some Globals
		$this->base = BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=forms';
		$this->base_short = 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=forms';

		// Global Views Data
		$this->vData['base_url'] = $this->base;
		$this->vData['base_url_short'] = $this->base_short;
		$this->vData['method'] = $this->EE->input->get('method');

		$this->EE->forms_helper->define_theme_url();

		$this->mcp_globals();

		// Add Right Top Menu
		$this->EE->cp->set_right_nav(array(
			'form:docs' 				=> $this->EE->cp->masked_url('http://www.devdemon.com/forms/docs/'),
		));

		$this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('form:mcp'));

		$this->EE->config->load('forms_config');

		// -----------------------------------------
		// Add Help!
		// -----------------------------------------
		if (isset($this->EE->session->cache['Forms']['JSON_help']) == FALSE)
		{
			$this->vData['helpjson'] = array();
			$this->vData['alertjson'] = array();

			foreach ($this->EE->lang->language as $key => $val)
			{
				if (strpos($key, 'form:help:') === 0)
				{
					$this->vData['helpjson'][substr($key, 10)] = $val;
					unset($this->EE->lang->language[$key]);
				}

				if (strpos($key, 'form:alert:') === 0)
				{
					$this->vData['alertjson'][substr($key, 11)] = $val;
					unset($this->EE->lang->language[$key]);
				}

			}

			$this->vData['helpjson'] = $this->EE->forms_helper->generate_json($this->vData['helpjson']);
			$this->vData['alertjson'] = $this->EE->forms_helper->generate_json($this->vData['alertjson']);
			$this->EE->session->cache['Forms']['JSON_help'] = TRUE;
		}

		// Debug
		//$this->EE->db->save_queries = TRUE;
		//$this->EE->output->enable_profiler(TRUE);
	}

	// ********************************************************************************* //

	public function index()
	{
		return $this->forms();
		/*
		// Page Title & BreadCumbs
		$this->vData['PageHeader'] = 'home';

		return $this->EE->load->view('mcp/index', $this->vData, TRUE);*/
	}

	// ********************************************************************************* //

	public function forms()
	{
		// Page Title & BreadCumbs
		$this->vData['PageHeader'] = 'forms';

		return $this->EE->load->view('mcp/forms', $this->vData, TRUE);
	}

	// ********************************************************************************* //

	public function view_form()
	{
		// Page Title & BreadCumbs
		$this->vData['PageHeader'] = 'forms';

		//----------------------------------------
		// Grab Form
		//----------------------------------------
		$this->EE->db->select('f.*, mb.screen_name');
		$this->EE->db->from('exp_forms f');
		$this->EE->db->join('exp_members mb', 'mb.member_id = f.member_id', 'left');
		$this->EE->db->where('f.form_id', $this->EE->input->get_post('form_id'));
		$query = $this->EE->db->get();

		if ($query->num_rows() != 1)
		{
			return show_error('Missing Form Info..');
		}

		$this->vData['form'] = $query->row();

		//----------------------------------------
		// Standard Fields
		//----------------------------------------
		$this->vData['standard_fields'] = array();
		$this->vData['standard_fields']['member'] = $this->EE->lang->line('form:member');
		$this->vData['standard_fields']['date'] = $this->EE->lang->line('form:date');
		$this->vData['standard_fields']['country'] = $this->EE->lang->line('form:country');
		$this->vData['standard_fields']['ip'] = $this->EE->lang->line('form:ip');
		$this->vData['dbfields'] = array();

		// -----------------------------------------
		// Grab all DB fields
		// -----------------------------------------
		$this->EE->db->select('*');
		$this->EE->db->from('exp_forms_fields');
		$this->EE->db->where('form_id', $this->EE->input->get_post('form_id'));
		$this->EE->db->where('field_type !=', 'pagebreak');
		$this->EE->db->order_by('field_order');
		$query = $this->EE->db->get();

		foreach($query->result() as $row)
		{
			$row->field_settings = @unserialize($row->field_settings);
			$this->vData['dbfields'][] = $row;
		}

		//----------------------------------------
		// Grab all members
		//----------------------------------------
		$this->vData['members'] = array();
		$this->EE->db->select('mb.screen_name, fe.member_id');
		$this->EE->db->from('exp_forms_entries fe');
		$this->EE->db->join('exp_members mb', 'mb.member_id = fe.member_id', 'left');
		$this->EE->db->group_by('fe.member_id');
		$this->EE->db->order_by('mb.screen_name');
		$query = $this->EE->db->get();

		foreach ($query->result() as $row)
		{
			if ($row->member_id == 0) $row->screen_name = $this->EE->lang->line('form:guest');
			$this->vData['members'][$row->member_id] = $row->screen_name;
		}


		return $this->EE->load->view('mcp/view_form', $this->vData, TRUE);
	}

	// ********************************************************************************* //

	public function create_form()
	{
		$vData = $this->vData;

		// For the Builder
		$this->EE->forms_helper->mcp_meta_parser('css', FORMS_THEME_URL . 'forms_builder.css', 'cfo-builder');
		$this->EE->forms_helper->mcp_meta_parser('css', FORMS_THEME_URL . 'forms_base.css', 'cfo-base');
		$this->EE->forms_helper->mcp_meta_parser('js', FORMS_THEME_URL . 'jquery.liveurltitle.js', 'jquery.liveurltitle', 'jquery');
		$this->EE->forms_helper->mcp_meta_parser('js', FORMS_THEME_URL . 'jquery-contained-sticky-scroll-min.js', 'jquery-contained-sticky-scroll', 'jquery');
		$this->EE->forms_helper->mcp_meta_parser('js', FORMS_THEME_URL . 'hogan.js', 'hogan', 'hogan');
		$this->EE->forms_helper->mcp_meta_parser('js', FORMS_THEME_URL . 'forms_builder.js', 'cfo-builder');
		$this->EE->cp->add_js_script(array('ui' => array('tabs', 'draggable', 'sortable'), 'plugin' => array('crypt')));

		// Page Title & BreadCumbs
		$vData['PageHeader'] = 'forms';
		$vData['field_id'] = 0;
		$vData['form'] = array();
		$vData['form']['admin_template'] = 0;
		$vData['form']['user_template'] = 0;
		$vData['dbfieldjson'] = '{}';
		$vData['field_name'] = 'form';

		$form_id = $this->EE->input->get('form_id') ? $this->EE->input->get('form_id') : 0;

		// -----------------------------------------
		// Add Blank
		// -----------------------------------------
		$fields = $this->EE->db->list_fields('exp_forms');

		foreach ($fields as $name)
		{
			$vData['form'][$name] = '';
		}

		// -----------------------------------------
		// Add Fields!
		// -----------------------------------------
		foreach ($this->EE->formsfields as $classname => $field)
		{
			$vData['form']['fields'][$field->info['category']][] = $classname;
		}

		//----------------------------------------
		// Reorder Categories, just in case
		//----------------------------------------
		$new_fields = $this->EE->config->item('cf_formfields_cats');
		foreach ($vData['form']['fields'] as $category => $fields)
		{
			$new_fields[$category] = $fields;
		}

		// Put it back
		$vData['form']['fields'] = $new_fields;

		// -----------------------------------------
		// Load Settings
		// -----------------------------------------
		$vData['form']['settings'] = $this->EE->config->item('cf_formsettings'); // Default form settings

		// -----------------------------------------
		// Add Config
		// -----------------------------------------
		$vData['config'] = $this->EE->config->item('cf_dropdown_options');

		// -----------------------------------------
		// Grab all Email Templates
		// -----------------------------------------
		$vData['email_templates']['admin'] = array();
		$vData['email_templates']['user'] = array();

		$query = $this->EE->db->select('template_id, template_label, template_type')->from('forms_email_templates')->where('site_id', $this->site_id)->where('form_id', 0)->order_by('template_label')->get();

		foreach($query->result() as $row)
		{
			$vData['email_templates'][$row->template_type][$row->template_id] = $row->template_label;
		}

		// Add Default Template Settings
		$template_fields = $this->EE->db->list_fields('exp_forms_email_templates');

		foreach ($template_fields as $field)
		{
			$vData['form']['templates']['admin'][$field] = '';
			$vData['form']['templates']['user'][$field] = '';
		}

		// -----------------------------------------
		// Grab Member Groups
		// -----------------------------------------
		$mgroups = $this->EE->db->query("SELECT group_id, group_title FROM exp_member_groups WHERE site_id = {$this->site_id} AND group_id != 1");
		foreach($mgroups->result() as $row) $vData['member_groups'][$row->group_id] = $row->group_title;
		$mgroups->free_result();

		// -----------------------------------------
		// Lets grab data!
		// -----------------------------------------
		if ($form_id > 0)
		{
			// -----------------------------------------
			// Grab the form!
			// -----------------------------------------
			$this->EE->db->select('*');
			$this->EE->db->from('exp_forms');
			$this->EE->db->where('form_id', $form_id);
			$query = $this->EE->db->get();

			if ($query->num_rows() == 1)
			{
				$vData['form'] = $this->EE->forms_helper->array_extend($vData['form'], $query->row_array());
				$vData['form']['settings'] = $this->EE->forms_helper->array_extend($vData['form']['settings'], unserialize($vData['form']['form_settings']));

				// -----------------------------------------
				// Grab all fields
				// -----------------------------------------
				$this->EE->db->select('*');
				$this->EE->db->from('exp_forms_fields');
				$this->EE->db->where('form_id', $form_id);
				$this->EE->db->order_by('field_order');
				$query = $this->EE->db->get();

				$dbfieldjson = array();

				foreach($query->result() as $dbfield)
				{
					$class_name = $dbfield->field_type;

					// Lets make our field
					$field = array();
					$field['title'] = $dbfield->title;
					$field['url_title'] = $dbfield->url_title;
					$field['description'] = $dbfield->description;
					$field['field_type'] = $class_name;
					$field['field_type_label'] = $this->EE->formsfields[$class_name]->info['title'];
					$field['settings'] = array();

					// Form input name="" for the field title/url_title/desc
					$field['form_name'] = $vData['field_name'].'[fields][]';

					// Create out JSON. We do it here to keep the JSON as clean as possible
					$dbfieldjson[ 'field_id_'.$dbfield->field_id ] = $field;

					// Form input name="" for custom field settings
					$field['form_name_settings'] = $vData['field_name'].'[fields]['.mt_rand(0, 999999).'][settings]';

					// Do we have any field settings stored?
					$field['settings'] = $dbfield->field_settings ? unserialize($dbfield->field_settings) : array();

					// We need to add the form settings
					$field['form_settings'] = $vData['form']['settings'];

					// Add form settings
					$field_settings['form_settings'] = $vData['form']['settings'];

					// Lets add the form field name for field settings
					$field_settings['cf_field_name_settings'] = $vData['field_name'].'[fields][][settings]';

					// Continue with out JSON
					$dbfieldjson[ 'field_id_'.$dbfield->field_id ]['field_content'] = $this->EE->formsfields[$class_name]->display_field($field, FALSE);
					$dbfieldjson[ 'field_id_'.$dbfield->field_id ]['field_settings'] = $this->EE->formsfields[$class_name]->display_settings($field, FALSE);
					$dbfieldjson[ 'field_id_'.$dbfield->field_id ]['field_required'] = ($dbfield->required == 1) ? TRUE : FALSE;
					$dbfieldjson[ 'field_id_'.$dbfield->field_id ]['field_id'] = $dbfield->field_id;
					$dbfieldjson[ 'field_id_'.$dbfield->field_id ]['ee_field_id'] = 0;  // We need this to find the wrapper
				}

				$vData['dbfieldjson'] = $this->EE->forms_helper->generate_json($dbfieldjson);


				// Add template data
				foreach ($template_fields as $field)
				{
					$vData['form']['templates']['admin'][$field] = '';
					$vData['form']['templates']['user'][$field] = '';
				}


				// -----------------------------------------
				// Grab all assigned Templates
				// -----------------------------------------
				foreach(array('admin', 'user') as $type)
				{
					if ($vData['form'][$type.'_template'] == -1)
					{
						$query = $this->EE->db->select('*')->from('exp_forms_email_templates')->where('form_id', $vData['form']['form_id'])->where('template_type', $type)->limit(1)->get();
						if ($query->num_rows > 0)
						{
							$vData['form']['templates'][$type] = $query->row_array();
						}
						else
						{
							$vData['form'][$type.'_template'] = 0;
						}
					}
				}

			}

		}

		// -----------------------------------------
		// Process From Fields
		// -----------------------------------------
		if (isset($this->EE->session->cache['Forms']['JSON_defaultfields']) == FALSE)
		{
			// Store
			$fieldjson = array();

			// Loop over all categories
			foreach($vData['form']['fields'] as $catfields)
			{
				// Loop over all categories within this field
				foreach($catfields as $class_name)
				{
					// Lets make our field
					$field = array();
					$field['title'] = $this->EE->formsfields[$class_name]->info['title'];
					$field['url_title'] = $class_name;
					$field['description'] = '';
					$field['field_type'] = $class_name;
					$field['field_type_label'] = $this->EE->formsfields[$class_name]->info['title'];
					$field['settings'] = array();

					// Form input name="" for the field title/url_title/desc
					$field['form_name'] = $vData['field_name'].'[fields][]';

					// Create out JSON. We do it here to keep the JSON as clean as possible
					$fieldjson[$class_name] = $field;

					// Form input name="" for custom field settings
					$field['form_name_settings'] = $vData['field_name'].'[fields]['.mt_rand(0, 999999).'][settings]';

					// Do we have any field settings stored?
					if (isset($vData['form']['field_settings'][$class_name]) == TRUE)
					{
						$field['settings'] = $vData['form']['field_settings'][$class_name];
					}

					// We need to add the form settings
					$field['form_settings'] = $vData['form']['settings'];

					// Continue with out JSON
					$fieldjson[$class_name]['field_content'] = $this->EE->formsfields[$class_name]->display_field($field, FALSE);
					$fieldjson[$class_name]['field_settings'] = $this->EE->formsfields[$class_name]->display_settings($field, FALSE);
					$fieldjson[$class_name]['field_required'] = FALSE;
					$fieldjson[$class_name]['field_id'] = 0;
				}
			}

			$vData['fieldjson'] = $this->EE->forms_helper->generate_json($fieldjson);
		}

		unset($fieldjson, $dbfieldjson, $mgroups);

		return $this->EE->load->view('mcp/forms_create', $vData, TRUE);
	}

	// ********************************************************************************* //

	public function update_form()
	{
		$data = (isset($_POST['form']) == TRUE) ? $_POST['form'] : array();

		$form_id = $this->EE->input->post('form_id') ? $this->EE->input->post('form_id') : 0;

		// -----------------------------------------
		// Grab the form!
		// -----------------------------------------
		$this->EE->db->select('*');
		$this->EE->db->from('exp_forms');
		$this->EE->db->where('form_id', $form_id);
		$query = $this->EE->db->get();

		// -----------------------------------------
		// Does it exist?
		// -----------------------------------------
		if ($query->num_rows() == 0)
		{
			// -----------------------------------------
			// Lets create it then!
			// -----------------------------------------
			$fdata = array();
			$fdata['site_id'] = $this->site_id;
			$fdata['member_id'] = $this->EE->session->userdata['member_id'];
			$fdata['form_title'] = $data['settings']['form_title'];
			$fdata['form_url_title'] = $data['settings']['form_url_title'];
			$fdata['date_created'] = $this->EE->localize->now;
			$fdata['form_settings'] = serialize($data['settings']);
			$fdata['form_type'] = 'normal';
			$form_id = $this->EE->forms_model->create_update_form($fdata);
		}
		else
		{
			$form_id = $query->row('form_id');

			// Update it!
			$fdata = array();
			$fdata['form_settings'] = serialize($data['settings']);
			$fdata['form_title'] = $data['settings']['form_title'];
			$fdata['form_url_title'] = $data['settings']['form_url_title'];
			$this->EE->forms_model->create_update_form($fdata, $form_id);
		}

		// -----------------------------------------
		// Grab all fields
		// -----------------------------------------
		$dbfields = array();
		$this->EE->db->select('*');
		$this->EE->db->from('exp_forms_fields');
		$this->EE->db->where('form_id', $form_id);
		$query = $this->EE->db->get();

		foreach($query->result() as $row)
		{
			$dbfields[$row->field_id] = $row;
		}

		// -----------------------------------------
		// Loop over all fields!
		// -----------------------------------------
		
		if (isset($data['fields']) == FALSE) $data['fields'] = array();

		foreach($data['fields'] as $order => $field)
		{
			// We need a label!
			if (isset($field['title']) == FALSE OR trim($field['title']) == FALSE) continue;

			// Check if it's empty
			if (isset($field['settings']) == FALSE) $field['settings'] = array();

			if (isset($field['type']) === FALSE) continue;
			if ($field['type'] == 'pagebreak' && isset($data['fields'][$order+1]['title']) == FALSE) continue;

			$fdata = array();
			$fdata['form_id'] = $form_id;
			$fdata['title'] = $field['title'];
			$fdata['url_title'] = $field['url_title'];
			$fdata['description'] = $field['description'];
			$fdata['field_type'] = $field['type'];
			$fdata['field_order'] = $order;
			$fdata['required'] = (isset($field['required']) == TRUE && $field['required'] == 'yes') ? 1 : 0;
			$fdata['no_dupes'] = (isset($field['no_dupes']) == TRUE && $field['no_dupes'] == 'yes') ? 1 : 0;
			$fdata['field_settings'] = $field['settings'];
			$fdata['field_id'] = (isset($field['field_id']) == TRUE && $field['field_id'] > 0) ? $field['field_id'] : 0;
			$field_id = $this->EE->forms_model->create_update_field($fdata);

			if (isset($dbfields[$field_id]) == TRUE) unset($dbfields[$field_id]);
		}

		// -----------------------------------------
		// Process Templates
		// -----------------------------------------

		foreach($data['templates'] as $type => $template)
		{
			if ($template['which'] == 'predefined')
			{
				$this->EE->forms_model->create_update_form(array($type.'_template' => $template['predefined']), $form_id);
			}
			elseif ($template['which'] == 'custom')
			{
				$fdata = array();
				$fdata['form_id'] = $form_id;
				$fdata['template_label'] = $this->EE->input->get_post('title');
				$fdata['template_name'] = $this->EE->input->get_post('url_title');
				$fdata['template_type'] = $type;
				$fdata['email_type'] 	= $template['custom']['email_type'];
				$fdata['email_wordwrap'] = $template['custom']['email_wordwrap'];
				if (isset($template['custom']['email_to'])) $fdata['email_to'] = $template['custom']['email_to'];
				$fdata['email_from'] 	= $template['custom']['email_from'];
				$fdata['email_from_email'] = $template['custom']['email_from_email'];
				$fdata['email_reply_to'] = $template['custom']['email_reply_to'];
				$fdata['email_reply_to_email'] = $template['custom']['email_reply_to_email'];
				if (isset($template['custom']['reply_to_author'])) $fdata['reply_to_author'] = $template['custom']['reply_to_author'];
				$fdata['email_subject']	= $template['custom']['email_subject'];
				$fdata['email_cc']		= $template['custom']['email_cc'];
				$fdata['email_bcc']		= $template['custom']['email_bcc'];
				$fdata['email_attachments'] = $template['custom']['email_attachments'];
				$fdata['template']		= $template['custom']['template'];
				$this->EE->forms_model->create_update_template($fdata);

				// Update the form too
				$this->EE->forms_model->create_update_form(array($type.'_template' => -1), $form_id);
			}
			else
			{
				$this->EE->forms_model->create_update_form(array($type.'_template' => 0), $form_id);
			}
		}

		// -----------------------------------------
		// Delete all old ones!
		// -----------------------------------------
		if (empty($dbfields) == FALSE)
		{
			$this->EE->forms_model->delete_fields($dbfields);
		}

		$this->EE->functions->redirect($this->base . '&method=forms');
	}

	// ********************************************************************************* //

	public function delete_form()
	{
		$this->EE->forms_model->delete_form( $this->EE->input->get('form_id') );
		$this->EE->functions->redirect($this->base . '&method=forms');
	}

	// ********************************************************************************* //

	public function entries()
	{
		// Page Title & BreadCumbs
		$this->vData['PageHeader'] = 'entries';

		// Get all forms
		$e = $this->EE->lang->line('form:entry_linked');
		$s = $this->EE->lang->line('form:salone');

		$this->vData['forms'] = array();

		$query = $this->EE->db->select('form_title, form_id')->from('exp_forms')->where('entry_id >', 0)->where('site_id', $this->site_id)->order_by('form_title')->get();
		foreach ($query->result() as $row)
		{
			$this->vData['forms'][$e][$row->form_id] = $row->form_title;
		}

		$query = $this->EE->db->select('form_title, form_id')->from('exp_forms')->where('entry_id', 0)->where('site_id', $this->site_id)->order_by('form_title')->get();
		foreach ($query->result() as $row)
		{
			$this->vData['forms'][$s][$row->form_id] = $row->form_title;
		}

		$query = $this->EE->db->select('form_title, form_id')->from('exp_forms')->where('entry_id >', 0)->where('site_id', $this->site_id)->order_by('form_title')->get();

		return $this->EE->load->view('mcp/entries', $this->vData, TRUE);
	}

	// ********************************************************************************* //

	public function templates()
	{
		// Page Title & BreadCumbs
		$this->vData['PageHeader'] = 'templates';

		return $this->EE->load->view('mcp/templates', $this->vData, TRUE);
	}

	// ********************************************************************************* //

	public function create_template()
	{
		// Page Title & BreadCumbs
		$this->vData['PageHeader'] = 'templates';

		$fields = $this->EE->db->list_fields('exp_forms_email_templates');

		foreach ($fields as $name) $this->vData[$name] = '';

		// Edit?
		if ($this->EE->input->get('template_id') != FALSE)
		{
			$query = $this->EE->db->select('*')->from('exp_forms_email_templates')->where('template_id', $this->EE->input->get('template_id'))->get();
			$this->vData = array_merge($this->vData, $query->row_array());
		}

		$this->vData['yes_no'] = array('no' => $this->EE->lang->line('form:no'), 'yes' => $this->EE->lang->line('form:yes'));
		$this->vData['email_types'] = array('text' => $this->EE->lang->line('form:tmpl:email:text'), 'html' => $this->EE->lang->line('form:tmpl:email:html'));
		$this->vData['template_types'] = array('user' => $this->EE->lang->line('form:tmpl:user'), 'admin' => $this->EE->lang->line('form:tmpl:admin'));

		return $this->EE->load->view('mcp/templates_create', $this->vData, TRUE);
	}

	// ********************************************************************************* //

	public function update_template()
	{
		//----------------------------------------
		// Create/Updating?
		//----------------------------------------
		if ($this->EE->input->get('delete') != 'yes')
		{
			$data = array();
			$data['template_label'] = $this->EE->input->post('template_label');
			$data['template_name'] = $this->EE->input->post('template_name');
			$data['template_type'] = $this->EE->input->post('template_type');
			$data['template_desc'] = $this->EE->input->post('template_desc');
			$data['email_type'] 	= $this->EE->input->post('email_type');
			$data['email_wordwrap'] = $this->EE->input->post('email_wordwrap');
			$data['email_to'] 		= $this->EE->input->post('email_to');
			$data['email_from'] 	= $this->EE->input->post('email_from');
			$data['email_from_email'] = $this->EE->input->post('email_from_email');
			$data['email_reply_to'] = $this->EE->input->post('email_reply_to');
			$data['email_reply_to_email'] = $this->EE->input->post('email_reply_to_email');
			$data['reply_to_author'] = $this->EE->input->post('reply_to_author');
			$data['email_subject']	= $this->EE->input->post('email_subject');
			$data['email_cc']		= $this->EE->input->post('email_cc');
			$data['email_bcc']		= $this->EE->input->post('email_bcc');
			$data['email_attachments'] = $this->EE->input->post('email_attachments');
			$data['template']		= $this->EE->input->post('template');

			$this->EE->forms_model->create_update_template($data, $this->EE->input->post('template_id'));

		}

		//----------------------------------------
		// Delete
		//----------------------------------------
		else
		{
			$this->EE->forms_model->delete_template($this->EE->input->get('template_id'));
		}

		$this->EE->functions->redirect($this->base . '&method=templates');
	}

	// ********************************************************************************* //

	public function lists()
	{
		// Page Title & BreadCumbs
		$this->vData['PageHeader'] = 'lists';

		$query = $this->EE->db->select('*')->from('exp_forms_lists')->order_by('list_label', 'ASC')->get();

		$this->vData['lists'] = $query->result();


		return $this->EE->load->view('mcp/lists', $this->vData, TRUE);
	}

	// ********************************************************************************* //

	public function create_list()
	{
		// Page Title & BreadCumbs
		$this->vData['PageHeader'] = 'lists';
		$this->vData['items'] = '';

		$fields = $this->EE->db->list_fields('exp_forms_lists');

		foreach ($fields as $name) $this->vData[$name] = '';

		// Edit?
		if ($this->EE->input->get('list_id') != FALSE)
		{
			$query = $this->EE->db->select('*')->from('exp_forms_lists')->where('list_id', $this->EE->input->get('list_id'))->get();
			$this->vData = array_merge($this->vData, $query->row_array());

			$this->vData['list_data'] = unserialize($this->vData['list_data']);

			foreach ($this->vData['list_data'] as $key => $val)
			{
				$this->vData['items'] .= ($key == $val) ? "{$val}\n": "{$key} : {$val}\n";
			}

			$this->vData['items'] = trim($this->vData['items']);
		}


		return $this->EE->load->view('mcp/lists_create', $this->vData, TRUE);
	}

	// ********************************************************************************* //

	public function update_list()
	{
		//----------------------------------------
		// Create/Updating?
		//----------------------------------------
		if ($this->EE->input->get('delete') != 'yes')
		{
			$data = array();
			$data['list_label'] = $this->EE->input->post('list_label');

			//----------------------------------------
			// Format Items
			//----------------------------------------
			if ($this->EE->input->post('items') == FALSE) show_error('Missing Items!');

			$data['list_data'] = array();
			$items = explode("\n", $this->EE->input->post('items'));
			
			foreach ($items as $line)
			{
				if (strpos($line, ' : ') !== FALSE)
				{
					$line = explode(' : ', $line);
					$data['list_data'][$line[0]] = $line[1];
				}
				else
				{
					$data['list_data'][$line] = $line;
				}
			}

			$data['list_data'] = serialize($data['list_data']);


			$this->EE->forms_model->create_update_list($data, $this->EE->input->post('list_id'));

		}

		//----------------------------------------
		// Delete
		//----------------------------------------
		else
		{
			$this->EE->forms_model->delete_list($this->EE->input->get('list_id'));
		}

		$this->EE->functions->redirect($this->base . '&method=lists');
	}

	// ********************************************************************************* //
	public function settings()
	{
		// Page Title & BreadCumbs
		$this->vData['PageHeader'] = 'settings';
		$conf = $this->EE->config->item('cf_module_defaults');

		// Grab Settings
		$query = $this->EE->db->query("SELECT settings FROM exp_modules WHERE module_name = 'Forms'");
		if ($query->row('settings') != FALSE)
		{
			$settings = @unserialize($query->row('settings'));
			if ($settings != FALSE && isset($settings['site:'.$this->site_id]))
			{
				$conf = $this->EE->forms_helper->array_extend($conf, $settings['site:'.$this->site_id]);
			}
		}

		$this->vData['config'] = $conf;

		return $this->EE->load->view('mcp/settings', $this->vData, TRUE);
	}

	// ********************************************************************************* //

	public function update_settings()
	{
		// Grab Settings
		$query = $this->EE->db->query("SELECT settings FROM exp_modules WHERE module_name = 'Forms'");
		if ($query->row('settings') != FALSE)
		{
			$settings = @unserialize($query->row('settings'));

			if (isset($settings['site:'.$this->site_id]) == FALSE)
			{
				$settings['site:'.$this->site_id] = array();
			}
		}

		$settings['site:'.$this->site_id] = $this->EE->input->post('settings');

		// Put it Back
		$this->EE->db->set('settings', serialize($settings));
		$this->EE->db->where('module_name', 'Forms');
		$this->EE->db->update('exp_modules');


		$this->EE->functions->redirect($this->base . '&method=index');
	}

	// ********************************************************************************* //

	public function mcp_globals()
	{
		$this->EE->cp->set_breadcrumb($this->base, $this->EE->lang->line('forms'));
		$this->EE->cp->add_js_script(array('ui' => array('tabs', 'datepicker', 'dialog')));

		// Add Global JS & CSS & JS Scripts
		$this->EE->forms_helper->mcp_meta_parser('gjs', '', 'ChannelRatings');
		$this->EE->forms_helper->mcp_meta_parser('css', FORMS_THEME_URL . 'forms_mcp.css', 'cr-pbf');
		$this->EE->forms_helper->mcp_meta_parser('css', FORMS_THEME_URL . 'colorbox/colorbox.css', 'jquery.colorbox');
		$this->EE->forms_helper->mcp_meta_parser('css', FORMS_THEME_URL . 'chosen/chosen.css', 'jquery.chosen');
		$this->EE->forms_helper->mcp_meta_parser('css', FORMS_THEME_URL . 'bootstrap.popovers.css', 'bootstrap.popovers');
		$this->EE->forms_helper->mcp_meta_parser('js',  FORMS_THEME_URL . 'jquery.dataTables.js', 'jquery.dataTables', 'jquery');
		$this->EE->forms_helper->mcp_meta_parser('js',  FORMS_THEME_URL . 'jquery.dataTables.ColReorder.js', 'jquery.dataTables.ColReorder', 'jquery');
		$this->EE->forms_helper->mcp_meta_parser('js',  FORMS_THEME_URL . 'chosen/jquery.chosen.js', 'jquery.chosen', 'jquery');
		$this->EE->forms_helper->mcp_meta_parser('js',  FORMS_THEME_URL . 'colorbox/jquery.colorbox.js', 'jquery.colorbox', 'jquery');
		$this->EE->forms_helper->mcp_meta_parser('js',  FORMS_THEME_URL . 'bootstrap.popovers.js', 'bootstrap.popovers', 'bootstrap');
		$this->EE->forms_helper->mcp_meta_parser('js',  FORMS_THEME_URL . 'forms_mcp.js', 'cr-pbf');


	}

	// ********************************************************************************* //

} // END CLASS

/* End of file mcp.forms.php */
/* Location: ./system/expressionengine/third_party/tagger/mcp.forms.php */