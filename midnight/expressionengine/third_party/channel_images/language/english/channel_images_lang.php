<?php if (!defined('BASEPATH')) die('No direct script access allowed');

$lang = array(

// Required for MODULES page
'channel_images'					=>	'Channel Images',
'channel_images_module_name'		=>	'Channel Images',
'channel_images_module_description'	=>	'Enables images to be associated with an entry.',

//----------------------------------------
'ci:home'			=>	'Home',
'ci:legacy_settings'=>	'Legacy Settings',
'ci:docs' 			=>	'Documentation',
'ci:yes'			=>	'Yes',
'ci:no'				=>	'No',
'ci:pref'		=>	'Preference',
'ci:value'		=>	'Value',
'ci:sizes'		=>	'Sizes',
'ci:images'		=>	'Images',

// MCP
'ci:location_path'	=>	'Server Location Path',
'ci:location_url'	=>	'Location URL',
'ci:no_legacy'		=>	'No Legacy Settings Found',
'ci:regenerate_sizes'=>	'Regenerate Sizes',
'ci:ci_fields'		=>	'Channel Images Fields',
'ci:grab_images'	=>	'Grab Images',
'ci:start_resize'	=>	'Start the regeneration process.',
'ci:import'			=>	'Import Images',
'ci:transfer_field'	=>	'Transfer To',
'ci:column_mapping'	=>	'Column Mapping',
'ci:dont_transfer'	=>	'Do Not Transfer',
'ci:import_entries'	=>	'Entries to Process',
//----------------------------------------
// FIELDTYPE
//----------------------------------------

// Actions
'ci:upload_actions'	=>	'Upload Actions',
'ci:click2edit'	=>	'Click to edit..',
'ci:hover2edit'	=>	'Hover to edit..',
'ci:wysiwyg'	=>	'WYSIWYG',
'ci:small_prev'	=>	'Small Preview',
'ci:big_prev'	=>	'Big Preview',
'ci:step'		=>	'Step',
'ci:action'		=>	'Action',
'ci:actions'	=>	'Actions',
'ci:add_action'	=>	'Add an Action',
'ci:settings'	=>	'Settings',
'ci:add_action_group'=>	'Add New Size',


'ci:loc_settings'	=>	'Upload Location Settings',
'ci:upload_location'=>	'Upload Location',
'ci:test_location'	=>	'Test Location',
'ci:specify_pref_cred' =>	'Specify Credential & Settings',
'ci:local'		=>	'Local Server',

// S3
'ci:s3'			=>	'Amazon S3',
'ci:s3:key'		=>	'AWS KEY',
'ci:s3:key_exp'	=>	'Amazon Web Services Key. Found in the AWS Security Credentials.',
'ci:s3:secret_key'	=>	'AWS SECRET KEY',
'ci:s3:secret_key_exp'	=>	'Amazon Web Services Secret Key. Found in the AWS Security Credentials.',
'ci:s3:bucket'		=>	'Bucket',
'ci:s3:bucket_exp'	=>	'Every object stored in Amazon S3 is contained in a bucket. Must be unique.',
'ci:s3:region'		=>	'Bucket Region',
'ci:s3:region:us-east-1' => 'USA-East (Northern Virginia)',
'ci:s3:region:us-west-1' => 'USA-West (Northern California)',
'ci:s3:region:eu'	 => 'Europe (Ireland)',
'ci:s3:region:ap-southeast-1' => 'Asia Pacific (Singapore)',
'ci:s3:region:ap-northeast-1' => 'Asia Pacific (Japan)',
'ci:s3:acl'		=>	'ACL',
'ci:s3:acl_exp'	=>	'ACL is a mechanism which decides who can access an object.',
'ci:s3:acl:public-read'	=>	'Public READ',
'ci:s3:acl:authenticated-read'		=>	'Public Authenticated Read',
'ci:s3:acl:private'		=>	'Owner-only read',
'ci:s3:storage'	=>	'Storage Redundancy',
'ci:s3:storage:standard'=>	'Standard storage redundancy',
'ci:s3:storage:reduced'	=>	'Reduced storage redundancy (cheaper)',
'ci:s3:directory'	=>	'Subdirectory (optional)',

// CloudFiles
'ci:cloudfiles'=>'Rackspace Cloud Files',
'ci:cloudfiles:username'	=>	'Username',
'ci:cloudfiles:api'			=>	'API Key',
'ci:cloudfiles:container'	=>	'Container',
'ci:cloudfiles:region'		=>	'Region',
'ci:cloudfiles:region:us'	=>	'United States',
'ci:cloudfiles:region:uk'	=>	'United Kingdom (London)',

'ci:fieldtype_settings'	=>	'Fieldtype Settings',
'ci:categories'	=>	'Categories',
'ci:categories_explain'=>	'Seperate each category with a comma.',
'ci:keep_original'	=>	'Keep Original Image',
'ci:keep_original_exp'	=>	'WARNING: If you do not upload the original image you will not be able to change the size of your existing images again.',
'ci:show_stored_images'	=>	'Show Stored Images',
'ci:limt_stored_images_author'	=>	'Limit Stored Images by Author?',
'ci:limt_stored_images_author_exp'	=>	'When using the Stored Images feature, all images uploaded by everyone will be searched. <br />Select YES to limit the searching to images uploaded by the current member.',
'ci:stored_images_search_type'	=>	'Stored Images Search Type',
'ci:entry_based' =>	'Entry Based',
'ci:image_based' =>	'Image Based',
'ci:allow_per_image_action'	=>	'Allow Per Image Action',
'ci:jeditable_event'=>	'Edit Field Event',
'ci:click'		=>	'Click',
'ci:hover'		=>	'Hover',
'ci:image_limit'	=>	'Image Limit',
'ci:image_limit_exp'=>	'Limit the amount of images a user can upload to this field. Leave empty to allow unlimited images.',
'ci:act_url'		=>	'ACT URL',
'ci:act_url:exp'	=>	'This URL is going to be used for all AJAX calls and image uploads',

// Field Columns
'ci:field_columns'		=>	'Field Columns',
'ci:field_columns_exp'	=>	'Specify a label for each column, leave the field blank to disable the column.',
'ci:row_num'		=>	'#',
'ci:id'				=>	'ID',
'ci:image'			=>	'Image',
'ci:title'			=>	'Title',
'ci:url_title'		=>	'URL Title',
'ci:desc'			=>	'Description',
'ci:category'		=>	'Category',
'ci:filename'		=>	'Filename',
'ci:actions:edit'	=>	'Edit',
'ci:actions:cover'	=>	'Cover',
'ci:actions:move'	=>	'Move',
'ci:actions:del'	=>	'Delete',
'ci:cifield_1'		=>	'Field 1',
'ci:cifield_2'		=>	'Field 2',
'ci:cifield_3'		=>	'Field 3',
'ci:cifield_4'		=>	'Field 4',
'ci:cifield_5'		=>	'Field 5',

// PBF
'ci:upload_images'	=>	'Upload Images',
'ci:stored_images'	=>	'Stored Images',
'ci:time_remaining'	=>	'Time Remaining',
'ci:stop_upload'	=>	'Stop Upload',
'ci:dupe_field'		=>	'Only one Channel Images field can be used at once.',
'ci:missing_settings'=>	'Missing Channel Images settings for this field.',
'ci:no_images'		=>	'No images have yet been uploaded.',
'ci:site_is_offline'=>	'Site is OFFLINE! Uploading images will/might not work.',
'ci:image_remain'	=>	'Images Remaining:',
'ci:img_limit_reached'=>'ERROR: Image Limit Reached',
'ci:submitwait'		=>	'You have uploaded image(s), those images are now being send to their final destination. This can take a while depending on the amount of images..',

// Stored Images
'ci:last'			=>	'Last',
'ci:entries'		=>	'Entries',
'ci:filter_keywords'	=>	'Keywords',
'ci:entry_images'	=>	'Entry Images',
'ci:loading_images'	=>	'Loading Images...',
'ci:loading_entries'=>	'Loading Entries..',
'ci:no_entry_sel'	=>	'No entry has been selected.',
'ci:no_images'		=>	'No Images found..',

// Action Per Image
'ci:apply_action'	=>	'Apply Action',
'ci:apply_action_exp'=>	'Select an action to execute on the selected image size.',
'ci:select_action'	=>	'Select an Action',
'ci:applying_action'=>	'Applying your selected action, please wait...',
'ci:preview'		=>	'Preview',
'ci:save'			=>	'Save',
'ci:original'		=>	'ORIGINAL',

// Pagination
'ci:pag_first_link' => '&lsaquo; First',
'ci:pag_last_link' => 'Last &rsaquo;',

'ci:required_field'	=>	'REQUIRED FIELD: Please add at least one image.',

// Errors
'ci:file_arr_empty'	=> 'No file was uploaded or file is not allowed by EE.(See EE Mime-type settings).',
'ci:tempkey_missing'	=> 'The temp key was not found',
'ci:file_upload_error'	=> 'No file was uploaded. (Maybe filesize was too big)',
'ci:no_settings'		=> 'No settings exist for this fieldtype',
'ci:location_settings_failure'	=>	'Upload Location Settings Missing',
'ci:location_load_failure'	=>	'Failure to load Upload Location Class',
'ci:tempdir_error'		=>	'The Local Temp dir is either not writable or does not exist',

'ci:temp_dir_failure'		=>	'Failed to create the temp dir, through Upload Location Class',
'ci:file_upload_error'		=>	'Failed to upload the image, through Upload Location Class',



'ci:no_upload_location_found' => 'Upload Location has not been found!.',
'ci:file_to_big'		=> 'The file is too big. (See module settings for max file size).',
'ci:extension_not_allow'=> 'The file extension is not allowed. (See module settings for file extensions)',
'ci:targetdir_error'	=> 'The target directory is either not writable or does not exist',
'ci:file_move_error'	=> 'Failed to move uploaded file to the temp directory, please check upload path permissions etc.',


// END
''=>''
);

/* End of file channel_images_lang.php */
/* Location: ./system/expressionengine/third_party/channel_images/language/english/channel_images_lang.php */