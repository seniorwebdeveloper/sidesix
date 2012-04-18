<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Channel Images Model File
 *
 * @package			DevDemon_ChannelImages
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2010 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com
 */
class Channel_images_model
{
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
		$this->site_id = $this->EE->config->item('site_id');
	}

	// ********************************************************************************* //

	/**
	 * Get Settings of a field
	 *
	 * @param int $field_id
	 * @access public
	 * @return array - Field Settings
	 */
	public function get_field_settings($field_id)
	{
		if (isset($this->EE->session->cache['Channel_Images']['Field'][$field_id]) == FALSE)
		{
			$query = $this->EE->db->select('field_settings')->from('exp_channel_fields')->where('field_id', $field_id)->get();
			$settings = unserialize(base64_decode($query->row('field_settings')));
			$this->EE->session->cache['Channel_Images']['Field'][$field_id] = $settings;
		}
		else
		{
			$settings = $this->EE->session->cache['Channel_Images']['Field'][$field_id];
		}

		return $settings;
	}

	// ********************************************************************************* //



	/**
	 * Get Field ID
	 * Since we moved to Field Based Settings, our legacy versions where not storing field_id's
	 * so we need to somehow get it from the channel_id
	 *
	 * @param object $image
	 * @access public
	 * @return int - The FieldID
	 */
	public function get_field_id($image)
	{
		// Easy way..
		if ($image->field_id > 1)
		{
			return $image->field_id;
		}

		// Hard way
		if (isset($this->EE->session->cache['Channel_Images']['Channel2Field'][$image->channel_id]) == FALSE)
		{
			// Then we need to use the Channel ID :(
			$query = $this->EE->db->query("SELECT cf.field_id FROM exp_channel_fields AS cf
											LEFT JOIN exp_channels AS c ON c.field_group = cf.group_id
											WHERE c.channel_id = {$image->channel_id} AND cf.field_type = 'channel_images'");
			if ($query->num_rows() == 0)
			{
				$query->free_result();
				return 0;
			}

			$this->EE->session->cache['Channel_Images']['Channel2Field'][$image->channel_id] = $query->row('field_id');
			$field_id = $query->row('field_id');

			$query->free_result();
		}
		else
		{
			$field_id = $this->EE->session->cache['Channel_Images']['Channel2Field'][$image->channel_id];
		}

		return $field_id;
	}

	// ********************************************************************************* //

	// TEMP SOLUTION FOR EE 2.1.1 SIGH!!!
	public function _assign_libraries()
	{

	}

} // END CLASS

/* End of file Channel_images_model.php  */
/* Location: ./system/expressionengine/third_party/channel_images/models/Channel_images_model.php */