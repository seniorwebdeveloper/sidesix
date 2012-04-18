<?php if (!defined('BASEPATH')) die('No direct script access allowed');

/**
 * Channel Forms Phone field
 *
 * @package			DevDemon_Forms
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2011 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com/forms/
 * @see				http://expressionengine.com/user_guide/development/fieldtypes.html
 */
class CF_Field_phone extends CF_Field
{

	/**
	 * Field info - Required
	 *
	 * @access public
	 * @var array
	 */
	public $info = array(
		'title'		=>	'Phone',
		'name' 		=>	'phone',
		'category'	=>	'power_tools',
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
		// Load Language File
		$this->EE->lang->load($this->info['name'], $this->EE->lang->user_lang, FALSE, TRUE, $this->field_path);

		$options = array();
		$options['name'] = '';
		$options['class'] = 'text';

		// -----------------------------------------
		// Default Settings
		// -----------------------------------------
		if (isset($field['settings']['show_cc']) == FALSE) $field['settings']['show_cc'] = 'no';
		if (isset($field['settings']['show_area']) == FALSE) $field['settings']['show_area'] = 'yes';
		if (isset($field['settings']['show_ext']) == FALSE) $field['settings']['show_ext'] = 'no';

		// If in publish field, lets disable it
		if ($template == FALSE) $options['readonly'] = 'readonly';

		$out = '<div class="dfinput_phones">';

		// -----------------------------------------
		// Show Country Code
		// -----------------------------------------
		if (isset($field['settings']['show_cc']) == TRUE && $field['settings']['show_cc'] == 'yes')
		{
			if ($template)
			{
				$options['name'] = $field['form_name'].'[cc]';

				// Form data?
				if (isset($data['cc'])) $options['value'] = $data['cc'];
			}

			$out .= '<div class="dfinput_left phone_cc">';
			$out .=		form_input($options);
			$out .= 	'<label>' . $this->EE->lang->line('form:cc_code') . '</label>';
			$out .= '</div>';
		}

		// -----------------------------------------
		// Show Area Code
		// -----------------------------------------
		if (isset($field['settings']['show_area']) == TRUE && $field['settings']['show_area'] == 'yes')
		{
			if ($template)
			{
				$options['name'] = $field['form_name'].'[area]';

				// Form data?
				if (isset($data['area'])) $options['value'] = $data['area'];
			}

			$out .= '<div class="dfinput_left phone_area">';
			$out .=		form_input($options);
			$out .= 	'<label>' . $this->EE->lang->line('form:area_code') . '</label>';
			$out .= '</div>';
		}

		// -----------------------------------------
		// Show Phone Number
		// -----------------------------------------
		if ($template)
		{
			$options['name'] = $field['form_name'].'[phone_number]';

			// Form data?
			if (isset($data['phone_number'])) $options['value'] = $data['phone_number'];
		}

		$out .= '<div class="dfinput_left phone_number">';
		$out .=		form_input($options);
		$out .= 	'<label>' . $this->EE->lang->line('form:phonenumber') . '</label>';
		$out .= '</div>';

		// -----------------------------------------
		// Show Extensions
		// -----------------------------------------
		if (isset($field['settings']['show_ext']) == TRUE && $field['settings']['show_ext'] == 'yes')
		{
			if ($template)
			{
				$options['name'] = $field['form_name'].'[ext]';

				// Form data?
				if (isset($data['cc'])) $options['value'] = $data['ext'];
			}

			$out .= '<div class="dfinput_left phone_extension">';
			$out .=		form_input($options);
			$out .= 	'<label>' . $this->EE->lang->line('form:extension') . '</label>';
			$out .= '</div>';
		}

		$out .= '<br clear="all">';
		$out .= '</div>'; // dfinput_phones

		return $out;
	}

	// ********************************************************************************* //

	public function validate($field=array(), $data)
	{
		// -----------------------------------------
		// Default Settings
		// -----------------------------------------
		if (isset($field['settings']['show_cc']) == FALSE) $field['settings']['show_cc'] = 'no';
		if (isset($field['settings']['show_area']) == FALSE) $field['settings']['show_area'] = 'yes';
		if (isset($field['settings']['show_ext']) == FALSE) $field['settings']['show_ext'] = 'no';

		// DO we need to check for required?
		if ($field['required'] != 1) return TRUE;

		// Prepare the error
		$error = array('type' => 'general', 'msg' => $this->EE->lang->line('form:error:required_field'));

		// Country Code
		if (isset($field['settings']['show_cc']) == TRUE && $field['settings']['show_cc'] == 'yes')
		{
			if ($data['cc'] == FALSE)
			{
				return $error;
			}
		}

		// Area Code
		if (isset($field['settings']['show_area']) == TRUE && $field['settings']['show_area'] == 'yes')
		{
			if ($data['area'] == FALSE)
			{
				return $error;
			}
		}

		// Phone Number
		if ($data['phone_number'] == FALSE)
		{
			return $error;
		}

		return TRUE;
	}

	// ********************************************************************************* //

	public function save($field=array(), $data)
	{
		return serialize($data);
	}

	// ********************************************************************************* //

	public function output_data($field=array(), $data, $type='template')
	{
		$out = '';

		$data = @unserialize($data);
		$cc = (isset($data['cc'])) ? $data['cc'] : '';
		$area = (isset($data['area'])) ? $data['area'] : '';
		$number = (isset($data['phone_number'])) ? $data['phone_number'] : '';
		$ext = (isset($data['ext'])) ? $data['ext'] : FALSE;

		$phone_format = 'usa';
		if (isset($field['settings']['phone_format'])) $phone_format = $field['settings']['phone_format'];

		switch ($phone_format)
		{
			case 'int':
				$out .= '+'.$cc.$area.$number;
				break;
			default:
				$out .= "({$area}) ". substr($number, 0, 3).'-'.substr($number, 3);
				break;
		}

		if ($ext) $out .= ' x'.$ext;

		return $out;
	}

	// ********************************************************************************* //

	public function field_settings($settings=array(), $template=TRUE)
	{
		$vData = $settings;

		return $this->EE->load->view('settings', $vData, TRUE);
	}

	// ********************************************************************************* //

}

/* End of file phone.php */
/* Location: ./system/expressionengine/third_party/forms/fields/phone/phone.php */