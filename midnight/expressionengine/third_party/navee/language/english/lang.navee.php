<?php
$lang                                = array(

//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
//	G E N E R A L
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
"navee_module_name"        => "NavEE",
"navee_module_description" => "Simple Navigation, Made Simple.",
"cp_step_1"                => "Step 1",
"cp_step_2"                => "Step 2",
"cp_step_3"                => "Step 3",
"cp_delete"                => "Delete",
"cp_edit"                  => "Edit",
"cp_save"                  => "Save",
"cp_code"                  => "Template Code",
"cp_navigation_group"      => "Navigation Group",
"cp_true"                  => "True",
"cp_false"                 => "False",

//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
//	I N D E X
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
"cp_header"       => "Welcome to NavEE",
"cp_welcome"      => "You must be new here&hellip;",
"cp_first_nav"    => "Don't worry! NavEE is really simple and kind of fun to use.",
"cp_start"        => "Let's Get Started",
"cp_click_button" => "To create your first nav group:",
"cp_click_me"     => "Click Here",
"cp_add_more"     => "Add More",
"cp_pre_beta"     => "NavEE Version 2.0",
"cp_beta_text"    => "Please report any bugs you might encounter by clicking the Get Help button at:",

//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
//	A D D   N A V I G A T I O N
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
"cp_an_add_nav_group"                => "Create Navigation Group",

//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
//	C O N F I G
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
"cp_conf"                            => "NavEE Configuration",
"cp_conf_install_directory"          => "Install Directory",
"cp_conf_install_dir_inst"           => "enter: /installed/here",
"cp_conf_update"                     => "Update Configurations",
"cp_conf_configure"                  => "Configure NavEE",
"cp_conf_sub_instr"                  => "If you have installed ExpressionEngine in a subdirectory of your site, enter the subdirectory below. For example, if EE is installed here: http://booyant.com/installed/here",
"cp_conf_include_index"              => "Include index.php",
"cp_conf_include_index_inst"         => "For Guided & Pages Nodes",
"cp_conf_stylesheet"                 => "Select Your NavEE Style",
"cp_conf_stylesheet_classic"         => "Classic",
"cp_conf_stylesheet_corporate"       => "Corporate",
"cp_conf_stylesheet_kor"             => "CP Theme",
"cp_conf_clear_cache"                => "Clear NavEE Cache",
"cp_conf_clear_on_update"            => "Clear on submit",
"cp_conf_general"                    => "General",
"cp_conf_member_access"              => "Member Group Access",
"cp_conf_hide_templates"             => "Hide Checked Templates",
"cp_conf_hide_templates_inst"        => "You can limit which templates are available in the Guided Method of nav creation. Simply check any that you don't want to appear as options.",
"cp_conf_no_member_groups"           => "In order to use the Member Group Access functionality, you must have at least one Member Group with access to the Control Panel other than your Super Admin Group.",
"cp_conf_member_access_inst"         => "You can restrict access to any Member Group by simply checking the box beside the group name. Only Member Groups with Control Panel access are listed.",
"cp_conf_entify_ee_tags"             => "Entify EE Tags",
"cp_conf_added_security"             => "Added security",
"cp_conf_remove_deleted_entries"     => "Remove Guided/Pages Nodes of Deleted Entries",
"cp_conf_remove_deleted_inst"        => "Also removes child nodes of deleted items.",
"cp_only_superadmins_can_admin_navs" => "Only Superadmins can add/delete nav groups",

//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
//	M A N A G E   N A V I G A T I O N
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
"cp_mn_manage"             => "Manage Navigation Group:",
"cp_mn_ee_code"            => "To start your navigation here, use this in your template.",
"cp_mn_optional"           => "More Options",
"cp_mn_optional_fewer"     => "Fewer Options",
"cp_mn_add_to_navigation"  => "Add to Navigation",
"cp_mn_update"             => "Update",
"cp_mn_submit_add"         => "Add",
"cp_mn_add_nav_group"      => "Add Navigation Group",
"cp_mn_optional_items"     => "Optional Items",
"cp_mn_select_pages"       => "Select Pages",
"cp_mn_enter_link"         => "Enter Link",
"cp_mn_add_item"           => "Add Item",
"cp_mn_add_item_desc"      => "Add a new item to the currently selected Nav Group",
"cp_mn_edit_item"          => "Edit Item",
"cp_mn_edit_item_desc"     => "Edit an existing nav item",
"cp_mn_new"                => "New Nav Group",
"cp_mn_change_tree"        => "Change Nav Group",
"cp_mn_get_code"           => "{code}",
"cp_mn_hide_code"          => "{hide}",
"cp_mn_manage_trees"       => "Manage Nav Groups",
"cp_mn_new_tree"           => "New Nav Group",
"cp_mn_edit_channel_entry" => "Edit Entry &rarr;",

//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
//	H E L P E R S
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
"cp_hlp_nav_1"           => "<p>Let's start by adding your first Navigation Group.</p> <p>NavEE let's you manage multiple navigation groups for things like: Main Navigation, Secondary Navigation, etc.</p>",
"cp_hlp_nav_2"           => "Back for more, eh? Good for you.",
"cp_hlp_navItem_1"       => "<p>Your Navigation group was successfully created.</p><p>Now,
add your first <strong>navigation item</strong> by clicking that big button over there.</p>",
"cp_hlp_navItem_2"       => "<p>That wasn't so bad, was it?</p><p>Now you can build the rest of your navigation in much the same way. You can make sub-navigation items by setting their <strong>parent</strong> when you create them.</p>",
//New Tree
"new_tree_h2"            => "New Nav Group",
"new_tree_fi_h3"         => "Full Instructions",
"new_tree_fi_p"          => "Refer to http://booyant.com/navee/getting-started for full documentation.",
"new_tree_qs_h3"         => "Quick Start",
"new_tree_qs_step1"      => "Use Add Item form to the right to add an item to the navigation group.",
"new_tree_qs_step2"      => "Link Text is the text that will be displayed on your webpage",
"new_tree_qs_step3"      => "Use Assign Link To Parent to nest navigation items inside of one another",
"new_tree_qs_step4"      => "Use the Create Link section to choose a method for creating the link and set the appropriate parameters for that method.",
"cp_all_fields_required" => "Please complete all fields below",
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
//	F O R M   C O N T E N T
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>	

"cp_form_text"                 => "Link Text",
"cp_form_type"                 => "Create Link",
"cp_form_link"                 => "Link",
"cp_form_pages"                => "Pages",
"cp_form_parent"               => "Assign Link To Parent",
"cp_form_select_parent"        => "Select Parent",
"cp_form_include_in"           => "Include In Navigation",
"cp_form_id"                   => "ID",
"cp_form_class"                => "Class",
"cp_form_rel"                  => "Rel",
"cp_form_name"                 => "Name",
"cp_form_title"                => "Title",
"cp_form_target"               => "Target",
"cp_form_regex"                => "Regex pattern for advanced 'selected' states",
"cp_form_top_level"            => "Top Level",
"cp_form_member_groups"        => "Member Groups",
"cp_form_member_groups_desc"   => "Limit who can access this NavEE item by selecting groups below.",
"cp_form_passive"              => "Passive Node (No selected class)",
"cp_pill_manual"               => "Manual",
"cp_pill_guided"               => "Guided",
"cp_pill_pages"                => "Pages",
"cp_form_link_manual"          => "Enter Link Manually",
"cp_form_link_guided"          => "Select a Template and Entry",
"cp_form_link_pages"           => "Select From Pages Module",
"cp_form_link_guided_template" => "Select a Template",
"cp_form_link_guided_channel"  => "Select a Channel",
"cp_form_link_guided_entry"    => "Select an Entry",
"cp_form_custom"               => "Include additional html in &lt;li&gt;",
"cp_form_custom_kids"          => "Include additional html in &lt;ul&gt; of children",
"cp_form_hdr_attributes"       => "Attributes",
"cp_form_hdr_custom"           => "Custom Attributes",
"cp_form_hdr_attributes"       => "Attributes",
"cp_form_hdr_disable"          => "Output",
"cp_form_hdr_regex"            => "Regular Expression",
"cp_form_hdr_members"          => "Member Groups",
"cp_form_select_channel"       => "Start by selecting a channel",
"cp_form_access_key"           => "Access Key",

//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
//	M E S S A G E S
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>	

"cp_msg_are_you_sure"                => "Are you absolutely sure?",
"cp_msg_sure"                        => "I'm Sure",
"cp_msg_not_sure"                    => "No",

//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
//	E R R O R S
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>	
"cp_err_all_fields"                  => "Can you fill out all the required fields down there?",
"cp_err_title_format"                => "Can you make sure that your title is a single word, with no spaces? Thanks!",
"cp_err_unknown"                     => "Hmmm. Something weird just happened. Can you try again?",
"cp_err_empty"                       => "It looks like you left a field empty. Can you fix that and try again?",
"cp_err_all_req_fields"              => "Hey, can you fill out those required fields down there?",
"cp_err_guided_fields"               => "Make sure you select a template, channel and entry below.",

//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
//	S U C C E S S 
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>	
"cp_suc_new_nav"                     => "You did a great job! Your Navigation Group was added successfully.",
"cp_suc_new_nav_item"                => "Your Navigation Item was successfully added. We're as excited as you are!",
"cp_suc_added"                       => "We've got that saved nicely for you.",
"cp_suc_sorted"                      => "Glad we got that sorted out!",
"cp_suc_delete_nav"                  => "And poof! It's gone. Just like that.",
"cp_suc_delete_navItem"              => "You won't be hearing from that Navigation Item again!",
"cp_suc_update_navItem"              => "I think you've made a big improvement there. All saved.",
"cp_suc_conf"                        => "Your configurations have been saved.",

//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
//	F I E L D T Y P E 
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>	
"ft_use_pages"                       => "Use Pages Info",
"ft_navigation"                      => "Navigation",
"ft_text"                            => "Text",
"ft_template"                        => "Template",
"ft_parent"                          => "Parent",
"ft_select_navigation"               => "Select Navigation Above",
"ft_select_a_nav"                    => "Select a Navigation",

//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
//	F I E L D T Y P E   E R R O R S
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>	

"ft_err_select_navigation"           => "A navigation must be selected",
"ft_err_pages"                       => "You selected Pages, but did not assign a Pages value",
"ft_err_valid_template"              => "Select a valid template",

//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
//	E X T E N S I O N
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>	
"ext_name"                           => "NavEE Extension",
"ext_description"                    => "Clears appropriate caches when entries are updated",


//
//
''                                   =>''
);