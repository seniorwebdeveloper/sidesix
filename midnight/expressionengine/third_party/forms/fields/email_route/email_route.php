<?php if (!defined('BASEPATH')) die('No direct script access allowed');

/**
 * Channel Forms EMAIL ROUTE field
 *
 * @package			DevDemon_Forms
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2011 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com/forms/
 * @see				http://expressionengine.com/user_guide/development/fieldtypes.html
 */
class CF_Field_email_route extends CF_Field
{

	/**
	 * Field info - Required
	 *
	 * @access public
	 * @var array
	 */
	public $info = array(
		'title'		=>	'Email Route',
		'name' 		=>	'email_route',
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
		$options = array();
		$options['name'] = $field['form_name'];
		$options['value'] = '';

		// -----------------------------------------
		// If in publish field, lets disable it
		// -----------------------------------------
		if ($template == FALSE)
		{
			$options['readonly'] = 'readonly';
			$options['name'] = '';
		}

		// -----------------------------------------
		// Items
		// -----------------------------------------
		$items = array();

		// Default values
		if (isset($field['settings']['emails']['labels']) == FALSE OR is_array($field['settings']['emails']['labels']) == FALSE)
		{
			$field['settings']['emails']['labels'] = array('Sales', 'Support', 'Info');
			$field['settings']['emails']['values'] = array('sales@domain.com', 'support@domain.com', 'info@domain.com');
			$field['settings']['emails']['default'] = 'sales@domain.com';
		}

		// Create the array
		foreach ($field['settings']['emails']['labels'] as $key => $val)
		{
			$items[ $key.sha1($field['settings']['emails']['values'][$key]) ] = $val;
		}

		// The default selection
		$options['value'] = (isset($field['settings']['emails']['default'])) ? $field['settings']['emails']['default'] : '';

		// Form data?
		if ($data != FALSE) $options['value'] = $data;

		// -----------------------------------------
		// Render
		// -----------------------------------------
		$out =	form_dropdown($options['name'], $items, $options['value']);

		return $out;
	}

	// ********************************************************************************* //

	public function save($field=array(), $data)
	{
		foreach($field['settings']['emails']['values'] as $key => $val)
		{
			$hash = $key.sha1($val);

			if ($data == $hash)
			{
				$this->EE->session->cache['Forms']['EmailAdminOverride'] = $val;
			}
		}

		return '';
	}

	// ********************************************************************************* //

	public function field_settings($settings=array(), $template=TRUE)
	{
		$vData = $settings;

		if (isset($vData['emails']['labels']) == FALSE OR is_array($vData['emails']['labels']) == FALSE)
		{
			$vData['emails']['labels'] = array('Sales', 'Support', 'Info');
			$vData['emails']['values'] = array('sales@domain.com', 'support@domain.com', 'info@domain.com');
			$vData['emails']['default'] = 'sales@domain.com';
		}

		return $this->EE->load->view('settings', $vData, TRUE);
	}

	// ********************************************************************************* //

	public function save_settings($settings=array())
	{
		foreach($settings['emails']['labels'] as $key => &$val)
		{
			$val = trim($val);
			if ($val == FALSE) unset($settings['emails']['labels'][$key]);
		}

		foreach($settings['emails']['values'] as $key => &$val)
		{
			$val = trim($val);
			if ($val == FALSE)
			{
				if (isset($settings['emails']['labels'][$key]) == FALSE) unset($settings['emails']['values'][$key]);
				else $val = $settings['emails']['labels'][$key];
			}
		}

		return $settings;
	}

	// ********************************************************************************* //


}

/* End of file email.php */
/* Location: ./system/expressionengine/third_party/forms/fields/email/email.php */