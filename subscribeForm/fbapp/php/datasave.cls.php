<?php

/**
 * CoffeeCup Software's Web Form Builder.
 *
 * Methods to handle data that function on all database connections.
 *
 *
 * @version $Revision: 2456 $
 * @author Cees de Gruijter
 * @category FB
 * @copyright Copyright (c) 2011 CoffeeCup Software, Inc. (http://www.coffeecup.com/)
 */

define( 'FB_UPLOADS_TABLE_POSTFIX', '_uploadrefs');


class DataSave {

	var $table;					// used by sqlite and mysql classes
	var $db;					// used by sqlite and mysql classes
	var $where = false;			// used by sqlite
	var $fields = false;		// used by sqlite, defaults to all fields db fields
	var $outputfields = false;	// all fields a user can possibly ask for
	var $lastrowid = false;		// becomes available AFTER a call to _InsertRow()

	var $settings;
	var $page;
	var $post;
	var $rules;
	var $errors = array();

	function DataSave ( $settings ) {

		$this->page =& FormPage::GetInstance();

		// how to store
		$this->settings =& $settings;

		// copy values to store, so that this class may modify data without side-effects
		$this->post = $this->page->post;

		// rules are needed because they determine the record format
		$this->rules =& $this->page->GetConfig( 'rules' );

		if( isset( $settings['tablename'] ) ) {
			$this->table = trim( $settings['tablename'] );
		}
	}


	/***-*** public methods start ***-***/

	// return false on failure or true on success
	function Save ( ) {

		if( ! $this->_GetTable() ) 			return false;

		$this->_FlattenPost();
		
		if( ! $this->_InsertRow() ) {

			$this->errors[] = array( 'err' => _T('Failed to store the data.') );
			return false;
		}

		return true;
	}


	// saves relationship between rowid, fieldname and uploaded file, which is very convenient
	// when reporting on mysql or sqlite
	function SaveUploadsRef ( $uploads ) {

		if( empty( $uploads ) )		return;

		if( $this->_GetUploadsRefTable() &&
			$this->_InsertUploadsRows( $uploads ) ) {

				return true;
		}

		return false;
	}


	function UpdatePost ( $post ) {
		$this->post = $post;
	}


	function SetWhereClause ( $where ) {

		$this->where = $where;
	}


	function SetFieldClause ( $fields ) {

		$this->fields = $fields;

	} 

	// returns un-escaped array of query fields
	function GetQueryFields ( ) {

		if( $this->fields === false )		$this->fields = $this->_GetTableFields();
		return $this->fields;
	}


	// returns all fields a user can possibly ask for
	function GetAllFields ( ) {

		if( $this->outputfields === false ) {

			$this->outputfields = $this->_GetTableFields();
			$this->outputfields[] = 'rowid';
			$this->outputfields[] = '_read_';
			$this->outputfields[] = '_starred_';

			// always remove internally used field(s)
			$this->outputfields = array_values( array_diff( $this->outputfields, array( '_flags_' ) ) );
		}

		return $this->outputfields;

	}


	function HasSetting ( $name ) {

		if( ! $this->_TableExists( FB_SETTINGS_TABLE ) )	return false;

		$qry = 'SELECT count(*) FROM ' . FB_SETTINGS_TABLE . ' WHERE name=?;';
		$sth = $this->db->prepare( $qry );

		if( $sth->execute( array( $name ) ) == false ) {

			writeErrorLog( 'Failed to read settings data:', $sth->errorInfo() );
			return false;
		}

		$count = $sth->fetch( PDO::FETCH_NUM );
		$sth->closeCursor();

		if( $count === false || ! $count[0] )				return false;

		return true;
	}


	/***-*** public methods end ***-***/



	/*** private, shared methods ***/

	function _FlattenPost ( ) {

		foreach( $this->post as $field => $value ) {
			
			if( is_array( $value ) )   $this->post[ $field ] = implode( $value, ', ');
		}
	}


	function _GetTable ( ) {
		
		if( ! $this->_TableExists( $this->table ) ) {

			return $this->_CreateTable();
		}

		return $this->_CheckFields();
	}


	function _GetUploadsRefTable ( ) {
	
		if( ! $this->_TableExists( $this->table . FB_UPLOADS_TABLE_POSTFIX ) )
			return $this->_CreateUploadsRefTable();
		else
			return true;
	}	


	// add any missing fields
	function _CheckFields ( ) {

		$dbfields =& $this->_GetTableFields();

		$missing = array();

		// notes: - array_diff() is not usuable because the table may contain more fields than post
		//        - _GetTableFields() returns all names in lower case for case insensitive compare
		foreach( array_keys( $this->post ) as $key ) {

			if( ! in_array( strtolower( $key ), $dbfields ) )
				$missing[] = $key;
		}

		if( count( $missing ) > 0 ) {

			// do the ALTER 1 field at a time, because sqlite doesn't allow more
			foreach( $missing as $name ) {

				$sql = 'ALTER TABLE ' . $this->_EscapeName( $this->table) . ' ADD ' . $this->_MakeCreateFieldsSQL( array( $name ) );
				$r = $this->_Exec( $sql );
				
				if( $r === false )		return false;
			}
		}
		return true;
	}


	function _InsertRow ( ) {
		
		$fields = '';
		$values = str_repeat( '?,', count($this->post) );
		$rules =& $this->page->GetConfig( 'rules' );

		$data = array();
		foreach( $this->post as $key => $value ) {

			$fields .= $this->_EscapeName( $key ) . ',';

			// check rules for special formatting needs
			if( isset( $rules[ $key ] ) && $rules[ $key ]['fieldtype'] == 'date' && ! empty( $value ) ) {
				
				$data[] = date('Y-m-d', $value );

			} else {

				$data[] = $value;
			}

		}

		$sql = 'INSERT INTO ' . $this->_EscapeName( $this->table )
			 . ' (' . rtrim( $fields, ',' ) . ') VALUES ( ' . rtrim( $values, ',' ) . ');';
 		$sth = $this->db->prepare( $sql );

		if( $sth === false ) {

			writeErrorLog( 'Failed compile query:', $sql );
			return false;

		} else if( ! $sth->execute( $data ) ) {

			writeErrorLog( 'Failed to insert data in from table:', $sth->errorInfo() );
			return false;
		}

		$this->lastrowid = $this->db->lastInsertId();
		 
		return true;
	}

	
	function _InsertUploadsRows ( $uploads ) {

		if( $this->lastrowid === false ) {

				return;
		}

		$sql = 'INSERT INTO ' . $this->_EscapeName( $this->table . FB_UPLOADS_TABLE_POSTFIX )
			 . ' (id, fieldname, orgname, storedname) VALUES( ?, ?, ?, ? );';

		$sth = $this->db->prepare( $sql );

		if( $sth === false ) {

			writeErrorLog( 'Failed compile query:', $sql );
			return false;
		}

		foreach( $uploads as $row ) {

			if( ! $sth->execute( array( $this->lastrowid,
									   $row['fieldname'],
									   $row['orgname'],
									   $row['storedname'] ) ) ) {
				writeErrorLog( 'Failed to insert data in upload refs table:', $sth->errorInfo() );
			}
		}
	}


	function _Exec ( $sql ) {

		$res = $this->db->exec( $sql );

		if( $res === false ) {
			writeErrorLog( 'Failed to execute query on table: ' . $this->table . '(' . $sql . ')', $this->db->errorInfo() );
			$this->errors[] = array( 'err' => 'Failed to execute query associated to this form.' );
			return false;
		}

		return true;
	}
	
}



?>