<?php 
/*
_|                                                      _|
_|_|_|     _|_|     _|_|   _|    _|   _|_|_| _|_|_|   _|_|_|_|
_|    _| _|    _| _|    _| _|    _| _|    _| _|    _|   _|
_|    _| _|    _| _|    _| _|    _| _|    _| _|    _|   _|
_|_|_|     _|_|     _|_|     _|_|_|   _|_|_| _|    _|     _|_|
                                 _|
                             _|_|

Description:		NavEE Module for Expression Engine 2.0
Developer:			Booyant, Inc.
Website:			www.booyant.com/navee
Location:			./system/expressionengine/third_party/modules/navee/tab.navee.php
Contact:			navee@booyant.com  / 978.OKAY.BOB

*/

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Navee_tab {

	function __construct(){
		// Make a local reference to the ExpressionEngine super object
		$this->EE =& get_instance();
	}
	
	function publish_tabs($channel_id, $entry_id = ''){
		$settings = array();
		$selected = array();
		$existing_files = array();

/*
		$query = $this->EE->db->get('download_files');
		
		foreach ($query->result() as $row){
			$existing_files[$row->file_id] = $row->file_name;
		}

		if ($entry_id != ''){
			
			$query = $this->EE->db->get_where('download_posts', array('entry_id' => $entry_id));

			foreach ($query->result() as $row)
			{
				$selected[] = $row->file_id;
			}
		}

		$id_instructions = lang('id_field_instructions');
		
		// Load the module lang file for the field label
		$this->EE->lang->loadfile('download');
*/
		$settings[] = array(
				'field_id'		=> 'download_field_ids',
				'field_label'		=> 'Text',
				'field_required'	=> 'n',
				'field_data'		=> $selected,				
				'field_list_items'	=> $existing_files,
				'field_fmt'		=> '',
				'field_instructions' 	=> 'do something',
				'field_show_fmt'	=> 'n',
				'field_pre_populate'	=> 'n',
				'field_text_direction'	=> 'ltr',
				'field_maxl'			=> '20',
				'field_type' 		=> 'navee'
			);

		return $settings;
	}
	
	function validate_publish(){
		return false;
	}
	
	function publish_data_db(){
		return false;
	}
	
	function publish_data_delete_db(){
		return false;
	}
	
}