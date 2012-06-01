<?php if (!defined('BASEPATH')) die('No direct script access allowed');

/**
 * Channel Images RESIZE action
 *
 * @package			DevDemon_ChannelImages
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2011 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com/channel_images/
 */
class CI_Action_resize extends Image_Action
{

	/**
	 * Action info - Required
	 *
	 * @access public
	 * @var array
	 */
	public $info = array(
		'title' 	=>	'Resize Image',
		'name'		=>	'resize',
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
		/*
		// Lets calculate how much memory we need
		$MB = 1048576;  // number of bytes in 1M
		$K64 = 65536;    // number of bytes in 64K
		$TWEAKFACTOR = 1.8;  // Or whatever works for you
		$image_info = @getimagesize($file);

		// PNG images misses channels
		if (isset($image_info['channels']) == FALSE) $image_info['channels'] = 3;

		$memory_needed = round( ($image_info[0] * $image_info[1] * $image_info['bits'] * $image_info['channels'] / 8 + $K64) * $TWEAKFACTOR);
		//$memory_needed = round( ($image_info[0] * $image_info[1] * $image_info['bits'] ) );
		$memory_limit = ((integer) ini_get('memory_limit')) * $MB;

		// Do we need more memory?
		if ( ($memory_limit > 0) == TRUE && ($memory_needed > $memory_limit) == TRUE )
		{
			return 'This image needs ' . round($memory_needed / $MB) . 'MB of memory. Only ' . ($memory_limit / $MB) . 'MB available.';
		}
		*/

		@set_include_path(PATH_THIRD.'channel_images/libraries/PHPThumb/');
		@set_include_path(PATH_THIRD.'channel_images/libraries/PHPThumb/thumb_plugins/');

		// Allow Upsizing?
		$upsize = TRUE;
		if (isset($this->settings['upsizing']) == TRUE && $this->settings['upsizing'] == 'no') $upsize = FALSE;

		// Include the library
	    if (class_exists('PhpThumbFactory') == FALSE) require_once PATH_THIRD.'channel_images/libraries/PHPThumb/ThumbLib.inc.php';

	    // Create Instance
	    $thumb = PhpThumbFactory::create($file, array('resizeUp' => $upsize, 'jpegQuality' => $this->settings['quality']));

	    // Resize it!
		$thumb->resize($this->settings['width'], $this->settings['height']);

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
		if (isset($vData['quality']) == FALSE) $vData['quality'] = '100';
		if (isset($vData['upsizing']) == FALSE) $vData['upsizing'] = 'no';

		return $this->EE->load->view('settings', $vData, TRUE);
	}

	// ********************************************************************************* //

}

/* End of file resize.php */
/* Location: ./system/expressionengine/third_party/channel_images/actions/resize/resize.php */