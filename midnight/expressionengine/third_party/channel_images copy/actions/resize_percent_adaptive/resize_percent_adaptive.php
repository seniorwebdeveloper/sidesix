<?php if (!defined('BASEPATH')) die('No direct script access allowed');

/**
 * Channel Images RESIZE PERCENT ADAPTIVE action
 *
 * @package			DevDemon_ChannelImages
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2011 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com/channel_images/
 */
class CI_Action_resize_percent_adaptive extends Image_Action
{

	/**
	 * Action info - Required
	 *
	 * @access public
	 * @var array
	 */
	public $info = array(
		'title' 	=>	'Resize (Adaptive Percentage)',
		'name'		=>	'resize_percent_adaptive',
		'version'	=>	'1.0',
		'enabled'	=>	TRUE,
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

	public function run($file)
	{
		@set_include_path(PATH_THIRD.'channel_images/libraries/PHPThumb/');
		@set_include_path(PATH_THIRD.'channel_images/libraries/PHPThumb/thumb_plugins/');

		// Include the library
	    if (class_exists('PhpThumbFactory') == FALSE) require_once PATH_THIRD.'channel_images/libraries/PHPThumb/ThumbLib.inc.php';

	    // Create Instance
	    $thumb = PhpThumbFactory::create($file, array('resizeUp' => FALSE, 'jpegQuality' => 100));

	    // Resize it!
		$thumb->adaptiveResizePercent($this->settings['width'], $this->settings['height'], $this->settings['percent']);

		// Save it
		$thumb->save($file);

		return TRUE;
	}

	// ********************************************************************************* //

	public function settings($settings)
	{
		$vData = $settings;

		if (isset($vData['width']) == FALSE) $vData['width'] = '100';
		if (isset($vData['height']) == FALSE) $vData['height'] = '100';
		if (isset($vData['percent']) == FALSE) $vData['percent'] = '20';


		return $this->EE->load->view('settings', $vData, TRUE);
	}

	// ********************************************************************************* //

}

/* End of file resize_percent_adaptive.php */
/* Location: ./system/expressionengine/third_party/channel_images/actions/resize_percent_adaptive/resize_percent_adaptive.php */