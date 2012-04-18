<?php if (!defined('BASEPATH')) die('No direct script access allowed');

/**
 * Channel Forms ADDRESS field
 *
 * @package			DevDemon_Forms
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2011 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com/forms/
 * @see				http://expressionengine.com/user_guide/development/fieldtypes.html
 */
class CF_Field_address extends CF_Field
{

	/**
	 * Field info - Required
	 *
	 * @access public
	 * @var array
	 */
	public $info = array(
		'title'		=>	'Address',
		'name' 		=>	'address',
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
		if (isset($field['settings']['hide_address2']) == FALSE) $field['settings']['hide_address2'] = 'no';
		if (isset($field['settings']['hide_state']) == FALSE) $field['settings']['hide_state'] = 'no';
		if (isset($field['settings']['hide_zip']) == FALSE) $field['settings']['hide_zip'] = 'no';
		if (isset($field['settings']['hide_country']) == FALSE) $field['settings']['hide_country'] = 'no';

		// If in publish field, lets disable it
		if ($template == FALSE) $options['readonly'] = 'readonly';

		$out = '';

		// -----------------------------------------
		// Address
		// -----------------------------------------
		if ($template)
		{
			$options['name'] = $field['form_name'].'[address]';

			// Form data?
			if (isset($data['address'])) $options['value'] = $data['address'];
		}
		$out .= '<div class="dfinput_full address_street">';
		$out .=		form_input($options);
		$out .= 	'<label>' . $this->EE->lang->line('form:street_addr') . '</label>';
		$out .= '</div>';

		// -----------------------------------------
		// Address 2
		// -----------------------------------------
		if (isset($field['settings']['hide_address2']) == TRUE && $field['settings']['hide_address2'] != 'yes')
		{
			if ($template)
			{
				$options['name'] = $field['form_name'].'[address2]';

				// Form data?
				if (isset($data['address2'])) $options['value'] = $data['address2'];
			}
			$out .= '<div class="dfinput_full address_street2">';
			$out .=		form_input($options);
			$out .= 	'<label>' . $this->EE->lang->line('form:address2') . '</label>';
			$out .= '</div>';
		}

		// -----------------------------------------
		// City
		// -----------------------------------------
		if ($template)
		{
			$options['name'] = $field['form_name'].'[city]';

			// Form data?
			if (isset($data['city'])) $options['value'] = $data['city'];
		}
		$out .= '<div class="dfinput_left address_city">';
		$out .=		form_input($options);
		$out .= 	'<label>' . $this->EE->lang->line('form:city') . '</label>';
		$out .= '</div>';

		// -----------------------------------------
		// State
		// -----------------------------------------
		if (isset($field['settings']['hide_state']) == TRUE && $field['settings']['hide_state'] != 'yes')
		{
			if ($template)
			{
				$options['name'] = $field['form_name'].'[state]';

				// Form data?
				if (isset($data['state'])) $options['value'] = $data['state'];
			}
			$out .= '<div class="dfinput_right address_state">';
			$out .=		form_input($options);
			$out .= 	'<label>' . $this->EE->lang->line('form:state_region') . '</label>';
			$out .= '</div>';
		}

		// -----------------------------------------
		// ZIP
		// -----------------------------------------
		if (isset($field['settings']['hide_zip']) == TRUE && $field['settings']['hide_zip'] != 'yes')
		{
			if ($template)
			{
				$options['name'] = $field['form_name'].'[zip]';

				// Form data?
				if (isset($data['zip'])) $options['value'] = $data['zip'];
			}
			$out .= '<div class="dfinput_left address_zip">';
			$out .=		form_input($options);
			$out .= 	'<label>' . $this->EE->lang->line('form:zip_postal') . '</label>';
			$out .= '</div>';
		}

		// -----------------------------------------
		// COUNTRY
		// -----------------------------------------
		if (isset($field['settings']['hide_country']) == TRUE && $field['settings']['hide_country'] != 'yes')
		{
			include(PATH_THIRD . 'forms/config/forms_countries.php');

			if ($template) $options['name'] = $field['form_name'].'[country]';

			$out .= '<div class="dfinput_right address_country">';
			$out .=		'<select name="'.$options['name'].'">';

			if ($template)
			{
				// Form data?
				$selected_country = (isset($data['country']) === TRUE) ? $data['country'] : '';

				foreach($cf_countries_list_eng as $country)
				{
					$selected = ($selected_country == $country) ? 'selected' : '';

					$out .= "<option {$selected}>{$country}</option>";
				}
			}
			else
			{
				$out .= '<option>' . reset($cf_countries_list_eng) . '</option>';
			}

			$out .=		'</select>';
			$out .= 	'<label>' . $this->EE->lang->line('form:country') . '</label>';
			$out .= '</div>';
		}

		$out .= '<br clear="all">';

		return $out;
	}

	// ********************************************************************************* //

	public function validate($field=array(), $data)
	{
		// -----------------------------------------
		// Default Settings
		// -----------------------------------------
		if (isset($field['settings']['hide_address2']) == FALSE) $field['settings']['hide_address2'] = 'no';
		if (isset($field['settings']['hide_state']) == FALSE) $field['settings']['hide_state'] = 'no';
		if (isset($field['settings']['hide_zip']) == FALSE) $field['settings']['hide_zip'] = 'no';
		if (isset($field['settings']['hide_country']) == FALSE) $field['settings']['hide_country'] = 'no';

		// DO we need to check for required?
		if ($field['required'] != 1) return TRUE;

		// Prepare the error
		$error = array('type' => 'general', 'msg' => $this->EE->lang->line('form:error:required_field'));

		// Address
		if ($data['address'] == FALSE)
		{
			return $error;
		}

		// Address 2
		if (isset($field['settings']['hide_address2']) == TRUE && $field['settings']['hide_address2'] != 'yes')
		{
			if ($data['address2'] == FALSE)
			{
				return $error;
			}
		}

		// City
		if ($data['city'] == FALSE)
		{
			return $error;
		}

		// State
		if (isset($field['settings']['hide_state']) == TRUE && $field['settings']['hide_state'] != 'yes')
		{
			if ($data['state'] == FALSE)
			{
				return $error;
			}
		}

		// Zip
		if (isset($field['settings']['hide_zip']) == TRUE && $field['settings']['hide_zip'] != 'yes')
		{
			if ($data['zip'] == FALSE)
			{
				return $error;
			}
		}

		// Country
		if (isset($field['settings']['hide_country']) == TRUE && $field['settings']['hide_country'] != 'yes')
		{
			if ($data['country'] == FALSE)
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
		// TODO: LOCALIZE THIS?

		$data = @unserialize($data);
		$out = '';

		// -----------------------------------------
		// Template? or Email
		// -----------------------------------------
		if ($type == 'html' OR $type == 'text')
		{
			if (isset($data['address']) === TRUE) $out .= 'Address: ' . $data['address'] . ' <br />';
			if (isset($data['address2']) === TRUE) $out .= 'Address 2: ' . $data['address2'] . ' <br />';
			if (isset($data['city']) === TRUE) $out .= 'City: ' . $data['city'] . ' <br />';
			if (isset($data['state']) === TRUE) $out .= 'State: ' . $data['state'] . ' <br />';
			if (isset($data['zip']) === TRUE) $out .= 'Zip: ' . $data['zip'] . ' <br />';
			if (isset($data['country']) === TRUE) $out .= 'Country: ' . $data['country'] . ' <br />';

			// Remove the BR's and add linebreaks instead
			if ($type == 'text')
			{
				$out = str_replace('<br />', chr(10), $out);
			}
		}
		else
		{
			if (isset($data['address']) === TRUE) $out .= $data['address'];
		}

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

/* End of file address.php */
/* Location: ./system/expressionengine/third_party/forms/fields/address/address.php */