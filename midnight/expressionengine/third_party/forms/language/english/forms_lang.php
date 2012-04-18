<?php if (!defined('BASEPATH')) die('No direct script access allowed');

$lang = array(

// Required for MODULES page
'forms'			=>	'Forms',
'forms_module_name'=>	'Forms',
'forms_module_description'	=>	'Create Forms',

//----------------------------------------
// General
//----------------------------------------
'form'            => 	'Form',
'form:home'       =>	'Channel Forms Home',
'form:docs'       =>	'Documentation',
'form:yes'        =>	'Yes',
'form:no'         =>	'No',
'form:none'       =>	'None',
'form:save'       =>	'Save',
'form:unknown'    =>	'Unknown',
'form:actions'    =>	'Actions',
'form:loading_dt' =>	'Loading data, please wait..',
'form:desc'       =>	'Description',
'form:settings'   =>	'Settings',
'form:delete'     =>	'Delete',
'form:from'       =>	'From',
'form:to'         =>	'To',


//----------------------------------------
// Fieldtype Settings
//----------------------------------------
'form:enabled'              =>	'Enabled',
'form:field_name'           =>	'Field Name',

'form:show_settings'        =>	'Default Settings',
'form:hide_settings'        =>	'Hide Settings',
'form:default_settings'     =>	'Default Settings',
'form:default_settings_exp' =>	'Any option choosen here will be applied to all future Forms. (Some options can be overriden on a per Form basis)',

//----------------------------------------
// PBF Specific
//----------------------------------------
'form:missing_settings' =>	'No Field Settings Found!',


//----------------------------------------
// FORM BUILDER
//----------------------------------------
'form:builder'		=>	'Form Builder',
'form:alerts'		=>	'Submission Alerts',
'form:adv_settings'	=>	'Advanced Settings',

// Form Staging
'form:first_drop_exp'   =>	'Drag & Drop form elements here',
'form:save_settings'    =>	'Save Settings',
'form:saving_settings'  =>	'Saving, please wait...',
'form:field_settings'   =>	'Field Settings',
'form:other_settings'   =>	'Other Settings',
'form:field_label'      =>	'Field Label',
'form:field_short_name' =>	'Field Short Name',
'form:field_desc'       =>	'Field Description',
'form:rules'            =>	'Rules',
'form:required'         =>	'Required',
'form:no_duplicates'    =>	'No Duplicates',
'form:placeholder'      =>	'Placeholder Text',
'form:enhanced_ui'      =>	'Enable Enhanced UI',

'form:id_css'           =>	'ID (CSS)',
'form:class_css'        =>	'Class (CSS)',
'form:style_css'        =>	'Style (CSS)',
'form:extra_attr'       =>	'Inline Attributes',
'form:validation_opt'   =>	'Validation',

// Form Builder Templates
'form:tmpl:none'		=>	'None',
'form:tmpl:predefined'	=>	'Predefined Template',
'form:tmpl:custom'		=>	'Custom',
'form:tmpl_predefined'	=>	'Prefined Templates',

// Form Builder Settings
'form:general'			=>	'General',
'form:restrictions'		=>	'Restrictions',
'form:label_placement'	=>	'Label Placement',
'form:desc_placement'	=>	'Description Placement',
'form:return_url'		=>	'Submission Return URL',
'form:place:top'		=>	'Above Inputs',
'form:place:left_align'	=>	'Left Aligned',
'form:place:right_align'=>	'Right Aligned',
'form:place:bottom'		=>	'Below Inputs',
'form:place:none'		=>	'Don\'t Show',
'form:limit_entries'	=>	'Limit number of entries',
'form:limit:total'		=>	'Total Entries',
'form:limit:day'		=>	'per day',
'form:limit:week'		=>	'per week',
'form:limit:month'		=>	'per month',
'form:limit:year'		=>	'per year',
'form:submit_button'	=>	'Submit Button',
'form:button:default'	=>	'Default',
'form:button:image'		=>	'Image',
'form:button:btext'		=>	'Button Text',
'form:button:btext_next'=>	'Next Page',
'form:button:bimg'		=>	'Image URL',
'form:button:bimg_next'	=>	'Next Page',
'form:form_enabled'		=>	'Form Enabled',
'form:open_fromto'		=>	'Open From-To',
'form:allow_mgroups'	=>	'Allowed Member Groups',
'form:multiple_entries'	=>	'Allow multiple submissions',

'form:post_submission'         =>	'Post Submission',
'form:success_msg_when'        =>	'Confirm Message Behaviour',
'form:success_msg'             =>	'Confirmation Message',
'form:success:before_redirect' =>	'Before Redirecting',
'form:success:after_redirect'  =>	'On the confirmation page',
'form:success:show_only'       =>	'Only Show Message (no redirect)',
'form:success:disabled'        =>	'Just Redirect (no confirmation)',

'form:security'	=>	'Security',
'form:snaptcha'	=>	'Enable Snaptcha Support',

// Choices
'form:choices'    =>	'Choices',
'form:label'      =>	'Label',
'form:value'      =>	'Value',
'form:enable_values'=>	'Enable Values',
'form:bulkadd'		=>	'Bulk Add / Predefined Choices',
'form:bulkchoice:exp'=>	'Select from one of the predefined lists and customize the choices or paste your own list to bulk add choices.',
'form:insert_choices'=>	'Insert Choices',

//----------------------------------------
// Submission Errors
//----------------------------------------
'form:error:missing_data'		=>	'Missing Data.',
'form:error:not_authorized'		=>	'You are not authorized to perform this action',
'form:error:captcha_required'	=>	'You must submit the word that appears in the image',
'form:error:captcha_incorrect'	=>	'You did not submit the word exactly as it appears in the image',
'form:error:required_field'		=>	'You must specify a value for this required field.',

//----------------------------------------
// MCP Speficic
//----------------------------------------
'form:mcp'		=>	'Forms Control Panel',
'form:home'		=>	'Dashboard',
'form:submissions'	=>	'Submissions',
'form:templates'	=>	'Email Templates',
'form:filter:form'	=>	'Filter by Form',
'form:filter:date'	=>	'Filter by Date',
'form:filter:keywords'	=>	'Filter by Keywords',
'form:filter:country'	=>	'Filter by Country',
'form:filter:members'	=>	'Filter by Members',
'form:date_from'	=>	'Date To',
'form:date_to'		=>	'Date From',
'form:keywords'		=>	'Keywords',
'form:id'			=>	'ID',
'form:fentry_id'	=>	'Submission ID',
'form:date'			=>	'Date',
'form:date_created'	=>	'Date Created',
'form:last_entry'	=>	'Last Submission',
'form:ip'			=>	'IP Address',
'form:member'		=>	'Member',
'form:country'		=>	'Country',
'form:guest'		=>	'Guest',
'form:submissions'	=>	'Submissions',
'form:type'			=>	'Type',
'form:export'		=>	'Export To ',

'form:view_submissions'	=>	'View Form Submissions',
'form:edit_form'	=>	'Edit Form',
'form:delete_form'	=>	'Delete Form',

// Export
'form:export:fields'		=>	'Form Fields',
'form:export:current_fields'=>	'Current visible fields',
'form:export:all_fields'	=>	'All available fields',
'form:export:entries'		=>	'Submissions',
'form:export:current_entries'	=>	'Current visible submissions',
'form:export:all_entries'	=>	'All submissions',
'form:export:delimiter'		=>	'Delimiter',
'form:export:comma'			=>	'Commas (,)',
'form:export:tabs'			=>	"Tabs (\t)",
'form:export:scolons'		=>	'Semi-colons (;)',
'form:export:pipes'			=>	'Pipes (|) ',
'form:export:enclosure'		=>	'Enclosure',
'form:export:none'			=>	'None',
'form:export:quote'			=>	'Single Quotes (\')',
'form:export:dblquote'		=>	'Double Quotes (")',
'form:export:include_header'=>	'Include Headers',
'form:export:member_info'	=>	'Member Field Info',
'form:export:screen_name'	=>	'Screenname',
'form:export:username'		=>	'Username',
'form:export:email'			=>	'Email Address',
'form:export:member_id'		=>	'Member ID',
'form:export:loading'		=>	'Preparing export, please wait...',

// New Form
'form:form_new'		=>	'Create New Form',
'form:form_name'	=>	'Form Label',
'form:form_url_title'=>	'Form Short Name',
'form:gen_info'		=>	'General Information',
'form:entry_linked'	=>	'Entry Linked',
'form:salone'		=>	'Stand Alone Forms',

// Lists
'form:lists'		=>	'Lists',
'form:list_label'	=>	'List Name',
'form:list_new'		=>	'New List',
'form:list_gen_info'=>	'General List Info',
'form:list_bulk'	=>	'Bulk Add/Edit/Remove',
'form:list:items'	=>	'List Items',
'form:option_setting_ex'	=>	'
Example 1: Option Label <br />
Example 2: option_value : Option Label
',

// Settings
'form:recaptcha_settings'	=>	'reCAPTCHA Settings',
'form:recaptcha_public'		=>	'reCAPTCHA Public Key',
'form:recaptcha_private'		=>	'reCAPTCHA Private Key',
'form:mailchimp_settings'	=>	'MailChimp Settings',
'form:createsend_settings'	=>	'Campaign Monitor Settings',
'form:api_key'		=>	'API Key',
'form:client_api_key'	=>	'Client API Key',

//----------------------------------------
// Email Templates
//----------------------------------------
'form:tmpl_label'	=>	'Template Label',
'form:tmpl_name'	=>	'Template Name',
'form:tmpl_new'		=>	'New Email Template',
'form:tmpl_gen_info'		=>	'General Template Info',
'form:tmpl_email_info'		=>	'Email Template Info',
'form:tmpl:email:type'		=>	'Email Type',
'form:tmpl:email:wordwrap'	=>	'Wordwrap',
'form:tmpl:email:to'		=>	'To (Email Address)',
'form:tmpl:email:from'		=>	'From (Name)',
'form:tmpl:email:from_email'=>	'From (Email Address)',
'form:tmpl:email:reply_to'	=>	'Reply To (Name)',
'form:tmpl:email:reply_to_email'	=>	'Reply To (Email Address)',
'form:tmpl:email:reply_to_author'	=>	'Fill in "Reply To" with submission author info?',
'form:tmpl:email:subject'	=>	'Subject',
'form:tmpl:email:cc'		=>	'CC',
'form:tmpl:email:bcc'		=>	'BCC',
'form:tmpl:email:template'	=>	'Template',
'form:tmpl:email:send_attach'=>	'Send Attachments',
'form:tmpl:email:text'		=>	'Text ',
'form:tmpl:email:html'		=>	'HTML ',
'form:tmpl:user'	=>	'User Notification',
'form:tmpl:admin'	=>	'Admin Notification',


//----------------------------------------
// Alert
//----------------------------------------
'form:alert:delete_form'	=>	"Are you sure you want to Delete this Form?\n\nAll Fields & Form Submissions associated with this Form will also be deleted!",

//----------------------------------------
// HELP
//----------------------------------------
'form:help:form_tools'		=>	'Standard Fields provide basic form functionality.',
'form:help:power_tools'		=>	'Advanced Fields are for specific uses. They enable advanced functionality not normally found in other fields.',
'form:help:list_tools'		=>	'List Fields provide predefined dropdowns.',
'form:help:field_label'		=>	'Enter the label of the form field. This is the field title the user will see when filling out the form.',
'form:help:field_short_name'=>	'Enter the short name of the form field. This is never displayed to the user, but used in Email Templates.',
'form:help:field_desc'		=>	'Enter the description for the form field. This will be displayed to the user and provide some direction on how the field should be filled out or selected.',
'form:help:required_field'	=>	'Select this option to make the form field required. A required field will prevent the form from being submitted if it is not filled out or selected.',
'form:help:no_dupes'		=>	'Select this option to limit user input to unique values only. This will require that a value entered in a field does not currently exist in the entry database for that field.',
'form:help:visibility'		=>	'Select the visibility for this field.<br>Field visibility set to Everyone will be visible by the user submitting the form. Form field visibility set to Admin Only will only be visible within the administration tool.<br><br>Setting a field to Admin Only is useful for creating fields that can be used to set a status or priority level on submitted entries.',
'form:help:enhanced_ui'		=>	'By selecting this option, the <a href="http://harvesthq.github.com/chosen/" target="_blank">Chosen</a> jQuery script will be applied to this field, enabling search capabilities to Drop Down fields and a more user-friendly interface for Multi Select fields.',
'form:help:thousands_sep'	=>	'Specify which character to use to separate groups of thousands. For example, a value of , would parse 10000 as 10,000. Default is , (comma).',
'form:help:dec_point'		=>	'Specify which character to use to separate decimals. For example, a value of . would parse 100,00 as 100.00 Default is . (dot).',
'form:help:decimals'		=>	'Sets the number of decimal points. For example, a value of 2 would parse 100.124 as 100.12 Default is 2.',
'form:help:enforce'			=>	'Force the user to enter a correct format. If left unchecked we will accept any format and try to parse it.',
'form:help:enable_other_choice'=>'Check this option to add a text input as the final choice of your radio button field. This allows the user to specify a value that is not a predefined choice.',
'form:help:label_placement'	=>	'Select the label placement. Labels can be top aligned above a field, left aligned to the left of a field,right aligned to the left of a field, or at the bottom of the fields.',
'form:help:desc_placement'	=>	'Select the description placement. Descriptions can be placed above the field inputs or below the field inputs.',
'form:help:return_url'		=>	'Return url will override your default return url',
'form:help:success_msg'		=>	'Enter the text you would like the user to see on the confirmation page of this form.',
'form:help:choices'		=>	'Add Choices to this field. You can mark each choice as checked by default by using the radio/checkbox fields on the left.',
'form:help:enable_values'=>	'Check this option to specify a value for each choice. Choice values are not displayed to the user viewing the form, but are accessible to administrators when viewing the entry.',
'form:help:snaptcha'	=>	'<a href="http://devot-ee.com/add-ons/snaptcha">Snaptcha</a> (by: PutYourLightsOn - Ben Croker) <p style="font-style:italic; font-size:11px; margin:6px 0;">QUOTE:<br>Snaptcha (Simple Non-obtrusive Automated Public Turing test to tell Computers and Humans Apart) is an invisible captcha and will be the last time you will ever have to think about protecting your forms from spam bots.</p> Buy it at <a href="http://devot-ee.com/add-ons/snaptcha">Devot-EE</a>',

'form:help:default_val'		=>	'If you would like to pre-populate the value of a field, enter it here.
<br><br>
You can also use these variables:
<ul>
<li>{user:*} - Any user session variable is available here: See <a href="http://expressionengine.com/user_guide/development/usage/session.html">EE Dev Docs</a> for the complete list</li>
<li>{user:referrer} - The User\'s HTTP URL Referrer</li>
<li>{date:usa} - Current Date mm/dd/yyyy</li>
<li>{date:eu} - Current Date dd/mm/yyyy</li>
<li>{datetime:usa} - Current Date mm/dd/yyyy hh:mm am/pm</li>
<li>{datetime:eu} - Current Date dd/mm/yyyy HH:mm</li>
<li>{segment_*} - URL Segments</li>
<li>{last_segment} - Last URL Segments</li>
</ul>
',

'form:email_template_exp' =>'
<pre>
Variables
{form:label} - The Form Label
{form:short_name} - The Form short name
{form:id} - The Form ID
{user:*} - Any user session variable is available here: See <a href="http://expressionengine.com/user_guide/development/usage/session.html">EE Dev Docs</a> for the complete list
{user:referrer} - The User\'s HTTP URL Referrer
{date:usa} - Current Date mm/dd/yyyy
{date:eu} - Current Date dd/mm/yyyy
{datetime:usa} - Current Date mm/dd/yyyy hh:mm am/pm
{datetime:eu} - Current Date dd/mm/yyyy HH:mm

Form Fields Variables

{field:FIELD_NAME} - Replace FIELD_NAME with a fields short name
{form:fields} {/form:fields} - This variable pair loops over all fields in your form

Variables Available in side the pair
	
	{field:label} - The field label
	{field:short_name} - The field short name
	{field:value} - The submitted data for this field
	{field:count} - Sequence number
	

</pre>
',

















// Form Validations
'form:val:alpha'	=>	'Alphabetic Characters',
'form:val:alphanum'	=>	'Alphabetic & Numeric Characters',
'form:val:numbers'	=>	'Whole Numbers',
'form:val:float'	=>	'Number (decimals accepted)',
'form:val:email'	=>	'Email Address',
'form:val:url'		=>	'URL',



// Form Settings
'form:entry_submission'	=>	'Save Submissions As Entries',

//----------------------------------------
// Tools
//----------------------------------------
'form:form_tools'	=>	'Form Tools',
'form:power_tools'	=>	'Power Tools',
'form:list_tools'	=>	'List Tools',


// END
''=>''
);

/* End of file forms_lang.php */
/* Location: ./system/expressionengine/third_party/forms/forms_lang.php */