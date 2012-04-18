<?php if (!defined('BASEPATH')) die('No direct script access allowed');

/**
 * Channel Forms DATE field
 *
 * @package			DevDemon_Forms
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2011 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com/forms/
 * @see				http://expressionengine.com/user_guide/development/fieldtypes.html
 */
class CF_Field_date extends CF_Field
{

	/**
	 * Field info - Required
	 *
	 * @access public
	 * @var array
	 */
	public $info = array(
		'title'		=>	'Date',
		'name' 		=>	'date',
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
		$options['name'] = $field['form_name'];
		$options['value'] = '';
		$options['class'] = 'text';

		// -----------------------------------------
		// If in publish field, lets disable it
		// -----------------------------------------
		if ($template == FALSE)
		{
			$options['readonly'] = 'readonly';
			$options['name'] = '';
		}

		// Default values
		if (isset($field['settings']['date_input_type']) == FALSE OR $field['settings']['date_input_type'] == FALSE)
		{
			$field['settings']['date_input_type'] = 'datepicker';
		}

		$out  = '';

		// -----------------------------------------
		// Datepicker?
		// -----------------------------------------
		if ($field['settings']['date_input_type'] == 'datepicker')
		{
			if ($data != FALSE) $options['value'] = $data;
			$out .= form_input($options['name'], $options['value'], ' class="formsfdatepicker" ');
			$out .= '<script type="text/javascript">$(function() { jQuery("input.formsfdatepicker").datepicker(); });</script>';
			return $out;
		}

		// -----------------------------------------
		// Date Field
		// -----------------------------------------
		if ($field['settings']['date_input_type'] == 'datefield')
		{
			$out .= '<div class="dfinput_dates">';
			$options['maxlength'] = '2';

			$out .= '<div class="df_date_elem">';
			$options['name'] = $field['form_name'].'[day]';
			if (isset($data['day'])) $options['value'] = $data['day'];
			$out .= form_input($options);
			$out .= 	'<label>' . $this->EE->lang->line('form:day') . '</label>';
			$out .= '</div>';

			$out .= '<div class="df_date_elem">';
			$options['name'] = $field['form_name'].'[month]';
			if (isset($data['month'])) $options['value'] = $data['month'];
			$out .= form_input($options);
			$out .= 	'<label>' . $this->EE->lang->line('form:month') . '</label>';
			$out .= '</div>';

			$out .= '<div class="df_date_elem">';
			$options['maxlength'] = '4';
			$options['name'] = $field['form_name'].'[year]';
			if (isset($data['year'])) $options['value'] = $data['year'];
			$out .= form_input($options);
			$out .= 	'<label>' . $this->EE->lang->line('form:year') . '</label>';
			$out .= '</div>';

			$out .= '<br clear="all">';
			$out .= '</div>';
		}

		// -----------------------------------------
		// Date Drop Down
		// -----------------------------------------
		if ($field['settings']['date_input_type'] == 'dateselect')
		{
			$out .= '<div class="dfinput_dates">';

			$out .= '<div class="df_date_elem">';
			$options['name'] = $field['form_name'].'[day]';
			$options['value'] = isset($data['day']) ? $data['day'] : '';
			$out .= form_dropdown($options['name'], array('',1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31), $options['value']);
			$out .= 	'<label>' . $this->EE->lang->line('form:day') . '</label>';
			$out .= '</div>';

			$out .= '<div class="df_date_elem_extra">';
			$options['name'] = $field['form_name'].'[month]';
			$options['value'] = isset($data['month']) ? $data['month'] : '';
			$items = array('' => '', '01' => $this->EE->lang->line('January'), '02' => $this->EE->lang->line('February'), '03' => $this->EE->lang->line('March'), '04' => $this->EE->lang->line('April'), '05' => $this->EE->lang->line('May_l'), '06' => $this->EE->lang->line('June'), '07' => $this->EE->lang->line('July'), '08' => $this->EE->lang->line('August'), '09' => $this->EE->lang->line('September'), '10' => $this->EE->lang->line('October'), '11' => $this->EE->lang->line('November'), '12' => $this->EE->lang->line('December'));
			$out .= form_dropdown($options['name'], $items, $options['value']);
			$out .= 	'<label>' . $this->EE->lang->line('form:month') . '</label>';
			$out .= '</div>';

			$out .= '<div class="df_date_elem">';
			$options['name'] = $field['form_name'].'[year]';
			$options['value'] = isset($data['year']) ? $data['year'] : '';
			$items = range((date('Y')+5), 1970);
			$items = array_reverse($items, true);
			$items[''] = ' ';
    		$items = array_reverse($items, true);
			$out .= form_dropdown($options['name'], $items, $options['value']);
			$out .= 	'<label>' . $this->EE->lang->line('form:year') . '</label>';
			$out .= '</div>';

			$out .= '<br clear="all">';
			$out .= '</div>';
		}


		return $out;
	}

	// ********************************************************************************* //

	public function validate($field=array(), $data)
	{
		if ($field['settings']['date_input_type'] == 'datefield')
		{
			// DO we need to check for required?
			if ($field['required'] != 1) return TRUE;

			// Prepare the error
			$error = array('type' => 'general', 'msg' => $this->EE->lang->line('form:error:required_field'));

			// Day
			if ($data['day'] == FALSE)
			{
				return $error;
			}

			// Month
			if ($data['month'] == FALSE)
			{
				return $error;
			}

			// Year
			if ($data['year'] == FALSE)
			{
				return $error;
			}
		}


		return TRUE;
	}

	// ********************************************************************************* //

	public function save($field=array(), $data)
	{
		return (is_array($data) == TRUE) ? serialize($data) : $data;
	}

	// ********************************************************************************* //

	public function output_data($field=array(), $data, $type='html')
	{
		if ($data == FALSE) return;

		// -----------------------------------------
		// Data
		// -----------------------------------------
		$data2 = @unserialize($data);
		if (is_array($data2) == TRUE)
		{
			$data = $data2['year'].'-'.$data2['month'].'-'.$data2['day'];
		}

		$date = strtotime($data);

		// -----------------------------------------
		// What Date Format?
		// -----------------------------------------
		$date_format="d/m/Y";
		if (isset($field['settings']['date_format']) == TRUE) $date_format = $field['settings']['date_format'];

		// Return!
		return date($date_format, $date);
	}

	// ********************************************************************************* //

	public function field_settings($settings=array(), $template=TRUE)
	{
		$vData = $settings;

		return $this->EE->load->view('settings', $vData, TRUE);
	}

	// ********************************************************************************* //

}

/* End of file date.php */
/* Location: ./system/expressionengine/third_party/forms/fields/date/date.php */