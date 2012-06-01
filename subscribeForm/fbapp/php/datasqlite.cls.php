<?php

/**
 * CoffeeCup Software's Web Form Builder.
 *
 * Generic methods on SQLite, used for either saving or accessing data.
 *
 *
 * @version $Revision: 2456 $
 * @author Cees de Gruijter
 * @category FB
 * @copyright Copyright (c) 2011 CoffeeCup Software, Inc. (http://www.coffeecup.com/)
 */

include_once 'fbapp/php/datasave.cls.php';

class DataSQLite extends DataSave {

	function DataSQLite ( $settings ) {

		parent::DataSave( $settings );
		$sqlite_folder = $this->page->GetStorageFolder( 2 );

		// database names longer than 248 chars won't work
		// (it seems sqlite has no problem with table names in excess of 450 chars)
		if( strlen( $this->settings['database'] ) > 200 )
			$dbname = substr( $this->settings['database'], 0, 200 ) . md5( $this->settings['database'] ) ;
		else
			$dbname = $this->settings['database'];

		try {
			try{
			
				$this->db = new PDO(  'sqlite:' . $sqlite_folder . $dbname );
			
			} catch ( PDOException $e ) {
			
				if( file_exists($sqlite_folder) || !mkdir( $sqlite_folder, 0755, true ) ) {
					throw $e;
				}
				$this->db = new PDO(  'sqlite:' . $sqlite_folder . $dbname );
			}
		 }
		catch ( PDOException $e ) {
			
			// Something happened and couldn't connect to SQLLite
			writeErrorLog( 'Problems connecting to SQLite ' . $sqlite_folder .
					   $dbname . ': [' . $e->getCode() . ']', $e->getMessage() );
			$this->errors[] = array( 'err' => _T( 'Failed to open database [%s] %s', array( $e->getCode(), $e->getMessage() ) ) );
		}
	}
	

	function GetTableNames ( ) {

		$qry = 'SELECT name FROM sqlite_master WHERE type=\'table\';';

		$result = $this->db->query( $qry );

		$names =array();

		while( ($name = $result->fetchColumn(0)) !== false ) {

			$names[] = $name;
		}

		$result->closeCursor();

		return $names;
	}


	function _TableExists ( $table ) {

		$qry = 'SELECT name FROM sqlite_master WHERE type=\'table\' AND name=?;';

		$sth= $this->db->prepare( $qry );

		if( ! $sth->execute( array( $table ) ) ) {
			writeErrorLog( 'Failed to execute:', $qry );
			return false;
		}

		$exists = ( $sth->fetchColumn(0) !== false );
		$sth->closeCursor();
		
		return $exists;
	}


	function _GetTableFields ( $tablename = false ) {

		if( ! $tablename )		$tablename = $this->table;

		$result = $this->db->query( 'pragma table_info(' . $this->_EscapeName( $tablename ) . ');' );
		$data = array();

		while( ( $field = $result->fetchColumn(1) ) !== false ) {
			
			// convert tolower case to make case insensitive compare easier
			$data[] = strtolower( $field );
		}

		return $data;
	}


	function GetRecordCount ( $tablename = false ) {

		if( ! $tablename )		$tablename = $this->table;

		$qry = 'SELECT count(*) FROM ' . $this->_EscapeName( $tablename );

		if( $this->where )			$qry .= ' WHERE ' . $this->where;

		$qry .= ';';

		$result = $this->db->query( $qry );

		if( $result === false )			return 0;

		$count = $result->fetchColumn(0);
		$result->closeCursor();

		return $count;
	}

	
	function _EscapeName ( $name ) {

		return '"' . str_replace( '"', '""', $name ) . '"';
	}


	// input format array( $key => $value, ... )
	function SetSetting ( $values ) {

		if( ! $this->_TableExists( FB_SETTINGS_TABLE ) &&
			! $this->_CreateSettingsTable() ) {

			return false;
		}

		$qry = 'INSERT OR REPLACE INTO ' . FB_SETTINGS_TABLE . '( name, value ) VALUES ( ?, ? );';
		$sth = $this->db->prepare( $qry );

		foreach( $values as $key => $value ) {

			if( $sth->execute( array( $key, $value ) ) == false ) {
				writeErrorLog( 'Failed to store settings data:', $sth->errorInfo() );
				return false;
			}
		}
		return true;
	}


	function _CreateSettingsTable ( ) {
		
		$sql = 'CREATE TABLE ' . FB_SETTINGS_TABLE . ' (name  TEXT, value  TEXT);'
			 . 'CREATE UNIQUE INDEX settings_index ON ' . FB_SETTINGS_TABLE . ' ( name );';
		return $this->_Exec( $sql );
	}



}



?>