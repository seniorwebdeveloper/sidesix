<?php if (!defined('BASEPATH')) die('No direct script access allowed');

/**
 * Channel Forms TEXT INPUT field
 *
 * @package			DevDemon_Forms
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2011 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com/forms/
 * @see				http://expressionengine.com/user_guide/development/fieldtypes.html
 */
class CF_Field_file_upload extends CF_Field
{

	/**
	 * Field info - Required
	 *
	 * @access public
	 * @var array
	 */
	public $info = array(
		'title'		=>	'File Upload',
		'name' 		=>	'file_upload',
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

		// -----------------------------------------
		// If in publish field, lets disable it
		// -----------------------------------------
		if ($template == FALSE)
		{
			$options['disabled'] = 'disabled';
			$options['name'] = '';
		}
		else
		{
			//$options['name'] = 'forms_field_id_' . $field['field_id'];
		}

		// -----------------------------------------
		// Normal Input ? Or Password Field
		// -----------------------------------------

		$out = form_upload($options);
		$out.= form_hidden($options['name'], 'DUMMY');


		return $out;
	}

	// ********************************************************************************* //

	public function validate($field=array(), $data)
	{
		// Load Language File
		$this->EE->lang->load($this->info['name'], $this->EE->lang->user_lang, FALSE, TRUE, $this->field_path);

		// Did we upload?
		if (isset($_FILES['fields']['error'][ $field['field_id'] ]) == TRUE && $_FILES['fields']['error'][ $field['field_id'] ] == 0)
		{
			$filename = $_FILES['fields']['name'][ $field['field_id'] ];
			$extension = substr( strrchr($filename, '.'), 1);

			// Lets check filesize
			if (isset($field['settings']['filesize']) == TRUE && $field['settings']['filesize'] > 0)
			{
				$maxbytes = $field['settings']['filesize'] * 1024 * 1024;
				if ($_FILES['fields']['size'][ $field['field_id'] ] > $maxbytes)
				{
					return  array('type' => 'general', 'msg' => str_replace('{size}', $field['settings']['filesize'], $this->EE->lang->line('form:filesize_exceed')), 'field_id' => $field['field_id']);
				}

			}

			// Lets check extensions
			if (isset($field['settings']['extensions']) == TRUE && $field['settings']['extensions'] != FALSE)
			{
				$allowed_ext = explode(',', $field['settings']['extensions']);
				foreach ($allowed_ext as &$ext) $ext = trim($ext);

				if (in_array($extension, $allowed_ext) == FALSE)
				{
					return  array('type' => 'general', 'msg' => str_replace('{ext}', $field['settings']['extensions'], $this->EE->lang->line('form:ext_not_allowed')), 'field_id' => $field['field_id']);
				}

			}
		}
		else
		{
			// Required field?
			if ($field['required'] == 1)
			{
				return  array('type' => 'required', 'msg' => $this->EE->lang->line('form:upload_error'), 'field_id' => $field['field_id']);
			}
		}

		return TRUE;
	}

	// ********************************************************************************* //

	public function save($field=array(), $data)
	{
		// Did we upload?
		if (isset($_FILES['fields']['error'][ $field['field_id'] ]) == FALSE OR $_FILES['fields']['error'][ $field['field_id'] ] != 0)
		{
			return '';
		}

		$prefs = $this->EE->forms_helper->get_upload_preferences(NULL, $field['settings']['upload_destination']);
		$filename = $this->EE->localize->now.'_'.$_FILES['fields']['name'][ $field['field_id'] ];
		$path = $prefs['server_path'] . $filename;

		if (@move_uploaded_file($_FILES['fields']['tmp_name'][ $field['field_id'] ], $path) !== FALSE)
    	{
    		if (isset($this->EE->session->cache['Forms']['UploadedFiles']) == FALSE) $this->EE->session->cache['Forms']['UploadedFiles'] = array();

    		$this->EE->session->cache['Forms']['UploadedFiles'][] = $path;
    		return $filename;
    	}

    	return '';
	}

	// ********************************************************************************* //

	public function output_data($field=array(), $data, $type='html')
	{
		$out = '';

		if ($data == FALSE) return $out;

		$prefs = $this->EE->forms_helper->get_upload_preferences(NULL, $field['settings']['upload_destination']);

		$url = $prefs['url'].$data;

		if ($type == 'html') return "<a href='{$url}' target='_blank'>{$data}</a>";
		if ($type == 'text') return $url;
		if ($type == 'line') return $data;

		return $out;
	}

	// ********************************************************************************* //
	public function field_settings($settings=array(), $pbf=FALSE)
	{
		$vData = $settings;

		// Get all upload preferences
		$prefs = $this->EE->forms_helper->get_upload_preferences();

		foreach ($prefs as $pref)
		{
			$vData['prefs'][ $pref['id'] ] = $pref['name'];
		}

		return $this->EE->load->view('settings', $vData, TRUE);
	}

	// ********************************************************************************* //

}

/* End of file file_upload.php */
/* Location: ./system/expressionengine/third_party/forms/fields/file_upload/file_upload.php */