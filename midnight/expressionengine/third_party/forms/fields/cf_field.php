<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Channel Forms Field File
 *
 * @package			DevDemon_Forms
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2011 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com/forms/
 */
class CF_Field
{
	/**
	 * Render this field?
	 *
	 * @var bool
	 * @access protected
	 */
	protected $show_field = TRUE;

	/**
	 * Show Element Wrappers?
	 *
	 * @var bool
	 * @access protected
	 */
	protected $show_wrappers = TRUE;

	/**
	 * Show Field Labels?
	 *
	 * @var boolean
	 * @access protected
	 */
	protected $show_field_label = TRUE;

	/**
	 * Convert this field to a hidden input?
	 *
	 * @var bool
	 * @access protected
	 */
	protected $hidden_field = FALSE;

	/**
	 * Hidden input value
	 *
	 * @var string
	 * @access protected
	 */
	protected $hidden_field_value = '';


	/**
	 * Constructor
	 *
	 * @access public
	 */
	function __construct()
	{
		// Creat EE Instance
		$this->EE =& get_instance();

		$this->site_id = $this->EE->forms_helper->get_current_site_id();

		$this->field_path = PATH_THIRD . 'forms/fields/' . $this->info['name'] . '/';
	}

	// ********************************************************************************* //

	/**
	 * Render the field!
	 *
	 * @param array $field - The Field Array
	 * @param bool $template - Rendering on the template? or CP
	 * @param mixed $data - The field data array from _POST
	 */
	public function render_field($field=array(), $template=TRUE, $data)
	{
		return '';
	}

	// ********************************************************************************* //

	/**
	 * Display Field
	 * Creates the HTML wrapeprs and labels etc. Then calls render_field().
	 *
	 * @param array $field - The Field Array
	 * @param bool $template - Rendering on the template? or CP
	 */
	public function display_field($field=array(), $template=TRUE)
	{
		$this->field_path = PATH_THIRD . 'forms/fields/' . $this->info['name'] . '/';

		// Add package path (so view files can render properly)
		$this->EE->load->add_package_path($this->field_path, FALSE);

		//----------------------------------------
		// Label/Desc Placements
		//----------------------------------------
		$label_place = (isset($field['form_settings']['label_placement']) == TRUE) ? $field['form_settings']['label_placement'] : 'top';
		$desc_place = (isset($field['form_settings']['desc_placement']) == TRUE) ? $field['form_settings']['desc_placement'] : 'bottom';

		// Disable Label?
		if (isset($field['form_settings']['label_placement']) == TRUE && $field['form_settings']['label_placement'] == 'none')
		{
			$this->show_field_label = FALSE;
		}

		// Lets add field_name just in case (form input name="")
		if (isset($field['form_name']) == FALSE) $field['form_name'] = '';

		//----------------------------------------
		// Rendering in the PBF/MCP?
		//----------------------------------------
		if ($template == FALSE)
		{
			// Empty Form Name Always!
			$field['form_name'] = '';

			// Is it a pagebreak!
			if ($this->info['name'] == 'pagebreak')
			{
				return $this->render_field($field['settings'], $template);
			}
		}

		//----------------------------------------
		// Add CSS Classes
		//----------------------------------------
		$main_class = array($this->info['name']);
		if ($label_place == 'left_align')	$main_class[] = 'dfleft_label';
		if ($label_place == 'right_align')	$main_class[] = 'dfright_label';
		if ($label_place == 'top')		$main_class[] = 'dftop_label';
		if ($label_place == 'bottom')	$main_class[] = 'dfbottom_label';

		// We need a field_id
		if (isset($field['field_id']) == FALSE) $field['field_id'] = 0;

		// Required?
		$required_class = (isset($field['required']) == TRUE && $field['required'] == 1) ? 'dform_required' : '';
		$required_span = (isset($field['required']) == TRUE && $field['required'] == 1) ? ' <span class="req">*</span>' : '';

		//----------------------------------------
		// Render!
		//----------------------------------------
		$out = '';
		$out .= '<div class="dform_element dform_'.implode(' ', $main_class).' '.$required_class.'" id="forms_field_'.$field['field_id'].'">';

		if ($this->show_field_label == TRUE)
		{
			// Label Placement Top?
			if ($label_place != 'bottom') $out .= '<label class="dform_label">' . $field['title'] .$required_span. '</label>';
		}

		// Desc Placement Top?
		if ($desc_place == 'top') $out .= '<p class="dform_desc">' . $field['description'] . '</p>';



		// Are they any erros?
		if (isset($_POST['forms_errors'][$field['field_id']]['msg']) == TRUE)
		{
			$out .= '<div class="dform_error">' . $_POST['forms_errors'][ $field['field_id'] ]['msg'] . '</div>';
		}

		// We need to add formdata back..
		$data = array();
		if (isset($_POST['fields'][ $field['field_id'] ])) $data = $_POST['fields'][ $field['field_id'] ];

		// Render the field
		$out .=		'<div class="dform_container">' . $this->render_field($field, $template, $data) . '</div>';


		if ($this->show_field_label == TRUE)
		{
			// Do we need to add a break
			if ($label_place == 'left_align' || $label_place == 'right_align') $out .= '<br clear="all">';

			// Label Placement BOTTOM?
			if ($label_place == 'bottom') $out .= '<label class="dform_label">' . $field['title'] .$required_span. '</label>';
		}

		// Desc Placement BOTTOM?
		if ($desc_place == 'bottom') $out .= '<div class="dform_desc">' . $field['description'] . '</div>';

		$out .= '</div>';

		//----------------------------------------
		// Hidden field override!
		//----------------------------------------
		if ($this->hidden_field === TRUE && $template === TRUE)
		{
			$out = '<div class="hiddenFields">'.form_hidden($field['form_name'], $this->hidden_field_value).'</div>';
		}

		//----------------------------------------
		// Last chance to override show_field
		//----------------------------------------
		if ($this->show_field == FALSE)
		{
			return '';
		}

		return $out;
	}

	// ********************************************************************************* //

	/**
	 * Validate user input
	 *
	 * @param array $field - The Field Array
	 * @param mixed $data - The submitted data. Can be string or array
	 * @access public
	 * @return mixed - Return TRUE on success or array/string on failure
	 */
	public function validate($field=array(), $data)
	{
		return TRUE;
	}

	// ********************************************************************************* //

	/**
	 * Prepare to save the field
	 *
	 * @param array $field - The field array
	 * @param mixed $data - The field data to be saved
	 * @access public
	 * @return string - ALWAYS
	 */
	public function save($field=array(), $data)
	{
		return (string) $data;
	}

	// ********************************************************************************* //

	/**
	 * Prepare Output Data
	 *
	 * @param array $field - The field
	 * @param string $data - The raw data from the DB
	 * @param string $type - line/text/html
	 * @access public
	 * @return string - The data to be outputted
	 */
	public function output_data($field=array(), $data, $type='html')
	{
		return (string) $data;
	}

	// ********************************************************************************* //

	public function display_settings($field=array(), $template=TRUE)
	{
		// We need settings
		if (isset($field['settings']) == FALSE) $field['settings'] = array();

		// Final Output
		$out = '';

		// Only for old EE2 versions!
		if (version_compare(APP_VER, '2.1.5', '<'))
		{
			$this->EE->load->_ci_view_path = $this->field_path.'views/';

		}

		// Add package path (so view files can render properly)
		$this->EE->load->add_package_path($this->field_path, FALSE);

		// Do we need to load LANG file?
		if (@is_dir($this->field_path . 'language/') == TRUE)
		{
			$this->EE->lang->load($this->info['name'], $this->EE->lang->user_lang, FALSE, TRUE, $this->field_path);
		}

		// Add some global vars!
		$vars = array();
		$vars['form_name_settings'] = $field['form_name_settings'];

		$this->EE->load->vars($vars);

		// Execute the settings method
		$out = $this->field_settings($field['settings'], $template=TRUE);

		// Cleanup by removing
		$this->EE->load->remove_package_path($this->field_path);

		return $out;
	}

	// ********************************************************************************* //

	public function field_settings($settings=array(), $template=TRUE)
	{
		return '';
	}

	// ********************************************************************************* //

	public function save_settings($settings=array())
	{
		return $settings;
	}

	// ********************************************************************************* //

	public function delete_field($field)
	{

	}

	// ********************************************************************************* //


} // END CLASS

/* End of file cf_field.php  */
/* Location: ./system/expressionengine/third_party/channel_fields/fields/cf_field.php */