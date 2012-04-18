<?php if (!defined('BASEPATH')) die('No direct script access allowed');

/**
 * Channel Forms HTML field
 *
 * @package			DevDemon_Forms
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2011 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com/forms/
 * @see				http://expressionengine.com/user_guide/development/fieldtypes.html
 */
class CF_Field_html extends CF_Field
{

	/**
	 * Field info - Required
	 *
	 * @access public
	 * @var array
	 */
	public $info = array(
		'title'		=>	'HTML',
		'name' 		=>	'html',
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

		// Disable Field Labels!
		$this->show_field_label = FALSE;
	}

	// ********************************************************************************* //

	public function render_field($field=array(), $template=TRUE, $data)
	{
		// Remove all wrappers!
		$this->show_wrappers = FALSE;

		if (isset($field['settings']['html']) === TRUE)
		{
			return $field['settings']['html'];
		}
		else
		{
			return '<em>HTML Code</em>';
		}
	}

	// ********************************************************************************* //

	public function field_settings($settings=array(), $template=TRUE)
	{
		$vData = $settings;

		return $this->EE->load->view('settings', $vData, TRUE);
	}

	// ********************************************************************************* //

}

/* End of file html.php */
/* Location: ./system/expressionengine/third_party/forms/fields/html/html.php */