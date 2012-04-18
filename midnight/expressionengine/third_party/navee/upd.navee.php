<?php 
/*                          
_|                                                      _|
_|_|_|     _|_|     _|_|   _|    _|   _|_|_| _|_|_|   _|_|_|_|
_|    _| _|    _| _|    _| _|    _| _|    _| _|    _|   _|
_|    _| _|    _| _|    _| _|    _| _|    _| _|    _|   _|
_|_|_|     _|_|     _|_|     _|_|_|   _|_|_| _|    _|     _|_|
                                 _|
                             _|_|

Description: 	NavEE Module for Expression Engine 2.x
Developer: 		Booyant, Inc.
Website: 		www.booyant.com/navee
Location: 		./system/expressionengine/third_party/modules/navee/upd.navee.php
Contact: 		navee@booyant.com  / 978.OKAY.BOB

*/

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// Include config file
require_once PATH_THIRD.'navee/config'.EXT;

class Navee_upd {

	var $version = NAVEE_VERSION;
	
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
 	//	C O N S T R U C T O R
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	
	function Navee_upd(){
		// Make a local reference to the ExpressionEngine super object
		$this->EE =& get_instance();
	}
	
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
 	//	I N S T A L L E R
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	
	function install(){
			
		$this->EE->load->dbforge();

		$data = array(
			'module_name' => 'Navee' ,
			'module_version' => $this->version,
			'has_cp_backend' => 'y',
			'has_publish_fields' => 'n'
		);

		$this->EE->db->insert('modules', $data);
		
		$data = array(
			'class'		=> 'Navee' ,
			'method'	=> 'add_navigation_handler'
		);

		$this->EE->db->insert('actions', $data);

		$fields = array(
						'navee_id'		=> array('type' 		 => 'int',
													'constraint'	 => '10',
													'unsigned'		 => TRUE,
													'auto_increment' => TRUE),
						'navigation_id' => array('type'=>'int', 'constraint'=>'10'),
						'site_id'       => array('type'=>'int', 'constraint'=>'10'),
						'entry_id'      => array('type'=>'int', 'constraint'=>'10'),
						'channel_id'    => array('type'=>'int', 'constraint'=>'10'),
						'template'      => array('type'=>'int', 'constraint'=>'10'),
						'type'          => array('type'=>'varchar', 'constraint'=>'20'),
						'parent'        => array('type'=>'int', 'constraint'=>'10'),
						'text'          => array('type'=>'varchar', 'constraint'=>'255'),					
						'link'          => array('type'=>'varchar', 'constraint'=>'255'),
						'class'         => array('type'=>'varchar', 'constraint'=>'255'),
						'id'            => array('type'=>'varchar', 'constraint'=>'255'),	
						'sort'          => array('type'=>'int', 'constraint'=>'10'),
						'include'       => array('type'	=> 'tinyint', 'constraint'=>'4'),
						'passive'       => array('type'	=> 'tinyint', 'constraint'=>'4'),
						'datecreated'   => array('type'=>'datetime'),
						'dateupdated'   => array('type'=>'datetime'),
						'ip_address'    => array('type'=>'varchar', 'constraint'=>'255'),
						'member_id'     => array('type'=>'int', 'constraint'=>'10'),
						'rel'           => array('type'=>'varchar', 'constraint'=>'255'),
						'name'          => array('type'=>'varchar', 'constraint'=>'255'),
						'target'        => array('type'=>'varchar', 'constraint'=>'255'),
						'title'         => array('type'=>'varchar', 'constraint'=>'255'),
						'regex'         => array('type'=>'varchar', 'constraint'=>'255'),
						'custom'        => array('type'=>'varchar', 'constraint'=>'255'),
						'custom_kids'   => array('type'=>'varchar', 'constraint'=>'255'),
						'access_key'    => array('type'=>'varchar', 'constraint'=>'1')
									
						);

		$this->EE->dbforge->add_field($fields);
		$this->EE->dbforge->add_key('navee_id', TRUE);
		$this->EE->dbforge->create_table('navee', TRUE);	
		unset($fields);
		
		$fields = array(
						'navigation_id'		=> array('type' 		 => 'int',
													'constraint'	 => '10',
													'unsigned'		 => TRUE,
													'auto_increment' => TRUE),
						'site_id'			=> array('type'=>'int', 'constraint'=>'10'),
						'nav_title'			=> array('type'=>'varchar', 'constraint'=>'255'),
						'nav_name'			=> array('type'=>'varchar', 'constraint'=>'255'),
						'nav_description'	=> array('type'=>'varchar', 'constraint'=>'255'),
						'datecreated'		=> array('type'=>'datetime'),
						'dateupdated'		=> array('type'=>'datetime'),
						'ip_address'		=> array('type'=>'varchar', 'constraint'=>'255'),
						'member_id'			=> array('type'=>'int', 'constraint'=>'10')	
									
						);

		$this->EE->dbforge->add_field($fields);
		$this->EE->dbforge->add_key('navigation_id', TRUE);
		$this->EE->dbforge->create_table('navee_navs', TRUE);	
		unset($fields);
		
		$fields = array(
						'navee_mem_id'		=> array('type' 		 => 'int',
													'constraint'	 => '10',
													'unsigned'		 => TRUE,
													'auto_increment' => TRUE),
						'site_id'			=> array('type'=>'int', 'constraint'=>'10'),
						'navee_id'			=> array('type'=>'int', 'constraint'=>'10'),
						'members'			=> array('type'=>'text', 'null'=>TRUE)
						);

		$this->EE->dbforge->add_field($fields);
		$this->EE->dbforge->add_key('navee_mem_id', TRUE);
		$this->EE->dbforge->create_table('navee_members', TRUE);	
		unset($fields);
		
		// Add the config table if it doesn't already exist
		$fields = array(
						'navee_config_id'		=> array('type' 		 => 'int',
													'constraint'	 => '10',
													'unsigned'		 => TRUE,
													'auto_increment' => TRUE),
						'site_id'				=> array('type'=>'int', 'constraint'=>'10'),
						'k'						=> array('type'=>'varchar', 'constraint'=>'255'),
						'v'						=> array('type'=>'text')
						);

		$this->EE->dbforge->add_field($fields);
		$this->EE->dbforge->add_key('navee_config_id', TRUE);
		$this->EE->dbforge->create_table('navee_config', TRUE);	
		unset($fields);
		
		// Add the cache table if it doesn't already exist
		$fields = array(
						'navee_cache_id'		=> array('type' 		 => 'int',
													'constraint'	 => '10',
													'unsigned'		 => TRUE,
													'auto_increment' => TRUE),
						'site_id'				=> array('type'=>'int', 'constraint'=>'10'),
						'navigation_id'			=> array('type'=>'int', 'constraint'=>'10'),
						'group_id'				=> array('type'=>'smallint', 'constraint'=>'4'),
						'parent'				=> array('type'=>'int', 'constraint'=>'10'),
						'recursive'				=> array('type'	=> 'tinyint', 'constraint'=>'4'),
						'ignore_include'		=> array('type'	=> 'tinyint', 'constraint'=>'4'),
						'start_from_parent'		=> array('type'	=> 'tinyint', 'constraint'=>'4'),
						'start_from_kid'		=> array('type'	=> 'tinyint', 'constraint'=>'4'),
						'single_parent'			=> array('type'	=> 'tinyint', 'constraint'=>'4'),
						'cache'					=> array('type'	=> 'longtext')
						
						);

		$this->EE->dbforge->add_field($fields);
		$this->EE->dbforge->add_key('navee_cache_id', TRUE);
		$this->EE->dbforge->create_table('navee_cache', TRUE);	
		unset($fields);
	
		return TRUE;
	}
	
	
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
 	//	U N I N S T A L L E R
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	
	function uninstall()
	{
		$this->EE->load->dbforge();

		$this->EE->db->select('module_id');
		$query = $this->EE->db->get_where('modules', array('module_name' => 'NavEE'));

		$this->EE->db->where('module_id', $query->row('module_id'));
		$this->EE->db->delete('module_member_groups');

		$this->EE->db->where('module_name', 'Navee');
		$this->EE->db->delete('modules');

		$this->EE->db->where('class', 'Navee');
		$this->EE->db->delete('actions');

		// N O T E 
		// If you would like NavEE to drop it's tables when you uninstall,
		// uncomment the next two lines.
		
		//$this->EE->dbforge->drop_table('navee');
		//$this->EE->dbforge->drop_table('navee_navs');
		//$this->EE->dbforge->drop_table('navee_members');
		//$this->EE->dbforge->drop_table('navee_config');
		
		return TRUE;

	}


	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
 	//	U P D A T E
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>	
	
	function update($current='')
	{
		$this->EE->load->dbforge();
		
		// Add the config table if it doesn't already exist
		$fields = array(
						'navee_config_id'		=> array('type' 		 => 'int',
													'constraint'	 => '10',
													'unsigned'		 => TRUE,
													'auto_increment' => TRUE),
						'site_id'				=> array('type'=>'int', 'constraint'=>'10'),
						'k'						=> array('type'=>'varchar', 'constraint'=>'255'),
						'v'						=> array('type'=>'text')
						);

		$this->EE->dbforge->add_field($fields);
		$this->EE->dbforge->add_key('navee_config_id', TRUE);
		$this->EE->dbforge->create_table('navee_config', TRUE);	
		unset($fields);
		
		if (!$this->EE->db->field_exists('site_id', 'navee_config')) {
   			$fields = array(
                        'site_id' 		=> array('type'=>'int', 'constraint'=>'10')
			);
			
			$this->EE->dbforge->add_column('navee_config', $fields);
		}
		unset($fields);
		
		// Version 2.0.0
		if (!$this->EE->db->field_exists('entry_id', 'navee')) {
   			$fields = array(
                        'entry_id' 		=> array('type'=>'int', 'constraint'=>'10'),
                        'channel_id' 	=> array('type'=>'int', 'constraint'=>'10'),
                        'template' 		=> array('type'=>'int', 'constraint'=>'10'),
                        'type'			=> array('type'=>'varchar', 'constraint'=>'20'),
                        'custom'		=> array('type'=>'varchar', 'constraint'=>'255'),
						'custom_kids'	=> array('type'=>'varchar', 'constraint'=>'255'),
						'passive'		=> array('type'	=> 'tinyint', 'constraint'=>'4')
			);
			
			$this->EE->dbforge->add_column('navee', $fields);
		}
		unset($fields);
		
		// Version 2.0.2
		// Add the cache table if it doesn't already exist
		$fields = array(
						'navee_cache_id'		=> array('type' 		 => 'int',
													'constraint'	 => '10',
													'unsigned'		 => TRUE,
													'auto_increment' => TRUE),
						'site_id'				=> array('type'=>'int', 'constraint'=>'10'),
						'navigation_id'			=> array('type'=>'int', 'constraint'=>'10'),
						'group_id'				=> array('type'=>'smallint', 'constraint'=>'4'),
						'parent'				=> array('type'=>'int', 'constraint'=>'10'),
						'recursive'				=> array('type'	=> 'tinyint', 'constraint'=>'4'),
						'ignore_include'		=> array('type'	=> 'tinyint', 'constraint'=>'4'),
						'start_from_parent'		=> array('type'	=> 'tinyint', 'constraint'=>'4'),
						'start_from_kid'		=> array('type'	=> 'tinyint', 'constraint'=>'4'),
						'single_parent'			=> array('type'	=> 'tinyint', 'constraint'=>'4'),
						'cache'					=> array('type'	=> 'longtext')
						
						);

		$this->EE->dbforge->add_field($fields);
		$this->EE->dbforge->add_key('navee_cache_id', TRUE);
		$this->EE->dbforge->create_table('navee_cache', TRUE);	
		unset($fields);
		
		$fields = array(
                        'cache' => array(
                        	'name'		=> 'cache',
                        	'type' 		=> 'longtext',
						),
						);
		$this->EE->dbforge->modify_column('navee_cache', $fields);
		unset($fields);
		
		
		$fields = array(
                        'v' => array(
                        	'name'		=> 'v',
                        	'type' 		=> 'text',
						),
						);
		$this->EE->dbforge->modify_column('navee_config', $fields);
		unset($fields);
		
		
		// Version 2.2.2
		if (!$this->EE->db->field_exists('access_key', 'navee')) {
   			$fields = array(
                        'access_key' 	=> array('type'=>'varchar', 'constraint'=>'1')
			);
			
			$this->EE->dbforge->add_column('navee', $fields);
		}
		unset($fields);

		// Version 2.2.5
		if (!$this->EE->db->field_exists('title', 'navee')) {
   			$fields = array(
                        'title' 	=> array('type'=>'varchar', 'constraint'=>'255')
			);
			
			$this->EE->dbforge->add_column('navee', $fields);
		}
		unset($fields);
		
		$this->EE->db->empty_table('navee_cache'); 
		
		return TRUE;
	}
	
}

?>