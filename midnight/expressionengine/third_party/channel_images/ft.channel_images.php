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
	 * $this->settings =
	 *  Array
	 *  (
	 *      [field_id] =>
	 *      [field_label] =>
	 *      [field_required] => n
	 *      [field_data] =>
	 *      [field_list_items] =>
	 *      [field_fmt] =>
	 *      [field_instructions] =>
	 *      [field_show_fmt] => n
	 *      [field_pre_populate] => n
	 *      [field_text_direction] => ltr
	 *      [field_type] =>
	 *      [field_name] =>
	 *      [field_channel_id] =>
	 *  )
	 */
	function display_field($data)
	{
		//----------------------------------------
		// Global Vars
		//----------------------------------------
		$vData = array();
		$vData['dupe_field'] = FALSE;
		$vData['missing_settings'] = FALSE;
		$vData['field_name'] = $this->field_name;
		$vData['field_id'] = $this->field_id;
		$vData['site_id'] = $this->site_id;
		$vData['temp_key'] = $this->EE->localize->now;
		$vData['channel_id'] = ($this->EE->input->get_post('channel_id') != FALSE) ? $this->EE->input->get_post('channel_id') : 0;
		$vData['entry_id'] = ($this->EE->input->get_post('entry_id') != FALSE) ? $this->EE->input->get_post('entry_id') : FALSE;
		$vData['total_images'] = 0;
		$vData['assigned_images'] = '';

		//----------------------------------------
		// Add Global JS & CSS & JS Scripts
		//----------------------------------------
		$this->EE->image_helper->mcp_meta_parser('gjs', '', 'ChannelImages');
		$this->EE->image_helper->mcp_meta_parser('css', CHANNELIMAGES_THEME_URL . 'channel_images_pbf.css', 'ci-pbf');
		$this->EE->image_helper->mcp_meta_parser('css', CHANNELIMAGES_THEME_URL . 'jquery.colorbox.css', 'jquery.colorbox');
		$this->EE->image_helper->mcp_meta_parser('js', CHANNELIMAGES_THEME_URL . 'jquery.editable.js', 'jquery.editable', 'jquery');
		$this->EE->image_helper->mcp_meta_parser('js', CHANNELIMAGES_THEME_URL . 'jquery.base64.js', 'jquery.base64', 'jquery');
		$this->EE->image_helper->mcp_meta_parser('js', CHANNELIMAGES_THEME_URL . 'jquery.execute.js', 'jquery.execute', 'jquery');
		$this->EE->image_helper->mcp_meta_parser('js', CHANNELIMAGES_THEME_URL . 'jquery.liveurltitle.js', 'jquery.liveurltitle', 'jquery');
		$this->EE->image_helper->mcp_meta_parser('js', CHANNELIMAGES_THEME_URL . 'jquery.colorbox.js', 'jquery.colorbox', 'jquery');
		$this->EE->image_helper->mcp_meta_parser('js', CHANNELIMAGES_THEME_URL . 'swfupload.js', 'swfupload', 'swfupload');
		$this->EE->image_helper->mcp_meta_parser('js', CHANNELIMAGES_THEME_URL . 'swfupload.queue.js', 'swfupload.queue', 'swfupload');
		$this->EE->image_helper->mcp_meta_parser('js', CHANNELIMAGES_THEME_URL . 'swfupload.speed.js', 'swfupload.speed', 'swfupload');
		$this->EE->image_helper->mcp_meta_parser('js',  CHANNELIMAGES_THEME_URL . 'channel_images_pbf.js', 'ci-pbf');

		$this->EE->cp->add_js_script(array(
		        'ui'        => array('sortable'),
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


		$vData['actions'] = &$this->EE->image_helper->get_actions();

		//----------------------------------------
		// We only want 1 channel_images field (for now)
		//----------------------------------------
		if (isset( $this->EE->session->cache['ChannelImages']['Dupe_Field'] ) == FALSE)
		{
			$this->EE->session->cache['ChannelImages']['Dupe_Field'] = TRUE;
		}
		else
		{
			// It's a dupe field, show a message
			$vData['dupe_field'] = TRUE;
			return $this->EE->load->view('pbf_field', $vData, TRUE);
		}

		//----------------------------------------
		// Some Inline JS
		//----------------------------------------
		$JS = '';

		// Add categories to JS
		$cats = array('' => '');

		if (isset($settings['categories']) == TRUE && empty($settings['categories']) == FALSE)
		{
			foreach ($settings['categories'] as $cat) $cats[$cat] = $cat;
		}
		$JS .= "<script type='text/javascript'> ChannelImages.Categories = " . $this->EE->javascript->generate_json($cats) . "; </script>";

		// Add sizes in JS
		$sizes = array('Original');
		if (isset($settings['action_groups']) == TRUE && empty($settings['action_groups']) == FALSE)
		{
			foreach ($settings['action_groups'] as $group)
			{
				if (isset($group['wysiwyg']) == FALSE OR $group['wysiwyg'] != 'yes') continue;
				$sizes[] = ucfirst($group['group_name']);
			}
		}
		$JS .= "<script type='text/javascript'> ChannelImages.Sizes = " . $this->EE->javascript->generate_json($sizes) . "; </script>";

		$this->EE->cp->add_to_head($JS);


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
				$image->linked = FALSE;

				// We need a good field_id to continue
				$image->field_id = $this->EE->channel_images_model->get_field_id($image);

				// Is it a linked image?
				// Then we need to "fake" the channel_id/field_id
				if ($image->link_image_id >= 1)
				{
					$image->entry_id = $image->link_entry_id;
					$image->field_id = $image->link_field_id;
					$image->channel_id = $image->link_channel_id;
					$image->linked = TRUE; // Display the break link icon
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

				// Settings
				$image->settings = $vData['settings'];

				$vData['assigned_images'] .= $this->EE->load->view('pbf_field_single_image', $image, TRUE);

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
					// Existing? lets get it!
					if ($img['imageid'] > 0)
					{
						$q = $this->EE->db->query("SELECT * FROM exp_channel_images WHERE image_id = ".$img['imageid']);
						$image = $q->row();

						// Is it a linked image?
						if ($image->link_image_id >= 1)
						{
							$image->entry_id = $image->link_entry_id;
							$image->field_id = $image->link_field_id;
							$image->linked = TRUE; // Display the break link icon
						}

						// Display SIzes URL
						$image->small_img_url = $preview_url . '&amp;fid='.$image->field_id.'&amp;d=' . $image->entry_id . '&amp;f=' . str_replace('.'.$image->extension, "__{$settings['small_preview']}.{$image->extension}", $image->filename);
						$image->big_img_url = $preview_url . '&amp;fid='.$image->field_id.'&amp;d=' . $image->entry_id . '&amp;f=' . str_replace('.'.$image->extension, "__{$settings['big_preview']}.{$image->extension}", $image->filename);
					}
					else
					{
						$image = (object) $img;
						$image->description = $image->desc;
						$image->field_id = $this->field_id;
						$image->image_id = 0;
						$image->extension = substr( strrchr($image->filename, '.'), 1);
						$image->link_image_id = 0;

						// Display SIzes URL
						$image->small_img_url = $preview_url . '&amp;fid=0&amp;d=' . $vData['temp_key'] . '&amp;f=' . str_replace('.'.$image->extension, "__{$settings['small_preview']}.{$image->extension}", $image->filename);
						$image->big_img_url = $preview_url . '&amp;fid=0&amp;d=' . $vData['temp_key'] . '&amp;f=' . str_replace('.'.$image->extension, "__{$settings['big_preview']}.{$image->extension}", $image->filename);
					}

					$image->linked = FALSE;

					// We need a good field_id to continue
					$image->field_id = $this->EE->channel_images_model->get_field_id($image);

					// Is it a linked image?
					// Then we need to "fake" the channel_id/field_id
					if ($image->link_image_id >= 1)
					{
						$image->entry_id = $image->link_entry_id;
						$image->field_id = $image->link_field_id;
						$image->channel_id = $image->link_channel_id;
						$image->linked = TRUE; // Display the break link icon
					}

					// Just in case lets try to get the field_id again
					$image->field_id = $this->EE->channel_images_model->get_field_id($image);

					// Get settings for that field..
					$temp_settings = $this->EE->channel_images_model->get_field_settings($image->field_id);

					// ReAssign Field ID (WE NEED THIS)
					$image->field_id = $this->field_id;

					// Settings
					$image->settings = $vData['settings'];

					// REQUIRED
					$image->form_error = TRUE;
					$image->image_order = $num;

					$vData['assigned_images'] .= $this->EE->load->view('pbf_field_single_image', $image, TRUE);

					unset($image);
				}
			}
		}

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
		$this->EE->load->library('image_helper');
		$this->EE->load->helper('url');

		$data = (isset($this->EE->session->cache['ChannelImages'])) ? $this->EE->session->cache['ChannelImages']['FieldData'][$this->field_id] : FALSE;
		$entry_id = $this->settings['entry_id'];
		$channel_id = $this->EE->input->post('channel_id');
		$field_id = $this->field_id;

		// Do we need to skip?
		if (isset($data['images']) == FALSE) return;


		// Grab Settings
		$settings = $this->settings['channel_images'];

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

		// -----------------------------------------
		// Upload all Images!
		// -----------------------------------------
		$temp_dir = APPPATH.'cache/channel_images/'.$key;

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
		$this->EE->db->where('site_id', $this->site_id);
		$query = $this->EE->db->get();

		// -----------------------------------------
		// Lets create an image hash! So we can check for unique images
		// -----------------------------------------
		$dbimages = array();
		foreach ($query->result() as $row)
		{
			$dbimages[] = $row->image_id.$row->filename;
		}


		if ($query->num_rows() > 0)
		{
			// Not fresh, lets see whats new.
			foreach ($data['images'] as $order => $file)
			{
				//Extension
				$extension = substr( strrchr($file['filename'], '.'), 1);

				// Remove unwanted stuff
				$file = $this->EE->security->xss_clean($file);

				// Mime type
				$filemime = 'image/jpeg';
				if ($extension == 'png') $filemime = 'image/png';
				elseif ($extension == 'gif') $filemime = 'image/gif';

				// Check for cover first
				if (isset($file['cover']) == FALSE) $file['cover'] = 0;

				// Check for linked_imageid
				if (isset($file['linked_imageid']) == FALSE) $file['linked_imageid'] = 0;
				$file['linked_entryid'] = 0;
				$file['linked_channelid'] = 0;
				$file['linked_fieldid'] = 0;

				// Check URL Title
				if (isset($file['url_title']) == FALSE OR $file['url_title'] == FALSE)
				{
					$file['url_title'] = url_title(trim(strtolower($file['title'])));
				}

				if ($this->EE->image_helper->in_multi_array($file['imageid'].$file['filename'], $dbimages) === FALSE)
				{
					// Parse Image Size
					$width=''; $height=''; $filesize='';

					// -----------------------------------------
					// Parse width/height/field_id/channel_id/entry_id
					// -----------------------------------------
					if ($file['linked_imageid'] > 0)
					{
						$imgquery = $this->EE->db->query("SELECT entry_id, field_id, channel_id, filesize, width, height, sizes_metadata FROM exp_channel_images WHERE image_id = {$file['linked_imageid']} ");
						$file['linked_entryid'] = $imgquery->row('entry_id');
						$file['linked_channelid'] = $imgquery->row('channel_id');
						$file['linked_fieldid'] = $imgquery->row('field_id');
						$width = $imgquery->row('width');
						$height = $imgquery->row('height');
						$filesize = $imgquery->row('filesize');
						$mt = $imgquery->row('sizes_metadata');
						if (is_string($mt) == FALSE) $mt = ''; // Some installs get weird mysql errors
					}
					else
					{
						$width = isset($metadata[$file['filename']]['width']) ? $metadata[$file['filename']]['width'] : 0;
						$height = isset($metadata[$file['filename']]['height']) ? $metadata[$file['filename']]['height'] : 0;
						$filesize = isset($metadata[$file['filename']]['size']) ? $metadata[$file['filename']]['size'] : 0;

						// -----------------------------------------
						// Parse Size Metadata!
						// -----------------------------------------
						$mt = '';
						foreach($settings['action_groups'] as $group)
						{
							$name = strtolower($group['group_name']);
							$size_filename = str_replace('.'.$extension, "__{$name}.{$extension}", $file['filename']);

							$mt .= $name.'|' . implode('|', $metadata[$size_filename]) . '/';
						}
					}

					// -----------------------------------------
					// New File
					// -----------------------------------------
					$data = array(	'site_id'	=>	$this->site_id,
									'entry_id'	=>	$entry_id,
									'channel_id'=>	$channel_id,
									'member_id'=>	$this->EE->session->userdata['member_id'],
									'link_image_id' => $file['linked_imageid'],
									'link_entry_id' => $file['linked_entryid'],
									'link_channel_id' => $file['linked_channelid'],
									'link_field_id' => $file['linked_fieldid'],
									'upload_date' => $this->EE->localize->now,
									'field_id'	=>	$field_id,
									'image_order'	=>	$order,
									'filename'	=>	$file['filename'],
									'extension' =>	$extension,
									'mime'		=>	$filemime,
									'filesize'	=>	$filesize,
									'width'		=>	$width,
									'height'	=>	$height,
									'title'		=>	$file['title'],
									'url_title'	=>	$file['url_title'],
									'description' => $file['desc'],
									'category' 	=>	(isset($file['category']) == true) ? $file['category'] : '',
									'cifield_1'	=>	$file['cifield_1'],
									'cifield_2'	=>	$file['cifield_2'],
									'cifield_3'	=>	$file['cifield_3'],
									'cifield_4'	=>	$file['cifield_4'],
									'cifield_5'	=>	$file['cifield_5'],
									'cover'		=>	$file['cover'],
									'sizes_metadata' => $mt,
								);

					$this->EE->db->insert('exp_channel_images', $data);
				}
				else
				{
					// -----------------------------------------
					// Old File
					// -----------------------------------------
					$data = array(	'cover'		=>	$file['cover'],
									'channel_id'=>	$channel_id,
									'field_id'	=>	$field_id,
									'image_order'=>	$order,
									'title'		=>	$file['title'],
									'url_title'	=>	$file['url_title'],
									'description'	=> $file['desc'],
									'category' 	=>	(isset($file['category']) == true) ? $file['category'] : '',
									'cifield_1'	=>	$file['cifield_1'],
									'cifield_2'	=>	$file['cifield_2'],
									'cifield_3'	=>	$file['cifield_3'],
									'cifield_4'	=>	$file['cifield_4'],
									'cifield_5'	=>	$file['cifield_5'],
									'mime'		=>	$filemime,
								);

					$this->EE->db->update('exp_channel_images', $data, array('image_id' =>$file['imageid']));
				}
			}
		}
		else
		{
			// No previous entries, fresh fresh
			foreach ($data['images'] as $order => $file)
			{
				//Extension
				$extension = substr( strrchr($file['filename'], '.'), 1);

				// Remove unwanted stuff
				$file = $this->EE->security->xss_clean($file);

				// Mime type
				$filemime = 'image/jpeg';
				if ($extension == 'png') $filemime = 'image/png';
				elseif ($extension == 'gif') $filemime = 'image/gif';

				// Check for cover first
				if (isset($file['cover']) == FALSE) $file['cover'] = 0;

				// Check for linked_imageid
				if (isset($file['linked_imageid']) == FALSE) $file['linked_imageid'] = 0;
				$file['linked_entryid'] = 0;
				$file['linked_channelid'] = 0;
				$file['linked_fieldid'] = 0;

				// Parse Image Size
				$width=''; $height=''; $filesize='';

				// Lets grab original width/height/field_id/channel_id/entry_id
				if ($file['linked_imageid'] > 0)
				{
					$imgquery = $this->EE->db->query("SELECT entry_id, field_id, channel_id, filesize, width, height FROM exp_channel_images WHERE image_id = {$file['linked_imageid']} ");
					$file['linked_entryid'] = $imgquery->row('entry_id');
					$file['linked_channelid'] = $imgquery->row('channel_id');
					$file['linked_fieldid'] = $imgquery->row('field_id');
					$width = $imgquery->row('width');
					$height = $imgquery->row('height');
					$filesize = $imgquery->row('filesize');
					$mt = $imgquery->row('sizes_metadata');
					if (is_string($mt) == FALSE) $mt = ''; // Some installs get weird mysql errors
				}
				else
				{
					$width = isset($metadata[$file['filename']]['width']) ? $metadata[$file['filename']]['width'] : 0;
					$height = isset($metadata[$file['filename']]['height']) ? $metadata[$file['filename']]['height'] : 0;
					$filesize = isset($metadata[$file['filename']]['size']) ? $metadata[$file['filename']]['size'] : 0;

					// -----------------------------------------
					// Parse Size Metadata!
					// -----------------------------------------
					$mt = '';
					foreach($settings['action_groups'] as $group)
					{
						$name = strtolower($group['group_name']);
						$size_filename = str_replace('.'.$extension, "__{$name}.{$extension}", $file['filename']);

						$mt .= $name.'|' . implode('|', $metadata[$size_filename]) . '/';
					}
				}

				// Check URL Title
				if (isset($file['url_title']) OR $file['url_title'] == FALSE)
				{
					$file['url_title'] = url_title(trim(strtolower($file['title'])));
				}

				// -----------------------------------------
				// New File
				// -----------------------------------------
				$data = array(	'site_id'	=>	$this->site_id,
								'entry_id'	=>	$entry_id,
								'channel_id'=>	$channel_id,
								'member_id'=>	$this->EE->session->userdata['member_id'],
								'link_image_id' => $file['linked_imageid'],
								'link_entry_id' => $file['linked_entryid'],
								'link_channel_id' => $file['linked_channelid'],
								'link_field_id' => $file['linked_fieldid'],
								'upload_date' => $this->EE->localize->now,
								'field_id'=>	$field_id,
								'image_order'		=>	$order,
								'filename'	=>	$file['filename'],
								'extension' =>	$extension,
								'mime'		=>	$filemime,
								'filesize'	=>	$filesize,
								'width'		=>	$width,
								'height'	=>	$height,
								'title'		=>	$file['title'],
								'url_title'	=>	$file['url_title'],
								'description' => $file['desc'],
								'category' 	=>	(isset($file['category']) == true) ? $file['category'] : '',
								'cifield_1'	=>	$file['cifield_1'],
								'cifield_2'	=>	$file['cifield_2'],
								'cifield_3'	=>	$file['cifield_3'],
								'cifield_4'	=>	$file['cifield_4'],
								'cifield_5'	=>	$file['cifield_5'],
								'cover'		=>	$file['cover'],
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

		// Which fields are WYGWAM/wyvern
		$query = $this->EE->db->select('field_id')->from('exp_channel_fields')->where('group_id', $field_group)->where('field_type', 'wygwam')->or_where('field_type', 'wyvern')->get();
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
				$this->EE->db->set($field, " REPLACE({$field}, 'fid=0&', 'fid={$this->field_id}&') ", FALSE);
				$this->EE->db->where('entry_id', $entry_id);
				$this->EE->db->update('exp_channel_data');

				$this->EE->db->set($field, " REPLACE({$field}, 'd={$key}&', 'd={$entry_id}&') ", FALSE);
				$this->EE->db->where('entry_id', $entry_id);
				$this->EE->db->update('exp_channel_data');
			}

		}

		//preg_match_all('/\< *[img][^\>]*[src] *= *[\"\']{0,1}([^\"\'\ >]*)/i', $field, $matches);

		return;
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
		$this->EE->image_helper->mcp_meta_parser('js', CHANNELIMAGES_THEME_URL . 'jquery.execute.js', 'jquery.execute', 'jquery');
		$this->EE->image_helper->mcp_meta_parser('js', CHANNELIMAGES_THEME_URL . 'jquery.colorbox.js', 'jquery.colorbox', 'jquery');
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
			$field_id = $data['field_id'];

			// Delete from db
			$this->EE->db->where('field_id', $field_id);
			$this->EE->db->or_where('link_field_id', $field_id);
			$this->EE->db->delete('exp_channel_images');
		}

		$fields = parent::settings_modify_column($data);

		return $fields;
	}

	// ********************************************************************************* //

}

/* End of file ft.channel_images.php */
/* Location: ./system/expressionengine/third_party/channel_images/ft.channel_images.php */