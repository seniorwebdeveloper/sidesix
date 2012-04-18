<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Channel Images AJAX File
 *
 * @package			DevDemon_ChannelFiles
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2010 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com
 */
class Channel_Images_AJAX
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
		$this->EE->load->library('image_helper');
		$this->EE->load->model('channel_images_model');
		$this->EE->lang->loadfile('channel_images');
		$this->EE->config->load('ci_config');

		if ($this->EE->input->get_post('site_id')) $this->site_id = $this->EE->input->get_post('site_id');
		else if ($this->EE->input->cookie('cp_last_site_id')) $this->site_id = $this->EE->input->cookie('cp_last_site_id');
		else $this->site_id = $this->EE->config->item('site_id');
	}

	// ********************************************************************************* //

	function upload_file()
	{
		$this->EE->config->load('ci_config');
		$this->EE->load->helper('url');

		// -----------------------------------------
		// Increase all types of limits!
		// -----------------------------------------
		@set_time_limit(0);
		@ini_set('memory_limit', '64M');
		@ini_set('memory_limit', '96M');
		@ini_set('memory_limit', '128M');
		@ini_set('memory_limit', '160M');
		@ini_set('memory_limit', '192M');
		@ini_set('memory_limit', '256M');
		@ini_set('memory_limit', '320M');
		@ini_set('memory_limit', '512M');

		error_reporting(E_ALL);
		@ini_set('display_errors', 1);

		// -----------------------------------------
		// Standard Vars
		// -----------------------------------------
		$o = array('success' => 'no', 'body' => '');
		$channel_id = $this->EE->input->post('channel_id');
		$field_id = $this->EE->input->post('field_id');
		$key = $this->EE->input->post('key');

		// -----------------------------------------
		// Is our $_FILES empty? Commonly when EE does not like the mime-type
		// -----------------------------------------
		if (isset($_FILES['channel_images_file']) == FALSE)
		{
			$o['body'] = $this->EE->lang->line('ci:file_arr_empty');
			exit( $this->EE->image_helper->generate_json($o) );
		}

		// -----------------------------------------
		// Lets check for the key first
		// -----------------------------------------
		if ($key == FALSE)
		{
			$o['body'] = $this->EE->lang->line('ci:tempkey_missing');
			exit( $this->EE->image_helper->generate_json($o) );
		}

		// -----------------------------------------
		// Upload file too big (PHP.INI)
		// -----------------------------------------
		if ($_FILES['channel_images_file']['error'] > 0)
		{
			$o['body'] = $this->EE->lang->line('ci:file_upload_error');
			exit( $this->EE->image_helper->generate_json($o) );
		}

		// -----------------------------------------
		// Load Settings
		// -----------------------------------------
		$settings = $this->EE->channel_images_model->get_field_settings($field_id);
		if (isset($settings['channel_images']['upload_location']) == FALSE)
		{
			$o['body'] = $this->EE->lang->line('ci:no_settings');
			exit( $this->EE->image_helper->generate_json($o) );
		}

		$settings = $settings['channel_images'];
		$settings = $this->EE->image_helper->array_extend($this->EE->config->item('ci_defaults'), $settings);

		// -----------------------------------------
		// Temp Dir to run Actions
		// -----------------------------------------
		$temp_dir = APPPATH.'cache/channel_images/'.$key.'/';

		if (@is_dir($temp_dir) === FALSE)
   		{
   			@mkdir($temp_dir, 0777, true);
   			@chmod($temp_dir, 0777);
   		}

		// Last check, does the target dir exist, and is writable
		if (is_really_writable($temp_dir) !== TRUE)
		{
			$o['body'] = $this->EE->lang->line('ci:tempdir_error');
			exit( $this->EE->image_helper->generate_json($o) );
		}


		// -----------------------------------------
		// File Name & Extension
		// -----------------------------------------
    	$original_filename = strtolower($this->EE->security->sanitize_filename(str_replace(' ', '_', $_FILES['channel_images_file']['name'])));

    	// Extension
    	$extension = '.' . substr( strrchr($original_filename, '.'), 1);

    	/*
    	// Remove Accents and such
    	if (function_exists('iconv') == TRUE)
    	{
    		try {
    			$original_filename2 = @iconv("UTF-8", "ASCII//IGNORE//TRANSLIT", $original_filename);
    		} catch (Exception $e) {
    			$original_filename2 = $original_filename;
    		}

    	}
    	else
    	{
    		$original_filename2 = $original_filename;
    	}
    	*/

    	// The original file stays with the same name
    	//$filename = $original_filename2;ß

    	$filename = $this->ascii_string($original_filename);

		// -----------------------------------------
		// Move File
		// -----------------------------------------
		if (@move_uploaded_file($_FILES['channel_images_file']['tmp_name'], $temp_dir.$filename) === FALSE)
    	{
    		$o['body'] = $this->EE->lang->line('ci:file_move_error');
	   		exit( $this->EE->image_helper->generate_json($o) );
    	}

		// -----------------------------------------
		// Load Actions :O
		// -----------------------------------------
		$actions = &$this->EE->image_helper->get_actions();

		// Just double check for actions groups
		if (isset($settings['action_groups']) == FALSE) $settings['action_groups'] = array();

		// -----------------------------------------
		// Loop over all action groups!
		// -----------------------------------------
		foreach ($settings['action_groups'] as $group)
		{
			$size_name = $group['group_name'];
			$size_filename = str_replace($extension, "__{$size_name}{$extension}", $filename);

			// Make a copy of the file
			@copy($temp_dir.$filename, $temp_dir.$size_filename);
			@chmod($temp_dir.$size_filename, 0777);

			// -----------------------------------------
			// Loop over all Actions and RUN! OMG!
			// -----------------------------------------
			foreach($group['actions'] as $action_name => $action_settings)
			{
				// RUN!
				$actions[$action_name]->settings = $action_settings;
				$res = $actions[$action_name]->run($temp_dir.$size_filename);

				if ($res !== TRUE)
				{
					@unlink($temp_dir.$size_filename);
					$o['body'] = 'ACTION ERROR: ' . $res;
	   				exit( $this->EE->image_helper->generate_json($o) );
				}
			}


		}

		// -----------------------------------------
		// Keep Original Image?
		// -----------------------------------------
		if (isset($settings['keep_original']) == TRUE && $settings['keep_original'] == 'no')
		{
			@unlink($temp_dir.$filename);
		}

		// -----------------------------------------
		// Which Previews?
		// -----------------------------------------
		if ( empty($settings['action_groups']) == FALSE && (isset($settings['no_sizes']) == FALSE OR $settings['no_sizes'] != 'yes') )
		{
			if (isset($settings['small_preview']) == FALSE OR $settings['small_preview'] == FALSE)
			{
				$settings['small_preview'] = $settings['action_groups'][1]['group_name'];
			}

			if (isset($settings['big_preview']) == FALSE OR $settings['big_preview'] == FALSE)
			{
				$settings['big_preview'] = $settings['action_groups'][1]['group_name'];
			}
		}
		else
		{
			// No sizes? Then lets make it be the the original one!
			$settings['small_preview'] = $filename;
			$settings['big_preview'] = $filename;
		}


		// Lets start our image array
		$image = array();

		// Preview URL
		$preview_url = $this->EE->image_helper->get_router_url('url', 'simple_image_url');


		// -----------------------------------------
		// Generate Image URL's
		// -----------------------------------------

		// Are we using the original file?
		if ($settings['small_preview'] == $filename)
		{
			$small_img_filename = $settings['small_preview'];
			$big_img_filename = $settings['small_preview'];
		}
		else
		{
			$small_img_filename = str_replace($extension, "__{$settings['small_preview']}{$extension}", $filename);
			$big_img_filename = str_replace($extension, "__{$settings['big_preview']}{$extension}", $filename);
		}


		// Create the URL's
		$image['small_img_url'] = "{$preview_url}&amp;fid=0&amp;d={$key}&amp;f={$small_img_filename}";
		$image['big_img_url'] = "{$preview_url}&amp;fid=0&amp;d={$key}&amp;f={$big_img_filename}";


		// -----------------------------------------
		// Output
		// -----------------------------------------

		// Add settings to Image
		$image['settings'] = $settings;
		if (isset($image['settings']['columns']) == FALSE) $image['settings']['columns'] = $this->EE->config->item('ci_columns');

    	$image['title'] = ucfirst(str_replace('_', ' ', str_replace($extension, '', $filename)));
    	$image['url_title'] = url_title(trim(strtolower($image['title'])));
    	$image['description'] = '';
    	$image['image_id'] = 0;
    	$image['category'] = '';
    	$image['cifield_1'] = '';
    	$image['cifield_2'] = '';
    	$image['cifield_3'] = '';
    	$image['cifield_4'] = '';
    	$image['cifield_5'] = '';
    	$image['cover'] = 0;
    	$image['filename'] = $filename;
    	$image['linked'] = FALSE;

		$o['body'] = $this->EE->load->view('pbf_field_single_image', $image, TRUE);
		$o['title'] = $image['title'];
		$o['url_title'] = $image['url_title'];
    	$o['filename'] = $filename;
    	$o['field_id'] = $field_id;

		$o['success'] = 'yes';

    	$out = trim($this->EE->image_helper->generate_json($o));

		exit( $out );

	}

	// ********************************************************************************* //

	public function delete_image()
	{
		//$this->EE->firephp->fb($_POST, 'POST');

		if ($this->EE->input->post('field_id') == false) exit('Missing Field_ID');

		$settings = $this->EE->channel_images_model->get_field_settings($this->EE->input->post('field_id'));
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

		// Delete from DB
		if ($this->EE->input->post('image_id') > 0)
		{
			$this->EE->db->from('exp_channel_images');
			$this->EE->db->where('image_id', $this->EE->input->post('image_id'));
			$this->EE->db->or_where('link_image_id', $this->EE->input->post('image_id'));
			$this->EE->db->delete();
		}

		// -----------------------------------------
		// Delete!
		// -----------------------------------------
		$entry_id = $this->EE->input->post('entry_id');
		$key = $this->EE->input->post('key');
		$filename = $this->EE->input->post('filename');
		$extension = '.' . substr( strrchr($filename, '.'), 1);

		foreach($settings['action_groups'] as $group)
		{
			$name = strtolower($group['group_name']);
			$name = str_replace($extension, "__{$name}{$extension}", $filename);

			if ($entry_id > 0) $res = $LOC->delete_file($entry_id, $name);
			else @unlink(APPPATH.'cache/channel_images/'.$key.'/'.$name);
		}


		// Delete original file from system
		if ($entry_id > 0) $res = $LOC->delete_file($entry_id, $filename);
		else @unlink(APPPATH.'cache/channel_images/'.$key.'/'.$filename);

		exit();
	}

	// ********************************************************************************* //

	function test_location()
	{
		$settings = $_POST['channel_images'];

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

		// Test Location!
		$res = $LOC->test_location();

		exit($res);
	}

	// ********************************************************************************* //

	public function apply_action()
	{
		@set_time_limit(0);
		@ini_set('memory_limit', '64M');
		@ini_set('memory_limit', '96M');
		@ini_set('memory_limit', '128M');
		@ini_set('memory_limit', '160M');
		@ini_set('memory_limit', '192M');

		error_reporting(E_ALL);
		@ini_set('display_errors', 1);

		// -----------------------------------------
		// Vars
		// -----------------------------------------
		$stage = $this->EE->input->post('stage');
		$preview_url = $this->EE->image_helper->get_router_url('url', 'simple_image_url');
		$key = $this->EE->input->post('key');
		$akey = $key + 1;
		$size = $this->EE->input->post('size');
		$filename = $this->EE->input->post('filename');
		$image_id = $this->EE->input->post('image_id');
		$field_id = $this->EE->input->post('field_id');
		$entry_id = $this->EE->input->post('entry_id');
		$action = $this->EE->input->post('action');

		// Extension
    	$extension = '.' . substr( strrchr($filename, '.'), 1);

		// Size?
		if ($size != 'ORIGINAL')
		{
			$filename = str_replace($extension, "__{$size}{$extension}", $filename);
		}

		// Grab Fields Settings
		if ($field_id == false) exit('Missing Field_ID');

		$settings = $this->EE->channel_images_model->get_field_settings($field_id);
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

		$temp_dir = APPPATH.'cache/channel_images/'.$akey.'/';

		// -----------------------------------------
		// Saving?
		// -----------------------------------------
		if ($stage == 'save')
		{
			if (file_exists($temp_dir.$filename) == FALSE) exit('ERROR: MISSING PREVIEW IMAGE FILE');

			if ($image_id > 0)
			{
				$response = $LOC->upload_file($temp_dir.$filename, $filename, $entry_id);
			}
			else
			{
				copy($temp_dir.$filename, APPPATH.'cache/channel_images/'.$key.'/'.$filename);
			}

			@unlink($temp_dir.$filename);
			exit();
		}

		// -----------------------------------------
		// Create Temp Location
		// -----------------------------------------
		if (is_dir($temp_dir) == FALSE)
		{
			@mkdir($temp_dir, 0777, true);
   			@chmod($temp_dir, 0777);
		}

		// -----------------------------------------
		// Copy Image to temp location
		// -----------------------------------------
		if ($image_id > 0)
		{
			$response = $LOC->download_file($entry_id, $filename, $temp_dir);
			if ($response !== TRUE) exit($response);
		}
		else
		{
			copy(APPPATH.'cache/channel_images/'.$key.'/'.$filename, $temp_dir.$filename);
		}

		// -----------------------------------------
		// Load Action
		// -----------------------------------------
		$actions = &$this->EE->image_helper->get_actions();
		if (isset($_POST['channel_images'][$action]) == FALSE) $action_settings = array();
		else $action_settings = $_POST['channel_images'][$action];

		$actions[$action]->settings = $action_settings;
		$res = $actions[$action]->run($temp_dir.$filename);

		if ($res !== TRUE)
		{
			exit('ACTION PROCESS ERROR: ' . $res);
		}

		// -----------------------------------------
		// Preview Only?
		// -----------------------------------------
		if ($stage == 'preview')
		{
			$img_url = $preview_url . '&amp;fid=0&amp;d=' . $akey . '&amp;f=' . $filename . '&amp;random=' . rand(100, 99999);
			echo '<img src="' . $img_url . '" />';
			exit();
		}



	}

	// ********************************************************************************* //

	public function load_entries()
	{
		$limit = $this->EE->input->get('limit') ? $this->EE->input->get('limit') : 100;
		$field_id = $this->EE->input->get('field_id');
		$entry_id = $this->EE->input->get('entry_id');

		if ($entry_id == FALSE) $entry_id = 99999999;

		if ($field_id == FALSE) exit('MISSING FIELD ID');

		// Get Field
		$query = $this->EE->db->query("SELECT group_id FROM exp_channel_fields WHERE field_id = {$field_id} LIMIT 1");
		if ($query->num_rows() == 0) exit("FIELD NOT FOUND");
		$field_group_id = $query->row('group_id');

		// Get Channels
		$channels = array();
		$query = $this->EE->db->query("SELECT channel_id FROM exp_channels WHERE field_group = {$field_group_id}");
		foreach($query->result() as $row) $channels[] = $row->channel_id;

		// Get entries
		$query = $this->EE->db->query("SELECT title, entry_id FROM exp_channel_titles WHERE status != 'closed' AND entry_id != {$entry_id} AND channel_id IN (".implode(',', $channels).") ORDER BY entry_date DESC");

		foreach ($query->result() as $row)
		{
			echo "<a href='#' rel='{$row->entry_id}'>&bull; {$row->title}</a>";
		}

		exit();
	}

	// ********************************************************************************* //

	public function load_images()
	{
		$this->EE->load->helper('form');

		// -----------------------------------------
		// Vars
		// -----------------------------------------
		$entry_id = $this->EE->input->get('entry_id');
		$field_id = $this->EE->input->get('field_id');
		$limit = $this->EE->input->get('limit') ? $this->EE->input->get('limit') : 50;
		$title = $this->EE->input->get('title');
		$desc = $this->EE->input->get('desc');
		$category = $this->EE->input->get('category');
		$cifield_1 = $this->EE->input->get('cifield_1');
		$cifield_2 = $this->EE->input->get('cifield_2');
		$cifield_3 = $this->EE->input->get('cifield_3');
		$cifield_4 = $this->EE->input->get('cifield_4');
		$cifield_5 = $this->EE->input->get('cifield_5');

		// -----------------------------------------
		// Settings
		// -----------------------------------------
		$settings = $this->EE->channel_images_model->get_field_settings($field_id);
		$settings = $settings['channel_images'];

		// -----------------------------------------
		// Start Grab The Images
		// -----------------------------------------
		$this->EE->db->select('*');
		$this->EE->db->from('exp_channel_images');
		$this->EE->db->where('field_id', $field_id);
		$this->EE->db->where('link_image_id', 0);
		if ($entry_id != FALSE) $this->EE->db->where('entry_id', $entry_id);

		// -----------------------------------------
		// Limit By what?
		// -----------------------------------------

		// Limit By Author
		if (isset($settings['stored_images_by_author']) == TRUE && $settings['stored_images_by_author'] == 'yes') $this->EE->db->where('member_id', $this->EE->session->userdata['member_id']);
		if ($title != FALSE && $title != $settings['columns']['title']) $this->EE->db->like('title', $title, 'both');
		if ($desc != FALSE && $desc != $settings['columns']['desc']) $this->EE->db->like('description', $desc, 'both');
		if ($category != FALSE && $category != $settings['columns']['category']) $this->EE->db->like('category', $category, 'both');
		if ($cifield_1 != FALSE && $cifield_1 != $settings['columns']['cifield_1']) $this->EE->db->like('cifield_1', $cifield_1, 'both');
		if ($cifield_2 != FALSE && $cifield_2 != $settings['columns']['cifield_2']) $this->EE->db->like('cifield_2', $cifield_2, 'both');
		if ($cifield_3 != FALSE && $cifield_3 != $settings['columns']['cifield_3']) $this->EE->db->like('cifield_3', $cifield_3, 'both');
		if ($cifield_4 != FALSE && $cifield_4 != $settings['columns']['cifield_4']) $this->EE->db->like('cifield_4', $cifield_4, 'both');
		if ($cifield_5 != FALSE && $cifield_5 != $settings['columns']['cifield_5']) $this->EE->db->like('cifield_5', $cifield_5, 'both');

		// -----------------------------------------
		// Grab it
		// -----------------------------------------
		$this->EE->db->limit($limit);
		//$this->EE->db->save_queries = TRUE;
		$query = $this->EE->db->get();
		//print_r($this->EE->db->queries);

		if ($query->num_rows() == 0) exit('<div><p>' . $this->EE->lang->line('ci:no_images') . '</p></div>');

		// -----------------------------------------
		// Which Previews?
		// -----------------------------------------
		if (isset($settings['small_preview']) == FALSE OR $settings['small_preview'] == FALSE)
		{
			$settings['small_preview'] = $settings['action_groups'][1]['group_name'];
		}

		if (isset($settings['big_preview']) == FALSE OR $settings['big_preview'] == FALSE)
		{
			$settings['big_preview'] = $settings['action_groups'][1]['group_name'];
		}

		// Preview URL
		$preview_url = $this->EE->image_helper->get_router_url('url', 'simple_image_url');

		// -----------------------------------------
		// Loop over all images
		// -----------------------------------------
		foreach ($query->result() as $image)
		{
			$image->linked = TRUE; // Display Unlink icon ;)

			// We need a good field_id to continue
			$image->field_id = $this->EE->channel_images_model->get_field_id($image);

			// Get settings for that field..
			$image->settings = $this->EE->channel_images_model->get_field_settings($image->field_id);

			$out = '<div class="img">';

			$filename_small = str_replace('.'.$image->extension, "__{$settings['small_preview']}.{$image->extension}", $image->filename);
			$filename_big = str_replace('.'.$image->extension, "__{$settings['big_preview']}.{$image->extension}", $image->filename);

			$image->small_img_url = $preview_url . '&amp;fid=' . $image->field_id . '&amp;d=' . $image->entry_id . '&amp;f=' . $filename_small;
			$image->big_img_url = $preview_url . '&amp;fid=' . $image->field_id . '&amp;d=' . $image->entry_id . '&amp;f=' . $filename_big;

			$out .= '<a href="' . $image->big_img_url . '" rel="'.$image->image_id.'" title="'.form_prep($image->title).'">';
			$out .= 	'<img src="' . $image->small_img_url . '" width="50px"/>';
			$out .= 	'<span class="add">&nbsp;</span>';
			$out .= '</a>';

			echo $out.'</div>';
		}

		exit();
	}

	// ********************************************************************************* //

	public function add_linked_image()
	{
		$this->EE->load->helper('form');

		$image_id = $this->EE->input->get('image_id');

		// Get Image Info
		$query = $this->EE->db->select('*')->from('exp_channel_images')->where('image_id', $image_id)->get();

		$image = $query->row();

		// -----------------------------------------
		// Settings
		// -----------------------------------------
		$settings = $this->EE->channel_images_model->get_field_settings($image->field_id);
		$settings = $settings['channel_images'];

		// -----------------------------------------
		// Which Previews?
		// -----------------------------------------
		if (isset($settings['small_preview']) == FALSE OR $settings['small_preview'] == FALSE)
		{
			$settings['small_preview'] = $settings['action_groups'][1]['group_name'];
		}

		if (isset($settings['big_preview']) == FALSE OR $settings['big_preview'] == FALSE)
		{
			$settings['big_preview'] = $settings['action_groups'][1]['group_name'];
		}

		// Preview URL
		$preview_url = $this->EE->image_helper->get_router_url('url', 'simple_image_url');

		$image->linked = TRUE; // Display Unlink icon ;)

		// We need a good field_id to continue
		$image->field_id = $this->EE->channel_images_model->get_field_id($image);

		// Get settings for that field..
		$image->settings = $this->EE->channel_images_model->get_field_settings($image->field_id);

		$out = '<div class="img">';

		$filename_small = str_replace('.'.$image->extension, "__{$settings['small_preview']}.{$image->extension}", $image->filename);
		$filename_big = str_replace('.'.$image->extension, "__{$settings['big_preview']}.{$image->extension}", $image->filename);

		$image->small_img_url = $preview_url . '&amp;fid=' . $image->field_id . '&amp;d=' . $image->entry_id . '&amp;f=' . $filename_small;
		$image->big_img_url = $preview_url . '&amp;fid=' . $image->field_id . '&amp;d=' . $image->entry_id . '&amp;f=' . $filename_big;

		// Add Master Field Settings
		$image->settings = $settings;

		$image->image_id_hidden = $image->image_id;
		$image->image_id = 0;
		$image->cover = 0;

		$o = array();
		$o['tr'] = base64_encode($this->EE->load->view('pbf_field_single_image', $image, TRUE));
		$o['img'] = $image;
		exit( $this->EE->image_helper->generate_json($o) );

		exit();
	}

	// ********************************************************************************* //

	public function grab_image_ids()
	{
		$field_id = $this->EE->input->post('field_id');


		// To which group id does this field belong?
		$query = $this->EE->db->select('group_id')->from('exp_channel_fields')->where('field_id', $field_id)->get();
		$group_id = $query->row('group_id');

		$query->free_result();

		// To which channels does this field_group belong to?
		$channels = array();
		$query = $this->EE->db->select('channel_id')->from('exp_channels')->where('field_group', $group_id)->get();

		foreach ($query->result() as $row) $channels[] = $row->channel_id;

		// Check for empty channels
		if (empty($channels) == TRUE)
		{
			exit('NO CHANNELS ASSIGNED TO THIS FIELD!');
		}

		$query->free_result();

		//Grab all images
		$images = $this->EE->db->select('filename, image_id')->from('exp_channel_images')->where_in('channel_id', $channels)->where('link_image_id', 0)->get();

		if ($images->num_rows() == 0)
		{
			exit('NO IMAGES FOUND');
		}
		else
		{
			echo '<a href="#" class="ci_start_resize" rel="'.$field_id.'">' . $this->EE->lang->line('ci:start_resize') . '</a>';
		}


		foreach ($images->result() as $row)
		{
			echo "<div class='Image Queued' id='IMG_{$row->image_id}' rel='{$row->image_id}'>{$row->filename}</div>";
		}


		exit();
	}

	// ********************************************************************************* //

	public function regenerate_image_size()
	{
		$o = array('success' => 'no', 'body' => '');

		$field_id = $this->EE->input->post('field_id');
		$image_id = $this->EE->input->post('image_id');

		if ($image_id == FALSE)
		{
			$o['body'] = 'MISSING IMAGE ID';
			exit( $this->EE->image_helper->generate_json($o) );
		}

		@set_time_limit(0);
		@ini_set('memory_limit', '64M');
		@ini_set('memory_limit', '96M');
		@ini_set('memory_limit', '128M');
		@ini_set('memory_limit', '160M');
		@ini_set('memory_limit', '192M');

		error_reporting(E_ALL);
		@ini_set('display_errors', 1);

		// Grab settings
		$settings = $this->EE->channel_images_model->get_field_settings($field_id);
		$settings = $settings['channel_images'];

		// Grab image info
		$query = $this->EE->db->select('entry_id, filename, extension')->from('exp_channel_images')->where('image_id', $image_id)->limit(1)->get();

		$filename = $query->row('filename');
		$extension = '.'.$query->row('extension');
		$entry_id = $query->row('entry_id');

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

		// Temp Dir
		$temp_dir = APPPATH.'cache/channel_images/'.$this->EE->localize->now.'/';

		// -----------------------------------------
		// Create Temp Location
		// -----------------------------------------
		if (is_dir($temp_dir) == FALSE)
		{
			@mkdir($temp_dir, 0777, true);
   			@chmod($temp_dir, 0777);
		}

		// -----------------------------------------
		// Copy Image to temp location
		// -----------------------------------------
		$response = $LOC->download_file($entry_id, $filename, $temp_dir);
		if ($response !== TRUE) exit($response);

		// -----------------------------------------
		// Load Actions :O
		// -----------------------------------------
		$actions = &$this->EE->image_helper->get_actions();

		// -----------------------------------------
		// Loop over all action groups!
		// -----------------------------------------
		$metadata = array();
		foreach ($settings['action_groups'] as $group)
		{
			$size_name = $group['group_name'];
			$size_filename = str_replace($extension, "__{$size_name}{$extension}", $filename);

			// Make a copy of the file
			@copy($temp_dir.$filename, $temp_dir.$size_filename);
			@chmod($temp_dir.$size_filename, 0777);

			// -----------------------------------------
			// Loop over all Actions and RUN! OMG!
			// -----------------------------------------
			foreach($group['actions'] as $action_name => $action_settings)
			{
				// RUN!
				$actions[$action_name]->settings = $action_settings;
				$res = $actions[$action_name]->run($temp_dir.$size_filename);

				if ($res !== TRUE)
				{
					@unlink($temp_dir.$size_filename);
					$o['body'] = 'ACTION ERROR: ' . $res;
	   				exit( $this->EE->image_helper->generate_json($o) );
				}
			}

			// Parse Image Size
		    $imginfo = @getimagesize($temp_dir.$size_filename);
		    $filesize = @filesize($temp_dir.$size_filename);

			$metadata[$size_name] = array('width' => @$imginfo[0], 'height' => @$imginfo[1], 'size' => $filesize);

			// -----------------------------------------
			// Upload the file back!
			// -----------------------------------------
			$res = $LOC->upload_file($temp_dir.$size_filename, $size_filename, $entry_id);

	    	if ($res !== TRUE)
	    	{
	    		$o['body'] = $res;
				exit( $this->EE->image_helper->generate_json($o) );
	    	}

	    	// Delete
	    	//@unlink($temp_dir.$size_filename);
		}

		// -----------------------------------------
		// Parse Size Metadata!
		// -----------------------------------------
		$mt = '';
		foreach($settings['action_groups'] as $group)
		{
			$name = strtolower($group['group_name']);
			$mt .= $name.'|' . implode('|', $metadata[$name]) . '/';
		}

		// -----------------------------------------
		// Parse Original Image Info
		// -----------------------------------------
		$imginfo = @getimagesize($temp_dir.$filename);
		$filesize = @filesize($temp_dir.$filename);
		$width = @$imginfo[0];
		$height = @$imginfo[1];

		// -----------------------------------------
		// Update Image
		// -----------------------------------------
		$this->EE->db->set('sizes_metadata', $mt);
		$this->EE->db->set('filesize', $filesize);
		$this->EE->db->set('width', $width);
		$this->EE->db->set('height', $height);
		$this->EE->db->where('image_id', $image_id);
		$this->EE->db->update('exp_channel_images');

		// Delete Temp File
		//@unlink($temp_dir.$filename);

		$o['success'] = 'yes';

		exit( $this->EE->image_helper->generate_json($o) );
	}

	// ********************************************************************************* //

	public function import_matrix_images()
	{
		$o = array('success' => 'no', 'body' => '');

		$this->EE->load->helper('url');

		$entry_id = $this->EE->input->get_post('entry_id');

		// -----------------------------------------
		// Increase all types of limits!
		// -----------------------------------------
		@set_time_limit(0);
		@ini_set('memory_limit', '64M');
		@ini_set('memory_limit', '96M');
		@ini_set('memory_limit', '128M');
		@ini_set('memory_limit', '160M');
		@ini_set('memory_limit', '192M');
		@ini_set('memory_limit', '256M');
		@ini_set('memory_limit', '320M');
		@ini_set('memory_limit', '512M');

		// -----------------------------------------
		// Find our image field!
		// -----------------------------------------
		if (array_search('image', $_POST['matrix']['fieldmap']) == FALSE)
		{
			$o['body'] = 'No Image Field Mapping found!';
			exit( $this->EE->image_helper->generate_json($o) );
		}

		// -----------------------------------------
		// Gather the usable cols
		// -----------------------------------------
		$cols = array();
		$col_select = '';

		foreach ($_POST['matrix']['fieldmap'] as $col_id => $map)
		{
			if ($map == FALSE) continue;

			$cols[$col_id] = $map;
			$col_select .= "col_id_{$col_id}, ";
		}

		// -----------------------------------------
		// Grab all Col Data
		// -----------------------------------------
		$query = $this->EE->db->select('entry_id, '.$col_select)->from('exp_matrix_data')->where('field_id', $_POST['matrix']['field_id'])->where('entry_id', $entry_id)->get();

		if ($query->num_rows() == 0)
		{
			$o['body'] = 'No Matrix Data Found!';
			exit( $this->EE->image_helper->generate_json($o) );
		}

		// -----------------------------------------
		// Create our Final Data Array
		// -----------------------------------------
		$data = array();

		foreach ($query->result_array() as $row)
		{
			$entry_id = $row['entry_id'];
			unset($row['entry_id']);

			$data[$entry_id][] = $row;
		}

		$query->free_result(); unset($query);

		// -----------------------------------------
		// Grab our Field Settings
		// -----------------------------------------
		$ci_field = $_POST['matrix']['ci_field'];
		$channel_id = $_POST['matrix']['channel_id'];
		$settings = $this->EE->image_helper->grab_field_settings($ci_field);
		$settings = $settings['channel_images'];

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

		// -----------------------------------------
		// Load Actions :O
		// -----------------------------------------
		$actions = &$this->EE->image_helper->get_actions();

		// -----------------------------------------
		// Which Col was our image?
		// -----------------------------------------
		$image_col = array_search('image', $cols);
		unset($cols[$image_col]);

		// -----------------------------------------
		// Create file dir array
		// -----------------------------------------
		$file_dirs = array();
		$temp = $this->EE->db->select('id, server_path')->get('exp_upload_prefs');

		foreach ($temp->result() as $val)
		{
			$file_dirs["{filedir_{$val->id}}"] = $val->server_path;
		}

		$file_dirs_search = array_keys($file_dirs);
		$file_dirs_replace = array_values($file_dirs);

		// -----------------------------------------
		// Loop over all entries and BEGIN!
		// -----------------------------------------
		foreach($data as $entry_id => $rows)
		{
			// Create the DIR!
			$LOC->create_dir($entry_id);

			// -----------------------------------------
			// Temp Dir to run Actions
			// -----------------------------------------
			$temp_dir = APPPATH.'cache/channel_images/'.$this->EE->localize->now.'-'.$entry_id.'/';

			if (@is_dir($temp_dir) === FALSE)
			{
				@mkdir($temp_dir, 0777, true);
				@chmod($temp_dir, 0777);
			}

			// Loop over all rows in the entry!
			foreach ($rows as $count => $row)
			{
				// -----------------------------------------
				// Create a Temp image array
				// -----------------------------------------
				$image_path = str_replace($file_dirs_search, $file_dirs_replace, $row['col_id_'.$image_col]);

				if (file_exists($image_path) == FALSE) continue;

				$image = array();
				$image['site_id']	= $this->site_id;
				$image['field_id'] = $ci_field;
				$image['image_order'] = $count;
				$image['member_id'] = $this->EE->session->userdata['member_id'];
				$image['entry_id'] = $entry_id;
				$image['channel_id'] = $channel_id;
				$image['filename'] = basename($image_path);
				$image['extension'] = end(explode('.', $image['filename']));
				$image['upload_date'] = $this->EE->localize->now;
				$image['filesize'] = @filesize($image_path);
				$image['title'] = 'Untitled';

				// Mime type
				$filemime = 'image/jpeg';
				if ($image['extension'] == 'png') $filemime = 'image/png';
				elseif ($image['extension'] == 'gif') $filemime = 'image/gif';
				$image['mime'] = $filemime;

				// -----------------------------------------
				// Loop through all columns and map
				// -----------------------------------------
				foreach($cols as $col_id => $map)
				{
					if ($map == 'image') continue;

					if (isset($row['col_id_'.$col_id]) === TRUE) $image[$map] = $row['col_id_'.$col_id];
				}

				// -----------------------------------------
				// Copy file to temp dir
				// -----------------------------------------
				copy($image_path, $temp_dir.$image['filename']);

				// -----------------------------------------
				// Loop over all action groups!
				// -----------------------------------------
				foreach ($settings['action_groups'] as $group)
				{
					$size_name = $group['group_name'];
					$size_filename = str_replace('.'.$image['extension'], "__{$size_name}.{$image['extension']}", $image['filename']);

					// Make a copy of the file
					@copy($temp_dir.$image['filename'], $temp_dir.$size_filename);
					@chmod($temp_dir.$size_filename, 0777);

					// -----------------------------------------
					// Loop over all Actions and RUN! OMG!
					// -----------------------------------------
					foreach($group['actions'] as $action_name => $action_settings)
					{
						// RUN!
						$actions[$action_name]->settings = $action_settings;
						$res = $actions[$action_name]->run($temp_dir.$size_filename);

					}


				}

				// -----------------------------------------
				// Keep Original Image?
				// -----------------------------------------
				if (isset($settings['keep_original']) == TRUE && $settings['keep_original'] == 'no')
				{
					@unlink($temp_dir.$image['filename']);
				}

				// -----------------------------------------
				// Upload all Images!
				// -----------------------------------------
				$metadata = array();
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


				$image['width'] = isset($metadata[$image['filename']]['width']) ? $metadata[$image['filename']]['width'] : 0;
				$image['height'] = isset($metadata[$image['filename']]['height']) ? $metadata[$image['filename']]['height'] : 0;
				$image['filesize'] = isset($metadata[$image['filename']]['size']) ? $metadata[$image['filename']]['size'] : 0;

				// -----------------------------------------
				// Parse Size Metadata!
				// -----------------------------------------
				$mt = '';
				foreach($settings['action_groups'] as $group)
				{
					$name = strtolower($group['group_name']);
					$size_filename = str_replace('.'.$image['extension'], "__{$name}.{$image['extension']}", $image['filename']);

					$mt .= $name.'|' . implode('|', $metadata[$size_filename]) . '/';
				}

				// Check URL Title
				if (isset($image['url_title']) == FALSE OR $image['url_title'] == FALSE)
				{
					$image['url_title'] = url_title(trim(strtolower($image['title'])));
				}

				$image['sizes_metadata'] = $mt;

				// -----------------------------------------
				// New File
				// -----------------------------------------
				$this->EE->db->insert('exp_channel_images', $image);


			}

		}

		$o['success'] = 'yes';
		exit( $this->EE->image_helper->generate_json($o) );
	}

	// ********************************************************************************* //

	private function ascii_string($string)
	{
		$string = strtr(utf8_decode($string), 
           utf8_decode(	'ŠŒŽšœžŸ¥µÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝßàáâãäåæçèéêëìíîïðñòóôõöøùúûüýÿ'),
           				'SOZsozYYuAAAAAAACEEEEIIIIDNOOOOOOUUUUYsaaaaaaaceeeeiiiionoooooouuuuyy');
		return $string;
	}

	// ********************************************************************************* //

} // END CLASS

/* End of file ajax.channel_images.php  */
/* Location: ./system/expressionengine/third_party/channel_images/ajax.channel_images.php */