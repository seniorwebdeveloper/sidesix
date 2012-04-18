<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Forms AJAX File
 *
 * @package			DevDemon_Forms
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2010 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com/forms/
 */
class Forms_AJAX
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
		$this->EE->lang->loadfile('forms');
		$this->EE->load->model('forms_model');

		if ($this->EE->input->get_post('site_id')) $this->site_id = $this->EE->input->get_post('site_id');
		else if ($this->EE->input->cookie('cp_last_site_id')) $this->site_id = $this->EE->input->cookie('cp_last_site_id');
		else $this->site_id = $this->EE->config->item('site_id');
	}

	// ********************************************************************************* //

	public function save_field_settings()
	{
		$this->EE->load->helper('form');

		//----------------------------------------
		// Prepare Arrays
		//----------------------------------------
		$field_settings = array();
		$form_settings = array();

		// Kill that!
		unset($_POST['ajax_method']);

		// Grab the settings array (should be the only one)
		$p = array_shift($_POST);

		//----------------------------------------
		// Grab our field
		//----------------------------------------
		if (isset($p['fields']) == TRUE && empty($p['fields']) == FALSE)
		{
			// There should be onle one :)
			$field = array_pop($p['fields']);
			$classname = $field['type'];

			// Our field settings?
			if (isset($field['settings']) == FALSE) $field['settings'] = array();
		}
		else
		{
			// Nothing found? Kill!
			exit('ERROR: No field settings found!');
		}

		//----------------------------------------
		// Grab our form settings
		//----------------------------------------
		if (isset($p['settings']) == TRUE && empty($p['settings']) == FALSE)
		{
			// There should be onle one :)
			$form_settings = $p['settings'];
			$field['form_settings'] = $form_settings;
		}
		else
		{
			// Nothing found? Kill!
			exit('ERROR: No Forms Settings found!!');
		}

		//----------------------------------------
		// Grab our Classes
		//----------------------------------------
		if (class_exists('CF_Field') == FALSE) include(PATH_THIRD.'forms/fields/cf_field.php');
		$final_class = 'CF_Field_'.$classname;

		// Do a simple check, we don't want fatal errors
    	if (class_exists($final_class) == FALSE)
    	{
    		// Include it of course! and get the class vars
    		require PATH_THIRD.'forms/fields/' .$classname.'/'. $classname.'.php';
    	}

    	$obj = new $final_class();
    	$field['settings'] = $obj->save_settings($field['settings'], FALSE);

		exit($obj->display_field($field, FALSE));
	}

	// ********************************************************************************* //

	public function submissions_dt()
	{
		$this->EE->load->helper('form');

		//----------------------------------------
		// Prepare Data Array
		//----------------------------------------
		$data = array();
		$data['aaData'] = array();
		$data['iTotalDisplayRecords'] = 0; // Total records, after filtering (i.e. the total number of records after filtering has been applied - not just the number of records being returned in this result set)
		$data['sEcho'] = $this->EE->input->get_post('sEcho');

		// Total records, before filtering (i.e. the total number of records in the database)
		$data['iTotalRecords'] = $this->EE->db->count_all('exp_forms_entries');

		//----------------------------------------
		// Date Ranges?
		//----------------------------------------
		$date_from = FALSE;
		if (isset($_POST['filter']['date']['from']) != FALSE && $_POST['filter']['date']['from'] != FALSE)
		{
			$date_from = strtotime($_POST['filter']['date']['from'] . ' 01:00 AM');
		}

		$date_to = FALSE;
		if (isset($_POST['filter']['date']['to']) != FALSE && $_POST['filter']['date']['to'] != FALSE)
		{
			$date_to = strtotime($_POST['filter']['date']['to'] . ' 11:59 PM');
		}

		//----------------------------------------
		// Forms Filter
		//----------------------------------------
		$filter_forms = FALSE;
		if (isset($_POST['filter']['forms']) != FALSE && empty($_POST['filter']['forms']) == FALSE)
		{
			$filter_forms = $_POST['filter']['forms'];
		}

		//----------------------------------------
		// Country Filter
		//----------------------------------------
		$filter_country = FALSE;
		if (isset($_POST['filter']['country']) != FALSE && empty($_POST['filter']['country']) == FALSE)
		{
			$filter_country = $_POST['filter']['country'];
		}

		$this->EE->db->save_queries = TRUE;

		//----------------------------------------
		// Total after filter
		//----------------------------------------
		$this->EE->db->select('COUNT(*) as total_records', FALSE);
		$this->EE->db->from('exp_forms_entries fe');
		$this->EE->db->join('exp_members mb', 'mb.member_id = fe.member_id', 'left');
		$this->EE->db->join('exp_forms f', 'f.form_id = fe.form_id', 'left');
		if ($date_from) $this->EE->db->where('fe.date >', $date_from);
		if ($date_to) $this->EE->db->where('fe.date <', $date_to);
		if ($filter_forms) $this->EE->db->where_in('fe.form_id', $filter_forms);
		if ($filter_country) $this->EE->db->where_in('fe.country', $filter_country);
		$this->EE->db->where('fe.site_id', $this->site_id);
		$query = $this->EE->db->get();
		$data['iTotalDisplayRecords'] = $query->row('total_records');
		$query->free_result();

		//----------------------------------------
		// Real Query
		//----------------------------------------
		$this->EE->db->select('fe.*, mb.screen_name, f.form_title');
		$this->EE->db->from('exp_forms_entries fe');
		$this->EE->db->join('exp_members mb', 'mb.member_id = fe.member_id', 'left');
		$this->EE->db->join('exp_forms f', 'f.form_id = fe.form_id', 'left');

		//----------------------------------------
		// Sort By
		//----------------------------------------
		$sort_cols = $this->EE->input->get_post('iSortingCols');

		for ($i = 0; $i < $sort_cols; $i++)
		{
			$col = $this->EE->input->get_post('iSortCol_'.$i);
			$sort =  $this->EE->input->get_post('sSortDir_'.$i);

			switch ($col)
			{
				case 0: // ID
					$this->EE->db->order_by('fe.fentry_id', $sort);
					break;
				case 1: // Member
					$this->EE->db->order_by('mb.screen_name', $sort);
					break;
				case 2: // Date
					$this->EE->db->order_by('fe.date', $sort);
					break;
				case 3: // Country
					$this->EE->db->order_by('fe.country', $sort);
					break;
				case 4: // IP
					$this->EE->db->order_by('fe.ip_address', $sort);
					break;
				case 5: // Form
					$this->EE->db->order_by('f.form_title', $sort);
					break;
			}
		}

		//----------------------------------------
		// Limit
		//----------------------------------------
		$limit = 10;
		if ($this->EE->input->get_post('iDisplayLength') !== FALSE)
		{
			$limit = $this->EE->input->get_post('iDisplayLength');
			if ($limit < 1) $limit = 999999;
		}

		//----------------------------------------
		// WHERE/LIKE
		//----------------------------------------
		$this->EE->db->where('fe.site_id', $this->site_id);
		if ($date_from) $this->EE->db->where('fe.date >', $date_from);
		if ($date_to) $this->EE->db->where('fe.date <', $date_to);
		if ($filter_forms) $this->EE->db->where_in('fe.form_id', $filter_forms);
		if ($filter_country) $this->EE->db->where_in('fe.country', $filter_country);

		//----------------------------------------
		// OFFSET & LIMIT & EXECUTE!
		//----------------------------------------
		$offset = 0;
		if ($this->EE->input->get_post('iDisplayStart') !== FALSE)
		{
			$offset = $this->EE->input->get_post('iDisplayStart');
		}

		$this->EE->db->limit($limit, $offset);
		$query = $this->EE->db->get();


		//----------------------------------------
		// Loop Over all
		//----------------------------------------
		foreach ($query->result() as $row)
		{
			// View Form
			$view_link = '<a href="' . $this->EE->input->post('mcp_base').AMP.'method=view_form'.AMP.'form_id='.$row->form_id.'" class="gForm">'.$row->form_title.'</a>';

			// Member Name
			if ($row->member_id == 0) $row->screen_name = $this->EE->lang->line('form:guest');

			//----------------------------------------
			// Create TR row
			//----------------------------------------
			$trow = array();
			$trow[] = '<a href="#" class="OpenFentry">' . $row->fentry_id . '</a>';;
			$trow[] = $row->screen_name;
			$trow[] = $this->EE->localize->decode_date('%d-%M-%Y %g:%i %A', $row->date);
			$trow[] = $row->country;
			$trow[] = long2ip($row->ip_address);
			$trow[] = $view_link;
			$data['aaData'][] = $trow;
		}

		exit($this->EE->forms_helper->generate_json($data));
	}

	// ********************************************************************************* //

	public function forms_dt()
	{
		$this->EE->load->helper('form');

		//----------------------------------------
		// Prepare Data Array
		//----------------------------------------
		$data = array();
		$data['aaData'] = array();
		$data['iTotalDisplayRecords'] = 0; // Total records, after filtering (i.e. the total number of records after filtering has been applied - not just the number of records being returned in this result set)
		$data['sEcho'] = $this->EE->input->get_post('sEcho');

		// Total records, before filtering (i.e. the total number of records in the database)
		$data['iTotalRecords'] = $this->EE->db->count_all('exp_forms_entries');

		//----------------------------------------
		// Total after filter
		//----------------------------------------
		$this->EE->db->select('COUNT(*) as total_records', FALSE);
		$this->EE->db->from('exp_forms f');
		$this->EE->db->join('exp_members mb', 'mb.member_id = f.member_id', 'left');
		$this->EE->db->where('f.site_id', $this->site_id);
		$query = $this->EE->db->get();
		$data['iTotalDisplayRecords'] = $query->row('total_records');
		$query->free_result();

		//----------------------------------------
		// Real Query
		//----------------------------------------
		$this->EE->db->select('f.*, mb.screen_name');
		$this->EE->db->from('exp_forms f');
		$this->EE->db->join('exp_members mb', 'mb.member_id = f.member_id', 'left');

		//----------------------------------------
		// Sort By
		//----------------------------------------
		$sort_cols = $this->EE->input->get_post('iSortingCols');

		for ($i = 0; $i < $sort_cols; $i++)
		{
			$col = $this->EE->input->get_post('iSortCol_'.$i);
			$sort =  $this->EE->input->get_post('sSortDir_'.$i);

			switch ($col)
			{
				case 0: // ID
					$this->EE->db->order_by('f.form_id', $sort);
					break;
				case 1: // Form Title
					$this->EE->db->order_by('f.form_title', $sort);
					break;
				case 2: // Form URL Title
					$this->EE->db->order_by('f.form_url_title', $sort);
					break;
				case 3: // Member
					$this->EE->db->order_by('mb.screen_name', $sort);
					break;
				case 5: // Submissions
					$this->EE->db->order_by('f.total_submissions', $sort);
					break;
				case 6: // Date Created
					$this->EE->db->order_by('f.date_created', $sort);
					break;
				case 7: // Last Submissions
					$this->EE->db->order_by('f.date_last_entry', $sort);
					break;

			}
		}

		//----------------------------------------
		// Limit
		//----------------------------------------
		$limit = 10;
		if ($this->EE->input->get_post('iDisplayLength') !== FALSE)
		{
			$limit = $this->EE->input->get_post('iDisplayLength');
			if ($limit < 1) $limit = 999999;
		}

		//----------------------------------------
		// WHERE/LIKE
		//----------------------------------------
		$this->EE->db->where('f.site_id', $this->site_id);

		//----------------------------------------
		// OFFSET & LIMIT & EXECUTE!
		//----------------------------------------
		$offset = 0;
		if ($this->EE->input->get_post('iDisplayStart') !== FALSE)
		{
			$offset = $this->EE->input->get_post('iDisplayStart');
		}

		$this->EE->db->limit($limit, $offset);
		$query = $this->EE->db->get();


		//----------------------------------------
		// Loop Over all
		//----------------------------------------
		foreach ($query->result() as $row)
		{
			// Form Type
			switch ($row->form_type)
			{
				case 'normal':
					$row->type = '<strong class="blue">' . $this->EE->lang->line('form:salone') . '</strong>';
					break;
				case 'entry':
					$row->type = '<strong class="green">' . $this->EE->lang->line('form:entry_linked') . '</strong>';
					break;
			}

			// View Form
			$view_link = '<a href="' . $this->EE->input->post('mcp_base').AMP.'method=view_form'.AMP.'form_id='.$row->form_id.'" class="gForm tooltips" title="'.$this->EE->lang->line('form:view_submissions').'">'.$row->form_title.'</a>';

			//----------------------------------------
			// Actions
			//----------------------------------------
			$actions = '';

			// Edit
			if ($row->entry_id > 0) $actions .= '<a href="' . $this->EE->input->post('ee_base').AMP.'C=content_publish'.AMP.'M=entry_form'.AMP.'channel_id='.$row->channel_id.AMP.'entry_id='.$row->entry_id.'" class="gEdit tooltips" title="'.$this->EE->lang->line('form:edit_form').'"></a>';
			else $actions .= '<a href="' . $this->EE->input->post('mcp_base').AMP.'method=create_form'.AMP.'form_id='.$row->form_id.'" class="gEdit tooltips" title="'.$this->EE->lang->line('form:edit_form').'"></a>';

			// Delete
			$actions .= '<a href="' . $this->EE->input->post('mcp_base').AMP.'method=delete_form'.AMP.'form_id='.$row->form_id.'" class="gDel tooltips dd-alert" data-alert="delete_form" title="'.$this->EE->lang->line('form:delete_form').'"></a>';

			//----------------------------------------
			// Create TR row
			//----------------------------------------
			$trow = array();
			$trow[] = $row->form_id;
			$trow[] = $view_link;
			$trow[] = $row->form_url_title;
			$trow[] = $row->screen_name;
			$trow[] = $row->type;
			$trow[] = $row->total_submissions;
			$trow[] = $this->EE->localize->decode_date('%d-%M-%Y %g:%i %A', $row->date_created);
			$trow[] = ($row->date_last_entry != FALSE) ? $this->EE->localize->decode_date('%d-%M-%Y %g:%i %A', $row->date_last_entry) : '';
			$trow[] = $actions;
			$data['aaData'][] = $trow;
		}

		exit($this->EE->forms_helper->generate_json($data));
	}

	// ********************************************************************************* //

	public function forms_entries_dt()
	{
		$this->EE->load->helper('form');

		$this->EE->db->save_queries = TRUE;
		$form_id = $this->EE->input->get_post('form_id');

		//----------------------------------------
		// Columns
		//----------------------------------------
		$cols = explode(',', $this->EE->input->get_post('sColumns'));
		$cols_inv = array_flip($cols);

		// Visible Cols
		$visible_cols = array();
		foreach ($_POST['visible_cols'] as $colname)
		{
			// Only for real fields! And fill with dummy data
			if (strpos($colname, 'field_id_') !== FALSE)
			{
				$visible_cols[$colname] = substr($colname, 9);
			}
		}

		//----------------------------------------
		// Prepare Data Array
		//----------------------------------------
		$data = array();
		$data['aaData'] = array();
		$data['iTotalDisplayRecords'] = 0; // Total records, after filtering (i.e. the total number of records after filtering has been applied - not just the number of records being returned in this result set)
		$data['sEcho'] = $this->EE->input->get_post('sEcho');

		// Total records, before filtering (i.e. the total number of records in the database)
		$query = $this->EE->db->select('COUNT(*) as total_records', FALSE)->from('exp_forms_entries')->where('form_id', $form_id)->get();
		$data['iTotalRecords'] = $query->row('total_records');

		//----------------------------------------
		// Date Ranges?
		//----------------------------------------
		$date_from = FALSE;
		if (isset($_POST['filter']['date']['from']) != FALSE && $_POST['filter']['date']['from'] != FALSE)
		{
			$date_from = strtotime($_POST['filter']['date']['from'] . ' 01:00 AM');
		}

		$date_to = FALSE;
		if (isset($_POST['filter']['date']['to']) != FALSE && $_POST['filter']['date']['to'] != FALSE)
		{
			$date_to = strtotime($_POST['filter']['date']['to'] . ' 11:59 PM');
		}

		//----------------------------------------
		// Country Filter
		//----------------------------------------
		$filter_country = FALSE;
		if (isset($_POST['filter']['country']) != FALSE && empty($_POST['filter']['country']) == FALSE)
		{
			$filter_country = $_POST['filter']['country'];
		}

		//----------------------------------------
		// Member Filter
		//----------------------------------------
		$filter_members = FALSE;
		if (isset($_POST['filter']['members']) != FALSE && empty($_POST['filter']['members']) == FALSE)
		{
			$filter_members = $_POST['filter']['members'];
		}

		$this->EE->db->save_queries = TRUE;

		//----------------------------------------
		// Total after filter
		//----------------------------------------
		$this->EE->db->select('COUNT(*) as total_records', FALSE);
		$this->EE->db->from('exp_forms_entries fe');
		$this->EE->db->join('exp_members mb', 'mb.member_id = fe.member_id', 'left');
		$this->EE->db->join('exp_forms f', 'f.form_id = fe.form_id', 'left');
		$this->EE->db->where('fe.form_id', $form_id);
		if ($date_from) $this->EE->db->where('fe.date >', $date_from);
		if ($date_to) $this->EE->db->where('fe.date <', $date_to);
		if ($filter_country) $this->EE->db->where_in('fe.country', $filter_country);
		if ($filter_members) $this->EE->db->where_in('fe.member_id', $filter_members);
		$this->EE->db->where('fe.site_id', $this->site_id);
		$query = $this->EE->db->get();
		$data['iTotalDisplayRecords'] = $query->row('total_records');
		$query->free_result();

		//----------------------------------------
		// Real Query
		//----------------------------------------
		$this->EE->db->select('fe.*, mb.screen_name, f.form_title');
		$this->EE->db->from('exp_forms_entries fe');
		$this->EE->db->join('exp_members mb', 'mb.member_id = fe.member_id', 'left');
		$this->EE->db->join('exp_forms f', 'f.form_id = fe.form_id', 'left');
		foreach ($visible_cols as $field_name => $field_id)
		{
			$this->EE->db->select("fe.fid_{$field_id} AS {$field_name}");
		}

		//----------------------------------------
		// Sort By
		//----------------------------------------
		$sort_cols = $this->EE->input->get_post('iSortingCols');

		for ($i = 0; $i < $sort_cols; $i++)
		{
			$col = $this->EE->input->get_post('iSortCol_'.$i);
			$sort =  $this->EE->input->get_post('sSortDir_'.$i);

			// Translate to column name
			$col = $cols[$col];

			switch ($col)
			{
				case 'fentry_id': // ID
					$this->EE->db->order_by('fe.fentry_id', $sort);
					break;
				case 'member': // Member
					$this->EE->db->order_by('mb.screen_name', $sort);
					break;
				case 'date': // Date
					$this->EE->db->order_by('fe.date', $sort);
					break;
				case 'country': // Country
					$this->EE->db->order_by('fe.country', $sort);
					break;
				case 'ip': // IP
					$this->EE->db->order_by('fe.ip_address', $sort);
					break;
				case (strpos($col, 'field_id_') !== FALSE): // FIELD ID
					if (isset($visible_cols[$col]) == FALSE) break; // Check if it's visible FIRST
					$this->EE->db->order_by($col, $sort);
					break;
			}
		}

		//----------------------------------------
		// Limit
		//----------------------------------------
		$limit = 10;
		if ($this->EE->input->get_post('iDisplayLength') !== FALSE)
		{
			$limit = $this->EE->input->get_post('iDisplayLength');
			if ($limit < 1) $limit = 999999;
		}

		//----------------------------------------
		// WHERE/LIKE
		//----------------------------------------
		$this->EE->db->where('fe.site_id', $this->site_id);
		$this->EE->db->where('fe.form_id', $form_id);
		if ($date_from) $this->EE->db->where('fe.date >', $date_from);
		if ($date_to) $this->EE->db->where('fe.date <', $date_to);
		if ($filter_country) $this->EE->db->where_in('fe.country', $filter_country);
		if ($filter_members) $this->EE->db->where_in('fe.member_id', $filter_members);

		//----------------------------------------
		// OFFSET & LIMIT & EXECUTE!
		//----------------------------------------
		$offset = 0;
		if ($this->EE->input->get_post('iDisplayStart') !== FALSE)
		{
			$offset = $this->EE->input->get_post('iDisplayStart');
		}

		$this->EE->db->limit($limit, $offset);
		$query = $this->EE->db->get();

		//----------------------------------------
		// Grab all fields!
		//----------------------------------------
		$fields = array();
		$q2 = $this->EE->db->select('*')->from('exp_forms_fields')->where('form_id', $form_id)->get();

		foreach ($q2->result_array() as $f)
		{
			$f['settings'] = @unserialize($f['field_settings']);
			$fields[ $f['field_id'] ] = $f;
		}

		//----------------------------------------
		// Loop Over all
		//----------------------------------------
		foreach ($query->result() as $row)
		{
			$trow = array();

			// Member Name
			if ($row->member_id == 0) $row->screen_name = $this->EE->lang->line('form:guest');

			$trow['id'] = $row->fentry_id;
			$trow['fentry_id'] = '<a href="#" class="OpenFentry">' . $row->fentry_id . '</a>';
			$trow['member'] = $row->screen_name;
			$trow['date'] = $this->EE->localize->decode_date('%d-%M-%Y %g:%i%A', $row->date);
			$trow['country'] = strtoupper($row->country);
			$trow['ip'] = long2ip($row->ip_address);

			// Loop over all fields!
			foreach ($cols as $field)
			{
				// Only for real fields! And fill with dummy data
				if (strpos($field, 'field_id_') !== FALSE)
				{
					$trow[$field] = '';
				}
			}

			// All visible
			foreach ($visible_cols as $field_name => $field_id)
			{
				$trow[$field_name] = $this->EE->formsfields[ $fields[$field_id]['field_type'] ]->output_data($fields[$field_id], $row->{$field_name}, 'line');
			}

			// Add to data
			$data['aaData'][] = $trow;
		}

		//print_r($this->EE->db->queries);

		exit($this->EE->forms_helper->generate_json($data));
	}

	// ********************************************************************************* //

	public function email_templates_dt()
	{
		$this->EE->load->helper('form');

		//----------------------------------------
		// Prepare Data Array
		//----------------------------------------
		$data = array();
		$data['aaData'] = array();
		$data['iTotalDisplayRecords'] = 0; // Total records, after filtering (i.e. the total number of records after filtering has been applied - not just the number of records being returned in this result set)
		$data['sEcho'] = $this->EE->input->get_post('sEcho');

		// Total records, before filtering (i.e. the total number of records in the database)
		$data['iTotalRecords'] = $this->EE->db->count_all('exp_forms_email_templates');

		//----------------------------------------
		// Total after filter
		//----------------------------------------
		$this->EE->db->select('COUNT(*) as total_records', FALSE);
		$this->EE->db->from('exp_forms_email_templates f');
		$this->EE->db->where('f.site_id', $this->site_id);
		$this->EE->db->where('f.form_id', 0);
		$query = $this->EE->db->get();
		$data['iTotalDisplayRecords'] = $query->row('total_records');
		$query->free_result();

		//----------------------------------------
		// Real Query
		//----------------------------------------
		$this->EE->db->select('f.*');
		$this->EE->db->from('exp_forms_email_templates f');

		//----------------------------------------
		// Sort By
		//----------------------------------------
		$sort_cols = $this->EE->input->get_post('iSortingCols');

		for ($i = 0; $i < $sort_cols; $i++)
		{
			$col = $this->EE->input->get_post('iSortCol_'.$i);
			$sort =  $this->EE->input->get_post('sSortDir_'.$i);

			switch ($col)
			{
				case 0: // ID
					$this->EE->db->order_by('f.template_id', $sort);
					break;
				case 1: // Template Label
					$this->EE->db->order_by('f.template_label', $sort);
					break;
				case 2: // Template Name
					$this->EE->db->order_by('f.template_name', $sort);
					break;
				case 3: // Template Type
					$this->EE->db->order_by('f.template_type', $sort);
					break;
			}
		}

		//----------------------------------------
		// WHERE/LIKE
		//----------------------------------------
		$this->EE->db->where('f.site_id', $this->site_id);
		$this->EE->db->where('f.form_id', 0);

		//----------------------------------------
		// OFFSET & LIMIT & EXECUTE!
		//----------------------------------------

		$limit = 10;
		if ($this->EE->input->get_post('iDisplayLength') !== FALSE)
		{
			$limit = $this->EE->input->get_post('iDisplayLength');
			if ($limit < 1) $limit = 999999;
		}

		$offset = 0;
		if ($this->EE->input->get_post('iDisplayStart') !== FALSE)
		{
			$offset = $this->EE->input->get_post('iDisplayStart');
		}

		$this->EE->db->limit($limit, $offset);
		$query = $this->EE->db->get();


		//----------------------------------------
		// Loop Over all
		//----------------------------------------
		foreach ($query->result() as $row)
		{

			//----------------------------------------
			// Actions
			//----------------------------------------
			$actions = '';
			$actions .= '<a href="' . $this->EE->input->post('mcp_base').AMP.'method=create_template'.AMP.'template_id='.$row->template_id.'" class="gEdit"></a>';
			$actions .= '<a href="' . $this->EE->input->post('mcp_base').AMP.'method=update_template'.AMP.'template_id='.$row->template_id.AMP.'delete=yes" class="gDel"></a>';

			//----------------------------------------
			// Create TR row
			//----------------------------------------
			$trow = array();
			$trow[] = $row->template_id;
			$trow[] = $row->template_label;
			$trow[] = $row->template_name;
			$trow[] = $this->EE->lang->line('form:tmpl:' . $row->template_type);
			$trow[] = $actions;
			$data['aaData'][] = $trow;
		}

		exit($this->EE->forms_helper->generate_json($data));
	}

	// ********************************************************************************* //

	public function export_entries()
	{
		@set_time_limit(0);
		@ini_set('memory_limit', '64M');
		@ini_set('memory_limit', '96M');
		@ini_set('memory_limit', '128M');
		@ini_set('memory_limit', '160M');
		@ini_set('memory_limit', '192M');
		@ini_set('memory_limit', '256M');
		@ini_set('memory_limit', '320M');
		@ini_set('memory_limit', '512M');

		//----------------------------------------
		// Vars
		//----------------------------------------
		$member_field = (isset($_POST['export']['member_info']) != FALSE) ? $_POST['export']['member_info'] : 'screen_name';

		//----------------------------------------
		// Get All Fields
		//----------------------------------------
		$dbfields = array();
		$this->EE->db->select('*');
		$this->EE->db->from('exp_forms_fields');
		$this->EE->db->where('form_id', $_POST['export']['form_id']);
		$this->EE->db->order_by('field_order');
		$query = $this->EE->db->get();

		foreach ($query->result_array() as $row)
		{
			$row['settings'] = @unserialize($row['field_settings']);
			$dbfields[ $row['field_id'] ] = $row;
		}

		$query->free_result();

		//----------------------------------------
		// What Fields?
		//----------------------------------------
		$fields2export = array();
		if (isset($_POST['export']['fields']) == TRUE && $_POST['export']['fields'] == 'current')
		{
			$fields2export = $_POST['export']['visible_cols'];
		}
		else
		{
			$fields2export[] = 'fentry_id';
			$fields2export[] = 'member';
			$fields2export[] = 'date';
			$fields2export[] = 'country';
			$fields2export[] = 'ip';

			foreach($dbfields as $row)
			{
				$fields2export[] = 'field_id_' . $row['field_id'];
			}
		}

		//----------------------------------------
		// Real Query
		//----------------------------------------
		$this->EE->db->select("fe.*, mb.{$member_field}, f.form_title");
		$this->EE->db->from('exp_forms_entries fe');
		$this->EE->db->join('exp_members mb', 'mb.member_id = fe.member_id', 'left');
		$this->EE->db->join('exp_forms f', 'f.form_id = fe.form_id', 'left');

		foreach ($fields2export as $key => $field)
		{
			if (strpos($field, 'field_id_') !== FALSE)
			{
				$field_id = substr($field, 9);
				$this->EE->db->select("fe.fid_{$field_id} AS {$field}");
			}
		}

		//----------------------------------------
		// Current Entries
		//----------------------------------------
		if ($_POST['export']['entries'] == 'current')
		{
			$this->EE->db->where_in('fe.fentry_id', $_POST['export']['current_entries']);
		}

		$query = $this->EE->db->get();

		//----------------------------------------
		// Columns!
		//----------------------------------------
		$columns = array();
		foreach ($fields2export as $efield)
		{
			if (strpos($efield, 'field_id_') !== FALSE)
			{
				$field_id = substr($efield, 9);
				$columns[] = $dbfields[$field_id]['title'];
			}
			else
			{
				$columns[] = $this->EE->lang->line('form:'.$efield);
			}

		}

		//----------------------------------------
		// Create Data Arrays
		//----------------------------------------
		$data = array();

		// Include Headers?
		if (isset($_POST['export']['include_header']) == TRUE && $_POST['export']['include_header'] == 'yes')
		{
			$data = array($columns);
		}

		foreach ($query->result() as $row)
		{
			$entry = array();

			// Loop over all visible rows
			foreach ($fields2export as $efield)
			{
				switch ($efield) {
					case 'fentry_id':
						$entry[] = $row->fentry_id;
						break;
					case 'member':
						if ($row->{$member_field} == FALSE)
						{
							switch ($member_field) {
								case 'member_id':
									$row->{$member_field} = 0;
									break;
								case 'username':
									$row->{$member_field} = strtoupper($this->EE->lang->line('form:guest'));
									break;
								case 'screen_name':
									$row->{$member_field} = strtoupper($this->EE->lang->line('form:guest'));
									break;

							}
						}
						$entry[] = $row->{$member_field};
						break;
					case 'date':
						$entry[] = $this->EE->localize->decode_date('%Y-%m-%d %g:%i %A', $row->date);
						break;
					case 'country':
						$entry[] = $row->country;
						break;
					case 'ip':
						$entry[] = long2ip($row->ip_address);
						break;
					case (strpos($efield, 'field_id_') !== FALSE):
						$ff_id = substr($efield, 9);
						$entry[] = $this->EE->formsfields[ $dbfields[$ff_id]['field_type'] ]->output_data($dbfields[$ff_id], $row->$efield, 'text');
						break;
				}
			}

			$data[] = $entry;
		}

		//$query->free_result(); unset($query);



		//print_r($query->result());
		//print_r($_POST);
		//print_r($data);
		//print_r($columns);

		// -----------------------------------------
		// Temp Dir to run Actions
		// -----------------------------------------
		$temp_dir = APPPATH.'cache/devdemon_forms/';

		if (@is_dir($temp_dir) === FALSE)
   		{
   			@mkdir($temp_dir, 0777, true);
   			@chmod($temp_dir, 0777);
   		}

		// Last check, does the target dir exist, and is writable
		if (is_really_writable($temp_dir) !== TRUE)
		{
			exit($this->EE->output->show_user_error('general', 'TEMP PATH IS NOT WRITABLE! (EE_CACHE_DIR/devdemon_forms/)'));
		}

		// Temp File
		$filename = 'export_' . date('Ymd-Hi');

		//----------------------------------------
		// CSV ?
		//----------------------------------------
		if (isset($_POST['export']['type']) == FALSE OR $_POST['export']['type'] == 'csv')
		{
			$filename .= '.csv';
			$fp = fopen($temp_dir.$filename, 'w');

			//----------------------------------------
			// What delimiter
			//----------------------------------------
			$delimiter = ',';
			switch ($_POST['export']['delimiter']) {
				case 'comma':
					$delimiter = ',';
					break;
				case 'tab':
					$delimiter = "\t";
					break;
				case 'semicolon':
					$delimiter = ';';
					break;
				case 'pipe':
					$delimiter = '|';
					break;;
			}

			//----------------------------------------
			// What enclosure
			//----------------------------------------
			$enclosure = '"';
			switch ($_POST['export']['enclosure']) {
				case 'quote':
					$enclosure = '\'';
					break;
				case 'double_quote':
					$enclosure = '"';
					break;
			}

			foreach ($data as $entry)
			{
				fputcsv($fp, $entry, $delimiter, $enclosure);
			}

			fclose($fp);

			// Server
			$this->server_file_to_browser($temp_dir.$filename, $filename, 'text/csv');
		}

		//----------------------------------------
		// XLS?
		//----------------------------------------
		elseif ($_POST['export']['type'] == 'xls')
		{
			$filename .= '.xlsx';

			include PATH_THIRD .'forms/libraries/PHPExcel.php';
			include PATH_THIRD .'forms/libraries/PHPExcel/Writer/Excel2007.php';

			$objPHPExcel = new PHPExcel();
			//$objPHPExcel->getProperties()->setCreator("Maarten Balliauw");
			//$objPHPExcel->getProperties()->setLastModifiedBy("Maarten Balliauw");
			//$objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
			//$objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
			//$objPHPExcel->getProperties()->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.");

			$objPHPExcel->setActiveSheetIndex(0);


			foreach ($data as $row => $entry)
			{
				foreach ($entry as $col => $val)
				{
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, $row+1, $val);
				}
			}

			$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
			$objWriter->save($temp_dir.$filename);

			// Server
			$this->server_file_to_browser($temp_dir.$filename, $filename, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		}


	}

	// ********************************************************************************* //

	public function show_form_entry()
	{
		//----------------------------------------
		// Data
		//----------------------------------------
		$fentry_id = $this->EE->input->get_post('fentry_id');
		$vData = array();

		//----------------------------------------
		// Grab fentry
		//----------------------------------------
		$query = $this->EE->db->select('fe.*, mb.screen_name, mb.email')->from('exp_forms_entries fe')->join('exp_members mb', 'mb.member_id = fe.member_id', 'left')->where('fentry_id', $fentry_id)->get();

		if ($query->num_rows() == 0)
		{
			exit('FORM ENTRY NOT FOUND!');
		}

		$vData['fentry'] = $query->row_array();

		// Guest?
		if ($vData['fentry']['member_id'] == 0)
		{
			$vData['fentry']['screen_name'] = strtoupper($this->EE->lang->line('form:guest'));
			$vData['fentry']['email'] = '';
		}

		//----------------------------------------
		// Grab all Fields
		//----------------------------------------
		$this->EE->db->select('*');
		$this->EE->db->from('exp_forms_fields');
		$this->EE->db->where('form_id', $vData['fentry']['form_id']);
		$this->EE->db->where('field_type !=', 'pagebreak');
		$this->EE->db->order_by('field_order', 'ASC');
		$query = $this->EE->db->get();
		foreach ($query->result_array() as $row)
		{
			$row['settings'] = @unserialize($row['field_settings']);
			$vData['dbfields'][] = $row;
		}

		$vData['dbfields'] = $this->EE->forms_helper->array_split($vData['dbfields'], 2);


		return $this->EE->load->view('mcp/view_form_entry', $vData, TRUE);

	}

	// ********************************************************************************* //

	public function choices_ajax_ui()
	{
		$vData = array();

		// Grab all lists
		$query = $this->EE->db->select('*')->from('exp_forms_lists')->order_by('list_label', 'ASC')->get();

		// Loop over lists
		foreach($query->result() as $row)
		{
			$vData['lists'][$row->list_label] = '';

			$row->list_data = unserialize($row->list_data);

			foreach ($row->list_data as $key => $val)
			{
				$vData['lists'][$row->list_label] .= ($key == $val) ? "{$val}\n": "{$key} : {$val}\n";
			}

			$vData['lists'][$row->list_label] = trim($vData['lists'][$row->list_label]);
		}


		exit($this->EE->load->view('form_builder/choices_ajax_ui', $vData, TRUE));
	}

	// ********************************************************************************* //

	private function server_file_to_browser($path, $filename, $mime)
	{
		$filesize = @filesize($path);

		header('Pragma: public');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Cache-Control: public', FALSE);
		header('Content-Description: File Transfer');
		header('Content-Type: ' . $mime);
		header('Accept-Ranges: bytes');
		header('Content-Disposition: attachment; filename="' . $filename . '";');
		header('Content-Transfer-Encoding: binary');
		if ($filesize != FALSE) header('Content-Length: ' . $filesize);

		if (! $fh = fopen($path, 'rb'))
		{
			exit('COULD NOT OPEN FILE.');
		}

		while (!feof($fh))
		{
			@set_time_limit(0);
			print(fread($fh, 8192));
			flush();
		}
		fclose($fh);

		@unlink($path);

		exit();
	}

	// ********************************************************************************* //


} // END CLASS

/* End of file ajax.forms.php  */
/* Location: ./system/expressionengine/third_party/forms/ajax.forms.php */