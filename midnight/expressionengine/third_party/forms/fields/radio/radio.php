<?php if (!defined('BASEPATH')) die('No direct script access allowed');

/**
 * Channel Forms Radio field
 *
 * @package			DevDemon_Forms
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2011 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com/forms/
 * @see				http://expressionengine.com/user_guide/development/fieldtypes.html
 */
class CF_Field_radio extends CF_Field
{

	/**
	 * Field info - Required
	 *
	 * @access public
	 * @var array
	 */
	public $info = array(
		'title'		=>	'Radio Button',
		'name' 		=>	'radio',
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
		$out  = '';

		// -----------------------------------------
		// Default values
		// -----------------------------------------
		if (isset($field['settings']['choices']) == FALSE OR is_array($field['settings']['choices']) == FALSE)
		{
			$field['settings']['choices'][]  = array('label' => 'First', 'value' => 'First');
			$field['settings']['choices'][]  = array('label' => 'Second', 'value' => 'Second');
			$field['settings']['choices'][]  = array('label' => 'Third', 'value' => 'Third');
			$field['settings']['values_enabled'] = 'no';
		}

		// Use Values?
		$use_values = ($field['settings']['values_enabled'] == 'yes') ? TRUE : FALSE;

		// Default Choice (always INT)
		if (isset($field['settings']['default_choice']) == FALSE) $field['settings']['default_choice'] = '';
		else $field['settings']['default_choice'] = (int) $field['settings']['default_choice'];

		// -----------------------------------------
		// Do we have any previous submits!
		// -----------------------------------------
		$check_submit = FALSE;
		if (empty($data) == FALSE)
		{
			$check_submit = TRUE;
		}

		// -----------------------------------------
		// Loop over all items!
		// -----------------------------------------
		$out .= '<ul class="radios">';
		foreach ($field['settings']['choices'] as $number => $choice)
		{
			// What Value are we going to use?
			$value = ($use_values ? $choice['value'] : $choice['label']);

			// Check checked!
			$checked = FALSE;
			if ($field['settings']['default_choice'] === $number) $checked = TRUE;

			// Now Check for returned val!
			if ($check_submit == TRUE)
			{
				if ($data == $value) $checked = TRUE;
				else $checked = FALSE;
			}

			$out .= '<li>' . form_radio($field['form_name'], $value, $checked) . '&nbsp; '.$choice['label'].'</li>';
		}
		$out .= '</ul>';

		return $out;
	}

	// ********************************************************************************* //

	public function field_settings($settings=array(), $template=TRUE)
	{
		$vData = $settings;

		if (isset($vData['choices']) == FALSE OR is_array($vData['choices']) == FALSE)
		{
			$vData['choices'][]  = array('label' => 'First', 'value' => 'First');
			$vData['choices'][]  = array('label' => 'Second', 'value' => 'Second');
			$vData['choices'][]  = array('label' => 'Third', 'value' => 'Third');
		}

		// Default Choice (always INT)
		if (isset($vData['default_choice']) == FALSE) $vData['default_choice'] = '';
		else $vData['default_choice'] = (int) $vData['default_choice'];

		return $this->EE->load->view('settings', $vData, TRUE);
	}

	// ********************************************************************************* //

	public function save_settings($settings=array())
	{
		foreach($settings['choices'] as $number => &$choice)
		{
			$choice['label'] = trim($choice['label']);
			$choice['value'] = trim($choice['value']);

			if ($choice['label'] == FALSE) unset ($settings['choices'][$number]);
			if ($choice['value'] == FALSE) $choice['value'] = $choice['label'];
		}

		return $settings;
	}

	// ********************************************************************************* //
}

/* End of file radio.php */
/* Location: ./system/expressionengine/third_party/forms/fields/radio/radio.php */