<?php

/**
 * CoffeeCup Software's Web Form Builder.
 *
 * Methods on SQLite for saving data.
 *
 *
 * @version $Revision: 2456 $
 * @author Cees de Gruijter
 * @category FB
 * @copyright Copyright (c) 2011 CoffeeCup Software, Inc. (http://www.coffeecup.com/)
 */


define( 'FB_SETTINGS_TABLE', '_fb_settings' );



class DataSaveSQLite extends DataSQLite {

	function DataSaveSQLite ( $settings ) {

		parent::DataSQLite( $settings );
	}
		

	function _CreateTable ( ) {
		
		$sql = 'CREATE TABLE ' . $this->_EscapeName( $this->table ) . ' (';
			
		// no need for an id, sqlite has the 'rowid' build-in

		// the form fields
		$fields = $this->_MakeCreateFieldsSQL();

		if( $fields === false ) return false;

		$sql .= $fields . ');';

		return $this->_Exec( $sql );
	}


	function _CreateUploadsRefTable ( ) {
		
		$sql = 'CREATE TABLE ' . $this->_EscapeName( $this->table . FB_UPLOADS_TABLE_POSTFIX )
			 . ' ( id INT, fieldname TEXT, orgname TEXT, storedname TEXT );';
		$sql .= 'DROP INDEX IF EXISTS row_index;';			// needed because we changed the table names
		$sql .= 'CREATE INDEX row_index ON ' . $this->_EscapeName( $this->table . FB_UPLOADS_TABLE_POSTFIX ) . ' ( id );';

		return $this->_Exec( $sql );
	}


	function _MakeCreateFieldsSQL ( $selection = false ) {

		$sql = '';

		foreach( $this->rules as $name => $format ) {

			if( $selection != false && ! in_array( $name, $selection) )
				continue;

			if( $name == '_submitted_' ) {

				$this->errors[] = array( 'err' => _T('Field name "_submitted_" isn\'t allowed, because it is reserved for internal use.') );
				return false;
			}

			switch( $format['fieldtype'] ) {

			case 'text':
			case 'hidden':
			case 'password':
			case 'url':
			case 'email':
			case 'regex':
			case 'listbox':
			case 'dropdown':
			case 'checkbox':
			case 'radiogroup':
			case 'fileupload':
			case 'tel':
				$sql .= $this->_EscapeName( $name ) . ' TEXT,';
				break;

			case 'number':
				$sql .= $this->_EscapeName( $name ) . ' INT,';
				break;

			case 'textarea':
				$sql .= $this->_EscapeName( $name ) . ' TEXT,';
				break;
				
			case 'date':
				$sql .= $this->_EscapeName( $name ) . ' TEXT,';
				break;

			default:
				writeErrorLog( __CLASS__ . ' hit upon an unhandled field type:', $format['fieldtype'] );
			}
		}

		// add the reserved fields
		foreach( $this->page->GetReservedFieldTypes() as $name => $type ) {

			if( $selection != false && ! in_array( $name, $selection) )
				continue;

			switch( $type ) {
			case 'datetime':
			case 'text':
			case 'ipaddress':
				$sql .= $this->_EscapeName( $name ) . ' TEXT,';
				break;

			case 'number':
				$sql .= $this->_EscapeName( $name ) . ' INT DEFAULT 0 ,';
				break;

			default:
				writeErrorLog( __CLASS__ . ' unhandled type in reserved fields:', $this->page->GetReservedFieldTypes() );
			}
		}

		return rtrim( $sql, ',');
	}

}



?>