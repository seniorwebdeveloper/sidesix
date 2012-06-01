<?php if (!defined('BASEPATH')) die('No direct script access allowed');

// include config file
include PATH_THIRD.'channel_images/config'.EXT;

/**
 * Channel Images Module FieldType
 *
 * @package			DevDemon_ChannelImages
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2011 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com
 * @see				http://expressionengine.com/user_guide/development/fieldtypes.html
 */
class Channel_images_ft extends EE_Fieldtype
{

	/**
	 * Field info - Required
	 *
	 * @access public
	 * @var array
	 */
	public $info = array(
		'name' 		=> CHANNEL_IMAGES_NAME,
		'version'	=> CHANNEL_IMAGES_VERSION,
	);

	/**
	 * The field settings array
	 *
	 * @access public
	 * @var array
	 */
	public $settings = array();

	public $has_array_data = TRUE;

	/**
	 * Constructor
	 *
	 * @access public
	 *
	 * Calls the parent constructor
	 */
	public function __construct()
	{
		if (version_compare(APP_VER, '2.1.4', '>')) { parent::__construct(); } else { parent::EE_Fieldtype(); }

		if ($this->EE->input->cookie('cp_last_site_id')) $this->site_id = $this->EE->input->cookie('cp_last_site_id');
		else if ($this->EE->input->get_post('site_id')) $this->site_id = $this->EE->input->get_post('site_id');
		else $this->site_id = $this->EE->config->item('site_id');

		$this->EE->load->add_package_path(PATH_THIRD . 'channel_images/');
		$this->EE->lang->loadfile('channel_images');
		$this->EE->load->library('image_helper');
		$this->EE->load->model('channel_images_model');
		$this->EE->image_helper->define_theme_url();

		$this->EE->config->load('ci_config');
	}

	// ********************************************************************************* //

	/**
	 * Display the field in the publish form
	 *
	 * @access public
	 * @param $data String Contains the current field data. Blank for new entries.
	 * @return String The custom field HTML
	 *
	 */
	function display_field($data)
	{
		//----------------------------------------
		// Global Vars
		//----------------------------------------
		$vData = array();
		$vData['missing_settings'] = FALSE;
		$vData['field_name'] = $this->field_name;
		$vData['field_id'] = $this->field_id;
		$vData['temp_key'] = $this->EE->localize->now;
		$vData['entry_id'] = ($this->EE->input->get_post('entry_id') != FALSE) ? $this->EE->input->get_post('entry_id') : FALSE;
		$vData['total_images'] = 0;
		$vData['assigned_images'] = array();

		//----------------------------------------
		// Add Global JS & CSS & JS Scripts
		//----------------------------------------
		$this->EE->image_helper->mcp_meta_parser('gjs', '', 'ChannelImages');
		$this->EE->image_helper->mcp_meta_parser('css', CHANNELIMAGES_THEME_URL . 'channel_images_pbf.css', 'ci-pbf');
		$this->EE->image_helper->mcp_meta_parser('css', CHANNELIMAGES_THEME_URL . 'jquery.colorbox.css', 'jquery.colorbox');
		$this->EE->image_helper->mcp_meta_parser('js', CHANNELIMAGES_THEME_URL . 'jquery.editable.js', 'jquery.editable', 'jquery');
		$this->EE->image_helper->mcp_meta_parser('js', CHANNELIMAGES_THEME_URL . 'jquery.base64.js', 'jquery.base64', 'jquery');
		$this->EE->image_helper->mcp_meta_parser('js', CHANNELIMAGES_THEME_URL . 'jquery.liveurltitle.js', 'jquery.liveurltitle', 'jquery');
		$this->EE->image_helper->mcp_meta_parser('js', CHANNELIMAGES_THEME_URL . 'jquery.colorbox.js', 'jquery.colorbox', 'jquery');
		$this->EE->image_helper->mcp_meta_parser('js', CHANNELIMAGES_THEME_URL . 'hogan.min.js', 'hogan', 'hogan');
		$this->EE->image_helper->mcp_meta_parser('js', CHANNELIMAGES_THEME_URL . 'swfupload.js', 'swfupload', 'swfupload');
		$this->EE->image_helper->mcp_meta_parser('js', CHANNELIMAGES_THEME_URL . 'swfupload.queue.js', 'swfupload.queue', 'swfupload');
		$this->EE->image_helper->mcp_meta_parser('js', CHANNELIMAGES_THEME_URL . 'swfupload.speed.js', 'swfupload.speed', 'swfupload');
		$this->EE->image_helper->mcp_meta_parser('js',  CHANNELIMAGES_THEME_URL . 'channel_images_pbf.js', 'ci-pbf');

		$this->EE->cp->add_js_script(array(
		        'ui'        => array('sortable', 'tabs'),
				'file'		=> array('json2')
		    )
		);

		//----------------------------------------
		// Settings
		//----------------------------------------
		$settings = $this->settings;

		// Settings SET?
		if ( (isset($settings['channel_images']['action_groups']) == FALSE OR empty($settings['channel_images']['action_groups']) == TRUE) && (isset($settings['channel_images']['no_sizes']) == FALSE OR $settings['channel_images']['no_sizes'] != 'yes') )
		{
			$vData['missing_settings'] = TRUE;
			return $this->EE->load->view('pbf_field', $vData, TRUE);
		}

		// Map it Back
		$settings = $settings['channel_images'];
		$defaults = $this->EE->config->item('ci_defaults');

		// Columns?
		if (isset($settings['columns']) == FALSE) $settings['columns'] = $this->EE->config->item('ci_columns');

		// Stored Images
		if (isset($settings['show_stored_images']) == FALSE) $settings['show_stored_images'] = $defaults['show_stored_images'];


		// Limit Images?
		if (isset($settings['image_limit']) == FALSE OR trim($settings['image_limit']) == FALSE) $settings['image_limit'] = 999999;


		$vData['settings'] = $this->EE->image_helper->array_extend($defaults, $settings);

		if (isset($this->session->cache['ChannelImages']['PerImageActionHolder']) == FALSE)
		{
			$vData['actions'] = &$this->EE->image_helper->get_actions();
			$this->session->cache['ChannelImages']['PerImageActionHolder'] = TRUE;
		}

		//----------------------------------------
		// CrossDomain Detect
		//----------------------------------------
		$vData['crossdomain'] = FALSE;
		$vData['current_domain'] = strtolower($_SERVER['HTTP_HOST']);
		$vData['act_domain'] = strtolower(parse_url($this->EE->image_helper->get_router_url(), PHP_URL_HOST));
		if ($vData['current_domain'] != $vData['act_domain']) $vData['crossdomain'] = TRUE;

		//----------------------------------------
		// Field JSON
		//----------------------------------------
		$vData['field_json'] = array();
		$vData['field_json']['key'] = $vData['temp_key'];
		$vData['field_json']['field_name'] = $this->field_name;
		$vData['field_json']['field_label'] = $this->settings['field_label'];
		$vData['field_json']['settings'] = $vData['settings'];
		$vData['field_json']['categories'] = array();

		// Add Categories
		if (isset($settings['categories']) == TRUE && empty($settings['categories']) == FALSE)
		{
			$vData['field_json']['categories'][''] = '';
			foreach ($settings['categories'] as $cat) $vData['field_json']['categories'][$cat] = $cat;
		}

		// Remove some unwanted stuff
		unset($vData['field_json']['settings']['categories']);
		unset($vData['field_json']['settings']['locations']);
		unset($vData['field_json']['settings']['import_path']);

		//----------------------------------------
		// JS Templates
		//----------------------------------------
		$vData['js_templates'] = FALSE;
		if (isset( $this->EE->session->cache['ChannelImages']['JSTemplates'] ) === FALSE)
		{
			$vData['js_templates'] = TRUE;
			$this->EE->session->cache['ChannelImages']['JSTemplates'] = TRUE;

			$vData['langjson'] = array();

			foreach ($this->EE->lang->language as $key => $val)
			{
				if (strpos($key, 'ci:json:') === 0)
				{
					$vData['langjson'][substr($key, 8)] = $val;
					unset($this->EE->lang->language[$key]);
				}

			}

			$vData['langjson'] = $this->EE->image_helper->generate_json($vData['langjson']);
		}

		//----------------------------------------
		// Existing Entry?
		//----------------------------------------
		if ($vData['entry_id'] != FALSE)
		{
			// -----------------------------------------
			// Grab all Images
			// -----------------------------------------
			$this->EE->db->select('*');
			$this->EE->db->from('exp_channel_images');
			$this->EE->db->where('entry_id', $vData['entry_id']);
			$this->EE->db->where('field_id', $this->field_id);

			if (isset($this->EE->session->cache['ep_better_workflow']['is_draft']) && $this->EE->session->cache['ep_better_workflow']['is_draft'])
			{
  				$this->EE->db->where('is_draft', 1);
			}
			else
			{
				$this->EE->db->where('is_draft', 0);
			}

			$this->EE->db->order_by('image_order');
			$query = $this->EE->db->get();

			// -----------------------------------------
			// Which Previews?
			// -----------------------------------------
			if (isset($settings['small_preview']) == FALSE OR $settings['small_preview'] == FALSE)
			{
				$temp = reset($settings['action_groups']);
				$settings['small_preview'] = $temp['group_name'];
			}

			if (isset($settings['big_preview']) == FALSE OR $settings['big_preview'] == FALSE)
			{
				$temp = reset($settings['action_groups']);
				$settings['big_preview'] = $temp['group_name'];
			}

			// Preview URL
			$preview_url = $this->EE->image_helper->get_router_url('url', 'simple_image_url');

			foreach ($query->result() as $image)
			{
				// We need a good field_id to continue
				$image->field_id = $this->EE->channel_images_model->get_field_id($image);

				// Is it a linked image?
				// Then we need to "fake" the channel_id/field_id
				if ($image->link_image_id >= 1)
				{
					$image->entry_id = $image->link_entry_id;
					$image->field_id = $image->link_field_id;
					$image->channel_id = $image->link_channel_id;
				}

				// Just in case lets try to get the field_id again
				$image->field_id = $this->EE->channel_images_model->get_field_id($image);

				// Get settings for that field..
				$temp_settings = $this->EE->channel_images_model->get_field_settings($image->field_id);

				$act_img_url = "{$preview_url}&amp;fid={$image->field_id}&amp;d={$image->entry_id}&amp;f=";
				if ( empty($settings['action_groups']) == FALSE && (isset($settings['no_sizes']) == FALSE OR $settings['no_sizes'] != 'yes') )
				{
					// Display SIzes URL
					$image->small_img_url = $act_img_url . str_replace('.'.$image->extension, "__{$settings['small_preview']}.{$image->extension}", $image->filename);
					$image->big_img_url = $act_img_url .str_replace('.'.$image->extension, "__{$settings['big_preview']}.{$image->extension}", $image->filename);
				}
				else
				{
					// Display SIzes URL
					$image->small_img_url = $act_img_url . $image->filename;
					$image->big_img_url = $act_img_url .$image->filename;
				}

				// ReAssign Field ID (WE NEED THIS)
				$image->field_id = $this->field_id;

				$vData['assigned_images'][] = $image;

				unset($image);
			}

			$vData['total_images'] = $query->num_rows();
		}

		//----------------------------------------
		// Form Submission Error?
		//----------------------------------------
		if (isset($_POST[$this->field_name]) OR isset($_POST['field_id_' . $this->field_id]))
		{
			// Post DATA?
			if (isset($_POST[$this->field_name])) {
				$data = $_POST[$this->field_name];
			}

			if (isset($_POST['field_id_' . $this->field_id])) {
				$data = $_POST['field_id_' . $this->field_id];
			}

			// First.. The Key!
			$vData['temp_key'] = $data['key'];

			if (isset($data['images']) == TRUE)
			{
				$vData['assigned_images'] = '';

				// Preview URL
				$preview_url = $this->EE->image_helper->get_router_url('url', 'simple_image_url');

				foreach($data['images'] as $num => $img)
				{
					$img = $this->EE->image_helper->decode_json(html_entity_decode($img['data']));

					// Existing? lets get it!
					if ($img->image_id > 0)
					{
						$image = $img;
					}
					else
					{
						$image = $img;

						if ($image->link_image_id > 0)
						{
							continue;
						}

						$image->image_id = 0;
						$image->extension = substr( strrchr($image->filename, '.'), 1);
						$image->field_id = $this->field_id;

						// Display SIzes URL
						$image->small_img_url = $preview_url . '&amp;temp_dir=yes&amp;fid='.$this->field_id.'&amp;d=' . $vData['temp_key'] . '&amp;f=' . str_replace('.'.$image->extension, "__{$settings['small_preview']}.{$image->extension}", $image->filename);
						$image->big_img_url = $preview_url . '&amp;temp_dir=yes&amp;fid='.$this->field_id.'&amp;d=' . $vData['temp_key'] . '&amp;f=' . str_replace('.'.$image->extension, "__{$settings['big_preview']}.{$image->extension}", $image->filename);
					}

					// We need a good field_id to continue
					$image->field_id = $this->EE->channel_images_model->get_field_id($image);

					// Is it a linked image?
					// Then we need to "fake" the channel_id/field_id
					if ($image->link_image_id >= 1)
					{
						$image->entry_id = $image->link_entry_id;
						$image->field_id = $image->link_field_id;
						$image->channel_id = $image->link_channel_id;
					}

					// Just in case lets try to get the field_id again
					$image->field_id = $this->EE->channel_images_model->get_field_id($image);

					// ReAssign Field ID (WE NEED THIS)
					$image->field_id = $this->field_id;


					$vData['assigned_images'][] = $image;

					unset($image);
				}
			}
		}

		$vData['field_json']['images'] = $vData['assigned_images'];
		$vData['field_json'] = $this->EE->image_helper->generate_json($vData['field_json']);

		return $this->EE->load->view('pbf_field', $vData, TRUE);
	}

	// ********************************************************************************* //

	/**
	 * Validates the field input
	 *
	 * @param $data Contains the submitted field data.
	 * @return mixed Must return TRUE or an error message
	 */
	public function validate($data)
	{
		// Is this a required field?
		if ($this->settings['field_required'] == 'y')
		{
			if (isset($data['images']) == FALSE OR empty($data['images']) == TRUE)
			{
				return $this->EE->lang->line('ci:required_field');
			}
		}

		return TRUE;
	}

	// ********************************************************************************* //

	/**
	 * Preps the data for saving
	 *
	 * @param $data Contains the submitted field data.
	 * @return string Data to be saved
	 */
	function save($data)
	{
		$this->EE->session->cache['ChannelImages']['FieldData'][$this->field_id] = $data;

		if (isset($data['images']) == FALSE)
		{
			return '';
		}
		else
		{
			return 'ChannelImages';
		}
	}

	// ********************************************************************************* //

	/**
	 * Handles any custom logic after an entry is saved.
	 * Called after an entry is added or updated.
	 * Available data is identical to save, but the settings array includes an entry_id.
	 *
	 * @param $data Contains the submitted field data. (Returned by save())
	 * @access public
	 * @return void
	 */
	function post_save($data)
	{
		return $this->_process_post_save($data);
	}

	// ********************************************************************************* //

	/**
	 * Handles any custom logic after an entry is deleted.
	 * Called after one or more entries are deleted.
	 *
	 * @param $ids array is an array containing the ids of the deleted entries.
	 * @access public
	 * @return void
	 */
	function delete($ids)
	{
		foreach ($ids as $entry_id)
		{
			// -----------------------------------------
			// ENTRY TO FIELD (we need settigns :()
			// -----------------------------------------
			$this->EE->db->select('field_id');
			$this->EE->db->from('exp_channel_images');
			$this->EE->db->where('entry_id', $entry_id);
			$this->EE->db->limit(1);
			$query = $this->EE->db->get();

			if ($query->num_rows() == 0) continue;

			$field_id = $query->row('field_id');

			// Grab the field settings
			$settings = $this->EE->image_helper->grab_field_settings($field_id);
			$settings = $settings['channel_images'];

			// -----------------------------------------
			// Load Location
			// -----------------------------------------
			$location_type = $settings['upload_location'];
			$location_class = 'CI_Location_'.$location_type;

			// Load Settings
			if (isset($settings['locations'][$location_type]) == FALSE)
			{
				$o['body'] = $this->EE->lang->line('ci:location_settings_failure');
				exit( $this->EE->image_helper->generate_json($o) );
			}

			$location_settings = $settings['locations'][$location_type];

			// Load Main Class
			if (class_exists('Image_Location') == FALSE) require PATH_THIRD.'channel_images/locations/image_location.php';

			// Try to load Location Class
			if (class_exists($location_class) == FALSE)
			{
				$location_file = PATH_THIRD.'channel_images/locations/'.$location_type.'/'.$location_type.'.php';

				if (file_exists($location_file) == FALSE)
				{
					$o['body'] = $this->EE->lang->line('ci:location_load_failure');
					exit( $this->EE->image_helper->generate_json($o) );
				}

				require $location_file;
			}

			// Init
			$LOC = new $location_class($location_settings);

			// -----------------------------------------
			// Delete From DB
			// -----------------------------------------
			$this->EE->db->where('entry_id', $entry_id);
			$this->EE->db->or_where('link_entry_id', $entry_id);
			$this->EE->db->delete('exp_channel_images');

			// -----------------------------------------
			// Delete!
			// -----------------------------------------
			$LOC->delete_dir($entry_id);
		}

	}

	// ********************************************************************************* //

	/**
	 * Display the settings page. The default ExpressionEngine rows can be created using built in methods.
	 * All of these take the current $data and the fieltype name as parameters:
	 *
	 * @param $data array
	 * @access public
	 * @return void
	 */
	public function display_settings($data)
	{
		$vData = array();

		// -----------------------------------------
		// Defaults
		// -----------------------------------------
		$vData = $this->EE->config->item('ci_defaults');

		// -----------------------------------------
		// Add JS & CSS
		// -----------------------------------------
		$this->EE->image_helper->mcp_meta_parser('gjs', '', 'ChannelImages');
		$this->EE->image_helper->mcp_meta_parser('css', CHANNELIMAGES_THEME_URL . 'jquery.colorbox.css', 'jquery.colorbox');
		$this->EE->image_helper->mcp_meta_parser('css', CHANNELIMAGES_THEME_URL . 'channel_images_fts.css', 'ci-fts');
		$this->EE->image_helper->mcp_meta_parser('js', CHANNELIMAGES_THEME_URL . 'jquery.editable.js', 'jquery.editable', 'jquery');
		$this->EE->image_helper->mcp_meta_parser('js', CHANNELIMAGES_THEME_URL . 'jquery.base64.js', 'jquery.base64', 'jquery');
		$this->EE->image_helper->mcp_meta_parser('js', CHANNELIMAGES_THEME_URL . 'jquery.colorbox.js', 'jquery.colorbox', 'jquery');
		$this->EE->image_helper->mcp_meta_parser('js', CHANNELIMAGES_THEME_URL . 'hogan.min.js', 'hogan', 'hogan');
		$this->EE->image_helper->mcp_meta_parser('js', CHANNELIMAGES_THEME_URL . 'channel_images_fts.js', 'ci-fts');
		$this->EE->cp->add_js_script(array('ui' => array('tabs', 'draggable', 'sortable')));

		$this->EE->load->library('javascript');
		$this->EE->javascript->output('ChannelImages.Init();');


		// -----------------------------------------
		// Upload Location
		// -----------------------------------------
		$vData['upload_locations'] = $this->EE->config->item('ci_upload_locs');

		// S3 Stuff
		$vData['s3']['regions'] = $this->EE->config->item('ci_s3_regions');
		foreach($vData['s3']['regions'] as $key => $val) $vData['s3']['regions'][$key] = $this->EE->lang->line('ci:s3:region:'.$key);
		$vData['s3']['acl'] = $this->EE->config->item('ci_s3_acl');
		foreach($vData['s3']['acl'] as $key => $val) $vData['s3']['acl'][$key] = $this->EE->lang->line('ci:s3:acl:'.$key);
		$vData['s3']['storage'] = $this->EE->config->item('ci_s3_storage');
		foreach($vData['s3']['storage'] as $key => $val) $vData['s3']['storage'][$key] = $this->EE->lang->line('ci:s3:storage:'.$key);

		// Cloudfiles Stuff
		$vData['cloudfiles']['regions'] = $this->EE->config->item('ci_cloudfiles_regions');
		foreach($vData['cloudfiles']['regions'] as $key => $val) $vData['cloudfiles']['regions'][$key] = $this->EE->lang->line('ci:cloudfiles:region:'.$key);

		// Local
		$vData['local']['locations'] = array();
		$locs = $this->EE->image_helper->get_upload_preferences();
		foreach ($locs as $loc) $vData['local']['locations'][ $loc['id'] ] = $loc['name'];

		// -----------------------------------------
		// Fieldtype Columns
		// -----------------------------------------
		$vData['columns'] = $this->EE->config->item('ci_columns');

		// -----------------------------------------
		// ACT URL
		// -----------------------------------------
		$vData['act_url'] = $this->EE->image_helper->get_router_url();

		// -----------------------------------------
		// Actions!
		// -----------------------------------------
		$vData['actions'] = &$this->EE->image_helper->get_actions();

		$vData['action_groups'] = array();

		if (isset($data['channel_images']['action_groups']) == FALSE && (isset($data['channel_images']['no_sizes']) == FALSE OR $data['channel_images']['no_sizes'] != 'yes') )
		{
			$vData['action_groups'] = $this->EE->config->item('ci_default_action_groups');
		}
		else
		{
			$vData = $this->EE->image_helper->array_extend($vData, $data['channel_images']);
		}

		foreach($vData['action_groups'] as &$group)
		{
			$actions = $group['actions'];
			$group['actions'] = array();

			foreach($actions AS $action_name => &$settings)
			{
				$new = array();
				$new['action_name'] = $vData['actions'][$action_name]->info['title'];
				$new['action_settings'] = $vData['actions'][$action_name]->display_settings($settings);
				$group['actions'][] = $new;
			}

			if (isset($group['wysiwyg']) == TRUE && $group['wysiwyg'] == 'no') unset($group['wysiwyg']);
		}

		// -----------------------------------------
		// Previews
		// -----------------------------------------
		if (isset($vData['small_preview']) == FALSE OR $vData['small_preview'] == FALSE)
		{
			$temp = reset($vData['action_groups']);
			$vData['small_preview'] = $temp['group_name'];
		}

		// Big Preview
		if (isset($vData['big_preview']) == FALSE OR $vData['big_preview'] == FALSE)
		{
			$temp = reset($vData['action_groups']);
			$vData['big_preview'] = $temp['group_name'];
		}

		$vData['action_groups'] = $this->EE->image_helper->generate_json($vData['action_groups']);


		// -----------------------------------------
		// Merge Settings
		// -----------------------------------------
		$vData = $this->EE->image_helper->array_extend($vData, $data);

		// -----------------------------------------
		// Display Row
		// -----------------------------------------
		$row = $this->EE->load->view('fts_settings', $vData, TRUE);
		$this->EE->table->add_row(array('data' => $row, 'colspan' => 2));
	}

	// ********************************************************************************* //

	/**
	 * Save the fieldtype settings.
	 *
	 * @param $data array Contains the submitted settings for this field.
	 * @access public
	 * @return array
	 */
	public function save_settings($data)
	{
		$settings = array();

		// Is it there?
		if (isset($_POST['channel_images']) == FALSE) return $settings;

		$P = $_POST['channel_images'];

		// We need this for the url_title() method!
		$this->EE->load->helper('url');

		// Get Actions
		$actions = &$this->EE->image_helper->get_actions();

		// -----------------------------------------
		// Loop over all action_groups (if any)
		// -----------------------------------------
		if (isset($P['action_groups']) == TRUE)
		{
			foreach($P['action_groups'] as $order => &$group)
			{
				// Format Group Name
				$group['group_name'] = strtolower(url_title($group['group_name']));

				// WYSIWYG
				if (isset($group['wysiwyg']) == FALSE OR $group['wysiwyg'] == FALSE)
				{
					$group['wysiwyg'] = 'no';
				}

				// -----------------------------------------
				// Process Actions
				// -----------------------------------------
				if (isset($group['actions']) == FALSE OR empty($group['actions']) == TRUE)
				{
					unset($P['action_groups'][$order]);
					continue;
				}

				foreach($group['actions'] as $action => &$action_settings)
				{
					if (isset($actions[$action]) == FALSE)
					{
						unset($group['actions'][$action]);
						continue;
					}

					$action_settings = $actions[$action]->save_settings($action_settings);
				}
			}

			// -----------------------------------------
			// Previews
			// -----------------------------------------
			if (isset($P['small_preview']) == TRUE && $P['small_preview'] != FALSE)
			{
				$P['small_preview'] = $P['action_groups'][$P['small_preview']]['group_name'];
			}
			else
			{
				$P['small_preview'] = $P['action_groups'][1]['group_name'];
			}

			// Big Preview
			if (isset($P['big_preview']) == TRUE && $P['big_preview'] != FALSE)
			{
				$P['big_preview'] = $P['action_groups'][$P['big_preview']]['group_name'];
			}
			else
			{
				$P['big_preview'] = $P['action_groups'][1]['group_name'];
			}
		}
		else
		{
			// Mark it as having no sizes!
			$P['no_sizes'] = 'yes';
			$P['action_groups'] = array();
		}


		// -----------------------------------------
		// Parse categories
		// -----------------------------------------
		$categories = array();
		foreach (explode(',', $P['categories']) as $cat)
		{
			$cat = trim ($cat);
			if ($cat != FALSE) $categories[] = $cat;
		}

		$P['categories'] = $categories;

		// -----------------------------------------
		// Put it Back!
		// -----------------------------------------
		$settings['channel_images'] = $P;

		return $settings;
	}

	// ********************************************************************************* //

	/**
	 * Allows the specification of an array of fields to be added,
	 * modified or dropped when custom fields are created, edited or deleted.
	 *
	 * $data contains the settings for this field as well an indicator of
	 * the action being performed ($data['ee_action'] with a value of delete, add or get_info).
	 *
	 *  By default, when a new custom field is created,
	 *  2 fields are added to the exp_channel_data table.
	 *  The content field (field_id_x) is a text field and the format field (field_ft_x)
	 *  is a tinytext NULL default. You may override or add to those defaults
	 *  by including an array of fields and field formatting options in this method.
	 *
	 * @param $data array Contains the submitted settings for this field.
	 * @access public
	 * @return array
	 */
	function settings_modify_column($data)
	{
		if ($data['ee_action'] == 'delete')
		{
			// Load the API
			if (class_exists('Channel_Images_API') != TRUE) include 'api.channel_images.php';
			$API = new Channel_Images_API();

			$field_id = $data['field_id'];

			// Grab all images
			$this->EE->db->select('image_id, field_id, entry_id, filename, extension');
			$this->EE->db->from('exp_channel_images');
			$this->EE->db->where('field_id', $field_id);
			$this->EE->db->where('link_image_id', 0);
			$query = $this->EE->db->get();

			foreach ($query->result() as $row)
			{
				$API->delete_image($row);
			}
		}

		$fields = parent::settings_modify_column($data);

		return $fields;
	}

	// ********************************************************************************* //

	/**
	 * Replace Tag - Replace the field tag on the frontend.
	 *
	 * @param  mixed   $data    contains the field data (or prepped data, if using pre_process)
	 * @param  array   $params  contains field parameters (if any)
	 * @param  boolean $tagdata contains data between tag (for tag pairs)
	 * @return string           template data
	 */
	public function replace_tag($data, $params=array(), $tagdata = FALSE)
	{
		// We always need tagdata
		if ($tagdata === FALSE) return '';

		if (isset($params['prefetch']) == TRUE && $params['prefetch'] == 'yes')
		{
			// In some cases EE stores the entry_ids of the whole loop
			// We can use this to our advantage by grabbing
			if (isset($this->EE->session->cache['channel']['entry_ids']) === TRUE)
			{
				$this->EE->channel_images_model->pre_fetch_data($this->EE->session->cache['channel']['entry_ids'], $params);
			}
		}

		return $this->EE->channel_images_model->parse_template($this->row['entry_id'], $this->field_id, $params, $tagdata);
	}

	// ********************************************************************************* //

	public function draft_save($data, $draft_action)
	{
		// -----------------------------------------
		// Are we creating a new draft?
		// -----------------------------------------
		if ($draft_action == 'create')
		{

			// We are doing this because if you delete an image in live mode
			// and hit the draft button, we need to reflect that delete action in the draft
			$images = array();
			if (isset($data['images']) == TRUE)
			{
				foreach ($data['images'] as $key => $file)
				{
					$file = $this->EE->image_helper->decode_json($file['data']);
					if (isset($file->delete) === TRUE)
					{
						unset($data['images'][$key]);
						continue;
					}

					if (isset($file->image_id) === TRUE && $file->image_id > 0) $images[] = $file->image_id;
				}
			}

			if (count($images) > 0)
			{
				// Grab all existing images
				$query = $this->EE->db->select('*')->from('exp_channel_images')->where_in('image_id', $images)->get();

				foreach ($query->result_array() as $row)
				{
					$row['is_draft'] = 1;
					unset($row['image_id']);
					$this->EE->db->insert('exp_channel_images', $row);
				}
			}
		}

		$this->_process_post_save($data, $draft_action);

		if (isset($data['images']) == FALSE) return '';
		else return 'ChannelImages';
	}

	// ********************************************************************************* //

	public function draft_discard()
	{
		$entry_id = $this->settings['entry_id'];
		$field_id = $this->settings['field_id'];

		// Load the API
		if (class_exists('Channel_Images_API') != TRUE) include 'api.channel_images.php';
		$API = new Channel_Images_API();

		// Grab all existing images
		$query = $this->EE->db->select('*')->from('exp_channel_images')->where('entry_id', $this->settings['entry_id'])->where('field_id', $this->settings['field_id'])->where('is_draft', 1)->get();

		foreach ($query->result() as $row)
		{
			$API->delete_image($row);
		}
	}

	// ********************************************************************************* //


	public function draft_publish()
	{

		// Load the API
		if (class_exists('Channel_Images_API') != TRUE) include 'api.channel_images.php';
		$API = new Channel_Images_API();

		// Grab all existing images
		$query = $this->EE->db->select('*')->from('exp_channel_images')->where('entry_id', $this->settings['entry_id'])->where('field_id', $this->settings['field_id'])->where('is_draft', 0)->get();

		foreach ($query->result() as $row)
		{
			$API->delete_image($row);
		}

		// Grab all existing images
		$query = $this->EE->db->select('image_id')->from('exp_channel_images')->where('entry_id', $this->settings['entry_id'])->where('field_id', $this->settings['field_id'])->where('is_draft', 1)->get();

		foreach ($query->result() as $row)
		{
			$this->EE->db->set('is_draft', 0);
			$this->EE->db->where('image_id', $row->image_id);
			$this->EE->db->update('exp_channel_images');
		}
	}

	// ********************************************************************************* //

	private function _process_post_save($data, $draft_action=NULL)
	{
		//print_r($data); exit();

		$this->EE->load->library('image_helper');
		$this->EE->load->helper('url');

		// Are we using Better Workflow?
		if ($draft_action !== NULL)
		{
			$is_draft = 1;
			$entry_id = $this->settings['entry_id'];
			$field_id = $this->settings['field_id'];
			$channel_id = $this->settings['channel_id'];
			$settings = $this->EE->channel_images_model->get_field_settings($field_id);
			$settings = $settings['channel_images'];
		}
		else
		{
			$is_draft = 0;
			$data = (isset($this->EE->session->cache['ChannelImages'])) ? $this->EE->session->cache['ChannelImages']['FieldData'][$this->field_id] : FALSE;
			$entry_id = $this->settings['entry_id'];
			$channel_id = $this->EE->input->post('channel_id');
			$field_id = $this->field_id;

			// Grab Settings
			$settings = $this->settings['channel_images'];
		}

		// Do we need to skip?
		if (isset($data['images']) == FALSE) return;

		// Our Key
		$key = $data['key'];

		// -----------------------------------------
		// Load Location
		// -----------------------------------------
		$location_type = $settings['upload_location'];
		$location_class = 'CI_Location_'.$location_type;
		$location_settings = $settings['locations'][$location_type];

		// Load Main Class
		if (class_exists('Image_Location') == FALSE) require PATH_THIRD.'channel_images/locations/image_location.php';

		// Try to load Location Class
		if (class_exists($location_class) == FALSE)
		{
			$location_file = PATH_THIRD.'channel_images/locations/'.$location_type.'/'.$location_type.'.php';

			require $location_file;
		}

		// Init!
		$LOC = new $location_class($location_settings);

		// Create the DIR!
		$LOC->create_dir($entry_id);

		// Image Widths,Height,Filesize
		$metadata = array();

		// Try to load Location Class
		if (class_exists($location_class) == FALSE)
		{
			$location_file = PATH_THIRD.'channel_images/locations/'.$location_type.'/'.$location_type.'.php';

			require $location_file;
		}

		// Load the API
		if (class_exists('Channel_Images_API') != TRUE) include 'api.channel_images.php';
		$API = new Channel_Images_API();

		// -----------------------------------------
		// Upload all Images!
		// -----------------------------------------
		$temp_dir = APPPATH.'cache/channel_images/field_'.$field_id.'/'.$key;

		// Loop over all files
		$tempfiles = @scandir($temp_dir);

		if (is_array($tempfiles) == TRUE)
		{
			foreach ($tempfiles as $tempfile)
			{
				if ($tempfile == '.' OR $tempfile == '..') continue;

				$file	= $temp_dir . '/' . $tempfile;

				$res = $LOC->upload_file($file, $tempfile, $entry_id);

		    	if ($res == FALSE)
		    	{

		    	}

		    	// Parse Image Size
		    	$imginfo = @getimagesize($file);

				// Metadata!
				$metadata[$tempfile] = array('width' => @$imginfo[0], 'height' => @$imginfo[1], 'size' => @filesize($file));

				@unlink($file);
			}
		}

		@unlink($temp_dir);

		// -----------------------------------------
		// Grab all the files from the DB
		// -----------------------------------------
		$this->EE->db->select('*');
		$this->EE->db->from('exp_channel_images');
		$this->EE->db->where('entry_id', $entry_id);
		$this->EE->db->where('field_id', $field_id);

		if ($is_draft === 1 && $draft_action == 'update')
		{
			$this->EE->db->where('is_draft', 1);
		}
		else
		{
			$this->EE->db->where('is_draft', 0);
		}

		$query = $this->EE->db->get();

		// -----------------------------------------
		// Lets create an image hash! So we can check for unique images
		// -----------------------------------------
		$dbimages = array();
		foreach ($query->result() as $row)
		{
			$dbimages[] = $row->image_id.$row->filename;
		}


		if ($is_draft === 1 && $draft_action == 'create')
		{
			$dbimages = array();
		}

		if (count($dbimages) > 0)
		{
			// Not fresh, lets see whats new.
			foreach ($data['images'] as $order => $file)
			{
				$file = $this->EE->image_helper->decode_json($file['data']);

				if (isset($file->delete) == TRUE)
				{
					$API->delete_image($file);
				}

				// If we are creating a new draft, we already copied all data.. So lets kill the ones that came through POST
				if ($is_draft === 1 && $file->image_id > 0)
				{
					continue;
				}

				//Extension
				$extension = substr( strrchr($file->filename, '.'), 1);

				// Mime type
				$filemime = 'image/jpeg';
				if ($extension == 'png') $filemime = 'image/png';
				elseif ($extension == 'gif') $filemime = 'image/gif';

				// Check for link_image_id
				if (isset($file->link_image_id) == FALSE) $file->link_image_id = 0;
				$file->link_entryid = 0;
				$file->link_channelid = 0;
				$file->link_fieldid = 0;

				// Check URL Title
				if (isset($file->url_title) == FALSE OR $file->url_title == FALSE)
				{
					$file->url_title = url_title(trim(strtolower($file->title)));
				}

				if ($this->EE->image_helper->in_multi_array($file->image_id.$file->filename, $dbimages) === FALSE)
				{
					// Parse Image Size
					$width=''; $height=''; $filesize='';

					// -----------------------------------------
					// Parse width/height/field_id/channel_id/entry_id
					// -----------------------------------------
					if ($file->link_image_id > 0)
					{
						$imgquery = $this->EE->db->query("SELECT entry_id, field_id, channel_id, filesize, width, height, sizes_metadata FROM exp_channel_images WHERE image_id = {$file->link_image_id} ");
						$file->link_entryid = $imgquery->row('entry_id');
						$file->link_channelid = $imgquery->row('channel_id');
						$file->link_fieldid = $imgquery->row('field_id');
						$width = $imgquery->row('width');
						$height = $imgquery->row('height');
						$filesize = $imgquery->row('filesize');
						$mt = $imgquery->row('sizes_metadata');
						if (is_string($mt) == FALSE) $mt = ''; // Some installs get weird mysql errors
					}
					else
					{
						$width = isset($metadata[$file->filename]['width']) ? $metadata[$file->filename]['width'] : 0;
						$height = isset($metadata[$file->filename]['height']) ? $metadata[$file->filename]['height'] : 0;
						$filesize = isset($metadata[$file->filename]['size']) ? $metadata[$file->filename]['size'] : 0;

						// -----------------------------------------
						// Parse Size Metadata!
						// -----------------------------------------
						$mt = '';
						foreach($settings['action_groups'] as $group)
						{
							$name = strtolower($group['group_name']);
							$size_filename = str_replace('.'.$extension, "__{$name}.{$extension}", $file->filename);

							$mt .= $name.'|' . implode('|', $metadata[$size_filename]) . '/';
						}
					}

					// -----------------------------------------
					// New File
					// -----------------------------------------
					$data = array(	'site_id'	=>	$this->site_id,
									'entry_id'	=>	$entry_id,
									'channel_id'=>	$channel_id,
									'member_id'	=>	$this->EE->session->userdata['member_id'],
									'is_draft'	=>	$is_draft,
									'link_image_id' => $file->link_image_id,
									'link_entry_id' => $file->link_entryid,
									'link_channel_id' => $file->link_channelid,
									'link_field_id' => $file->link_fieldid,
									'upload_date' => $this->EE->localize->now,
									'field_id'	=>	$field_id,
									'image_order'	=>	$order,
									'filename'	=>	$file->filename,
									'extension' =>	$extension,
									'mime'		=>	$filemime,
									'filesize'	=>	$filesize,
									'width'		=>	$width,
									'height'	=>	$height,
									'title'		=>	$this->EE->security->xss_clean($file->title),
									'url_title'	=>	$this->EE->security->xss_clean($file->url_title),
									'description' => $this->EE->security->xss_clean($file->description),
									'category' 	=>	(isset($file->category) == true) ? $file->category : '',
									'cifield_1'	=>	$this->EE->security->xss_clean($file->cifield_1),
									'cifield_2'	=>	$this->EE->security->xss_clean($file->cifield_2),
									'cifield_3'	=>	$this->EE->security->xss_clean($file->cifield_3),
									'cifield_4'	=>	$this->EE->security->xss_clean($file->cifield_4),
									'cifield_5'	=>	$this->EE->security->xss_clean($file->cifield_5),
									'cover'		=>	$file->cover,
									'sizes_metadata' => $mt,
								);

					$this->EE->db->insert('exp_channel_images', $data);
				}
				else
				{
					// -----------------------------------------
					// Old File
					// -----------------------------------------
					$data = array(	'cover'		=>	$file->cover,
									'channel_id'=>	$channel_id,
									'field_id'	=>	$field_id,
									'is_draft'	=>	$is_draft,
									'image_order'=>	$order,
									'title'		=>	$this->EE->security->xss_clean($file->title),
									'url_title'	=>	$this->EE->security->xss_clean($file->url_title),
									'description' => $this->EE->security->xss_clean($file->description),
									'category' 	=>	(isset($file->category) == true) ? $file->category : '',
									'cifield_1'	=>	$this->EE->security->xss_clean($file->cifield_1),
									'cifield_2'	=>	$this->EE->security->xss_clean($file->cifield_2),
									'cifield_3'	=>	$this->EE->security->xss_clean($file->cifield_3),
									'cifield_4'	=>	$this->EE->security->xss_clean($file->cifield_4),
									'cifield_5'	=>	$this->EE->security->xss_clean($file->cifield_5),
									'mime'		=>	$filemime,
								);

					$this->EE->db->update('exp_channel_images', $data, array('image_id' =>$file->image_id));
				}
			}
		}
		else
		{
			// No previous entries, fresh fresh
			foreach ($data['images'] as $order => $file)
			{
				$file = $this->EE->image_helper->decode_json($file['data']);

				// If we are creating a new draft, we already copied all data.. So lets kill the ones that came through POST
				if ($is_draft === 1 && $file->image_id > 0)
				{
					/*
					if (isset($file->delete) == TRUE)
					{
						$API->delete_image($file);
					}*/

					continue;
				}

				//Extension
				$extension = substr( strrchr($file->filename, '.'), 1);

				// Mime type
				$filemime = 'image/jpeg';
				if ($extension == 'png') $filemime = 'image/png';
				elseif ($extension == 'gif') $filemime = 'image/gif';

				// Check for link_image_id
				if (isset($file->link_image_id) == FALSE) $file->link_image_id = 0;
				$file->link_entryid = 0;
				$file->link_channelid = 0;
				$file->link_fieldid = 0;

				// Parse Image Size
				$width=''; $height=''; $filesize='';

				// Lets grab original width/height/field_id/channel_id/entry_id
				if ($file->link_image_id > 0)
				{
					$imgquery = $this->EE->db->query("SELECT entry_id, field_id, channel_id, filesize, width, height FROM exp_channel_images WHERE image_id = {$file->link_image_id} ");
					$file->link_entryid = $imgquery->row('entry_id');
					$file->link_channelid = $imgquery->row('channel_id');
					$file->link_fieldid = $imgquery->row('field_id');
					$width = $imgquery->row('width');
					$height = $imgquery->row('height');
					$filesize = $imgquery->row('filesize');
					$mt = $imgquery->row('sizes_metadata');
					if (is_string($mt) == FALSE) $mt = ''; // Some installs get weird mysql errors
				}
				else
				{
					$width = isset($metadata[$file->filename]['width']) ? $metadata[$file->filename]['width'] : 0;
					$height = isset($metadata[$file->filename]['height']) ? $metadata[$file->filename]['height'] : 0;
					$filesize = isset($metadata[$file->filename]['size']) ? $metadata[$file->filename]['size'] : 0;

					// -----------------------------------------
					// Parse Size Metadata!
					// -----------------------------------------
					$mt = '';
					foreach($settings['action_groups'] as $group)
					{
						$name = strtolower($group['group_name']);
						$size_filename = str_replace('.'.$extension, "__{$name}.{$extension}", $file->filename);

						$mt .= $name.'|' . implode('|', $metadata[$size_filename]) . '/';
					}
				}

				// Check URL Title
				if (isset($file->url_title) OR $file->url_title == FALSE)
				{
					$file->url_title = url_title(trim(strtolower($file->title)));
				}

				// -----------------------------------------
				// New File
				// -----------------------------------------
				$data = array(	'site_id'	=>	$this->site_id,
								'entry_id'	=>	$entry_id,
								'channel_id'=>	$channel_id,
								'member_id'=>	$this->EE->session->userdata['member_id'],
								'is_draft'	=>	$is_draft,
								'link_image_id' => $file->link_image_id,
								'link_entry_id' => $file->link_entryid,
								'link_channel_id' => $file->link_channelid,
								'link_field_id' => $file->link_fieldid,
								'upload_date' => $this->EE->localize->now,
								'field_id'=>	$field_id,
								'image_order'	=>	$order,
								'filename'	=>	$file->filename,
								'extension' =>	$extension,
								'mime'		=>	$filemime,
								'filesize'	=>	$filesize,
								'width'		=>	$width,
								'height'	=>	$height,
								'title'		=>	$this->EE->security->xss_clean($file->title),
								'url_title'	=>	$this->EE->security->xss_clean($file->url_title),
								'description' => $this->EE->security->xss_clean($file->description),
								'category' 	=>	(isset($file->category) == true) ? $file->category : '',
								'cifield_1'	=>	$this->EE->security->xss_clean($file->cifield_1),
								'cifield_2'	=>	$this->EE->security->xss_clean($file->cifield_2),
								'cifield_3'	=>	$this->EE->security->xss_clean($file->cifield_3),
								'cifield_4'	=>	$this->EE->security->xss_clean($file->cifield_4),
								'cifield_5'	=>	$this->EE->security->xss_clean($file->cifield_5),
								'cover'		=>	$file->cover,
								'sizes_metadata' => $mt,
							);

				$this->EE->db->insert('exp_channel_images', $data);
			}
		}

		// -----------------------------------------
		// WYGWAM
		// -----------------------------------------

		// Which field_group is assigned to this channel?
		$query = $this->EE->db->select('field_group')->from('exp_channels')->where('channel_id', $channel_id)->get();
		if ($query->num_rows() == 0) return;
		$field_group = $query->row('field_group');

		// Which fields are WYGWAM/wyvern AND Textarea
		$query = $this->EE->db->select('field_id')->from('exp_channel_fields')->where('group_id', $field_group)->where('field_type', 'wygwam')->or_where('field_type', 'wyvern')->or_where('field_type', 'textarea')->get();
		if ($query->num_rows() == 0) return;

		// Harvest all of them
		$fields = array();

		foreach ($query->result() as $row)
		{
			$fields[] = 'field_id_' . $row->field_id;
		}

		if (count($fields) > 0)
		{
			// Grab them!
			foreach ($fields as $field)
			{
				$this->EE->db->set($field, " REPLACE({$field}, 'temp_dir=yes&amp;', '') ", FALSE);
				$this->EE->db->where('entry_id', $entry_id);
				$this->EE->db->update('exp_channel_data');

				$this->EE->db->set($field, " REPLACE({$field}, 'd={$key}&amp;', 'd={$entry_id}&amp;') ", FALSE);
				$this->EE->db->where('entry_id', $entry_id);
				$this->EE->db->update('exp_channel_data');
			}

		}

		// Delete old dirs
		$API->clean_temp_dirs($this->field_id);

		//preg_match_all('/\< *[img][^\>]*[src] *= *[\"\']{0,1}([^\"\'\ >]*)/i', $field, $matches);
		return;
	}

}

/* End of file ft.channel_images.php */
/* Location: ./system/expressionengine/third_party/channel_images/ft.channel_images.php */