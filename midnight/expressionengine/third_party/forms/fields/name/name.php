<?php if (!defined('BASEPATH')) die('No direct script access allowed');

/**
 * Channel Forms NAME field
 *
 * @package			DevDemon_Forms
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2011 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com/forms/
 * @see				http://expressionengine.com/user_guide/development/fieldtypes.html
 */
class CF_Field_name extends CF_Field
{

	/**
	 * Field info - Required
	 *
	 * @access public
	 * @var array
	 */
	public $info = array(
		'title'		=>	'Name',
		'name' 		=>	'name',
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
		$options['value'] = '';
		$options['class'] = 'text';

		// -----------------------------------------
		// Default Settings
		// -----------------------------------------
		if (isset($field['settings']['show_prefix']) == FALSE) $field['settings']['show_prefix'] = 'no';
		if (isset($field['settings']['show_suffix']) == FALSE) $field['settings']['show_suffix'] = 'no';
		if (isset($field['settings']['prefix_select']) == FALSE) $field['settings']['prefix_select'] = 'no';

		// If in publish field, lets disable it
		if ($template == FALSE) $options['readonly'] = 'readonly';

		$out = '<div class="dfinput_names">';

		// -----------------------------------------
		// Show Prefix
		// -----------------------------------------
		if (isset($field['settings']['show_prefix']) == TRUE && $field['settings']['show_prefix'] == 'yes')
		{
			if ($template)
			{
				$options['name'] = $field['form_name'].'[prefix]';

				// Form data?
				if (isset($data['prefix'])) $options['value'] = $data['prefix'];
			}

			$out .= '<div class="dfinput_left name_prefix">';

			// Dropdown?
			if (isset($field['settings']['prefix_select']) == TRUE && $field['settings']['prefix_select'] == 'yes')
			{
				$arr = array();
				$arr['Mr.'] = 'Mr';
				$arr['Mrs.'] = 'Mrs';
				$arr['Ms.'] = 'Ms';
				$arr['Miss.'] = 'Miss';
				$arr['Dr.'] = 'Dr. (Doctor)';
				$arr['Prof.'] = 'Prof. (Professor)';
				//$arr['Gov.'] = 'Gov. (Governor)';
				//$arr['Hon.'] = 'Hon. (Honorable)';

				$out .=		form_dropdown($options['name'], $arr, $options['value'], 'style="width:90%;"');
			}
			else
			{
				$out .=		form_input($options);
			}
			$out .= 	'<label>' . $this->EE->lang->line('form:prefix') . '</label>';
			$out .= '</div>';
		}

		// -----------------------------------------
		// Show First Name
		// -----------------------------------------
		if ($template)
		{
			$options['name'] = $field['form_name'].'[first_name]';

			// Form data?
			if (isset($data['first_name'])) $options['value'] = $data['first_name'];
		}

		$out .= '<div class="dfinput_left name_first">';
		$out .=		form_input($options);
		$out .= 	'<label>' . $this->EE->lang->line('form:first_name') . '</label>';
		$out .= '</div>';

		// -----------------------------------------
		// Show Last Name
		// -----------------------------------------
		if ($template)
		{
			$options['name'] = $field['form_name'].'[last_name]';

			// Form data?
			if (isset($data['last_name'])) $options['value'] = $data['last_name'];
		}

		$out .= '<div class="dfinput_left name_last">';
		$out .=		form_input($options);
		$out .= 	'<label>' . $this->EE->lang->line('form:last_name') . '</label>';
		$out .= '</div>';

		// -----------------------------------------
		// Show Suffix
		// -----------------------------------------
		if (isset($field['settings']['show_suffix']) == TRUE && $field['settings']['show_suffix'] == 'yes')
		{
			if ($template)
			{
				$options['name'] = $field['form_name'].'[suffix]';

				// Form data?
				if (isset($data['suffix'])) $options['value'] = $data['suffix'];
			}

			$out .= '<div class="dfinput_left name_suffix">';
			$out .=		form_input($options);
			$out .= 	'<label>' . $this->EE->lang->line('form:suffix') . '</label>';
			$out .= '</div>';
		}

		$out .= '<br clear="all">';
		$out .= '</div>'; // dfinput_names

		return $out;
	}

	// ********************************************************************************* //

	public function validate($field=array(), $data)
	{
		// -----------------------------------------
		// Default Settings
		// -----------------------------------------
		if (isset($field['settings']['show_prefix']) == FALSE) $field['settings']['show_prefix'] = 'no';
		if (isset($field['settings']['show_suffix']) == FALSE) $field['settings']['show_suffix'] = 'no';

		// DO we need to check for required?
		if ($field['required'] != 1) return TRUE;

		// Prepare the error
		$error = array('type' => 'general', 'msg' => $this->EE->lang->line('form:error:required_field'));

		// Prefix
		if (isset($field['settings']['show_prefix']) == TRUE && $field['settings']['show_prefix'] == 'yes')
		{
			if ($data['prefix'] == FALSE)
			{
				return $error;
			}
		}

		// First Name
		if ($data['first_name'] == FALSE)
		{
			return $error;
		}

		// Last Name
		if ($data['last_name'] == FALSE)
		{
			return $error;
		}

		// Suffix
		if (isset($field['settings']['show_suffix']) == TRUE && $field['settings']['show_suffix'] == 'yes')
		{
			if ($data['suffix'] == FALSE)
			{
				return $error;
			}
		}

		return TRUE;
	}

	// ********************************************************************************* //

	public function save($field=array(), $data)
	{
		return serialize($data);
	}

	// ********************************************************************************* //

	public function output_data($field=array(), $data, $type='html')
	{
		$data = @unserialize($data);
		
		$prefix = (isset($data['prefix'])) ? $data['prefix'] : '';
		$first_name = (isset($data['first_name'])) ? $data['first_name'] : '';
		$last_name = (isset($data['last_name'])) ? $data['last_name'] : '';
		$suffix = (isset($data['suffix'])) ? $data['suffix'] : '';

		return ($prefix . ' ' . $first_name . ' ' . $last_name . ' ' . $suffix);
	}

	// ********************************************************************************* //

	public function field_settings($settings=array(), $template=TRUE)
	{
		$vData = $settings;

		return $this->EE->load->view('settings', $vData, TRUE);
	}

	// ********************************************************************************* //

}

/* End of file name.php */
/* Location: ./system/expressionengine/third_party/forms/fields/name/name.php */