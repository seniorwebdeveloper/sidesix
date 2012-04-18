<?php 
/*
_|                                                      _|
_|_|_|     _|_|     _|_|   _|    _|   _|_|_| _|_|_|   _|_|_|_|
_|    _| _|    _| _|    _| _|    _| _|    _| _|    _|   _|
_|    _| _|    _| _|    _| _|    _| _|    _| _|    _|   _|
_|_|_|     _|_|     _|_|     _|_|_|   _|_|_| _|    _|     _|_|
                                 _|
                             _|_|

Description:		NavEE Extension for Expression Engine 2.x
Developer:			Booyant, Inc.
Website:			www.booyant.com/navee
Location:			./system/expressionengine/third_party/modules/navee/ext.navee.php
Contact:			navee@booyant.com  / 978.OKAY.BOB

*/

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// Include config file
require_once PATH_THIRD.'navee/config'.EXT;

class Navee_ext {

	var $name					= "NavEE Extension";
    var $description			= "Clears appropriate caches when entries are updated";
    var $settings_exist			= 'n';
    var $docs_url				= 'http://booyant.com/navee';
    var $version				= NAVEE_VERSION;
    
    var $settings				= array();
    var $site_id				= 1;
    var $remove_deleted_entries = false;
    
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	//	C O N S T R U C T O R
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>

 	function __construct($settings=''){
		$this->EE 			=& get_instance();
		$this->settings		= $settings;
		$this->site_id 		= $this->EE->config->item('site_id');

		if ($this->EE->db->table_exists('navee_config')){
			$this->EE->db->select("k,v");
			$this->EE->db->where("site_id", $this->site_id);
			$this->EE->db->where("k", "remove_deleted_entries");
			$q = $this->EE->db->get("navee_config");
				
			if ($q->num_rows() > 0){
				$r = $q->row();
				$this->remove_deleted_entries = $r->v;
			}
		}

    }
    
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	//	A C T I V A T E   E X T E N S I O N
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>

	function activate_extension(){
		
		$hooks = array(
			'entry_submission_end'	=> 'entry_submission_end',
			'delete_entries_loop'	=> 'delete_entries_loop'
		);
		
		foreach($hooks as $k=>$v){
			$data = array(
				'class'     => __CLASS__,
				'method'    => $v,
				'hook'      => $v,
				'settings'  => serialize($this->settings),
				'priority'  => 10,
				'version'   => $this->version,
				'enabled'   => 'y'
			);
		
			$this->EE->db->insert('extensions', $data);
		}


	}
	
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	//	U P D A T E   E X T E N S I O N
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	
	function update_extension($current = ''){
		if ($current == '' OR $current == $this->version){
			return FALSE;
		}
		
		$this->EE->db->where('class', __CLASS__);
		$this->EE->db->update(
			'extensions',
			array('version' => $this->version)
		);
	}
	
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	//	D I S A B L E   E X T E N S I O N
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	function disable_extension(){
		$this->EE->db->where('class', __CLASS__);
		$this->EE->db->delete('extensions');
	}
	
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	//	S E T T I N G S
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	
	function settings(){
		$settings = array();
		
		// No settings at this time
		
		return $settings;
	}
	
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	//	E N T R Y   S U B M I S S I O N   E N D
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	
	function entry_submission_end($id,$meta,$data){
		
		$this->_clear_cache($id);
		return true;
		
	}
	
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	//	D E L E T E   E N T R I E S   L O O P
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	
	function delete_entries_loop($val, $channel_id){
		
		$this->_clear_cache($val);
		
		if ($this->remove_deleted_entries == "true"){
			
			include_once("mcp.navee.php");
			$mcp		= new Navee_mcp();
			
			
			// Let's first figure out if this item has any child elements
			$this->EE->db->select("navee_id, navigation_id");
			$this->EE->db->where("entry_id", $val);
			$this->EE->db->where("site_id", $this->site_id);
			$q = $this->EE->db->get("navee");
			
			if ($q->num_rows() > 0){
				foreach ($q->result() as $r){
					$nav 	= $mcp->_getNav($r->navigation_id, $r->navee_id,true, true); 
					if (sizeof($nav)>0){
						// If there are child elements, let's delete them as well
						$mcp->_delete_navigation_child_items($nav);
					}
				
				}
			}	
			
			// Now that all the child elements are deleted - let's delete the main ones
			$this->EE->db->where("entry_id", $val);
			$this->EE->db->where("site_id", $this->site_id);
			$this->EE->db->delete("navee");
			
		}
		
		return true;
	}
	
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	//	C L E A R   C A C H E
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	
	function _clear_cache($id){
		
		$navIDs = array();
		
		$this->EE->db->select("navigation_id");
		$this->EE->db->where("site_id", $this->site_id);
		$this->EE->db->where("entry_id", $id);
		$q = $this->EE->db->get("navee");
		
		if ($q->num_rows() > 0){
				foreach ($q->result() as $r){
					if (($r->navigation_id) > 0 && (!in_array($r->navigation_id, $navIDs))){
						array_push($navIDs, $r->navigation_id);
					}
				}
		}
		
		if (sizeof($navIDs)>0){
			$this->EE->db->where_in("navigation_id", $navIDs);
			$this->EE->db->where("site_id", $this->site_id);
			$this->EE->db->delete("navee_cache");
		}
			
		return true;

	}
	
	

}