<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Channel Images API File
 *
 * @package			DevDemon_ChannelFiles
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2010 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com
 */
class Channel_Images_API
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
	}

	// ********************************************************************************* //

	public function delete_image($image)
	{
		if (isset($image->field_id) == FALSE) return FALSE;

		// Grab the field settings
		$settings = $this->EE->channel_images_model->get_field_settings($image->field_id);
		$settings = $settings['channel_images'];

		// Location
		$location_type = $settings['upload_location'];
		$location_class = 'CI_Location_'.$location_type;
		$location_settings = $settings['locations'][$location_type];
		$location_file = PATH_THIRD.'channel_images/locations/'.$location_type.'/'.$location_type.'.php';

		// Load Main Class
		if (class_exists('Image_Location') == FALSE) require PATH_THIRD.'channel_images/locations/image_location.php';
		if (class_exists($location_class) == FALSE) require $location_file;
		$LOC = new $location_class($location_settings);

		// Delete From DB
		$this->EE->db->where('image_id', $image->image_id);
		$this->EE->db->or_where('link_image_id', $image->image_id);
		$this->EE->db->delete('exp_channel_images');

		// Is there another instance of the image still there?
		$this->EE->db->select('image_id');
		$this->EE->db->from('exp_channel_images');
		$this->EE->db->where('entry_id', $image->entry_id);
		$this->EE->db->where('field_id', $image->field_id);
		$this->EE->db->where('filename', $image->filename);
		$query = $this->EE->db->get();

		if ($query->num_rows() == 0)
		{
			// Loop over all action groups
			foreach($settings['action_groups'] as $group)
			{
				$name = strtolower($group['group_name']);
				$name = str_replace('.'.$image->extension, "__{$name}.{$image->extension}", $image->filename);

				$res = $LOC->delete_file($image->entry_id, $name);
			}

			// Delete original file from system
			$res = $LOC->delete_file($image->entry_id, $image->filename);
		}

		return TRUE;
	}

	// ********************************************************************************* //

	public function clean_temp_dirs($field_id)
	{
		$temp_path = APPPATH.'cache/channel_images/field_'.$field_id.'/';

		if (file_exists($temp_path) !== TRUE) return;

		$this->EE->load->helper('file');

		// Loop over all files
		$tempdirs = @scandir($temp_path);

		foreach ($tempdirs as $tempdir)
		{
			if ($tempdir == '.' OR $tempdir == '..') continue;
			if ( ($this->EE->localize->now - $tempdir) < 7200) continue;

			@chmod($temp_path.$tempdir, 0777);
			@delete_files($temp_path.$tempdir, TRUE);
			@unlink($temp_path.$tempdir);
		}
	}

	// ********************************************************************************* //


} // END CLASS

/* End of file api.channel_images.php  */
/* Location: ./system/expressionengine/third_party/channel_images/api.channel_images.php */