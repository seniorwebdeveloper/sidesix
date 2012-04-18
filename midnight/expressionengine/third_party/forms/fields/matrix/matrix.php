<?php if (!defined('BASEPATH')) die('No direct script access allowed');

/**
 * Channel Forms MATRIX field
 *
 * @package			DevDemon_Forms
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2011 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com/forms/
 * @see				http://expressionengine.com/user_guide/development/fieldtypes.html
 */
class CF_Field_matrix extends CF_Field
{

	/**
	 * Field info - Required
	 *
	 * @access public
	 * @var array
	 */
	public $info = array(
		'title'		=>	'Matrix/Grid',
		'name' 		=>	'matrix',
		'category'	=>	'power_tools',
		'version'	=>	'1.0',
		'disabled'	=>	TRUE,
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
		$options = array();
		$options['name'] = $settings['field_name'];
		if (isset($settings['placeholder']) == TRUE) $options['placeholder'] = $settings['placeholder'];

		$out  = '<div class="element '.$this->info['name'].'">';
		$out .=		'<label>' . $field['title'] . '</label>';
		$out .=		form_input($options);
		$out .= '</div>';
		return $out;
	}

	// ********************************************************************************* //

	public function validate($settings=array(), $field=array(), $data='')
	{
		// Load Language File
		$result = $this->EE->lang->load('matrix', $this->EE->lang->user_lang, FALSE, TRUE, PATH_THIRD . 'forms/fields/matrix/');

		// Prepare the error
		$error = array('type' => 'general', 'msg' => $this->EE->lang->line('form:not_matrix'));

		// Is empty.. Kill it
		if ($data == FALSE) return $error;

		$result = preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $data);

		if ($result == 0) return $error;

		return TRUE;
	}

	// ********************************************************************************* //

	public function display_field($settings=array(), $pbf=FALSE)
	{
		$out = '';

		$out .= form_input(array(
						'name'        => '',
						'id'          => '',
						'value'       => '',
						'disabled'   => 'disabled',
						'style'       => 'width:65%;',
		));

		return $out;
	}

	// ********************************************************************************* //

	public function field_settings($settings=array(), $pbf=FALSE)
	{
		$vData = $settings;


		return $this->EE->load->view('settings', $vData, TRUE);
	}

	// ********************************************************************************* //

	public function save_settings($settings=array(), $pbf=FALSE)
	{
		return $settings;
	}

	// ********************************************************************************* //


}

/* End of file matrix.php */
/* Location: ./system/expressionengine/third_party/forms/fields/matrix/matrix.php */