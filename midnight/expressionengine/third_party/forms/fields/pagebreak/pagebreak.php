<?php if (!defined('BASEPATH')) die('No direct script access allowed');

/**
 * Channel Forms PAGEBREAKL field
 *
 * @package			DevDemon_Forms
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2011 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com/forms/
 * @see				http://expressionengine.com/user_guide/development/fieldtypes.html
 */
class CF_Field_pagebreak extends CF_Field
{

	/**
	 * Field info - Required
	 *
	 * @access public
	 * @var array
	 */
	public $info = array(
		'title'		=>	'Pagebreak',
		'name' 		=>	'pagebreak',
		'category'	=>	'form_tools',
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

	public function render_field($settings=array(), $field=array())
	{
		return '';
	}

	// ********************************************************************************* //

	public function display_field($settings=array(), $pbf=FALSE)
	{
		return '<img src="'.FORMS_THEME_URL.'img/form_pagebreak.png">';
	}

	// ********************************************************************************* //


}

/* End of file email.php */
/* Location: ./system/expressionengine/third_party/forms/fields/email/email.php */