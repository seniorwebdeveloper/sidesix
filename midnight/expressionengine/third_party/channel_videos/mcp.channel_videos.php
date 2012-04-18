<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Channel Videos Module Control Panel Class
 *
 * @package			DevDemon_ChannelVideos
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2010 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com/channel_videos/
 * @see				http://expressionengine.com/user_guide/development/module_tutorial.html#control_panel_file
 */
class Channel_videos_mcp
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

		// Load Models & Libraries & Helpers
		$this->EE->load->library('channel_videos_helper');
		//$this->EE->load->model('points_model');

		// Some Globals
		$this->base = BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=channel_videos';
		$this->base_short = 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=channel_videos';
		$this->site_id = $this->EE->config->item('site_id');

		// Global Views Data
		$this->vData['base_url'] = $this->base;
		$this->vData['base_url_short'] = $this->base_short;
		$this->vData['method'] = $this->EE->input->get('method');

		if (! defined('DEVDEMON_THEME_URL')) define('DEVDEMON_THEME_URL', $this->EE->config->item('theme_folder_url') . 'third_party/');

		$this->mcp_globals();

		// Add Right Top Menu
		$this->EE->cp->set_right_nav(array(
			'video:docs' 			=> $this->EE->cp->masked_url('http://www.devdemon.com/channel_videos/docs/'),
		));

		// Debug
		//$this->EE->db->save_queries = TRUE;
		//$this->EE->output->enable_profiler(TRUE);
	}

	// ********************************************************************************* //

	/**
	 * MCP PAGE: Index
	 *
	 * @access public
	 * @return string
	 */
	public function index()
	{
		// Page Title & BreadCumbs
		$this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('channel_videos'));



		return $this->EE->load->view('mcp_index', $this->vData, TRUE);
	}

	// ********************************************************************************* //



	private function mcp_globals()
	{
		$this->EE->cp->set_breadcrumb($this->base, $this->EE->lang->line('channel_videos_module_name'));

		$this->EE->channel_videos_helper->mcp_meta_parser('gjs', '', 'ChannelVideos');
		$this->EE->channel_videos_helper->mcp_meta_parser('css', DEVDEMON_THEME_URL . 'channel_videos/channel_videos_mcp.css', 'channel_videos-mcp');
		$this->EE->channel_videos_helper->mcp_meta_parser('js', DEVDEMON_THEME_URL . 'channel_videos/channel_videos_mcp.js', 'channel_videos-mcp');
	}

	// ********************************************************************************* //

	public function ajax_router()
	{

		// -----------------------------------------
		// Ajax Request?
		// -----------------------------------------
		if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
		{
			// Load Library
			if (class_exists('Channel_Videos_AJAX') != TRUE) include 'ajax.channel_videos.php';

			$AJAX = new Channel_Videos_AJAX();

			// Shoot the requested method
			$method = $this->EE->input->get_post('ajax_method');
			echo $AJAX->$method();
			exit();
		}
	}


} // END CLASS

/* End of file mcp.shop.php */
/* Location: ./system/expressionengine/third_party/points/mcp.shop.php */