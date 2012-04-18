<?php if (!defined('BASEPATH')) die('No direct script access allowed');

/**
 * Channel Forms ENTRIES LIST field
 *
 * @package			DevDemon_Forms
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2011 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com/forms/
 * @see				http://expressionengine.com/user_guide/development/fieldtypes.html
 */
class CF_Field_entries_list extends CF_Field
{

	/**
	 * Field info - Required
	 *
	 * @access public
	 * @var array
	 */
	public $info = array(
		'title' 	=>	'Entries List',
		'name' 		=>	'entries_list',
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
		if (isset($field['settings']['store']) == FALSE) $field['settings']['store'] = 'entry_title';

		$out = form_dropdown($field['form_name'], $this->get_entries($field['settings']));

		return $out;
	}

	// ********************************************************************************* //

	public function field_settings($settings=array(), $template=TRUE)
	{
		$vData = $settings;

		// -----------------------------------------
		// What Channels Exists?
		// -----------------------------------------
		$vData['fboards'] = array();
		$query = $this->EE->db->select('channel_id, channel_title')->from('exp_channels')->order_by('channel_title', 'ASC')->get();

		foreach ($query->result() as $row)
		{
			$vData['dbchannels'][$row->channel_id] = $row->channel_title;
		}

		// -----------------------------------------
		// Settings
		// -----------------------------------------
		if (isset($vData['channels']) == FALSE) $vData['channels'] = '';
		if (isset($vData['grouped']) == FALSE) $vData['grouped'] = 'yes';
		if (isset($vData['store']) == FALSE) $vData['store'] = 'entry_title';

		return $this->EE->load->view('settings', $vData, TRUE);
	}

	// ********************************************************************************* //

	private function get_entries($settings)
	{
	    // What to store?
	    if (isset($settings['store']) == FALSE) $settings['store'] = 'entry_title';

		$this->EE->db->select('ct.entry_id, ct.title, ct.url_title');
		$this->EE->db->from('exp_channel_titles ct');

		if (isset($settings['channels']) == TRUE && empty($settings['channels']) == FALSE)
		{
			$this->EE->db->where_in('ct.channel_id', $settings['channels']);
		}

		// Grouped?
		if (isset($settings['grouped']) == TRUE && $settings['grouped'] == 'yes')
		{
			$grouped = TRUE;
			$this->EE->db->join('exp_channels c', 'c.channel_id = ct.channel_id', 'left');
			$this->EE->db->select('c.channel_title');
			$this->EE->db->order_by('c.channel_title', 'ASC');
			$this->EE->db->order_by('ct.title', 'ASC');
		}
		else
		{
			$grouped = FALSE;
			$this->EE->db->order_by('ct.title', 'ASC');
		}

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
				switch ($settings['store'])
				{
					case 'entry_id':
						$out[$row->channel_title][$row->entry_id] = $row->title;
						break;
					case 'entry_title':
						$out[$row->channel_title][$row->title] = $row->title;
						break;
					case 'entry_url_title':
						$out[$row->channel_title][$row->url_title] = $row->title;
						break;
				}
			}
		}
		else
		{
			foreach ($query->result() as $row)
			{
				switch ($settings['store'])
				{
					case 'entry_id':
						$out[$row->entry_id] = $row->title;
						break;
					case 'entry_title':
						$out[$row->title] = $row->title;
						break;
					case 'entry_url_title':
						$out[$row->url_title] = $row->title;
						break;
				}
			}
		}

		return $out;
	}

	// ********************************************************************************* //

}

/* End of file entries_list.php */
/* Location: ./system/expressionengine/third_party/forms/fields/entries_list/entries_list.php */