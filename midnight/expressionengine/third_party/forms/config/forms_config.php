<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if (isset($this->EE) == FALSE) $this->EE =& get_instance(); // For EE 2.2.0+

$config['cf_formfields_cats'] = array('form_tools' => array(), 'power_tools' => array(), 'list_tools' => array());
$config['cf_formfields']['form_tools']	= array('text_input', 'textarea', 'select', 'radio', 'checkbox');
$config['cf_formfields']['power_tools']	= array('captcha');
$config['cf_formfields']['list_tools']	= array('entries_list');

$config['cf_formsettings']['form_enabled']             = 'yes';
$config['cf_formsettings']['label_placement']          = 'top';
$config['cf_formsettings']['desc_placement']           = 'bottom';
$config['cf_formsettings']['open_fromto']['from']      = '';
$config['cf_formsettings']['open_fromto']['to']        = '';
$config['cf_formsettings']['member_groups']            = array();
$config['cf_formsettings']['multiple_entries']         = 'yes';
$config['cf_formsettings']['submit_button']['type']    = 'default';
$config['cf_formsettings']['submit_button']['text']    = 'Save';
$config['cf_formsettings']['submit_button']['text_next_page'] = 'Next Page';
$config['cf_formsettings']['submit_button']['img_url'] = '';
$config['cf_formsettings']['submit_button']['img_url_next_page'] = '';
$config['cf_formsettings']['limit_entries']['number']  ='';
$config['cf_formsettings']['limit_entries']['type']    ='';
$config['cf_formsettings']['return_url']               = '';
$config['cf_formsettings']['confirmation']['when']      = 'before_redirect';
$config['cf_formsettings']['confirmation']['text']      = '';
$config['cf_formsettings']['snaptcha']                 = 'no';

$config['cf_module_defaults']['recaptcha']['public']          = '';
$config['cf_module_defaults']['recaptcha']['private']         = '';
$config['cf_module_defaults']['mailchimp']['api_key']         = '';
$config['cf_module_defaults']['createsend']['api_key']        = '';
$config['cf_module_defaults']['createsend']['client_api_key'] = '';

$config['cf_validation_options'] = array();
$config['cf_validation_options']['none']     = $this->EE->lang->line('form:none');
$config['cf_validation_options']['alpha']    = $this->EE->lang->line('form:val:alpha');
$config['cf_validation_options']['alphanum'] = $this->EE->lang->line('form:val:alphanum');
$config['cf_validation_options']['numbers']  = $this->EE->lang->line('form:val:numbers');
$config['cf_validation_options']['float']    = $this->EE->lang->line('form:val:float');
$config['cf_validation_options']['email']    = $this->EE->lang->line('form:val:email');
$config['cf_validation_options']['url']      = $this->EE->lang->line('form:val:url');

$config['cf_dropdown_options']['yes_no']['no']               = $this->EE->lang->line('form:no');
$config['cf_dropdown_options']['yes_no']['yes']              = $this->EE->lang->line('form:yes');
$config['cf_dropdown_options']['email_types']['text']        = $this->EE->lang->line('form:tmpl:email:text');
$config['cf_dropdown_options']['email_types']['html']        = $this->EE->lang->line('form:tmpl:email:html');
$config['cf_dropdown_options']['template_types']['user']     = $this->EE->lang->line('form:tmpl:user');
$config['cf_dropdown_options']['template_types']['admin']    = $this->EE->lang->line('form:tmpl:admin');
$config['cf_dropdown_options']['limit_types']['total']       = $this->EE->lang->line('form:limit:total');
$config['cf_dropdown_options']['limit_types']['day']         = $this->EE->lang->line('form:limit:day');
$config['cf_dropdown_options']['limit_types']['week']        = $this->EE->lang->line('form:limit:week');
$config['cf_dropdown_options']['limit_types']['month']       = $this->EE->lang->line('form:limit:month');
$config['cf_dropdown_options']['limit_types']['year']        = $this->EE->lang->line('form:limit:year');
$config['cf_dropdown_options']['form_types']['entry_linked'] = $this->EE->lang->line('form:entry_linked');
$config['cf_dropdown_options']['form_types']['normal']       = $this->EE->lang->line('form:salone');