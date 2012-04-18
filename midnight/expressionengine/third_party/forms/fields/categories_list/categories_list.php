<?php if (!defined('BASEPATH')) die('No direct script access allowed');

/**
 * Channel Forms CATEGORIES LIST field
 *
 * @package			DevDemon_Forms
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2011 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com/forms/
 * @see				http://expressionengine.com/user_guide/development/fieldtypes.html
 */
class CF_Field_categories_list extends CF_Field
{

	/**
	 * Field info - Required
	 *
	 * @access public
	 * @var array
	 */
	public $info = array(
		'title'		=>	'Categories List',
		'name' 		=>	'categories_list',
		'category'	=>	'list_tools',
		'version'	=>	'1.0',
	);

	/**
	 * Constructor
	 *
	 * @access public
	 *
	 * Calls the parent constructor
	 */
	public function __construct()
	{
		parent::__construct();
	}

	// ********************************************************************************* //

	public function render_field($field=array(), $template=TRUE, $data)
	{
		// -----------------------------------------
		// Default Settings
		// -----------------------------------------
		if (isset($field['settings']['grouped']) == FALSE) $field['settings']['grouped'] = 'no';
		if (isset($field['settings']['store']) == FALSE) $field['settings']['store'] = 'cat_name';

		$out = form_dropdown($field['form_name'], $this->get_categories($field['settings']));

		return $out;
	}

	// ********************************************************************************* //

	public function field_settings($settings=array(), $template=TRUE)
	{
		$vData = $settings;

		// -----------------------------------------
		// What Category Groups Exist?
		// -----------------------------------------
		$vData['category_groups'] = array();
		$query = $this->EE->db->select('group_id, group_name')->from('exp_category_groups')->where('site_id', $this->site_id)->order_by('group_name', 'ASC')->get();

		foreach ($query->result() as $row)
		{
			$vData['category_groups'][$row->group_id] = $row->group_name;
		}

		// -----------------------------------------
		// Settings
		// -----------------------------------------
		if (isset($vData['cat_groups']) == FALSE) $vData['cat_groups'] = '';
		if (isset($vData['grouped']) == FALSE) $vData['grouped'] = 'yes';
		if (isset($vData['store']) == FALSE) $vData['store'] = 'cat_name';

		return $this->EE->load->view('settings', $vData, TRUE);
	}

	// ********************************************************************************* //

	private function get_categories($settings)
	{
	    // What to store?
	    $store = 'cat_name';
	    if (isset($settings['store']) == TRUE && $settings['store'] == 'cat_id')
	    {
	        $store = 'cat_id';
	    }

		$this->EE->db->select('c.cat_id, c.cat_name');
		$this->EE->db->from('exp_categories c');

		if (isset($settings['cat_groups']) == TRUE && empty($settings['cat_groups']) == FALSE)
		{
			$this->EE->db->where_in('c.group_id', $settings['cat_groups']);
		}

		if (isset($settings['grouped']) == TRUE && $settings['grouped'] == 'yes')
		{
			$grouped = TRUE;
			$this->EE->db->join('exp_category_groups cg', 'c.group_id = cg.group_id', 'left');
			$this->EE->db->select('cg.group_name');
			$this->EE->db->order_by('cg.group_name', 'ASC');
			$this->EE->db->order_by('c.cat_name', 'ASC');
		}
		else
		{
			$grouped = FALSE;
			$this->EE->db->order_by('c.cat_name', 'ASC');
		}

		$this->EE->db->where('c.site_id', $this->site_id);

		$query = $this->EE->db->get();

		if ($query->num_rows() == 0)
		{
			return array();
		}

		$out = array();

		// Do we need to group them?
		if ($grouped == TRUE)
		{
			foreach ($query->result() as $row)
			{
			    $to_store = ($store == 'cat_name') ? $row->cat_name : $row->cat_id;
			    $out[$row->group_name][$to_store] = $row->cat_name;
			}
		}
		else
		{
			foreach ($query->result() as $row)
			{
				$to_store = ($store == 'cat_name') ? $row->cat_name : $row->cat_id;
				$out[$to_store] = $row->cat_name;
			}
		}

		return $out;
	}

	// ********************************************************************************* //

}

/* End of file categories_list.php */
/* Location: ./system/expressionengine/third_party/forms/fields/categories_list/categories_list.php */