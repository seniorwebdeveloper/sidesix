<?php if (!defined('BASEPATH')) die('No direct script access allowed');

/**
 * Channel Forms EMAIL field
 *
 * @package			DevDemon_Forms
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2011 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com/forms/
 * @see				http://expressionengine.com/user_guide/development/fieldtypes.html
 */
class CF_Field_email extends CF_Field
{

	/**
	 * Field info - Required
	 *
	 * @access public
	 * @var array
	 */
	public $info = array(
		'title'		=>	'Email',
		'name' 		=>	'email',
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

	public function render_field($field=array(), $template=TRUE, $data)
	{
		$options = array();
		$options['name'] = $field['form_name'];
		$options['class'] = 'text';

		// -----------------------------------------
		// If in publish field, lets disable it
		// -----------------------------------------
		if ($template == FALSE)
		{
			$options['readonly'] = 'readonly';
			$options['name'] = '';
		}

		// -----------------------------------------
		// Placeholder Text
		// -----------------------------------------
		if (isset($field['settings']['placeholder']) == TRUE)
		{
			$options['placeholder'] = $field['settings']['placeholder'];
			$options['data-placeholder'] = $field['settings']['placeholder'];
		}

		// -----------------------------------------
		// Use Member Email?
		// -----------------------------------------
		if (isset($field['settings']['use_member_email']) == TRUE && $field['settings']['use_member_email'] == 'yes' && $this->EE->session->userdata['member_id'] > 0 && $template === TRUE)
		{
			$options['value'] = $this->EE->session->userdata['email'];

			// Hide this field?
			if (isset($field['settings']['hide_if_member_email']) == TRUE && $field['settings']['hide_if_member_email'] == 'yes')
			{
				$this->hidden_field = TRUE;
				$this->hidden_field_value = $this->EE->session->userdata['email'];
				return;
			}
		}

		// Form data?
		if ($data != FALSE) $options['value'] = $data;

		// -----------------------------------------
		// Render
		// -----------------------------------------
		$out =	form_input($options);

		return $out;
	}

	// ********************************************************************************* //

	public function validate($field=array(), $data)
	{
		// Load Language File
		$this->EE->lang->load($this->info['name'], $this->EE->lang->user_lang, FALSE, TRUE, $this->field_path);

		// Prepare the error
		$error = $this->EE->lang->line('form:not_email');

		// Is empty.. Kill it
		if ($data == FALSE) return $error;

		$result = preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $data);

		if ($result == 0) return $error;

		return TRUE;
	}

	// ********************************************************************************* //

	public function save($field=array(), $data)
	{
		// -----------------------------------------
		// Master Email?!?!
		// -----------------------------------------
		if (isset($field['settings']['master_email']) == TRUE && $field['settings']['master_email'] == 'yes')
		{
			$this->EE->session->userdata['email'] = $data;
		}

		return (string) $data;
	}

	// ********************************************************************************* //

	public function field_settings($settings=array(), $template=TRUE)
	{
		$vData = $settings;

		return $this->EE->load->view('settings', $vData, TRUE);
	}

	// ********************************************************************************* //


}

/* End of file email.php */
/* Location: ./system/expressionengine/third_party/forms/fields/email/email.php */