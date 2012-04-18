<?php 
/*
_|                                                      _|
_|_|_|     _|_|     _|_|   _|    _|   _|_|_| _|_|_|   _|_|_|_|
_|    _| _|    _| _|    _| _|    _| _|    _| _|    _|   _|
_|    _| _|    _| _|    _| _|    _| _|    _| _|    _|   _|
_|_|_|     _|_|     _|_|     _|_|_|   _|_|_| _|    _|     _|_|
                                 _|
                             _|_|

Description:		NavEE Module for Expression Engine 2.x
Developer:			Booyant, Inc.
Website:			www.booyant.com/navee
Location:			./system/expressionengine/third_party/modules/navee/ft.navee.php
Contact:			navee@booyant.com  / 978.OKAY.BOB

*/

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Navee_mcp {

	var $hasErrors                       = false;
	var $message                         = "";
	var $nav                             = array();
	var $navee_navs                      = array();
	var $site_id                         = "1";
	var $ee_install_directory            = "";
	var $include_index                   = false;
	var $theme_folder_directory          = "";
	var $stylesheet                      = "navee.css";
	var $blockedMemberGroups             = array();
	var $blockedTemplates                = array();
	var $entify_ee_tags                  = false;
	var $remove_deleted_entries          = false;
	var $only_superadmins_can_admin_navs = false;
	var $ce_cache_installed              = false;

	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	//	C O N S T R U C T O R
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>

	function Navee_mcp(){

		// Make a local reference to the ExpressionEngine super object
		$this->EE =& get_instance();
		$this->site_id = $this->EE->config->item('site_id');
		
		// Figure out if CE Cache is installed
		$this->EE->db->select("module_id");
		$this->EE->db->where("module_name", "Ce_cache");
		$q = $this->EE->db->get("modules");
		if ($q->num_rows() > 0){
			$this->ce_cache_installed = true;
		}
		$q->free_result();
		
		// Set base configurations
		$this->EE->db->select("k,v");
		$this->EE->db->where("site_id", $this->site_id);
		$keys = array("include_index", "install_directory", "stylesheet", "blockedMemberGroups", "blockedTemplates", "entify_ee_tags", "remove_deleted_entries", "only_superadmins_can_admin_navs");
		$this->EE->db->where_in("k", $keys);
		$q = $this->EE->db->get("navee_config");
			
		if ($q->num_rows() > 0){
			foreach ($q->result() as $r){
				switch ($r->k) {
					case "install_directory":
						$this->ee_install_directory            = $this->_formatInstallDirectory($r->v);
						break;
					case "include_index":
						$this->include_index                   = $r->v;
						break;
					case "stylesheet":
						$this->stylesheet                      = $r->v;
						break;
					case "blockedMemberGroups":
						$this->blockedMemberGroups             = unserialize($r->v);
						break;
					case "blockedTemplates":
						$this->blockedTemplates                = unserialize($r->v);
						break;
					case "entify_ee_tags":
						$this->entify_ee_tags                  = $r->v;
						break;
					case "remove_deleted_entries":
						$this->remove_deleted_entries          = $r->v;
						break;	
					case "only_superadmins_can_admin_navs":
						$this->only_superadmins_can_admin_navs = $r->v;
						break;	
				}
			}
		}

		$q->free_result();
		
		// Figure out if the theme is in the third_party folder or not
		if (is_dir($this->EE->config->item('theme_folder_path').'third_party/navee')){
			$this->theme_folder_directory = "third_party/";
		}
		
		// Comment out the following line to enable caching
		$this->EE->db->cache_off();
	}

	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	//	M O D U L E   M A I N   P A G E
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>

	function index(){
		$vars = array();
		$this->EE->load->library('table');
		$this->EE->load->library('javascript');
		$this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('cp_header'));
		
		$this->EE->cp->add_to_head('<link rel="stylesheet" type="text/css" href="'.$this->EE->config->item('theme_folder_url').$this->theme_folder_directory.'navee/css/'.$this->stylesheet.'" />');

		// Javascript
			$this->EE->javascript->output(array('
				
				$("#navEE").delegate(".x", "click", function(){
					$(this).parent("div").fadeOut(200);
				});

				$("body").live("click", function(){
					$(".navee_helper").fadeOut(333);
				});

				$("#navEE .navee_delete").click(function(){
					var id = $(this).attr("id").split("_");
					$(".navee_alert").html("'.$this->EE->lang->line('cp_msg_are_you_sure').' <a href=\''.BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=navee'.AMP.'method=delete_navigation'.AMP.'id="+id[1]+"\' class=\'x\'>'.$this->EE->lang->line('cp_msg_sure').'</a><a href=\'javascript:;\' class=\'x navee_trash_no_dump\'>'.$this->EE->lang->line('cp_msg_not_sure').'</a>").fadeIn(333);
				});

				$("#navEE td > div").hover(
					function(){
						if (!$(this).children(".navee_accept").is(":visible")){
							$(this).children(".navee_edit").show();
						}
					},
					function(){
						$(this).children(".navee_edit").hide();
					}
				);

				$("#navEE").delegate(".navee_edit", "click", function(){
					var id = $(this).attr("id").split("_");
					if (id[2] == "name") {
						content = $(this).siblings("span").children("a").html()
					} else {
						content = $(this).siblings("span").html();
					}
					$(this).hide();
					$(this).siblings(".navee_accept").show();
					$(this).siblings("span").html("<input type=\'text\' value=\'"+content+"\' />");
				});

				$("#navEE").delegate(".navee_accept", "click", function(){
					var content = $(this).siblings("span").children("input").val();
					var id = $(this).attr("id").split("_");
					
					if (jQuery.trim(content).length > 0){
						$.ajax({
							type: "GET",
							cache: false,
							url: "'.html_entity_decode(BASE).'&C=addons_modules&M=show_module_cp&module=navee&method=update_navigation&content="+content+"&id="+id[3]+"&type="+id[2],
							data: "",
							success: function(msg){
								//alert( "Data Saved: " + msg );
								if (msg.length > 0) {
									$(".navee_alert").html(msg+"<a href=\'javascript:;\' class=\'x\'>X</a>").slideDown(333);
								}

							}
						});

						if (id[2] == "name") {
							content = "<a href=\''.html_entity_decode(BASE).'&C=addons_modules&M=show_module_cp&module=navee&method=manage_navigation&id="+id[3]+"\'>"+content+"</a>";
						}

						$(this).hide();
						$(this).siblings("span").html(content);
						$.ee_notice("'.$this->EE->lang->line('cp_suc_added').'", {type: \'success\'});

					} else {
						$.ee_notice("'.$this->EE->lang->line('cp_err_empty').'", {type: \'error\', open: true});
					}
				});
			'));

			$this->EE->javascript->compile();

		// Check to see if there is anything in the database
			$this->EE->db->select("*");
			$this->EE->db->order_by("nav_name", "ASC");
			$this->EE->db->where("site_id", $this->site_id);
			$q = $this->EE->db->get("navee_navs");
			if ($q->num_rows() > 0){
				// Create a list of navs
					$vars["instructions"]                    = false;

					foreach ($q->result() as $row){
						$vars["navs"][$row->navigation_id]["navigation_id"] 	= $row->navigation_id;
						$vars["navs"][$row->navigation_id]["nav_name"] 			= $row->nav_name;
						$vars["navs"][$row->navigation_id]["nav_title"] 		= $row->nav_title;
						$vars["navs"][$row->navigation_id]["nav_description"] 	= $row->nav_description;
						$vars["blockedMemberGroups"][$row->navigation_id] 		= array();
						if (isset($this->blockedMemberGroups[$row->navigation_id])){
							$vars["blockedMemberGroups"][$row->navigation_id] = $this->blockedMemberGroups[$row->navigation_id];
						}
					}
			} else {
				// How about some instructions to be helpful
					$vars["instructions"] = true;
			}
			$q->free_result();

			$vars["manage_nav_link"]		= BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=navee'.AMP.'method=manage_navigation';
			$vars["add_nav_link"]			= BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=navee'.AMP.'method=add_navigation';
			$vars["config_link"]			= BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=navee'.AMP.'method=config';
			$vars["theme_folder_url"]		= $this->EE->config->item('theme_folder_url').$this->theme_folder_directory;
			$vars["memberGroupId"]			= $this->EE->session->userdata['group_id'];
			$vars["blockedMemberGroups"][0]	= array();

			if ($this->only_superadmins_can_admin_navs && $this->EE->session->userdata['group_id'] !== "1"){
				$vars["hideAddDelete"] = true;
			} else {
				$vars["hideAddDelete"] = false;
			}

			if (isset($this->blockedMemberGroups[0])){
				$vars["blockedMemberGroups"][0]	= $this->blockedMemberGroups[0];
			}
		return $this->EE->load->view('/mcp/index', $vars, TRUE);
	}
	
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	//	C O N F I G
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>

	function config(){
		$vars = array();
		
		// Let's make sure this user has access to this nav
		if (isset($this->blockedMemberGroups[0])){
			if (in_array($this->EE->session->userdata['group_id'], $this->blockedMemberGroups[0])){
				$this->EE->cp->set_breadcrumb(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=navee'.AMP.'method=index', $this->EE->lang->line('navee_module_name'));
			$this->EE->cp->set_variable('cp_page_title', 'Nuh-uh');
			$this->EE->cp->add_to_head('<link rel="stylesheet" type="text/css" href="'.$this->EE->config->item('theme_folder_url').$this->theme_folder_directory.'navee/css/'.$this->stylesheet.'" />');
				return $this->EE->load->view('/mcp/no_way_jose', $vars, TRUE);
			}
		}
		
		$vars["install_directory"]               = "";
		$vars["include_index"]                   = "";
		$vars["entify_ee_tags"]                  = "false";
		$vars["remove_deleted_entries"]          = "false";
		$vars["only_superadmins_can_admin_navs"] = "false";
		$vars["stylesheet"]                      = "navee.css";
		$vars["blockedMemberGroups"]             = array();
		$vars["blockedTemplates"]                = array();
		
		// Stylesheet Array
		$vars["stylesheets"][0]["name"]		= $this->EE->lang->line("cp_conf_stylesheet_classic");
		$vars["stylesheets"][0]["file"]		= "navee.css";
		
		$vars["stylesheets"][1]["name"]		= $this->EE->lang->line("cp_conf_stylesheet_corporate");
		$vars["stylesheets"][1]["file"]		= "corporate.css";
		
		$vars["stylesheets"][2]["name"]		= $this->EE->lang->line("cp_conf_stylesheet_kor");
		$vars["stylesheets"][2]["file"]		= "kor.css";
		
		$this->EE->load->library('table');
		$this->EE->load->library('javascript');
		$this->EE->cp->set_breadcrumb(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=navee'.AMP.'method=index', $this->EE->lang->line('navee_module_name'));
		$this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('cp_conf'));
		
		
		$this->EE->cp->add_to_head('<link rel="stylesheet" type="text/css" href="'.$this->EE->config->item('theme_folder_url').$this->theme_folder_directory.'navee/css/'.$this->stylesheet.'" />');

		// Get an array of all existing navigations & member groups
		$vars["navs"] 			= $this->_getNaveeNavs();
		$vars["member_groups"]	= $this->_getMemberGroups(true);
		$vars["templates"]		= $this->_getTemplateArray();
		
		// Check to see if there is anything in the database
		$this->EE->db->select("k, v");
		$this->EE->db->where("site_id", $this->site_id);
		$q = $this->EE->db->get("navee_config");
		
		if ($q->num_rows() > 0){
			foreach ($q->result() as $row){
				if ($row->k == "blockedMemberGroups" || $row->k == "blockedTemplates"){
					$vars[$row->k] = unserialize($row->v);
				} else {
					$vars[$row->k] = $row->v;
				}
			}
		}

		$q->free_result();

		return $this->EE->load->view('/mcp/config', $vars, TRUE);
	}
	
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	//	C O N F I G   H A N D L E R
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>

	function config_handler(){
		
		// Let's drop all the current configurations
		$this->EE->db->where('site_id', $this->site_id);
		$this->EE->db->delete('navee_config');
		
		$blockedMemberGroups 	= array();
		$mg						= array();
		$blockedTemplates		= array();
		$tp						= array(); 
		
		foreach ($_POST AS $k=>$v){
			if (
					($k !== "navee_submit") &&
					($k !== "clear_cache") &&
					(substr($k,0,2) !== "mg") &&
					(substr($k,0,2) !== "tp") && 
					(strlen($v) > 0)
				){
				$data = array(
					'k' 				=> $k,
					'v' 				=> $this->EE->input->post($k),
					'site_id'			=> $this->site_id,
				);
				$this->EE->db->insert('navee_config', $data);
			}
			
			if (substr($k,0,2) == "mg"){
				$mg = explode("_", $k);
				if (!isset($blockedMemberGroups[$mg[1]])){
					$blockedMemberGroups[$mg[1]] = array();
				}
				array_push($blockedMemberGroups[$mg[1]], $mg[2]);
			}
			
			if (substr($k,0,2) == "tp"){
				$tp = explode("_", $k);
				array_push($blockedTemplates, $tp[1]);
			}
		}
		
		if (sizeof($blockedMemberGroups)>0){
			$data = array(
					'k' 				=> 'blockedMemberGroups',
					'v' 				=> serialize($blockedMemberGroups),
					'site_id'			=> $this->site_id,
			);
			$this->EE->db->insert('navee_config', $data);
		}
		
		if (sizeof($blockedTemplates)>0){
			$data = array(
					'k' 				=> 'blockedTemplates',
					'v' 				=> serialize($blockedTemplates),
					'site_id'			=> $this->site_id,
			);
			
			$this->EE->db->insert('navee_config', $data);
		}
		
		if ($_POST["clear_cache"] == "true"){
			$this->_clearCache();
		}
		
		
		$this->EE->session->set_flashdata('message_success', $this->EE->lang->line('cp_suc_conf'));
		$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=navee'.AMP.'method=index');
		
	}

	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	//	M A N A G E   N A V I G A T I O N 
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>

	function manage_navigation(){
		$vars = array();
		$this->EE->load->library('table');
		$this->EE->load->library('javascript');
		$this->EE->load->library('session');
		
		// Let's make sure this user has access to this nav
		if (isset($this->blockedMemberGroups[$this->EE->input->get("id")])){
			if (in_array($this->EE->session->userdata['group_id'], $this->blockedMemberGroups[$this->EE->input->get("id")])){
				$this->EE->cp->set_breadcrumb(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=navee'.AMP.'method=index', $this->EE->lang->line('navee_module_name'));
			$this->EE->cp->set_variable('cp_page_title', 'Nuh-uh');
			$this->EE->cp->add_to_head('<link rel="stylesheet" type="text/css" href="'.$this->EE->config->item('theme_folder_url').$this->theme_folder_directory.'navee/css/'.$this->stylesheet.'" />');
				return $this->EE->load->view('/mcp/no_way_jose', $vars, TRUE);
			}
		}	

		$vars["navigation_id"] 	= $this->EE->input->get("id");
		$this->nav				= $this->_getNav($this->EE->input->get("id"),0,true,true);
		$selected_node			= 0;

		$this->EE->db->select("nav_name");
		$this->EE->db->where("navigation_id", $vars["navigation_id"]);
		$this->EE->db->where("site_id", $this->site_id);
		$q = $this->EE->db->get("navee_navs");

		if ($q->num_rows() == 1){
			$this->EE->db->select("navee_id");
			$this->EE->db->where("navigation_id", $vars["navigation_id"]);
			$this->EE->db->where("site_id", $this->site_id);
			$qNav = $this->EE->db->get("navee");
			if ($qNav->num_rows() == 0){
				$vars["nav_empty"] 	= true;
				$firstNaveeId	 	= 0;
			} else {
				$vars["nav_empty"] 	= false;
				$firstNaveeId	 	= $this->nav[0]["navee_id"];
			}

			// Set some alerts and helpers

			$vars["alert"] 			= "";
			$vars["helper"] 		= "";
			$vars["selectPages"]	= "";
			$vars["selectPagesBtn"]	= "";
			$vars["enterLinkBtn"]	= "";
			
			if ($this->EE->input->get("navee_id")){
				$selected_node			= $this->EE->input->get("navee_id");
				$vars["navItemForm"]	= $this->get_navigation_item_form($selected_node);
				
			} else {
				$vars["navItemForm"]	= $this->new_navigation_item_form($vars["navigation_id"]);
			}
			

			switch($qNav->num_rows()){
				case 0:
					// Get an array of Pages
						$pages 				= $this->EE->config->item('site_pages');
						
						if (isset($pages[$this->site_id]["uris"])){
							$numPages = sizeof($pages[$this->site_id]["uris"]);
						} else {
							$numPages = 0;
						}
						
						$vars["numPages"] 	= $numPages;

					$vars["helper"] = $this->EE->lang->line('cp_hlp_navItem_1');
					break;
				case 1:
					$vars["helper"] = $this->EE->lang->line('cp_hlp_navItem_2');
					break;
				default:
					$vars["helper"] = "";
			}

			$qNav->free_result();

			$r = $q->row();
			$this->EE->cp->set_breadcrumb(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=navee'.AMP.'method=index', $this->EE->lang->line('navee_module_name'));
			$this->EE->cp->set_variable('cp_page_title', $r->nav_name);
			$this->EE->cp->add_to_head('<link rel="stylesheet" type="text/css" href="'.$this->EE->config->item('theme_folder_url').$this->theme_folder_directory.'navee/css/'.$this->stylesheet.'" />');

			// Javascript
			
			if ($this->EE->session->flashdata('message_success')){
				$this->EE->javascript->output(array('
					$("#navee_success_add").show();
					setTimeout(function() { $("#navee_success_add").fadeOut("200"); }, 3000);
				'));
			}
			$this->EE->javascript->output(array('
				
///////////////////////////////////////////////////////////
//
// B I T S   &   P I E C E S
//
///////////////////////////////////////////////////////////

	$(".tree-view").parent(".pageContents").addClass("tree-view");
	
	var ogNavEE_Form_margin_top = $("#navEE-Form").css("margin-top");
					
	function naveeEncode(str){
		str = str.replace(/\?/g, "__navEE__3f");
		str = str.replace(/;/g, "__navEE__3b");
		str = str.replace(/:\/\//g, "__navEE__3a2f2f");
		str = str.replace(/\./g, "__navEE__2e");
		return str;
	}
	
	$("#navEE .x").click(function(){
		$(this).parent("div").fadeOut(333);
	});
	
	$("body").click(function(){
		$(".navee_helper").fadeOut(333);
	});
	
	$("#navee_cp_nav ul").bind("mousedown", function(e) {
  		e.stopPropagation();
	});
	
	$(".navee_cp_text").attr("unselectable","on");
	
///////////////////////////////////////////////////////////
//
// A D D   T O   N A V I G A T I O N
//
///////////////////////////////////////////////////////////
	
	$("#navEE #navEE-Form-Add").click(function(){
			$.ajax({
					type: "GET",
					cache: false,
					url: "'.html_entity_decode(BASE).'&C=addons_modules&M=show_module_cp&module=navee&method=new_navigation_item_form",
					data: "id='.$vars["navigation_id"].'",
					success: function(msg){
						//alert( "Data Saved: " + msg );
						$("#navEE-Form-Content").html(msg);
						$("#navEE-Form-Header p").html("'.$this->EE->lang->line('cp_mn_add_item_desc').'");
						$("#navEE-Form-Header h3").html("'.$this->EE->lang->line('cp_mn_add_item').'");
						$("div#navee_cp_nav ul li").removeClass("selected");
						//$("#navEE-Form").css("border", "1px solid #BDC0C2");
						//$("#navEE-Form").removeClass("active");
						//Jeffs krazy border especial
						//$("#navEE-Form").removeClass("active");
						$("#navEE-Form").removeClass();
						//End Jeffs krazy border especial
						$("#navEE-Form").css("margin-top", ogNavEE_Form_margin_top);
						$("html:not(:animated),body:not(:animated)").animate({ scrollTop: 100}, 300 );
						$("#navee_text").focus();
					}
			});
	});
					
					
///////////////////////////////////////////////////////////
//
// S H O W   T O O L B A R
//
///////////////////////////////////////////////////////////

	$("#navEE #navee_cp_nav li div").hover(
		function(){
			if (!$(this).parent("li").hasClass("selected")){
				$(this).children(".navee_edit").show();
			}
			$(this).children(".navee_trash").show();
		}, function(){
			$(this).children(".navee_edit").hide();
			$(this).children(".navee_trash").hide();
		}
	);
					
///////////////////////////////////////////////////////////
//
// S H O W   C O D E   B O X
//
///////////////////////////////////////////////////////////

	$("#navEE").delegate(".navee_get_code", "click", function(){
		$(".navee_ee_tag").slideToggle(333);
		if ($(this).html() == "'.$this->EE->lang->line('cp_mn_get_code').'"){
			$(this).html("'.$this->EE->lang->line('cp_mn_hide_code').'");
		} else {
			$(this).html("'.$this->EE->lang->line('cp_mn_get_code').'");
		}
	});

///////////////////////////////////////////////////////////
//
// L I N K   T Y P E   S E L E C T I O N
//
///////////////////////////////////////////////////////////

	
	$("#navEE").delegate(".pill > li", "click", function(){
		$(this).addClass("selected").siblings("li").removeClass("selected");
		
		switch($(this).attr("id")){
			case "guided":
				$(this).parent("ul").siblings("label").parent("li").siblings(".naveeLinkType").hide().siblings("#naveeGuided").show();
				break;
			case "pages":
				$(this).parent("ul").siblings("label").parent("li").siblings(".naveeLinkType").hide().siblings("#pagesSelect").show();
				break;
			default:
				$(this).parent("ul").siblings("label").parent("li").siblings(".naveeLinkType").hide().siblings("#naveeLink").show();
		}
		
		$(this).parent("ul").parent("li").parent("ol").siblings(".navee_input_type").val($(this).attr("id"));
	});
	
	$("#navEE").delegate(".navee_channels_select", "change", function(){
		if ($(this).val() == 0) {
			$("#navEE #navEE-Form-Content #navee_entries").html("<option>'.$this->EE->lang->line('cp_form_select_channel').'</option>");
		} else {
			$.ajax({
						type: "GET",
						cache: false,
						url: "'.html_entity_decode(BASE).'&C=addons_modules&M=show_module_cp&module=navee&method=get_channel_entries",
						data: "id="+$(this).closest(".navee_edit_form").parent("li").attr("id")+"&channel_id="+$(this).val(),
						dataType: "json",
						success: function(msg){
							
								$("#navEE #navEE-Form-Content #navee_entries").html(msg[0].entries);
							
						}
				});
		}
	});
	

///////////////////////////////////////////////////////////
//
// S H O W   E D I T   F O R M
//
///////////////////////////////////////////////////////////
/*	
	$("#navEE").delegate(".navee_edit", "click", function(){
		var containerHeight 	= $("#navEE").height();
		var containerOffset		= $("#navEE").offset().top;
		var containerBottom		= containerHeight + containerOffset-15;
		var nodeTop 			= $(this).parent("div").offset().top;
		var newTop				= 0;
		
		id = $(this).parent("div").parent("li").attr("id");
		if ($(this).siblings(".navee_edit_form").is(":visible")){
			$("#navee_cp_nav #"+id+" .navee_edit_form").slideUp(333);
		} else {
			$.ajax({
					type: "GET",
					cache: false,
					url: "'.html_entity_decode(BASE).'&C=addons_modules&M=show_module_cp&module=navee&method=get_navigation_item_form",
					data: "id="+id,
					success: function(msg){
						//alert( "Data Saved: " + msg );
						$("#navEE-Form-Content").html(msg);
						$("#navEE-Form-Header h3").html("'.$this->EE->lang->line('cp_mn_edit_item').'");
						$("#navEE-Form-Header p").html("'.$this->EE->lang->line('cp_mn_edit_item_desc').'");
					
						$("#navee_cp_nav li").removeClass("selected");
						$("#navee_cp_nav #"+id).addClass("selected");
						//Jeffs border especial
						var numParents = $("#navee_cp_nav #"+id).parentsUntil("#navee_cp_nav").length;
						$("#navEE-Form").removeClass();
						$("#navEE-Form").addClass("rentsNum"+numParents);
						$("#navEE-Form").addClass("active");
						//End Jeffs border especial
						
						var formHeight = $("#navEE-Form").height();
						
						if ((nodeTop + formHeight) > containerBottom){
							newTop = $("#navee_cp_nav").height() - formHeight - 40;
						} else {
							newTop = nodeTop - 250;
						}
						
						if (newTop < 0) {
							newTop = 0;
						}
								
						$("#navEE-Form").css("margin-top", newTop+"px");
						
						var destination = $("#navEE-Form").offset().top-100;
						var speed = 300;
						$("html:not(:animated),body:not(:animated)").animate({ scrollTop: destination}, speed );
						$("#navee_text").focus();
					}
			});
		}
	});
*/		

$("#navEE").delegate(".navee_edit", "click", function(){
	var t = $(this);
	showNavEEedit(t);
});

$("#navEE").delegate(".navee_cp_text", "dblclick", function(){
	var t = $(this).siblings(".navee_edit");
	showNavEEedit(t);
});


function showNavEEedit(t){
	var containerHeight 	= $("#navEE").height();
		var containerOffset		= $("#navEE").offset().top;
		var containerBottom		= containerHeight + containerOffset-15;
		var nodeTop 			= t.parent("div").offset().top;
		var newTop				= 0;
		
		id = t.parent("div").parent("li").attr("id");
		if (t.siblings(".navee_edit_form").is(":visible")){
			$("#navee_cp_nav #"+id+" .navee_edit_form").slideUp(333);
		} else {
			$.ajax({
					type: "GET",
					cache: false,
					url: "'.html_entity_decode(BASE).'&C=addons_modules&M=show_module_cp&module=navee&method=get_navigation_item_form",
					data: "id="+id,
					success: function(msg){
						//alert( "Data Saved: " + msg );
						$("#navEE-Form-Content").html(msg);
						$("#navEE-Form-Header h3").html("'.$this->EE->lang->line('cp_mn_edit_item').'");
						$("#navEE-Form-Header p").html("'.$this->EE->lang->line('cp_mn_edit_item_desc').'");
					
						$("#navee_cp_nav li").removeClass("selected");
						$("#navee_cp_nav #"+id).addClass("selected");
						//Jeffs border especial
						var numParents = $("#navee_cp_nav #"+id).parentsUntil("#navee_cp_nav").length;
						$("#navEE-Form").removeClass();
						$("#navEE-Form").addClass("rentsNum"+numParents);
						$("#navEE-Form").addClass("active");
						//End Jeffs border especial
						
						var formHeight = $("#navEE-Form").height();
						
						if ((nodeTop + formHeight) > containerBottom){
							newTop = $("#navee_cp_nav").height() - formHeight - 40;
						} else {
							newTop = nodeTop - 250;
						}
						
						if (newTop < 0) {
							newTop = 0;
						}
								
						$("#navEE-Form").css("margin-top", newTop+"px");
						
						var destination = $("#navEE-Form").offset().top-100;
						var speed = 300;
						$("html:not(:animated),body:not(:animated)").animate({ scrollTop: destination}, speed );
						$("#navee_text").focus();
					}
			});
		}
	}
			
///////////////////////////////////////////////////////////
//
// S H O W   O P T I O N A L   I N F O
//
///////////////////////////////////////////////////////////

	$("#navEE").delegate(".navee_optional_btn", "click", function(){
		if ($(this).parent("li").parent("ul").siblings(".navee_optional").is(":visible")){
			$(this).html("'.$this->EE->lang->line('cp_mn_optional').'");
			var destination = $("#navEE-Form").offset().top-100;
			var speed = 300;
			$("html:not(:animated),body:not(:animated)").animate({ scrollTop: destination}, speed );
		} else {
			$(this).html("'.$this->EE->lang->line('cp_mn_optional_fewer').'");
		}
		$(this).parent("li").parent("ul").siblings(".navee_optional").slideToggle(333);
	});
	
///////////////////////////////////////////////////////////
//
// S W I T C H   N A V I G A T I O N S
//
///////////////////////////////////////////////////////////

	$("#navEE").delegate("#navEE-Nav-Select select", "change", function(){
		if ($(this).val() == 0){
			$(location).attr("href","'.html_entity_decode(BASE).'&C=addons_modules&M=show_module_cp&module=navee&method=index");
		} else if ($(this).val() == "-1"){
			$(location).attr("href","'.html_entity_decode(BASE).'&C=addons_modules&M=show_module_cp&module=navee&method=add_navigation");
		} else if ($(this).val() == "-"){
			return false;
		} else {
			$(location).attr("href","'.html_entity_decode(BASE).'&C=addons_modules&M=show_module_cp&module=navee&method=manage_navigation&id="+$(this).val());
		}
		
	});
					
///////////////////////////////////////////////////////////
//
// U P D A T E   N O D E
//
///////////////////////////////////////////////////////////

	$("#navEE").delegate(".navee_update_btn", "click", function(){
	
		// V A R I A B L E S
	
			qs 				= "";
			textStr			= "";
			parentId 		= 0;
			old_parent		= 0;
			id 				= $(this).closest("form").children("#naveeCPFormId").val();
			type 			= $(this).parent("li").parent("ul").siblings(".navee_input_type").val();
			errors			= false;
			entry_id		= 0;
			channel_id		= 0;
					
		// Q U E R Y   S T R I N G
			
			$(this).parent("li").parent("ul").parent("form").find("input").each(function(){
				
				// append & after the first item
				if (qs.length > 0){
					qs = qs + "&";
				}
				
				// inputs
				if ($(this).attr("type") == "checkbox") {
					if ($(this).is(":checked")) {
						qs = qs + $(this).attr("name") + "=" + encodeURIComponent(naveeEncode($(this).val()));
					}
				} else {
					qs = qs + $(this).attr("name") + "=" + encodeURIComponent(naveeEncode($(this).val()));
				}
				
				// exceptions
				switch($(this).attr("name")){
					case "navee_text":
						textStr = $(this).val();
						break;
					case "old_parent":
						old_parent = $(this).val();
						break;
				}
			});
		
			// selects
			$(this).parent("li").parent("ul").parent("form").find("select").each(function(){
				if (qs.length > 0){
					qs = qs + "&";
				}
				qs = qs + $(this).attr("name") + "=" + encodeURIComponent(naveeEncode($(this).val()));
				
				switch($(this).attr("name")){
					case "navee_parent":
						parentId = $(this).val();
						break;
					case "navee_channels":
						channel_id = $(this).val();
						break;
					case "navee_entries":
						entry_id = $(this).val();
						break;
				}
			});						

			
		// A J A X
			
			if (!errors) {
				$.ajax({
						type: "GET",
						cache: false,
						url: "'.html_entity_decode(BASE).'&C=addons_modules&M=show_module_cp&module=navee&method=update_navigation_item",
						data: qs,
						success: function(msg){
							//alert( "Data Saved: " + msg );
							if (msg == "error") {
								$.ee_notice("'.$this->EE->lang->line('cp_err_all_req_fields').'", {type: \'error\', open: true});
							} else {
								$.ee_notice("'.$this->EE->lang->line('cp_suc_update_navItem').'", {type: \'success\'});
								$("#"+id).children("div").children(".navee_cp_text").html(textStr);
								$("#navee_success_edit").show();
								setTimeout(function() { $("#navee_success_edit").fadeOut("200"); }, 3000);
								
								switch(type){
									
									case "guided":
										editEntryLink = "'.html_entity_decode(BASE).'&C=content_publish&M=entry_form&entry_id="+$("#navee_entries").val()+"&channel_id="+$(".navee_channels_select").val();
										
										if ($(".navee_channels_select").val() > 0 && $("#navee_entries").val() > 0){
											if (!$("#navee_entries").siblings("label").children("a").is("*")){
												$(".naveeEntriesLabel").append("<a class=\"edit_entry\" href=\""+editEntryLink+"\">'.$this->EE->lang->line('cp_mn_edit_channel_entry').'</a>");
											} else {
												$(".naveeEntriesLabel").children(".edit_entry").attr("href", editEntryLink);
											}
										}
										break;
									
									case "pages":
										editEntryLink = "'.html_entity_decode(BASE).'&C=content_publish&M=entry_form&entry_id="+$(".pagesDropdown").val()+"&channel_id="+msg;
										
										if ($(".pagesDropdown").val() > 0){
											if (!$(".pagesDropdown").siblings("label").children("a").is("*")){
												$(".pagesDropdown").siblings("label").append("<a class=\"edit_entry\" href=\""+editEntryLink+"\">'.$this->EE->lang->line('cp_mn_edit_channel_entry').'</a>");
											} else {
												$(".pagesDropdown").siblings("label").children(".edit_entry").attr("href", editEntryLink);
											}
										}
								}
								
							}
						}
				});
	
			// S O R T I N G
		
				if (parentId !== old_parent){
					
					// Mark the soon to be moved item for deletion
					$("#navee_cp_nav #"+id).addClass("trashQueue");
					
					if (parentId == 0){
						
						// If this item is being moved to the top level
						$("#navee_cp_nav #"+id).appendTo("#navee_cp_nav > ul");
					
					} else {
						
						// If this item is being moved to an existing child element
						if ($("#navee_cp_nav #"+parentId+" > ul").length == 0){
							
							// If this is the parents first child
							$("#navee_cp_nav #"+parentId).append("<ul><li id=\'"+id+"\' class=\'selected\'>"+$("#navee_cp_nav #"+id).html()+"</li></ul>");

							$(".trashQueue").remove();
							
						} else {
						
							// If the parent already has children		
							$("#navee_cp_nav #"+id).appendTo("#navee_cp_nav #"+parentId+" > ul");
						}	
					}
					
					if($("#navee_cp_nav #"+old_parent+" > ul > li").size()==0){
						$("#navee_cp_nav #"+old_parent+" > ul").remove();
					}
					
					// Move the form accordingly
					var containerHeight 	= $("#navEE").height();
					var containerOffset		= $("#navEE").offset().top;
					var containerBottom		= containerHeight + containerOffset-15;
					var nodeTop 			= $("#navee_cp_nav #"+id).children("div").offset().top;
					var newTop				= 0;
					
					var formHeight = $("#navEE-Form").height();
								
					if ((nodeTop + formHeight) > containerBottom){
						newTop = $("#navee_cp_nav").height() - formHeight - 40;
					} else {
						newTop = nodeTop - 250;
					}
					
					if (newTop < 0) {
						newTop = 0;
					}
							
					$("#navEE-Form").css("margin-top", newTop+"px");
					
					
				}
			}
	});
					
///////////////////////////////////////////////////////////
//
// T R A S H
//
///////////////////////////////////////////////////////////

	$("#navEE .navee_trash").click(function(){
		var id = $(this).parent("div").parent("li").attr("id");
		$(".navee_alert").html("'.$this->EE->lang->line('cp_msg_are_you_sure').' <a id=\'navee_trash_'.$vars["navigation_id"].'_"+id+"\' href=\'javascript:;\' class=\'x navee_trash_dump\'>'.$this->EE->lang->line('cp_msg_sure').'</a><a id=\'navee_trash_close_'.$vars["navigation_id"].'_"+id+"\' href=\'javascript:;\' class=\'x navee_trash_no_dump\'>'.$this->EE->lang->line('cp_msg_not_sure').'</a>").css("top", $(window).scrollTop()+100).fadeIn(333);
	});
	
	$("#navEE").delegate(".navee_trash_dump", "click", function(){
		var id = $(this).attr("id").split("_");
		var navee_id 	= id[3];
		var nav_id		= id[2];
	
		$.ajax({
				type: "GET",
				cache: false,
				url: "'.html_entity_decode(BASE).'&C=addons_modules&M=show_module_cp&module=navee&method=delete_navigation_item",
				data: "navid="+nav_id+"&id="+navee_id,
				success: function(msg){
					//alert( "Data Saved: " + msg );
					$.ee_notice("'.$this->EE->lang->line('cp_suc_delete_navItem').'", {type: \'success\'});
					$("#navEE #navee_cp_nav #"+navee_id).remove();
					$(".navee_alert").fadeOut(333);
				}
		});
		
		if ($("#naveeCPFormId").val() == navee_id) {
			$.ajax({
				type: "GET",
				cache: false,
				url: "'.html_entity_decode(BASE).'&C=addons_modules&M=show_module_cp&module=navee&method=new_navigation_item_form",
				data: "id='.$vars["navigation_id"].'",
				success: function(msg){
					//alert( "Data Saved: " + msg );
					$("#navEE-Form-Content").html(msg);
					$("#navEE-Form-Header p").html("'.$this->EE->lang->line('cp_mn_add_item_desc').'");
					$("#navEE-Form-Header ul li a").removeClass("selected");
					$("#navEE-Form-Add").addClass("selected");
					$("div#navee_cp_nav ul li").removeClass("selected");
					//$("#navEE-Form").css("border", "1px solid #BDC0C2");
					//Jeffs krazy border especial
					//$("#navEE-Form").removeClass("active");
					$("#navEE-Form").removeClass();
					//End Jeffs krazy border especial
					$("#navEE-Form").css("margin-top", "0px");
					$("html:not(:animated),body:not(:animated)").animate({ scrollTop: 100}, 300 );
				}
			});
					
		}
	});
	
	$("#navEE").delegate(".navee_trash_no_dump", "click", function(){
		$(this).parent("div").fadeOut(333);
	});
					
///////////////////////////////////////////////////////////
//
// S O R T I N G
//
///////////////////////////////////////////////////////////

	$("#navEE #navee_cp_nav ul").sortable({
			opacity: 0.8,
			update: function(event, ui) { 
			var parentId = $(this).parent().attr("id");
			if (parentId == "navee_cp_nav"){
				parentId = 0;
			}
			var ids = $(this).sortable("toArray");
			var gets = "";
			
			if (ui.item.attr("class") == "selected"){			
				var containerHeight 	= $("#navEE").height();
				var containerOffset		= $("#navEE").offset().top;
				var containerBottom		= containerHeight + containerOffset-15;
				var nodeTop 			= ui.item.children("div").offset().top;
				var newTop				= 0;
				
				var formHeight = $("#navEE-Form").height();
							
				if ((nodeTop + formHeight) > containerBottom){
					newTop = $("#navee_cp_nav").height() - formHeight - 40;
				} else {
					newTop = nodeTop - 250;
				}
				
				if (newTop < 0) {
					newTop = 0;
				}
						
				$("#navEE-Form").css("margin-top", newTop+"px");
			}
			
			for (i=0; i<ids.length; i++){
				if (gets.length > 0){
					gets = gets + ",";
				}
				gets = gets + parentId+"_"+ids[i];
			}
	
			$.ajax({
				type: "GET",
				cache: false,
				url: "'.html_entity_decode(BASE).'&C=addons_modules&M=show_module_cp&module=navee&method=sort_navigation_items",
				data: "id="+gets,
				success: function(msg){
					//alert( "Data Saved: " + msg );
					
					$.ee_notice("'.$this->EE->lang->line('cp_suc_sorted').'", {type: \'success\'});
			
				}
			});
		}
	});	
'));

			if ($selected_node > 0){
				$this->EE->javascript->output(array('
		///////////////////////////////////////////////////////////
		//
		// L O A D   I N I T I A L   N O D E
		//
		///////////////////////////////////////////////////////////
		
			var containerHeight 	= $("#navEE").height();
			var containerOffset		= $("#navEE").offset().top;
			var containerBottom		= containerHeight + containerOffset-15;
			var nodeTop 			= $("#'.$selected_node.'").children("div").offset().top;
			var newTop				= 0;	
			
			
			id = "'.$selected_node.'";
			$.ajax({
					type: "GET",
					cache: false,
					url: "'.html_entity_decode(BASE).'&C=addons_modules&M=show_module_cp&module=navee&method=get_navigation_item_form",
					data: "id="+id,
					success: function(msg){
						//alert( "Data Saved: " + msg );
						$("#navEE-Form-Content").html(msg);
						$("#navEE-Form-Header h3").html("'.$this->EE->lang->line('cp_mn_edit_item').'");
						$("#navEE-Form-Header p").html("'.$this->EE->lang->line('cp_mn_edit_item_desc').'");
					
						$("#navee_cp_nav li").removeClass("selected");
						$("#navee_cp_nav #"+id).addClass("selected");
						//Jeffs border especial
						//var newBorder = $("#navee_cp_nav #"+id).children("div").children("span").css("background-color");
						//$("#navEE-Form").css("border", "3px solid "+newBorder);
						var numParents = $("#navee_cp_nav #"+id).parentsUntil("#navee_cp_nav").length;
						$("#navEE-Form").removeClass();
						$("#navEE-Form").addClass("rentsNum"+numParents);
						$("#navEE-Form").addClass("active");
						//End Jeffs border especial
						
						var formHeight = $("#navEE-Form").height();
						
						if ((nodeTop + formHeight) > containerBottom){
							newTop = $("#navee_cp_nav").height() - formHeight - 40;
						} else {
							newTop = nodeTop - 250;
						}
						
						if (newTop < 0) {
							newTop = 0;
						}
								
						$("#navEE-Form").css("margin-top", newTop+"px");
						
						var destination = $("#navEE-Form").offset().top-100;
						var speed = 300;
						$("html:not(:animated),body:not(:animated)").animate({ scrollTop: destination}, speed );
						$("#navee_text").focus();
					}
			});
		

				'));
			}
				$this->EE->javascript->compile();

			// View Variables
				$vars["parents"] 		= $this->_styleNavSelect($this->nav, "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;");
				$vars["nav"] 			= $this->_styleNavCP($this->nav,$selected_node);
				$vars["navee_navs"]		= $this->_getNaveeNavs();
				$vars["new_nav_url"]	= BASE.AMP."C=addons_modules".AMP."M=show_module_cp".AMP."module=navee".AMP."method=add_navigation";
				$vars["navee_parent"] 	= $this->EE->session->flashdata('navee_parent');
				$vars["navee_text"] 	= $this->EE->session->flashdata('navee_text');
				$vars["navee_link"] 	= $this->EE->session->flashdata('navee_link');
				$vars["navee_class"] 	= $this->EE->session->flashdata('navee_class');
				$vars["navee_id"] 		= $this->EE->session->flashdata('navee_id');
				$vars["navee_rel"] 		= $this->EE->session->flashdata('navee_rel');
				$vars["navee_name"] 	= $this->EE->session->flashdata('navee_name');
				$vars["navee_regex"] 	= $this->EE->session->flashdata('navee_regex');
			
			return $this->EE->load->view('/mcp/manage_navigation', $vars, TRUE);
		} else {
			return false;
		}
		$q->free_result();
	}

	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	//	A D D   N A V I T E M   H A N D L E R
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>

	function add_navitem_handler(){

		$error = false;

		// V A L I D A T I O N
		
		if (!(	$this->EE->input->post("navee_text") &&
				$this->EE->input->post("navee_navigation_id"))){
				
			$this->hasErrors = true;
			$this->_addMessage($this->EE->lang->line('cp_err_all_req_fields'));	
		}
		
		if ($this->EE->input->post("type")=="guided"){
			//if (!($this->EE->input->post("navee_channels") > 0) || !($this->EE->input->post("navee_entries") > 0)){
			//	$this->hasErrors = true;
			//	$this->_addMessage($this->EE->lang->line('cp_err_guided_fields'));	
			//}
		}

		if ($this->hasErrors){
			foreach ($_POST as $k=>$v){
				$this->EE->session->set_flashdata($k, $v);
			}

			// If we found errors, let's throw some messages around
			$this->EE->session->set_flashdata('message_failure', $this->message);
			$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=navee'.AMP.'method=manage_navigation'.AMP.'id='.$this->EE->input->post("navee_navigation_id"));
		} else {
		
			// V A R I A B L E S
			
					$type 		= "manual";
					$entry_id 	= 0;
					$template	= 0; 
					$channel	= 0;
					
			// V A R I A B L E    O V E R R I D E S
					
					// Type
					if (strlen($this->EE->input->post("type")) > 0){
						$type = $this->EE->input->post("type");
					}
					
					// Template
					if ($this->EE->input->post("navee_templates") > 0){
						$template = $this->EE->input->post("navee_templates");
					}
					
					// Channel
					if ($this->EE->input->post("navee_channels") > 0){
						$channel = $this->EE->input->post("navee_channels");
					}
					
					// Entry ID
					if ($this->EE->input->post("type") == "guided"){
						if ($this->EE->input->post("navee_entries") > 0){
							$entry_id = $this->EE->input->post("navee_entries");
						}
					} elseif ($this->EE->input->post("type") == "pages"){
						if ($this->EE->input->post("pages") > 0){
							$entry_id = $this->EE->input->post("pages");
						}
					}

			// Member Groups
			$memberGroups = $this->_serializeMemberGroups($_POST);	
				
			// Everything looks ok, create the navigation
			$cur_date = date('Y-m-d H:i:s');
			$data = array(
				'navigation_id' 	=> $this->EE->input->post("navee_navigation_id"),
				'site_id'			=> $this->site_id,
				'parent' 			=> $this->EE->input->post("navee_parent"),
				'text'				=> $this->EE->input->post("navee_text"),
				'link'				=> $this->EE->input->post("navee_link"),
				'class'				=> $this->EE->input->post("navee_class"),
				'id'				=> $this->EE->input->post("navee_id"),
				'include'			=> $this->EE->input->post("navee_include"),
				'passive'			=> $this->EE->input->post("navee_passive"),
				'rel'				=> $this->EE->input->post("navee_rel"),
				'name'				=> $this->EE->input->post("navee_name"),
				'target'			=> $this->EE->input->post("navee_target"),
				'access_key'		=> $this->EE->input->post("navee_access_key"),
				'title'				=> $this->EE->input->post("navee_item_title"),
				'custom'			=> $this->EE->input->post("navee_custom"),
				'sort'				=> $this->_nextSort($this->EE->input->post("navee_navigation_id"), $this->EE->input->post("navee_parent")),
				'datecreated'		=> $cur_date,
				'dateupdated'		=> $cur_date,
				'ip_address'		=> $this->EE->input->ip_address(),
				'member_id'			=> $this->EE->session->userdata['member_id'],
				'type'				=> $type,
				'template'			=> $template,
				'entry_id'			=> $entry_id,
				'channel_id'		=> $channel
			);

			// If the config variable for entity EE tags is set, let's do that.
			if ($this->entify_ee_tags == "true"){
				$findme 	= array("{","}");
				$replaceme	= array("&#123;", "&#125;");
				foreach ($data as $k=>$v){
					$data[$k] = str_replace($findme, $replaceme, $data[$k]);
				}
			}
		

			$this->EE->db->insert('navee', $data);
			$id = $this->EE->db->insert_id();
			
			// Insert new entries into navee_members if they exist
			if (strlen($memberGroups)>0){
				$data = array(
					'site_id' 		=> $this->site_id,
					'navee_id' 		=> $id,
					'members' 		=> $memberGroups
				);

				$this->EE->db->insert('navee_members', $data);
			}
			
			// Delete any cached data associated with this nav
			$this->_clearCache($this->EE->input->post("navee_navigation_id"));
		
			$this->EE->session->set_flashdata('message_success', $this->EE->lang->line('cp_suc_new_nav_item'));
			$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=navee'.AMP.'method=manage_navigation'.AMP.'id='.$this->EE->input->post("navee_navigation_id"));
		}
	}

	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	//	A D D   N A V I G A T I O N 
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>

	function add_navigation(){
		$vars = array();
		$this->EE->load->library('table');
		$this->EE->load->library('javascript');
		$this->EE->cp->set_breadcrumb(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=navee'.AMP.'method=index', $this->EE->lang->line('navee_module_name'));
		$this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('cp_an_add_nav_group'));

		$this->EE->cp->add_to_head('<link rel="stylesheet" type="text/css" href="'.$this->EE->config->item('theme_folder_url').$this->theme_folder_directory.'navee/css/'.$this->stylesheet.'" />');

		$this->EE->javascript->compile();

		// Check to see if there is anything in the database
			$this->EE->db->select("navigation_id");
			$q = $this->EE->db->get("navee_navs");

			$vars["helper"] 	="";

			switch($q->num_rows()){
				case 0:
					$vars["helper"] = $this->EE->lang->line('cp_hlp_nav_1');
					break;
			}

			$q->free_result();
			
			$vars["navee_name"] 		= $this->EE->session->flashdata('navee_name');
			$vars["navee_title"] 		= $this->EE->session->flashdata('navee_title');
			$vars["navee_description"] 	= $this->EE->session->flashdata('navee_description');

		return $this->EE->load->view('/mcp/add_navigation', $vars, TRUE);
	}

	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	//	A D D   N A V I G A T I O N   H A N D L E R
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>

	function add_navigation_handler(){

		$error = false;

		// Let's do some validation
		if (!(	$this->EE->input->post("navee_name") && 
				$this->EE->input->post("navee_title") && 
				$this->EE->input->post("navee_description"))){

			$this->hasErrors = true;
			$this->_addMessage($this->EE->lang->line('cp_err_all_fields'));

		} elseif (!preg_match('/^[a-zA-Z0-9-_]+$/', $this->EE->input->post("navee_title"))){
			$this->hasErrors = true;
			$this->_addMessage($this->EE->lang->line('cp_err_title_format'));
		}

		if ($this->hasErrors){

			// If we found errors, let's throw some messages around
			$this->EE->session->set_flashdata('message_failure', $this->message);
			foreach ($_POST as $k=>$v){
				$this->EE->session->set_flashdata($k, $v);
			}
			$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=navee'.AMP.'method=add_navigation');
		} else {

			// Everything looks ok, create the navigation
			$cur_date = date('Y-m-d H:i:s');
			$data = array(
				'nav_title' 		=> $this->EE->input->post("navee_title"),
				'site_id'			=> $this->site_id, 
				'nav_name' 			=> $this->EE->input->post("navee_name"), 
				'nav_description'	=> $this->EE->input->post("navee_description"),
				'datecreated'		=> $cur_date,
				'dateupdated'		=> $cur_date,
				'ip_address'		=> $this->EE->input->ip_address(),
				'member_id'			=> $this->EE->session->userdata['member_id']
			);

			$this->EE->db->insert('navee_navs', $data);

			$this->EE->session->set_flashdata('message_success', $this->EE->lang->line('cp_suc_new_nav'));
			$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=navee'.AMP.'method=manage_navigation'.AMP.'id='.$this->EE->db->insert_id());
		}
	}

	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	//	U P D A T E   N A V I G A T I O N   I T E M 
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>

	function update_navigation_item(){
		if ($this->EE->input->get("id") && ($this->EE->input->get("navee_parent") !== $this->EE->input->get("id"))){
			
			// V A L I D A T I O N
			
				if (!($this->EE->input->get("navee_text"))){
					$this->hasErrors = true;
				}

				if ($this->hasErrors){
					print("error");
					exit;
				}
				
			// V A R I A B L E S
			
					$type 		= "manual";
					$entry_id 	= 0;
					$template	= 0; 
					$channel	= 0;
					$custom_kids	= "";

					
			// V A R I A B L E    O V E R R I D E S
					
					// Type
					if (strlen($this->EE->input->get("type")) > 0){
						$type = $this->EE->input->get("type");
					}
					
					// Template
					if ($this->EE->input->get("navee_templates") > 0){
						$template = $this->EE->input->get("navee_templates");
					}
					
					// Channel
					if ($this->EE->input->get("navee_channels") > 0){
						$channel = $this->EE->input->get("navee_channels");
					}
					
					// Entry ID
					if ($this->EE->input->get("type") == "guided"){
						if ($this->EE->input->get("navee_entries") > 0){
							$entry_id = $this->EE->input->get("navee_entries");
						}
					} elseif ($this->EE->input->get("type") == "pages"){
						if ($this->EE->input->get("pages") > 0){
							$entry_id = $this->EE->input->get("pages");
						}
					}
					
				
			// M E M B E R   G R O U P S
			
				$memberGroups = $this->_serializeMemberGroups($_GET);
			
			// N O D E   S O R T I N G
			
				$this->EE->db->select("*");
				$this->EE->db->where("navee_id", $this->EE->input->get("id"));
				$q = $this->EE->db->get("navee");
				$sort = 1;

				if ($q->num_rows() == 1){
					$r 				= $q->row();
					$navigation_id 	= $r->navigation_id; 
					if ($r->parent !== $this->EE->input->get("navee_parent")){
						$this->EE->db->select("*");
						$this->EE->db->where("parent", $this->EE->input->get("navee_parent"));
						$this->EE->db->order_by("sort", "desc");
						$qPar = $this->EE->db->get("navee",1);
						if ($qPar->num_rows() == 1){
							$rPar = $qPar->row();
							$sort = $rPar->sort + 1;
						}
						$qPar->free_result();

					} else {
						$sort = $r->sort;
					}
				}
				$q->free_result();
				
			// C U S T O M   K I D S
			if ($this->EE->input->get("navee_custom_kids")){
				$custom_kids = $this->EE->input->get("navee_custom_kids");
			}
				
			// U P D A T E   D A T A	

				$data = array(
					'parent' 			=> $this->EE->input->get("navee_parent"),
					'text'				=> $this->_naveeDecode(urldecode(html_entity_decode($this->EE->input->get("navee_text"),ENT_COMPAT,"UTF-8"))),
					'link'				=> $this->_naveeDecode(html_entity_decode($this->EE->input->get("navee_link"),ENT_COMPAT,"UTF-8")),
					'class'				=> $this->_naveeDecode(urldecode($this->EE->input->get("navee_class"))),
					'id'				=> $this->_naveeDecode(urldecode($this->EE->input->get("navee_id"))),	
					'include'			=> $this->EE->input->get("navee_include"),
					'passive'			=> $this->EE->input->get("navee_passive"),
					'sort'				=> $sort,
					'rel'				=> $this->_naveeDecode(urldecode($this->EE->input->get("navee_rel"))),
					'name'				=> $this->_naveeDecode(urldecode($this->EE->input->get("navee_name"))),
					'target'			=> $this->_naveeDecode(urldecode($this->EE->input->get("navee_target"))),
					'access_key'		=> $this->_naveeDecode(urldecode($this->EE->input->get("navee_access_key"))),
					'title'				=> $this->_naveeDecode(urldecode($this->EE->input->get("navee_item_title"))),
					'regex'				=> $this->_naveeDecode(urldecode($this->EE->input->get("navee_regex"))),
					'type'				=> $type,
					'template'			=> $template,
					'entry_id'			=> $entry_id,
					'channel_id'		=> $channel,
					'custom'			=> $this->_naveeDecode(urldecode($this->EE->input->get("navee_custom"))),
					'custom_kids'		=> $this->_naveeDecode(urldecode($custom_kids))
				);
				
				// If the config variable for entity EE tags is set, let's do that.
				if ($this->entify_ee_tags == "true"){
					$findme 	= array("{","}");
					$replaceme	= array("&#123;", "&#125;");
					foreach ($data as $k=>$v){
						$data[$k] = str_replace($findme, $replaceme, $data[$k]);
					}
				}

				$this->EE->db->where('navee_id', $this->EE->input->get("id"));
				$this->EE->db->update('navee', $data);
				
				// Delete any existing entries in navee_members
				$this->EE->db->where("site_id", $this->site_id);
				$this->EE->db->where("navee_id", $this->EE->input->get("id"));
				$this->EE->db->delete('navee_members');
				
				// Insert new entries into navee_members if they exist
				if (strlen($memberGroups)>0){
					$data = array(
						'site_id' 		=> $this->site_id,
						'navee_id' 		=> $this->EE->input->get("id"),
						'members' 		=> $memberGroups
					);

					$this->EE->db->insert('navee_members', $data);
				}
				
				// Delete any cached data associated with this nav
				$this->_clearCache($navigation_id);
				
				$sort++;
			
			print($this->_getChannelId($entry_id));
			exit;	
			return true;
		} else {
			return false;
		}
	}
	

	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	//	G E T   N A V I G A T I O N   I T E M   F O R M
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>

	function get_navigation_item_form($id=0){
		$returnMe = "";
		
		if (($id==0) && ($this->EE->input->get("id")>0)){
			$id = $this->EE->input->get("id");
		}
		
		if ($id > 0){
			// Get an array of Pages
			$pages 						= $this->EE->config->item('site_pages');
			$selectPages 				= "";
			$selectPagesBtn				= "";
			$enterLinkBtn				= "";
			$naveeGuidedSelected 		= "";
			$naveeGuidedPillClass		= "";
			$naveeManualSelected		= "";
			$naveeManualPillClass		= "first";
			$naveePagesSelected			= "";
			$naveePagesPillClass		= "last";
			$naveeGuidedEntryLink		= "";
			$naveePagesEntryLink		= "";
			$vars						= array();
	
			// Get a kid count
			$this->EE->db->where("parent", $id);
			$this->EE->db->where("site_id", $this->site_id);
			$kids = $this->EE->db->count_all_results("navee");
			
			// Get item information
			$this->EE->db->select("n.*,nn.nav_title");
			$this->EE->db->from("navee AS n");
			$this->EE->db->join("navee_navs AS nn", "nn.navigation_id = n.navigation_id", "LEFT OUTER");
			$this->EE->db->where("navee_id", $id);
			$q = $this->EE->db->get();
			if ($q->num_rows() == 1){
				$r = $q->row();
				$this->nav = $this->_getNav($r->navigation_id,0,true,true,true,$id);
				
				// Format the pages select input
				if (isset($pages[$this->site_id]["uris"])){
					$numPages = sizeof($pages[$this->site_id]["uris"]);
					
					// Form : Select : Pages
					$vars["pages"] 					= $pages[$this->site_id]["uris"];
					$vars["entry_id"] 				= $r->entry_id;
					$vars["ee_install_directory"]	= $this->ee_install_directory;
					$vars["include_index"]			= $this->include_index;
					$vars["index_page"]				= $this->EE->config->item('index_page');
					$selectPages 					= $this->EE->load->view('/mcp/forms/select/pages', $vars, TRUE);
					unset($vars);
					
				} else {
					$numPages						= 0;
				}
				
				if (!$numPages){
					$naveeGuidedPillClass			= "last";
				}
				
				// Determine which link type to show
				switch ($r->type) {
					case "guided":
						$naveeGuidedSelected 		= " naveeLinkSelected";
						
						if (strlen($naveeGuidedPillClass)>0){
							$naveeGuidedPillClass	.= " ";
						}
						
						$vars["channel_id"] 	= $r->channel_id;
						$vars["entry_id"]		= $r->entry_id;
						$vars["base"]			= BASE.AMP;
						$naveeGuidedEntryLink 	= $this->EE->load->view('/mcp/a/edit_channel_link', $vars, TRUE);
						unset($vars);
					
						$naveeGuidedPillClass		.= "selected";
						break;
					case "pages":
						$naveePagesSelected 		= " naveeLinkSelected";
						$naveePagesPillClass		.= " selected";
						
						$vars["channel_id"]		= $this->_getChannelId($r->entry_id);
						$vars["entry_id"]		= $r->entry_id;
						$vars["base"]			= BASE.AMP;
						$naveePagesEntryLink 	= $this->EE->load->view('/mcp/a/edit_channel_link', $vars, TRUE);
						unset($vars);
						
						break;
					default:
						$naveeManualSelected 		= " naveeLinkSelected";
						$naveeManualPillClass		.= " selected";
						break;
				}
				
				$naveeManualPillClass				= " class='".$naveeManualPillClass."'";
				$naveePagesPillClass				= " class='".$naveePagesPillClass."'";
				if (strlen($naveeGuidedPillClass)>0){
					$naveeGuidedPillClass			= " class='".$naveeGuidedPillClass."'";
				}
				
				// Form : Select : Templates
				$vars["templates"] 		= $this->_getTemplateArray(true);
				$vars["template_id"]	= $r->template;
				$templateSelect 		= $this->EE->load->view('/mcp/forms/select/template', $vars, TRUE);
				unset($vars);
				
				// Form : Select : Channels
				$vars["channels"] 		= $this->_getChannelArray();
				$vars["channel_id"]		= $r->channel_id;
				$channelSelect 			= $this->EE->load->view('/mcp/forms/select/channels', $vars, TRUE);
				unset($vars);
				
				// Form : Select : Entries
				if ($r->channel_id > 0){
					$vars["e"] 				= $this->_getEntryObject($r->channel_id);
					$vars["entry_id"]		= $r->entry_id;
					$entrySelect 			= $this->EE->load->view('/mcp/forms/select/entry', $vars, TRUE);
					unset($vars);
				} else {
					$vars["e"]				= "";
					$entrySelect 			= $this->EE->load->view('/mcp/forms/select/entry_empty', $vars, TRUE);
				}
				
				
				// Form : Select : Inc In Nav
				$vars["val"] 			= $r->include;
				$incInNavSelect 		= $this->EE->load->view('/mcp/forms/select/includeInNav', $vars, TRUE);
				unset($vars);
				
				// Form : Select : Passive
				$vars["val"] 			= $r->passive;
				$passiveSelect 			= $this->EE->load->view('/mcp/forms/select/passive', $vars, TRUE);
				unset($vars);
				
				// Form : Select : Target
				$vars["val"] 			= $r->target;
				$targetSelect 			= $this->EE->load->view('/mcp/forms/select/target', $vars, TRUE);
				unset($vars);
				
				// Form : Checkboxes : Member Groups
				$vars["groups"] 		= $this->_getMemberGroups();
				$vars["selected"]		= $this->_getSelectedMemberGroups($id);
				$memberGroupsCheckboxes = $this->EE->load->view('/mcp/forms/checkboxes/member_groups', $vars, TRUE);
				unset($vars);

				// Form for editing nav item
				$returnMe .= "<form class='naveeCPForm' onsubmit='return false;' name='naveeCPForm_".$r->navee_id."'>
									<input type='hidden' name='id' id='naveeCPFormId' value='".$r->navee_id."' />
									<input type='hidden' name='type' value='".$r->type."' class='navee_input_type' />
									<input type='hidden' name='old_parent' value='".$r->parent."' class='old_parent' />
									<ol class='required'>
										<li>
											<label for='navee_text'>".$this->EE->lang->line('cp_form_text')."</label>
											<input type='input' name='navee_text' id='navee_text' value='".htmlspecialchars($r->text, ENT_QUOTES)."' />
										</li>
										
										<li>
											<label for='navee_parent'>".$this->EE->lang->line('cp_form_parent')."<span>".$this->EE->lang->line('cp_form_select_parent')."</span></label>
											<select name='navee_parent'>
												<option value='0'>".$this->EE->lang->line('cp_form_top_level')."</option>
												".$this->_styleNavSelect($this->nav,"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;",$r->parent)."
											</select>
										</li>
										
										<li id='create-link-selector'>
											<label for='navee_type'>".$this->EE->lang->line('cp_form_type')."</label>
											<ul class='pill'>
												<li id='manual'".$naveeManualPillClass.">".$this->EE->lang->line('cp_pill_manual')."</li>
												<li id='guided'".$naveeGuidedPillClass.">".$this->EE->lang->line('cp_pill_guided')."</li>";
				if ($numPages>0){								
												
					$returnMe .=				"<li id='pages'".$naveePagesPillClass.">".$this->EE->lang->line('cp_pill_pages')."</li>";
				}
				$returnMe .=				"</ul>
										</li>

										<li id='naveeLink' class='naveeLinkType".$naveeManualSelected."'>
											<label for='navee_link'>".$this->EE->lang->line('cp_form_link_manual')."</label>
											<input type='input' name='navee_link' value='".$r->link."' />
										</li>
										
										
										
										<li id='naveeGuided' class='naveeLinkType".$naveeGuidedSelected."'>
											<ol>
												<li>
													<label for='navee_templates'>".$this->EE->lang->line('cp_form_link_guided_template')."</label>
													".$templateSelect."
												</li>
												
												<li>
													<label for='navee_channels'>".$this->EE->lang->line('cp_form_link_guided_channel')."</label>
													".$channelSelect."
												</li>
												
												<li>
													<label for='navee_entries' class='naveeEntriesLabel'>".$this->EE->lang->line('cp_form_link_guided_entry').$naveeGuidedEntryLink."</label>
													<select name='navee_entries' id='navee_entries'>";
													
													if ($r->type == "guided"){
														$returnMe .= $entrySelect;
													} else {
														$returnMe .= "<option>".$this->EE->lang->line('cp_form_select_channel')."</option>";
													}
														
					$returnMe .=					"</select>
												</li>
											</ol>
												
										</li>
										
										";

				if ($numPages>0){
						$returnMe .= "<li id='pagesSelect' class='naveeLinkType".$naveePagesSelected."'>
											<label for='navee_pages'>".$this->EE->lang->line('cp_form_link_pages').$naveePagesEntryLink."</label>".$selectPages."
										</li>";
				}

				$returnMe .= "</ol>

								<div class='navee_optional'>
								<h3>".$this->EE->lang->line('cp_mn_optional_items')."</h3>
									<ol id='navee_group_access'>
										<li>
											<label for='navee_include'>".$this->EE->lang->line('cp_form_hdr_disable')."<span>".$this->EE->lang->line('cp_form_include_in')."</span></label>
											".$incInNavSelect."
										</li>
										
										<li>
											<label for='navee_passive'><span>".$this->EE->lang->line('cp_form_passive')."</span></label>
											".$passiveSelect."
										</li>
									</ol>

									<ol id='navee_group_attributes'>
										<li>
											<label for='navee_id'>".$this->EE->lang->line('cp_form_hdr_attributes')."<span>".$this->EE->lang->line('cp_form_id')."</span></label>
											<input type='input' name='navee_id' value='".$r->id."' />
										</li>

										<li>
											<label for='navee_class'><span>".$this->EE->lang->line('cp_form_class')."</span></label>
											<input type='input' name='navee_class' value='".$r->class."' />
										</li>

										<li>
											<label for='navee_rel'><span>".$this->EE->lang->line('cp_form_rel')."</span></label>
											<input type='input' name='navee_rel' value='".$r->rel."' />
										</li>

										<li>
											<label for='navee_name'><span>".$this->EE->lang->line('cp_form_name')."</span></label>
											<input type='input' name='navee_name' value='".$r->name."' />
										</li>

										<li>
											<label for='navee_item_title'><span>".$this->EE->lang->line('cp_form_title')."</span></label>
											<input type='input' name='navee_item_title' value='".$r->title."' />
										</li>
										
										<li>
											<label for='navee_access_key'><span>".$this->EE->lang->line('cp_form_access_key')."</span></label>
											<input type='input' name='navee_access_key' maxlength='1' value='".$r->access_key."' />
										</li>
										
										<li>
											<label for='navee_target'><span>".$this->EE->lang->line('cp_form_target')."</span></label>
											".$targetSelect."
										</li>
									</ol>
									
									<ol id='navee_group_custom'>	
										<li>
											<label for='navee_custom'>".$this->EE->lang->line('cp_form_hdr_custom')."<span>".$this->EE->lang->line('cp_form_custom')."</span></label>
											<input type='input' name='navee_custom' value='".$r->custom."' />
										</li>";
										
				if ($kids > 0){
					$returnMe .=		"<li>
											<label for='navee_custom_kids'><span>".$this->EE->lang->line('cp_form_custom_kids')."</span></label>
											<input type='input' name='navee_custom_kids' value='".$r->custom_kids."' />
										</li>";	
				}

				$returnMe .=			"</ol>
											
									<ol id='navee_group_regex'>
										<li>
											<label for='navee_regex'>".$this->EE->lang->line('cp_form_hdr_regex')."<span>".$this->EE->lang->line('cp_form_regex')."</span></label>
											<input type='input' name='navee_regex' value='".$r->regex."' />
										</li>
									</ol>
									
									<ol id='navee_group_members'>	
										<li>
											<label for='navee_member_groups'>".$this->EE->lang->line('cp_form_hdr_members')."<span>".$this->EE->lang->line('cp_form_member_groups_desc')."</span></label>
											".$memberGroupsCheckboxes."
										</li>
									</ol>
								</div>
								
								<div class='navee_ee_tag'><p>".$this->EE->lang->line('cp_mn_ee_code')."</p><textarea readonly='readonly'>{exp:navee:nav nav_title=\"".$r->nav_title."\" start_node=\"".$r->navee_id."\"}</textarea></div>
								
								<ul class='actions'>
									<li class='optional-btn'><a href='javascript:;' class='navee_optional_btn btn options'>".$this->EE->lang->line('cp_mn_optional')."</a></li>
									<li class='get-code'><a href='javascript:;' class='navee_get_code btn'>".$this->EE->lang->line('cp_mn_get_code')."</a></li>
									<li class='navee_submit'><span class='navee_success' id='navee_success_edit'></span><input type='submit' value='".$this->EE->lang->line('cp_mn_update')."' name='navee_update' class='navee_update_btn btn action' /></li>
								</ul>
								</form>";
				$q->free_result();
				if ($this->EE->input->get("navee_id")>0){
					return $returnMe;
				} else {
					print($returnMe);
					exit;
				}
				
			}
		}
		return false;
	}


	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	//	N E W   N A V I G A T I O N   I T E M   F O R M
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>

	function new_navigation_item_form($id=0){
		$this->EE->load->helper('form');
		
		$returnMe 		= "";
		$ajaxCall 		= false;
		$guidedClass	= "";
		$naveeGuidedSelected 		= "";
		$naveeGuidedPillClass		= "";
		$naveeManualSelected		= "";
		$naveeManualPillClass		= "first";
		$naveePagesSelected			= "";
		$naveePagesPillClass		= "last";
		
		// Determine if this is being build as an AJAX call or on Page Load
		if ($id == 0){
			$ajaxCall = true;
			$id = $this->EE->input->get("id");	
		}
		
		if ($id > 0){

			// Get an array of Pages
			$pages 					= $this->EE->config->item('site_pages');
			$selectPages 			= "";
			
			if (isset($pages[$this->site_id]["uris"])){
				
				$numPages = sizeof($pages[$this->site_id]["uris"]);
				
				// Form : Select : Pages
				$vars["pages"] 					= $pages[$this->site_id]["uris"];
				$vars["entry_id"] 				= "";
				$vars["ee_install_directory"]	= $this->ee_install_directory;
				$vars["include_index"]			= $this->include_index;
				$vars["index_page"]				= $this->EE->config->item('index_page');
				$selectPages 					= $this->EE->load->view('/mcp/forms/select/pages', $vars, TRUE);
						
			} else {
				$numPages 						= 0;
			}
			
			if (!$numPages){
				$naveeGuidedPillClass					= "last";
			}
			
			// Determine which link type to show
			switch ($this->EE->session->flashdata('type')) {
				case "guided":
					$naveeGuidedSelected 		= " naveeLinkSelected";
					
					if (strlen($naveeGuidedPillClass)>0){
						$naveeGuidedPillClass	.= " ";
					}
					
					$naveeGuidedPillClass		.= "selected";
					break;
				case "pages":
					$naveePagesSelected 		= " naveeLinkSelected";
					$naveePagesPillClass		.= " selected";
					break;
				default:
					$naveeManualSelected 		= " naveeLinkSelected";
					$naveeManualPillClass		.= " selected";
					break;
			}
			
			$naveeManualPillClass				= " class='".$naveeManualPillClass."'";
			$naveePagesPillClass				= " class='".$naveePagesPillClass."'";
			if (strlen($naveeGuidedPillClass)>0){
				$naveeGuidedPillClass			= " class='".$naveeGuidedPillClass."'";
			}
			
			// Set the nav variable
			$this->nav = $this->_getNav($id,0,true,true);
			
			// Form : Select : Templates
				$vars["templates"] 		= $this->_getTemplateArray(true);
				$vars["template_id"]	= "";
				$templateSelect 		= $this->EE->load->view('/mcp/forms/select/template', $vars, TRUE);
				unset($vars);
				
			// Form : Select : Channels
				$vars["channels"] 		= $this->_getChannelArray();
				$vars["channel_id"]		= "";
				$channelSelect 			= $this->EE->load->view('/mcp/forms/select/channels', $vars, TRUE);
				unset($vars);
				
			// Form : Select : Inc In Nav
				$vars["val"] 			= "1";
				$incInNavSelect 		= $this->EE->load->view('/mcp/forms/select/includeInNav', $vars, TRUE);
				unset($vars);
			
			// Form : Select : Passive
				$vars["val"] 			= "0";
				$passiveSelect 			= $this->EE->load->view('/mcp/forms/select/passive', $vars, TRUE);
				unset($vars);
			
			// Form : Select : Target
				$vars["val"] 			= $this->EE->session->flashdata('navee_target');
				$targetSelect 			= $this->EE->load->view('/mcp/forms/select/target', $vars, TRUE);
				unset($vars);

			// Form : Checkboxes : Member Groups
				$vars["groups"] 		= $this->_getMemberGroups();
				$vars["selected"]		= array();
				$memberGroupsCheckboxes = $this->EE->load->view('/mcp/forms/checkboxes/member_groups', $vars, TRUE);
				unset($vars);
				
			// Form for editing nav item

			$returnMe .= form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=navee'.AMP.'method=add_navitem_handler');
			$returnMe .= "
							<input type='hidden' name='navee_navigation_id' value='".$id."' />
							<input type='hidden' name='type' value='manual' class='navee_input_type' />
								<ol class='required'>
									<li>
										<label for='navee_text'>".$this->EE->lang->line('cp_form_text')."</label>
										<input type='input' id='navee_text' name='navee_text' value='".$this->EE->session->flashdata('navee_text')."' />
									</li>
									
									<li>
										<label for='navee_parent'>".$this->EE->lang->line('cp_form_parent')."<span>".$this->EE->lang->line('cp_form_select_parent')."</span></label>
										<select name='navee_parent'>
											<option value='0'>".$this->EE->lang->line('cp_form_top_level')."</option>
											".$this->_styleNavSelect($this->nav,"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;",$this->EE->session->flashdata('navee_parent'))."
										</select>
									</li>
									
									<li id='create-link-selector'>
										<label for='navee_type'>".$this->EE->lang->line('cp_form_type')."</label>
										<ul class='pill'>
											<li id='manual'".$naveeManualPillClass.">".$this->EE->lang->line('cp_pill_manual')."</li>
											<li id='guided'".$naveeGuidedPillClass.">".$this->EE->lang->line('cp_pill_guided')."</li>";
			if ($numPages>0){								
											
				$returnMe .=				"<li id='pages'".$naveePagesPillClass.">".$this->EE->lang->line('cp_pill_pages')."</li>";
			}
			$returnMe .=				"</ul>
									</li>

									<li id='naveeLink' class='naveeLinkType".$naveeManualSelected."'>
										<label for='navee_link'>".$this->EE->lang->line('cp_form_link_manual')."</label>
										<input type='input' name='navee_link' value='' />
									</li>
									
									<li id='naveeGuided' class='naveeLinkType".$naveeGuidedSelected."'>
											<ol>
												<li>
													<label for='navee_templates'>".$this->EE->lang->line('cp_form_link_guided_template')."</label>
													".$templateSelect."
												</li>
												
												<li>
													<label for='navee_channels'>".$this->EE->lang->line('cp_form_link_guided_channel')."</label>
													".$channelSelect."
												</li>
												
												<li>
													<label for='navee_guided'>".$this->EE->lang->line('cp_form_link_guided_entry')."</label>
													<select name='navee_entries' id='navee_entries'>
														<option>Start by selecting a channel</option>
													</select>
												</li>
											</ol>
												
										</li>";

			if ($numPages>0){
					$returnMe .= "<li id='pagesSelect' class='naveeLinkType".$naveePagesSelected."'>
											<label for='navee_pages'>".$this->EE->lang->line('cp_form_link_pages')."</label>
											".$selectPages."
										</li>";
			}

			$returnMe .= "</ol>

							<div class='navee_optional'>
							<h3>".$this->EE->lang->line('cp_mn_optional_items')."</h3>
								<ol id='navee_group_access'>
									<li>
										<label for='navee_include'>".$this->EE->lang->line('cp_form_hdr_disable')."<span>".$this->EE->lang->line('cp_form_include_in')."</span></label>
										".$incInNavSelect."
									</li>
									
									<li>
										<label for='navee_passive'><span>".$this->EE->lang->line('cp_form_passive')."</span></label>
										".$passiveSelect."
									</li>
								</ol>
								
								<ol id='navee_group_attributes'>
									<li>
										<label for='navee_id'>".$this->EE->lang->line('cp_form_hdr_attributes')."<span>".$this->EE->lang->line('cp_form_id')."</span></label>
										<input type='input' name='navee_id' value='' />
									</li>

									<li>
										<label for='navee_class'><span>".$this->EE->lang->line('cp_form_class')."</span></label>
										<input type='input' name='navee_class' value='' />
									</li>

									<li>
										<label for='navee_rel'><span>".$this->EE->lang->line('cp_form_rel')."</span></label>
										<input type='input' name='navee_rel' value='' />
									</li>

									<li>
										<label for='navee_name'><span>".$this->EE->lang->line('cp_form_name')."</span></label>
										<input type='input' name='navee_name' value='' />
									</li>

									<li>
											<label for='navee_item_title'><span>".$this->EE->lang->line('cp_form_title')."</span></label>
											<input type='input' name='navee_item_title' value='' />
									</li>
									
									<li>
										<label for='navee_access_key'><span>".$this->EE->lang->line('cp_form_access_key')."</span></label>
										<input type='input' name='navee_access_key' maxlength='1' value='' />
									</li>
									
									
									<li>
										<label for='navee_target'><span>".$this->EE->lang->line('cp_form_target')."</span></label>
										".$targetSelect."
									</li>
								</ol>
								
								
								<ol id='navee_group_custom'>	
									<li>
										<label for='navee_custom'>".$this->EE->lang->line('cp_form_hdr_custom')."<span>".$this->EE->lang->line('cp_form_custom')."</span></label>
										<input type='input' name='navee_custom' value='' />
									</li>
								</ol>
		
								<ol id='navee_group_regex'>
									<li>
										<label for='navee_regex'>".$this->EE->lang->line('cp_form_hdr_regex')."<span>".$this->EE->lang->line('cp_form_regex')."</span></label>
										<input type='input' name='navee_regex' value='' />
									</li>
								</ol>
								
								<ol id='navee_group_members'>	
									<li>
											<label for='navee_member_groups'>".$this->EE->lang->line('cp_form_hdr_members')."<span>".$this->EE->lang->line('cp_form_member_groups_desc')."</span></label>
											".$memberGroupsCheckboxes."
									</li>

								</ol>
							</div>
							<ul class='actions'>
								<li class='optional-btn'><a href='javascript:;' class='navee_optional_btn btn options'>".$this->EE->lang->line('cp_mn_optional')."</a></li>
								<li class='navee_submit'><span class='navee_success' id='navee_success_add'></span><input type='submit' id='add-to-nav-btn' class='btn action' value='".$this->EE->lang->line('cp_mn_submit_add')."' name='navee_submit' /></li>
							</ul>";
			$returnMe .= form_close();
			if ($ajaxCall){
				print($returnMe);
				exit;
			} else {
				return $returnMe;
			}
			
		} else {
			return false;
		}
	}
	
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	//	G E T   C H A N N E L   E N T R I E S
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>

	function get_channel_entries(){
		
		$entries = "";

		if ($this->EE->input->get("id") && $this->EE->input->get("channel_id")){
			
			$q = $this->_getEntryObject($this->EE->input->get("channel_id"));
			
			if ($q->num_rows() > 0){
				
				foreach ($q->result() as $r){
					$entries .= "<option value='".$r->entry_id."'>".$r->title."</option>";
				}
				
				$q->free_result();
				$data[] = array(
							"entries"	=> $entries,
							"id"		=> $this->EE->input->get("id")
				);
				print($this->EE->javascript->generate_json($data));
				exit;
			}
		}

		return false;
	}

	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	//	G E T   P A R E N T   S E L E C T   B Y   I D
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>

	function get_parent_select_by_id(){
		
		if ($this->EE->input->get("id")){
			
			$this->nav = $this->_getNav($this->EE->input->get("id"),0,true,true,true);

			$data[] = array(
						"options"	=> $this->_styleNavSelect($this->nav)
			);
				print($this->EE->javascript->generate_json($data));
				exit;
		}

		return false;
	}


	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	//	D E L E T E   N A V I G A T I O N   I T E M
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>

	function delete_navigation_item(){
		
		$error = false;
		
		// Let's do some validation
		if (!$this->EE->input->get("id")){		
			$this->hasErrors = true;
			$this->_addMessage($this->EE->lang->line('cp_err_unknown'));	
		}

		if ($this->hasErrors){

			// If we found errors, let's throw some messages around
			$this->EE->session->set_flashdata('message_failure', $this->message);
			$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=navee'.AMP.'method=manage_navigation&id='.$this->EE->input->get("navid"));
		} else {
			
			// Everything looks ok, delete the navigation
			
			// Let's first figure out if this item has any child elements
			$nav 	= $this->_getNav($this->EE->input->get("navid"), $this->EE->input->get("id"),true, true); 
			if (sizeof($nav)>0){
				// If there are child elements, let's delete them as well
				$this->_delete_navigation_child_items($nav);
			}


				
			// Delete the current nav item
			$data = array(
				'navee_id' 		=> $this->EE->input->get("id")
			);

			$this->EE->db->delete('navee', $data);
			
			// Delete any cached data associated with this nav
			$this->_clearCache($this->EE->input->get("navid"));
			
			// Other stuff
			$this->EE->session->set_flashdata('message_success', $this->EE->lang->line('cp_suc_delete_navItem'));
			$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=navee'.AMP.'method=manage_navigation&id='.$this->EE->input->get("navid"));

		}
	}
		

	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	//	S O R T   N A V I G A T I O N   I T E M S
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>

	function sort_navigation_items(){
		if ($this->EE->input->get("id")){
			$chunk = split(",", $this->EE->input->get("id"));
			$sort = 1;
			
			$this->EE->db->select("navigation_id");
			$this->EE->db->where("navee_id", $this->EE->input->get("id"));
			$q = $this->EE->db->get("navee");

			if ($q->num_rows() == 1){
				$r 				= $q->row();
				$navigation_id 	= $r->navigation_id; 
			}
			
			foreach ($chunk as $k=>$v){
				$bit = split("_", $v);
				$parent 	= $bit[0];
				$navee_id 	= $bit[1];

				$data = array(
					'parent' 			=> $parent, 
					'sort'				=> $sort
				);

				$this->EE->db->where('navee_id', $navee_id);
				$this->EE->db->update('navee', $data); 
				$sort++;
			}
			
			// Delete any cached data associated with this nav
			$this->_clearCache($navigation_id);
				
			return true;
		} else {
			return false;
		}
	}

	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	//	D E L E T E   N A V I G A T I O N 
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>

	function delete_navigation(){
		
		$error = false;
		
		// Let's do some validation
		if (!$this->EE->input->get("id")){
			$this->hasErrors = true;
			$this->_addMessage($this->EE->lang->line('cp_err_unknown'));
		}

		if ($this->hasErrors){

			// If we found errors, let's throw some messages around
			$this->EE->session->set_flashdata('message_failure', $this->message);
			$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=navee'.AMP.'method=index');
		} else {

			// Everything looks ok, delete the navigation
			$data = array(
				'navigation_id' 	=> $this->EE->input->get("id")
			);

			$this->EE->db->delete('navee_navs', $data);

			$this->EE->session->set_flashdata('message_success', $this->EE->lang->line('cp_suc_delete_nav'));
			$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=navee'.AMP.'method=index');
		}
	}

	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	//	U P D A T E   N A V I G A T I O N 
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>

	function update_navigation(){

		$error = false;

		$types = array(
				'0' 	=> 'name',
				'1'		=> 'title',
				'2'		=> 'description'
			);

		// Let's do some validation
		if (!($this->EE->input->get("id") && $this->EE->input->get("type") && $this->EE->input->get("content")) && in_array($this->EE->input->get("type"), $types)){
			$this->hasErrors = true;
			$this->_addMessage($this->EE->lang->line('cp_err_unknown'));
		}

		if ($this->EE->input->get("type")=="title"){
			if (!preg_match('/^[a-zA-Z0-9-_]+$/', $this->EE->input->get("content"))){
				$this->hasErrors = true;
				$this->_addMessage($this->EE->lang->line('cp_err_title_format'));
			}
		}

		if ($this->hasErrors){
			// If we found errors, let's throw some messages around
			print($this->message);
			exit;
		} else {

			// Everything looks ok, update the navigation
			$cur_date = date('Y-m-d H:i:s');

			$data = array();
			$data['dateupdated'] 	= $cur_date;
			$data['member_id']		= $this->EE->session->userdata['member_id'];

			switch ($this->EE->input->get("type")) {
				case "name":
				$data['nav_name'] = $this->EE->input->get("content");
				break;
			case "title":
				$data['nav_title'] = $this->EE->input->get("content");
				break;
			case "description":
				$data['nav_description'] = $this->EE->input->get("content");
				break;
			}

			$this->EE->db->where('navigation_id', $this->EE->input->get("id"));
			$this->EE->db->update('navee_navs', $data);

			exit;

		}
	}

	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	//	G E T   N A V
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>

	function _getNav($navId, $parent=0, $recursive=true, $ignoreInclude=false, $skipOwnKids=false, $skipID=0){

		$nav = array();

		$this->EE->db->select("n.navee_id,
							   n.parent,
							   n.text,
							   n.link,
							   n.class,
							   n.id,
							   n.sort,
							   n.include,
							   n.rel,
							   n.name,
							   n.target,
							   n.regex,
							   nn.nav_title");
		$this->EE->db->from("navee AS n");
		$this->EE->db->join("navee_navs AS nn", "n.navigation_id=nn.navigation_id");
		$this->EE->db->where("n.navigation_id", $navId);
		$this->EE->db->where("n.parent", $parent);
		$this->EE->db->where("n.site_id", $this->site_id);
		$this->EE->db->order_by("n.sort", "asc");
		if (!$ignoreInclude){
			$this->EE->db->where("n.include", 1);
		}
		$q = $this->EE->db->get();
		if ($q->num_rows() > 0){
			$count = 0;
			foreach ($q->result() as $r){
				$nav[$count]["navee_id"] 	= $r->navee_id;
				$nav[$count]["parent"] 		= $r->parent;
				$nav[$count]["text"] 		= $r->text;
				$nav[$count]["link"] 		= $r->link;
				$nav[$count]["class"] 		= $r->class;
				$nav[$count]["id"] 			= $r->id;
				$nav[$count]["sort"] 		= $r->sort;
				$nav[$count]["include"] 	= $r->include;
				$nav[$count]["rel"]			= $r->rel;
				$nav[$count]["name"]		= $r->name;
				$nav[$count]["target"]		= $r->target;
				$nav[$count]["regex"]		= $r->regex;
				$nav[$count]["nav_title"]	= $r->nav_title;

				if ($recursive){
					if ($skipOwnKids){
						if ($skipID == $r->navee_id){
							$nav[$count]["kids"] = array();
						} else {
							$nav[$count]["kids"] = $this->_getNav($navId, $r->navee_id, $recursive, $ignoreInclude, $skipOwnKids, $skipID);
						}
					} else {
						$nav[$count]["kids"] = $this->_getNav($navId, $r->navee_id, $recursive, $ignoreInclude, $skipOwnKids, $skipID);
					}
					
				} else {
					$nav[$count]["kids"] = array();
				}
				$count++;
			}
		}
		$q->free_result();

		return $nav;
	}
	

	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	//	G E T   N A V E E   N A V S 
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>

	function _getNaveeNavs(){

		$navee_navs				= array();
		$blockedMemberGroups	= $this->blockedMemberGroups;
		$memberGroup			= $this->EE->session->userdata['group_id'];
		$hasAccess				= true;
	
		$this->EE->db->select("*");
		$this->EE->db->order_by("nav_name", "ASC");
		$this->EE->db->where("site_id", $this->site_id);
		$q = $this->EE->db->get("navee_navs");
		if ($q->num_rows() > 0){
			foreach ($q->result() as $r){
				$hasAccess = true;
				if (isset($blockedMemberGroups[$r->navigation_id])){
					if (in_array($memberGroup, $blockedMemberGroups[$r->navigation_id])){
						$hasAccess = false;
					}
				}
				
				if ($hasAccess){
					$navee_navs[$r->navigation_id]["navigation_id"] 	= $r->navigation_id;
					$navee_navs[$r->navigation_id]["nav_title"] 		= $r->nav_title;
					$navee_navs[$r->navigation_id]["nav_name"] 			= $r->nav_name;
					$navee_navs[$r->navigation_id]["nav_description"] 	= $r->nav_title;
				}
			}
		}
		$q->free_result();

		return $navee_navs;
	}
	
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	//	G E T   T E M P L A T E   A R R A Y
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>

	function _getTemplateArray($excludeBlocked=false){
		$templates = array();
		$this->EE->db->select("t.template_name, t.template_id, tg.group_name");
		$this->EE->db->from("templates AS t");
		$this->EE->db->join("template_groups tg", "t.group_id=tg.group_id");
		$this->EE->db->where("t.site_id", $this->site_id);
		
		if ($excludeBlocked && (sizeof($this->blockedTemplates)>0)){
			$this->EE->db->where_not_in("t.template_id", $this->blockedTemplates);
		}
		
		$this->EE->db->order_by("tg.group_name, t.template_name");
		$q = $this->EE->db->get();
		
		$group = "";
		$i = 1;
		
		if ($q->num_rows() > 0){
			foreach ($q->result() as $r){
				if (strtolower($r->group_name) !== $group){
					$group = strtolower($r->group_name);
					$i = 1;	
				}
				if ($r->template_name == "index"){
					$templates[$group][0]["id"] 	= $r->template_id;
					$templates[$group][0]["name"] 	= $r->template_name;
				} else {
					$templates[$group][$i]["id"] 	= $r->template_id;
					$templates[$group][$i]["name"] 	= $r->template_name;
					$i++;
				}
			}
		}
		
		$q->free_result();
		
		foreach ($templates as $k=>$v){
			$hasIndex 	= false;
			$tempArray	= array();
			
			foreach($v as $kk=>$vv){
				if ($vv["name"] == "index"){
					$hasIndex = true;
				}
			}
			
			if (!$hasIndex){
				
				unset($tempArray);
				$count 		= 0;
				$tempArray 	= array();
				
				foreach($v as $kk=>$vv){
					$tempArray[$count] = $vv;
					$count++;
				}
				
				unset($templates[$k]);
				$templates[$k] = $tempArray;
			}
		}
		
		ksort($templates);
		
		return $templates;
	}
		
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	//	G E T   C H A N N E L   A R R A Y
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>

	function _getChannelArray(){
		$channels = array();
		$this->EE->db->select("channel_id, channel_title");
		$this->EE->db->where("site_id", $this->site_id);
		$this->EE->db->order_by("channel_title", "ASC");
		$q = $this->EE->db->get("channels");
		
		$i = 0;
		
		if ($q->num_rows() > 0){
			foreach ($q->result() as $r){
				$channels[$i]["id"] 		= $r->channel_id;
				$channels[$i]["title"]		= $r->channel_title;
				$i++;
			}
		}
		
		$q->free_result();
		
		return $channels;
	}
	
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	//	G E T   E N T R Y   O B J E C T
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>

	function _getEntryObject($channel_id){
		
		$this->EE->db->select("entry_id, title");
		$this->EE->db->where("site_id", $this->site_id);
		$this->EE->db->where("channel_id", $channel_id);
		$this->EE->db->order_by("title", "asc");
		$q = $this->EE->db->get("channel_titles");		
		return $q;
	}
	
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	//	G E T   M E M B E R   G R O U P S
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>

	function _getMemberGroups($can_access_cp_only=false){

		$groups = array();

		$this->EE->db->select("group_id, group_title");
		$this->EE->db->order_by("group_title", "ASC");
		$this->EE->db->where("site_id", $this->site_id);
		
		if ($can_access_cp_only){
			$this->EE->db->where("can_access_cp","y");	
			$this->EE->db->where("group_id !=",1);
		}
		
		$q = $this->EE->db->get("member_groups");
		if ($q->num_rows() > 0){
			foreach ($q->result() as $r){
				$groups[$r->group_id]["group_id"] 		= $r->group_id;
				$groups[$r->group_id]["group_title"] 	= $r->group_title;
			}
		}
		$q->free_result();

		return $groups;
	}
	
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	//	G E T   S E L E C T E D   M E M B E R   G R O U P S
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>

	function _getSelectedMemberGroups($navee_id){

		$groups = array();

		$this->EE->db->select("members");
		$this->EE->db->where("site_id", $this->site_id);
		$this->EE->db->where("navee_id", $navee_id);
		$q = $this->EE->db->get("navee_members");
		if ($q->num_rows() > 0){
			$row = $q->row();
			$groups = unserialize($row->members);
		}
		$q->free_result();

		return $groups;
	}
	
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~------------->>
	//	G E T   N A V  I T E M   A R R A Y   B Y   E N T R Y   I D
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~------------->>

	function _getNavItemArrayByEntryId($id){
		
		$navItems = array();
		
		if ($id > 0){
			$this->EE->db->select("n.navigation_id, n.navee_id, n.text, nn.nav_name");
			$this->EE->db->from("navee AS n");
			$this->EE->db->join("navee_navs AS nn", "nn.navigation_id = n.navigation_id", "LEFT OUTER");
			$this->EE->db->where("n.site_id", $this->site_id);
			$this->EE->db->where("n.entry_id", $id);
			$this->EE->db->where("nn.navigation_id >",0);
			$this->EE->db->order_by("nn.nav_name", "asc");
			$q = $this->EE->db->get("");
			
			
			if ($q->num_rows() > 0){
				foreach ($q->result() as $r){
					$navItems[$r->nav_name][$r->navee_id]["text"] 			= $r->text;
					$navItems[$r->nav_name][$r->navee_id]["navigation_id"] 	= $r->navigation_id;
					$navItems[$r->nav_name][$r->navee_id]["navee_id"] 		= $r->navee_id;
				}
			}
			$q->free_result();
		}
		
		return $navItems;
	}
	
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~------------->>
	//	G E T   C H A N N E L  I D
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~------------->>

	function _getChannelId($entry_id){
		
		$channel_id = "";
		
		if ($entry_id > 0){
			$this->EE->db->select("channel_id");
			$this->EE->db->from("channel_data");
			$this->EE->db->where("site_id", $this->site_id);
			$this->EE->db->where("entry_id", $entry_id);
			$q = $this->EE->db->get("");
			
			if ($q->num_rows() > 0){
				$r = $q->row();
				$channel_id = $r->channel_id;	
			}
		}
		
		return $channel_id;
	}
	
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	//	S T Y L E   N A V E E   N A V S 
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>

	function _styleNaveeNavs($navs){

		$returnMe = "<ul id='nav-nav'>";
		$manage_nav_link = BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=navee'.AMP.'method=manage_navigation';

		foreach ($navs as $k=>$v){
			$returnMe .= "<li><a href='".$manage_nav_link."&id=".$v["navigation_id"]."' title='".$this->EE->lang->line('cp_edit')." ".$this->EE->lang->line('cp_navigation_group').": ".$v["nav_name"]."'>".$v["nav_name"]."</a><li>";
		}

		$returnMe .= "<li class='nav-nav-add'><a href='".BASE.AMP."C=addons_modules".AMP."M=show_module_cp".AMP."module=navee".AMP."method=add_navigation"."' title='".$this->EE->lang->line('cp_mn_add_nav_group')."'>+</a></li>";
		
		$returnMe .= "</ul>";
		
		return $returnMe;
	}
	
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	//	S T Y L E   N A V   C P
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>

	function _styleNavCP($nav,$navee_id=0){
		$returnMe = "";

		if (sizeof($nav)>0){

			$returnMe .= "<ul>";
			
			foreach ($nav as $k=>$v){
				$selected_class = "";
				if ($navee_id == $v["navee_id"]){
					$selected_class = " class='selected'";
				}
				// Open the <li> for our nav item
				$returnMe .= "<li id='".$v["navee_id"]."'".$selected_class."><div><span class='navee_cp_text'>".$v["text"]."</span>";

				// Edit / Delete functionality
				$returnMe .= "<a class='navee_trash icn' title='".$this->EE->lang->line('cp_delete')."' href='javascript:;'>X</a><a class='navee_edit icn' title='".$this->EE->lang->line('cp_edit')."' href='javascript:;'>Edit</a></div>";
								
				// If our nav item has kids, let's recurse
				if (sizeof($v["kids"])>0){
					$returnMe .= $this->_styleNavCP($v["kids"],$navee_id);
				}

				// Close out the </li>
				$returnMe .= "</li>";
			}
			$returnMe .= "</ul>";
		}
		return $returnMe;
	}

	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	//	S T Y L E   N A V   S E L E C T
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>

	function _styleNavSelect($nav,$spaces="",$selected=""){
		$returnMe 	= "";
		$vars		= array();

		if (sizeof($nav)>0){
			foreach ($nav as $k=>$v){
				
				// Check to see if this is the selected item
				if ($v["navee_id"] == $selected){
					$vars["selected"] = "true";
				} else {
					$vars["selected"] = "false";
				}
				
				$vars["navee_id"] 	= $v["navee_id"];
				$vars["spaces"]		= $spaces;
				$vars["text"]		= $v["text"];
				
				$returnMe .= $this->EE->load->view('/mcp/forms/select/option/parent', $vars, TRUE);
				unset($vars);
				
				if (sizeof($v["kids"])>0){
					$returnMe .= $this->_styleNavSelect($v["kids"],$spaces."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;",$selected);
				}
			}
		}
		return $returnMe;
	}
	
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	//	S E R I A L I Z E   M E M B E R   G R O U P S
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>

	function _serializeMemberGroups($fields){
		$groups = array();
		$getVar = array();
		$count = 0;
		foreach($fields as $k=>$v){
			
			$getVar = explode("_", $k);
			if (sizeof($getVar) > 0){
				if ($getVar[0] == "memGroup"){
					$groups[$count] = $getVar[1];
					$count++;
				}
			}
		}
		
		if (sizeof($groups) > 0){
			return serialize($groups);
		} else {
			return "";
		}
		
	}
	
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	//	D E L E T E   N A V I G A T I O N   C H I L D   I T E M S
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	
	function _delete_navigation_child_items($nav){
		foreach($nav as $k=>$v){
			if (sizeof($v["kids"])>0){
				$this->_delete_navigation_child_items($v["kids"]);
			}
			
			$data = array(
				'navee_id' 		=> $v["navee_id"]
			);

			$this->EE->db->delete('navee', $data);
		}
	}	
	
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	//	C L E A R   C A C H E
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	
	function _clearCache($navigation_id=0){
		// Delete any cached data
		
		if ($navigation_id > 0){
			$data = array(
				'navigation_id' 		=> $navigation_id,
				'site_id' 				=> $this->site_id
			);
		} else {
			$data = array(
				'site_id' 				=> $this->site_id
			);
		}
		$this->EE->db->delete('navee_cache', $data);
		
		
		//check to see if CE Cache is installed
		if ($this->ce_cache_installed){
    		//include the cache_break class
			if (!class_exists('Ce_cache_break')){
        		include PATH_THIRD . 'ce_cache/libraries/Ce_cache_break.php';
        	}

			//instantiate the class
			$cache_break = new Ce_cache_break();

				//the cache items to remove or refresh
			$items = array();

			//item tag names that you would like to remove or refresh
			$tags = array("navee");

			//whether or not to refresh the local items after they are cleared
			$refresh = TRUE;

	        //the number of seconds to wait between refreshing (and deleting)
	        //items. Only applicable if refreshing.
	        $refresh_time = 1;

			$cache_break->break_cache($items, $tags, $refresh, $refresh_time);
		}
	
		return true;
	}

	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	//	N E X T   S O R T
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>

	function _nextSort($navigation_id, $parent=0){
	 	$this->EE->db->select("sort");
	 	$this->EE->db->where("parent", $parent);
	 	$this->EE->db->where("navigation_id", $navigation_id);
	 	$this->EE->db->order_by("sort", "desc");
	 	$q = $this->EE->db->get("navee", 1);
	 	if ($q->num_rows() == 1){
			$r = $q->row();
			return ($r->sort + 1);
		} else {
			return 1;
		}
		$q->free_result();
	}
	
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	//	I S   P A G E S   I N S T A L L E D
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	
	function _isPagesInstalled(){	
		
		$this->EE->db->from("modules");
		$this->EE->db->where("module_name", "Pages");
		
		if ($this->EE->db->count_all_results()>0){
			return true;
		} else {
			return false;
		}
	}

	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	//	A D D   M E S S A G E
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>

	function _addMessage($msg){
		if (strlen($this->message) > 0){
			$this->message .= "<br />";
		} else {
			$this->message .= "<h2>NavEE Errors</h2>";
		}

		$this->message .= $msg;
	}
	
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	//	N A V E E   D E C O D E
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>

	function _naveeDecode($str){
		$str = str_replace("__navEE__3f", "?", $str);
		$str = str_replace("__navEE__3b", ";", $str);
		$str = str_replace("__navEE__3a2f2f", "://", $str);
		$str = str_replace("__navEE__2e", ".", $str);
		return $str;
	}
	
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	//	F O R M A T   I N S T A L L   D I R E C T O R Y
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>

	function _formatInstallDirectory($dir){
		$dir = preg_replace('/^\//i', '', $dir);
		$dir = preg_replace('/\/$/i', '', $dir);
		return $dir;	
	}
}
?>
