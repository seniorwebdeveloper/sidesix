<?php
/*
_|                                                      _|
_|_|_|     _|_|     _|_|   _|    _|   _|_|_| _|_|_|   _|_|_|_|
_|    _| _|    _| _|    _| _|    _| _|    _| _|    _|   _|
_|    _| _|    _| _|    _| _|    _| _|    _| _|    _|   _|
_|_|_|     _|_|     _|_|     _|_|_|   _|_|_| _|    _|     _|_|
                                 _|
                             _|_|

Description:		NavEE Fieldtype for Expression Engine 2.x
Developer:			Booyant, Inc.
Website:			www.booyant.com/navee
Location:			./system/expressionengine/third_party/modules/navee/ft.navee.php
Contact:			its.go.time@booyant.com  / 978.OKAY.BOB
*/


if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// Include config file
require_once PATH_THIRD.'navee/config'.EXT;

class Navee_ft extends EE_Fieldtype {
	
	var $info = array(
		'name'		=> 'NavEE',
		'version'	=> NAVEE_VERSION
	);
	
	
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	//	D I S P L A Y   F I E L D
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	
	function display_field($data){

		include_once("mod.navee.php");
		include_once("mcp.navee.php");
		$mod				= new Navee();
		$mcp				= new Navee_mcp();
		$vars 				= array();
		
		// JS | CSS | Language File
			$this->_naveeJS();
			$this->_naveeCSS();
			$this->EE->lang->loadfile('navee');
		
		// Form : Select : Navs
			$vars["navs"] 			= $mcp->_getNaveeNavs();
			$navsSelect				= $this->EE->load->view('/mcp/forms/select/navs', $vars, TRUE);
			unset($vars);
		
		// Form : Select : Templates
			$vars["templates"] 		= $mcp->_getTemplateArray(true);
			$vars["template_id"]	= $this->EE->input->post("navee_templates");
			if ($mcp->_isPagesInstalled()){
				$vars["addPagesOption"]	= "true";
			}
			$templateSelect 		= $this->EE->load->view('/mcp/forms/select/template', $vars, TRUE);
			unset($vars);

		// Field
		$vars["navsSelect"] 		= $navsSelect;
		$vars["templateSelect"]		= $templateSelect;
		$vars["text"]				= $this->EE->input->get("naveeText");
		$vars["manageNavLink"]		= BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=navee'.AMP.'method=manage_navigation';
		$vars["navItems"]			= $mcp->_getNavItemArrayByEntryId($this->EE->input->get("entry_id"));
		
		return $this->EE->load->view('/ft/fieldtype', $vars, TRUE);		
	}
	
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	//	P O S T   S A V E
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	
	function post_save($data){
		$this->EE =& get_instance();
		$this->EE->lang->loadfile('navee');
		
		if ($this->EE->input->post("naveeNav") > 0) {
		
			include_once("mod.navee.php");
			include_once("mcp.navee.php");
			
			$mod      = new Navee();
			$mcp      = new Navee_mcp();
			$cur_date = date('Y-m-d H:i:s');
			$type     = "guided";
			$template = $this->EE->input->post("navee_templates");
			
			if ($this->EE->input->post("navee_templates") == "pages"){
				$type     = "pages";
				$template = 0;
			}
			
			$data 		= array(
					'navigation_id' => $this->EE->input->post("naveeNav"),
					'site_id'       => $this->settings["site_id"], 
					'entry_id'      => $this->settings["entry_id"],
					'channel_id'    => $this->EE->input->post("channel_id"),
					'template'      => $template,
					'type'          => $type,
					'parent'        => $this->EE->input->post("naveeParent"),
					'text'          => $this->EE->input->post("naveeText"),
					'sort'          => $mcp->_nextSort($this->EE->input->post("naveeNav"), $this->EE->input->post("naveeParent")),
					'include'       => 1,
					'passive'       => 0,
					'datecreated'   => $cur_date,
					'dateupdated'   => $cur_date,
					'member_id'     => $this->EE->session->userdata['member_id'],
					'ip_address'    => $this->EE->input->ip_address(),
					'link'			=> '',
					'class'			=> '',
					'id'			=> '',
					'rel'			=> '',
					'name'			=> '',
					'target'		=> '',
					'regex'			=> '',
					'custom'		=> '',
					'custom_kids'	=> '',
					'access_key'	=> '',
					'title'			=> ''
				);
	
			$this->EE->db->insert('navee', $data);
			$mcp->_clearCache($this->EE->input->post("naveeNav"));
		}
		
	}
	
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	//	V A L I D A T E
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	
	function validate($data){
		$this->EE->lang->loadfile('navee');
		
		// Start by storing flash data for all items in case there is an error
		$this->EE->session->set_flashdata('naveeText', $this->EE->input->post("naveeText"));
		
		if (strlen($this->EE->input->post("naveeText"))>0){
			if (!($this->EE->input->post("naveeNav") > 0)){
				return $this->EE->lang->line('ft_err_select_navigation');
			}
			
			if (($this->EE->input->post('navee_templates') == "pages") && 
				(
					($this->EE->input->post('pages__pages_uri') == "/example/pages/uri/") || 
					($this->EE->input->post('pages__pages_uri') == "")
				)){
			
				return $this->EE->lang->line('ft_err_pages');
			}
			
			if ($this->EE->input->post('navee_templates') == "0"){
				return $this->EE->lang->line('ft_err_valid_template');
			}
			
		}
		return true;
	}
	
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	//	R E P L A C E   T A G
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	
	function replace_tag($data, $params = array(), $tagdata = FALSE){
	}
	
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	//	D I S P L A Y   G L O B A L    S E T T I N G S
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	
	//function display_global_settings(){		
	//}
	
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	//	S A V E   G L O B A L   S E T T I N G S
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	/*
	
	function save_global_settings(){
		return array_merge($this->settings, $_POST);
	}
	*/
	
	
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	//	D I S P L A Y   S E T T I N G S
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	
	function display_settings($data){
	}
	
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	//	S A V E   S E T T I N G S
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	
	function save_settings($data){
		return false;
	}
	
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	//	I N S T A L L
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	
	function install(){
		return array();
	}
	
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	//	J A V A S C R I P T
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	
	function _naveeJS(){		
		$this->EE->cp->load_package_js('navee');
	}
	
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	//	C S S
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	
	function _naveeCSS(){	
		$this->EE->cp->add_to_head('<link rel="stylesheet" type="text/css" href="'.$this->EE->config->item('theme_folder_url').$this->_themeFolderDirectory().'navee/css/navee_fieldtype.css" />');
	}
	
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	//	T H E M E   F O L D E R   D I R E C T O R Y
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	
	function _themeFolderDirectory(){
		// Figure out if the theme is in the third_party folder or not
		$directory = "";
		
		if (is_dir($this->EE->config->item('theme_folder_path').'third_party/navee')){
			$directory = "third_party/";
		}
		
		return $directory;
	}
	

	
}