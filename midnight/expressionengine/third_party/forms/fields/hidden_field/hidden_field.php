<?php if (!defined('BASEPATH')) die('No direct script access allowed');

/**
 * Channel Forms HIDDEN field
 *
 * @package			DevDemon_Forms
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2011 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com/forms/
 * @see				http://expressionengine.com/user_guide/development/fieldtypes.html
 */
class CF_Field_hidden_field extends CF_Field
{

	/**
	 * Field info - Required
	 *
	 * @access public
	 * @var array
	 */
	public $info = array(
		'title'		=>	'Hidden Field',
		'name' 		=>	'hidden_field',
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
		$value = '';

		// -----------------------------------------
		// Default Value
		// -----------------------------------------
		if (isset($field['settings']['default_value']) == TRUE)
		{
			$value = $this->parse_default_value($field['settings']['default_value']);
		}

		// -----------------------------------------
		// Template Parsing?
		// -----------------------------------------
		if ($template === TRUE)
		{
			$this->hidden_field = TRUE;
			$this->hidden_field_value = $value;

			// Form data?
			if ($data != FALSE) $this->hidden_field_value = $data;

			return;
		}

		// -----------------------------------------
		// BackEnd! We should show a text input and blur it
		// -----------------------------------------
		$options = array();
		$options['name'] = '';
		$options['value'] = $value;
		$options['disabled'] = 'disabled';

		$out =	form_input($options);

		return $out;
	}

	// ********************************************************************************* //

	public function validate($field=array(), $data)
	{
		return TRUE;
	}

	// ********************************************************************************* //

	public function save($field=array(), $data)
	{
		return (string) $data;
	}

	// ********************************************************************************* //

	public function field_settings($settings=array(), $template=TRUE)
	{
		$vData = $settings;

		return $this->EE->load->view('settings', $vData, TRUE);
	}

	// ********************************************************************************* //

	private function parse_default_value($out)
	{
		//----------------------------------------
		// Parse available variables!
		//----------------------------------------
		$vars = array();
		$vars['{user:referrer}'] = (isset($_SERVER['HTTP_REFERER']) == TRUE) ? $_SERVER['HTTP_REFERER'] : '';
		$vars['{date:usa}'] = $this->EE->localize->decode_date('%m/%d/%Y', $this->EE->localize->now);
		$vars['{date:eu}'] = $this->EE->localize->decode_date('%d/%m/%Y', $this->EE->localize->now);
		$vars['{datetime:usa}'] = $this->EE->localize->decode_date('%m/%d/%Y %h:%i %A', $this->EE->localize->now);
		$vars['{datetime:eu}'] =  $this->EE->localize->decode_date('%d/%m/%Y %H:%i', $this->EE->localize->now);

		// Parse it!
		$out = str_replace(array_keys($vars), array_values($vars), $out);

		// Parse all user session data too
		foreach($this->EE->session->userdata as $var => $val)
		{
			// Val has arrays? Ignore them!
			if (is_array($val) == TRUE) continue;

			$out = str_replace('{user:'.$var.'}', $val, $out);
		}

		//----------------------------------------
		// Parse segment variables
		//----------------------------------------
		if (isset($this->EE->TMPL) == TRUE)
		{
			// Parse {last_segment} variable
			$seg_array = $this->EE->uri->segment_array();
			$out = str_replace('{last_segment}', end($seg_array), $out);

			// Parse URI segments
			// This code lets admins fetch URI segments which become
			// available as:  {segment_1} {segment_2}
			for ($i = 1; $i < 10; $i++)
			{
				$out = str_replace(LD.'segment_'.$i.RD, $this->EE->uri->segment($i), $out);
			}
		}

		return $out;
	}

	// ********************************************************************************* //

}

/* End of file hidden.php */
/* Location: ./system/expressionengine/third_party/forms/fields/hidden/hidden.php */