<?php

/**
 * CoffeeCup Software's Web Form Builder.
 *
 * Methods to handle MySQL data bases.
 *
 *
 * @version $Revision: 2456 $
 * @author Cees de Gruijter
 * @category FB
 * @copyright Copyright (c) 2011 CoffeeCup Software, Inc. (http://www.coffeecup.com/)
 */


define( 'CC_FB_FILEUPLOAD_EXT', '_upload' );

class DataSaveMySQL extends DataSave {

	var $stored_file_rowids = array();			// rowids of uploaded files stored in the database

	function DataSaveMySQL ( $settings ) {

		parent::DataSave( $settings );

		// note that a connect attempt may throw an exception that may reveal the userid/password
		try {

			$this->_Connect();

		} catch ( PDOException $e ) {

			switch( $e->getCode() ) {

				case 1049:
					if( $this->_CreateMySqlDb() ) {

						// try to connect again
						try {
							
							$this->_Connect();
						
						} catch (  PDOException $e ) {

							writeErrorLog( 'Connection retry after DB creation failed: [' . $e->getCode() . ']', $e->getMessage() );					
						}

					} else {

						writeErrorLog( 'Failed to create a new database.' );
						$errors[] = array( 'err' => _T('Failed to create new database [%s] %s.', array( $e->getCode(), $e->getMessage() ) ) );
					}
					break;

				default:
					writeErrorLog( 'Problems connecting to MySQL: [' . $e->getCode() . ']', $e->getMessage() );
					$this->errors[] = array( 'err' => _T('Failed to open database [%s] %s.', array( $e->getCode(), $e->getMessage() ) ) );
			}
		}
	}
	

	function _Connect ( ) {

		$dsn = 'mysql:host=' . $this->settings['server']
			 . ';port=' . $this->settings['port']
			 . ';dbname='. $this->settings['database'];

		$this->db = new PDO( $dsn,
							 $this->settings['username'],
							 $this->settings['password'],
							 array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8") );
	}
	

	function SaveUploads ( ) {

		foreach( $_FILES as $fieldname => $filedata ) {

			if( empty( $filedata['tmp_name'] ) )
				continue;
				
			// find if we need to save this specific file
			if( $this->page->GetRulePropertyByName( $fieldname, 'database' ) ) {

				if( ! $this->_GetUploadTable() )		return;
				$this->_MoveFile( $fieldname, $filedata );

			}
		}
		
	}


	function _CreateTable ( ) {
		
		$sql = 'CREATE TABLE ' . $this->_EscapeName( $this->table ) . '(';
		
		// for internal use, similar to what sqlite has build-in
		$sql .=	'_rowid_ int(11) NOT NULL AUTO_INCREMENT,';

		// the form fields
		$sql .= $this->_MakeCreateFieldsSQL();

		// the formalities
		$sql .= ', PRIMARY KEY ( _rowid_ ) ';
		$sql .= ') ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;';

		return $this->_Exec( $sql );
	}


	function _CreateUploadsRefTable ( ) {
		
		$sql = 'CREATE TABLE ' . $this->_EscapeName( $this->table . FB_UPLOADS_TABLE_POSTFIX ) . '(';

 		// for internal use, similar to what sqlite has build-in
		$sql .=	'_rowid_ int(11) NOT NULL AUTO_INCREMENT,';

		$sql .= 'id INT(11) NOT NULL DEFAULT \'0\''
			  . ',fieldname varchar(255) NOT NULL DEFAULT \'\''
			  . ',orgname varchar(255) NOT NULL DEFAULT \'\''
			  . ',storedname varchar(255) NOT NULL DEFAULT \'\''
			  . ', PRIMARY KEY ( _rowid_ )'
			  . ') ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;';

		return $this->_Exec( $sql );
	}


	function _GetUploadTable ( ) {
		
		if( ! $this->_TableExists( $this->table . CC_FB_FILEUPLOAD_EXT ) ) {

			return $this->_CreateUploadTable();	
		}

		return true;
	}


	function _CreateUploadTable ( ) {

		// note max field sizes
		// BLOB 	maximum length of 65535 characters.
		// MEDIUMBLOB	maximum length of 16777215 characters.
		// LONGBLOB 	maximum length of 4294967295 characters.

		$cfg = $this->page->GetConfig( 'rules' );
		$max = 0;
		foreach( $cfg as $name => $rule ) {

			if( $rule[ 'fieldtype' ] == 'fileupload' && isset( $rule[ 'maxbytes'] ) )
				$max = max( $max, $rule[ 'maxbytes'] );
		}

		if( $max > 0 && $max < 65535 )
			$type = 'blob';	
		else if( $max > 0 && $max > 16777215 )
			$type = 'longblob';
		else
			$type = 'mediumblob';			// reasonable default

		$sql = 'CREATE TABLE ' . $this->_EscapeName( $this->table . CC_FB_FILEUPLOAD_EXT ) . ' ('
			 . '_rowid_ int(11) NOT NULL AUTO_INCREMENT'
			 . ', id int(11) NOT NULL'
			 . ', name varchar(255)'
			 . ', fdata ' . $type
			 . ', PRIMARY KEY ( _rowid_ ), KEY id (id) '
			 . ') ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;';

		return $this->_Exec( $sql );
	}


	// MySQL version 5.0 gives an error on: ...prepare( 'SHOW TABLES LIKE ?;' )
	// thus loop over the table names in the script 
	function _TableExists ( $table ) {

		$result = $this->db->query( 'SHOW TABLES' );
	
		if( $result === false ) {

			writeErrorLog( 'Failed table exists test in MySQL:', $this->db->errorInfo() );
			return true;		// so that at least the table is not created again
		}

		$exists = false;

		foreach( $result as $row ) {

			if( $row[0] == $table ) {
				$exists = true;
				break;
			}
		}

		$result->closeCursor();

		return $exists;
	}


	function _GetTableFields ( ) {

		$result = $this->db->query( 'DESCRIBE ' . $this->_EscapeName( $this->table ) );
		$data = array();
 
		while( ( $field = $result->fetchColumn(0) ) !== false ) {

			// convert tolower case to make case insensitive compare easier
			$data[] = strtolower( $field );
		}

		$result->closeCursor();

		return $data;
	}


	function _MakeCreateFieldsSQL ( $selection = false ) {

		$sql = '';

		foreach( $this->rules as $name => $format ) {

			if( $selection != false && ! in_array( $name, $selection) )
				continue;

			switch( $format['fieldtype'] ) {
			case 'text':
			case 'hidden':
			case 'password':
			case 'email':
			case 'url':
			case 'regex':
			case 'listbox':
			case 'dropdown':
			case 'checkbox':
			case 'radiogroup':
			case 'fileupload':
			case 'tel':
				$sql .= $this->_EscapeName( $name ) . ' varchar(255) NOT NULL DEFAULT \'\',';
				break;

			case 'number':
				$sql .= $this->_EscapeName( $name ) . ' int(11),';
				break;

			case 'textarea':
				// note: max length 255 before MySQL 5.0.3, and 65,535 in 5.0.3 and later versions.
				if(  $format[ 'maxlength' ] > 255 && strcmp( mysql_get_server_info(), '5.0.3' ) < 0 )
					$sql .= $this->_EscapeName( $name ) . ' text(' . $format[ 'maxlength' ] . ') NOT NULL DEFAULT \'\',';
				else
					$sql .= $this->_EscapeName( $name ) . ' varchar(' . $format[ 'maxlength' ] . ') NOT NULL DEFAULT \'\',';
				break;
				
			case 'date':
				$sql .= $this->_EscapeName( $name ) . ' date NOT NULL DEFAULT \'0000-00-00\',';
				break;

			default:
				writeErrorLog( __CLASS__ . ' hit upon an unhandled field type:', $format['fieldtype'] );

			}
		}

		// add the reserved fields
		foreach( $this->page->GetReservedFieldTypes() as $name =>$type ) {

			if( $selection != false && ! in_array( $name, $selection) )
				continue;

			switch( $type ) {
			case 'datetime':
				$sql .= $this->_EscapeName( $name ) . ' datetime NOT NULL DEFAULT \'0000-00-00 00:00:00\',';
				break;

			case 'text': 
				$sql .= $this->_EscapeName( $name ) . ' varchar(255) NOT NULL DEFAULT \'\',';
				break;

			case 'number': 
				$sql .= $this->_EscapeName( $name ) . ' int(11) NOT NULL DEFAULT \'0\',';
				break;
			
			case 'ipaddress':
					$sql .= $this->_EscapeName( $name ) . ' varchar(128) NOT NULL DEFAULT \'0.0.0\',';
				break;

			default:
				writeErrorLog( __CLASS__ . ' unhandled type in reserved fields:', $this->GetReservedFieldTypes() );
			}

		}

		return rtrim( $sql, ',');
	}


	function _CreateMySqlDb ( ) {

		// try to connect
		if( ! ($link = mysql_connect( $this->settings['server'] . ':' . $this->settings['port'], 
									  $this->settings['username'],
									  $this->settings['password'] ) ) )
		{
			$this->errors[] = array( 'err' => _T( 'We\'re unable to connect to your database server. Please be sure you have entered your database settings correctly.') );
			return false;
		}

		// Ensure that the connection is utf8 encoded
		@mysql_query( 'SET NAMES \'utf8\'', $link );

		// If we can't select their DB, lets try to create our own.
		if( ! mysql_select_db( $this->settings['database'], $link ) ) {
			if( ! mysql_query( 'CREATE DATABASE ' . $this->_EscapeName( $this->settings['database'] ), $link)) {

			$this->errors[] = array( 'err' => _T('We\'re unable to create your database. If you believe the database already exists, check that you have the permissions to select it. If it doesn\'t exist, you need permissions to create it. If you are still experiencing troubles, please contact your server administrator.') );
			return false;

			} elseif( ! mysql_select_db( $this->settings['database'], $link ) ) {

				$this->errors[] = array( 'err' => _T('We can\'t select your database. Please be sure that you have the proper permissions to select it. If you are still experiencing trouble, please contact your server administrator.') );
				return false;
			}
		}

		writeErrorLog( 'Created a new database.' );

		return true;
	}


	function _MoveFile ( $fieldname, $filedata ) {

		$fp = fopen( $filedata['tmp_name'] , 'r' );
		$content = fread( $fp, $filedata['size'] );

		$sql = 'INSERT INTO ' . $this->_EscapeName( $this->table . CC_FB_FILEUPLOAD_EXT ) . ' (name, fdata) VALUES (?,?)';
		$sth = $this->db->prepare( $sql );

		if( $sth === false ) {

			writeErrorLog( 'Failed compile query:', $sql );			

		} else if( ! $sth->execute( array( $fieldname, $content ) ) ) {

			writeErrorLog( 'Failed to insert file data:', $sth->errorInfo() );

		} else {

			// remember the id
			$res = $this->db->query( 'SELECT LAST_INSERT_ID();' );
			if( $res !== false ) {

				$this->stored_file_rowids[] = $res->fetchColumn(0);
				$res->closeCursor();
			}
		}
	}


	function UpdateStoredFileIds ( ) {

		if( count( $this->stored_file_rowids ) == 0 )		return;

		$sql = 'UPDATE ' . $this->_EscapeName( $this->table . CC_FB_FILEUPLOAD_EXT )
			 . ' SET id=' . $this->lastrowid . ' WHERE _rowid_ IN ('
			 . implode( ',', $this->stored_file_rowids) . ');';

		$this->db->exec( $sql );
	}


	function _EscapeName ( $name ) {

		return '`' . $name . '`' ;
	}

}

?>