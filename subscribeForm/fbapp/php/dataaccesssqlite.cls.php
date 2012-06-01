<?php

/**
 * CoffeeCup Software's Web Form Builder.
 *
 * Methods on SQLite for accessing saved data.
 *
 *
 * @version $Revision: 2456 $
 * @author Cees de Gruijter
 * @category FB
 * @copyright Copyright (c) 2011 CoffeeCup Software, Inc. (http://www.coffeecup.com/)
 */


class DataAccessSQLite extends DataSQLite {

	/*	"pagination": {
	 *		"page_current": 2,				<- 1-based page number
	 *		"page_previous": 1,				<- null if on first page
	 *		"page_next": 3,					<- null if on last page
	 *		"page_last": 8,					
	 *		"items_per_page": 10,
	 *		"items_total": 78,
	 *		"items_starting_index": 11,
	 *		"items_ending_index": 20
	 */
	var $pagination = false;		// data structure with pagination information
	var $sort = false;				// data structure that defines sort order
	var $error = false;				// any error condition that was encountered
	var $result = false;			// feedback from data manipulation queries

	function DataAccessSQLite ( $settings ) {

		parent::DataSQLite( $settings );
	}


	// return false if the field is not in the table 
	function SetSortOrder ( $field, $asc = true ) {

		if( in_array( $field, $this->_GetTableFields() ) ) {

			$this->sort['field'] = $this->_EscapeName( $field );
			$this->sort['asc'] = $asc;
			return true;
		}

		// rowid is a sqlite special field and is missing fields list
		if( preg_match( '/_?rowid_?/', $field ) )
		{
			$this->sort['field'] = 'rowid';
			$this->sort['asc'] = $asc;
			return true;
		}

		$this->sort = false;
		return false;
	}


	// Return associative array on success or false on failure
	function GetAllRows ( $tablename = false ) {

		if( ! $tablename )		$tablename = $this->table;
	
		$qry = $this->_QrySelectFrom( $tablename );

		if( $this->where ) {

			$qry .= ' WHERE ' . $this->where;
		}

		if( $this->sort ) {

			$qry .= ' ORDER BY ' . $this->sort['field'] . ($this->sort['asc'] ? ' ASC' : ' DESC');
		}
		$qry .= ';';

		$result = $this->db->query( $qry );

		if( $result === false )			return false;

		$data = array();

		while( $row = $result->fetch( PDO::FETCH_ASSOC ) ) {
			$data[] = $row;
		}

		return $data;
	}


	// return the data of the requested page and sets the pagination structure
	// or false on failure
	function GetPage ( $page, $pagelength, $tablename = false ) {

		if( ! $tablename )		$tablename = $this->table;

		$itemcount = $this->GetRecordCount();

		$start = ($page - 1) * $pagelength;
	
		if( $start < 0 || $start > $itemcount )
			return false;

		$qry = $this->_QrySelectFrom( $tablename );
		if( $this->where )				$qry .= ' WHERE ' . $this->where;

		$data = array();

		if( $this->sort ) {
			$qry .= ' ORDER BY ' . $this->sort['field'] . ($this->sort['asc'] ? ' ASC' : ' DESC');
		}
		$qry .= ' LIMIT ' . $pagelength . ' OFFSET ' . $start . ';';

		$r = $this->db->query( $qry );

		if( $r === false ) {
			writeErrorLog( 'Error in query: ' . $qry, $this->db->errorInfo() );
			$this->error = implode( ' ,', $this->db->errorInfo() );
			return false;
		}

		$rows = array();
		while( $row = $r->fetch( PDO::FETCH_ASSOC ) ) {
			$rows[] = $row;
		}

		$r->closeCursor();

		// add the remainder of the pagination
		// pages that don't exist should return null instead of 0
		$this->pagination['items_total'] = $itemcount;
		$this->pagination[ 'page_last' ] = ($itemcount > 0 ? ceil( $itemcount / $pagelength ) : 1);
		$this->pagination[ 'page_current' ] = $page;
		$this->pagination[ 'page_previous' ] = ($page == 1 ? null : $page - 1);
		$this->pagination[ 'page_next' ] = ($this->pagination[ 'page_last' ] == $page ? null : $page + 1);
		$this->pagination[ 'items_per_page' ] = $pagelength;
		$this->pagination[ 'items_starting_index' ] =  $start + 1;
		$this->pagination[ 'items_ending_index' ] = $start + count( $rows );

		return $rows;
	}

	
	function GetItemData ( $rowid, $tablename = false ) {

		if( ! is_numeric( $rowid ) ) {
			$this->error = 'Row ID must be numeric.';
			return false;
		}

		if( ! $tablename )		$tablename = $this->table;

		$qry = $this->_QrySelectFrom( $tablename, true );
		$qry .= ' WHERE _rowid_=?;';

		$sth = $this->db->prepare( $qry );
		$sth->execute( array( $rowid ) );

		$data = $sth->fetch( PDO::FETCH_ASSOC );

		if( $data === false ) {

			writeErrorLog( 'Failed to get item data:', $sth->errorInfo() ); 
			$this->error = implode( ' ,', $r->errorInfo() );
			return false;
		}

		$sth->closeCursor(); 

		return $data;
	}


	function UpdateItem ( $rowid, $read, $starred , $tablename = false ) {

		if( ! is_numeric( $rowid ) ) {
			$this->error = 'Row ID must be numeric.';
			return false;
		}

		if( ! $tablename )		$tablename = $this->table;

		$flag = 0;
		if( $read !== false && $starred !== false ) {

			// update if both are defined
			if( $read )		$flag |= FLAG_READ;
			if( $starred )	$flag |= FLAG_STARRED;

		} else {

			// get current value and modify
			$qry = 'SELECT _flags_ FROM ' . $this->_EscapeName( $tablename ) . ' WHERE _rowid_=' . $rowid . ';';
			$result = $this->db->query( $qry );

			if( $result === false )	{

				writeErrorLog( 'Failed to get item with id: ' . $rowid, $this->db->errorInfo() );
				$this->error = 'Failed to get item with id: ' . $rowid;
				return false;
			}

			if( $r = $result->fetch( PDO::FETCH_NUM )  ) {

				$flag = $r[0];

			} else {

				$this->error = 'Couldn\'t find item with id: ' . $rowid;
				return false;
			}

			// update if both are defined
			if( $read !== false ) {
				if( $read )			$flag |= FLAG_READ;
				else 				$flag = $flag & ~FLAG_READ;
			}

			if( $starred !== false ) {
				if( $starred )		$flag |= FLAG_STARRED;
				else				$flag = $flag & ~FLAG_STARRED;
			}
		}

		$sql = 'UPDATE ' . $this->_EscapeName( $tablename ) . ' SET _flags_=? WHERE _rowid_=?;';
		$sth = $this->db->prepare( $sql );

		if( $sth->execute( array( $flag, (int)$rowid ) ) === false ) {

			writeErrorLog( 'Failed to execute query for update item:', $sth->errorInfo() ); 
			$this->error = implode( ' ,', $sth1->errorInfo() );
			return false;
		}

		$this->result = 'Updated item ' . $rowid;

		return true;
	}
	

	// returns array( field => array( fieldname => '...', orgname => '...', storedname => '...' ), ... )
	// or false on error
	function GetItemFiles ( $rowid, $tablename = false ) {

		if( ! is_numeric( $rowid ) ) {
			$this->error = 'Row ID must be numeric.';
			return false;
		}

		if( ! $tablename )		$tablename = $this->table;

		// check table existance, it is only created when forms have file upload fields
		if( ! $this->_TableExists( $tablename . FB_UPLOADS_TABLE_POSTFIX ) ) {
			return array();
		}

		$qry = 'SELECT * FROM ' . $this->_EscapeName( $tablename . FB_UPLOADS_TABLE_POSTFIX ) . ' WHERE id=?;';
		$sth = $this->db->prepare( $qry );

		if( $sth->execute( array( $rowid ) ) === false ) {

			writeErrorLog( 'Failed to execute prepared query for get item files:', $sth->errorInfo() ); 
			$this->error = implode( ' ,', $sth->errorInfo() );
			return false;
		}

		$data = array();
		while( $row = $sth->fetch( PDO::FETCH_ASSOC ) ) {
			$data[ $row['fieldname'] ] = $row ;
		}

		$sth->closeCursor(); 

		return $data;
	}


	function GetPagination ( ) {

		return $this->pagination;
	}


	// Get the rowids that match the where clause and delete the rows one by one instead of deleting
	// records directly, because the rowids are needed to delete associated files
	function DeleteWhere ( $where, $tablename = false  ) {

		if( ! $tablename )		$tablename = $this->table;

		$rows = $this->_GetRowIdsByWhere( $where, $tablename );

		if( $rows === false || count( $rows ) == 0 ) {
			$this->result = 'No rows matched selection criteria.';
			return true;
		}

		$sql1 = 'DELETE FROM ' . $this->_EscapeName( $tablename ) .' WHERE _rowid_=?;';
		$sth1 = $this->db->prepare( $sql1 );
		$sth2 = false;
		$sth3 = false;

		if( ! $this->_TableExists( $tablename . FB_UPLOADS_TABLE_POSTFIX ) ) {

			$sql2 = 'DELETE FROM ' . $this->_EscapeName( $tablename . FB_UPLOADS_TABLE_POSTFIX ) . ' WHERE id=?;';
			$sql3 = 'SELECT storedname FROM ' . $this->_EscapeName( $tablename . FB_UPLOADS_TABLE_POSTFIX ) . ' WHERE id=?;';
			$sth2 = $this->db->prepare( $sql2 );
			$sth3 = $this->db->prepare( $sql3 );
		}

		$this->db->beginTransaction();
		$count = 0;

		foreach( $rows as $rowid ) {

			if( $sth1->execute( array( (int)$rowid ) ) === false ) {

				writeErrorLog( 'Failed to execute prepared query for delete item:', $sth1->errorInfo() ); 
				$this->error = implode( ' ,', $sth1->errorInfo() );
				$this->db->rollBack();
				return false;

			} else {

				// only do this if the table exists and we compiled the query
				if( $sth2 !== false ) {

					// delete any reference file
					if( $sth3->execute( array( (int)$rowid ) ) === false ) {

						writeErrorLog( 'Failed to execute prepared select query on: ' . $tablename . FB_UPLOADS_TABLE_POSTFIX, $sth3->errorInfo() );
						$this->error = implode( ' ,', $sth1->errorInfo() );

					} else {
						
						$path = $this->page->GetStorageFolder( 1 ) . '/';

						while( ($name = $sth3->fetchColumn( 0 )) !== false ) {
							unlink( $path . $name );
						}
					}

					// delete row from the file table
					if( $sth2->execute( array( (int)$rowid ) ) === false ) {

						writeErrorLog( 'Failed to execute prepared query for delete file reference:', $sth2->errorInfo() );
						$this->error = implode( ' ,', $sth2->errorInfo() );
						$this->db->rollBack();
						return false;
					}
				}

				$count += $sth1->rowCount();

			}
		}
		$this->db->commit();

		$this->result = 'Deleted ' . $count . ' record' . ($count == 0 ? '.' : 's.' );

		return true;
	}


	function UpdateWhere ( $where, $read, $starred, $tablename = false  ) {

		$rows = $this->_GetRowIdsByWhere( $where, $tablename );

		if( $rows === false ) {

			return false;

		} else if( ! is_array( $rows ) ) {

			$this->error = 'UpdateRows expects the first parameter to be an array()';
			return false;

		} else if( count( $rows ) == 0 ) {

			$this->result = 'No rows matched selection criteria.';
			return true;
		}

		$count = 0;
		foreach( $rows as $id ) {

			if( ! $this->UpdateItem ( $id, $read, $starred , $tablename ) )		return false;
			++$count;
		}

		$this->result = 'Updated ' . $count . ' record' . ($count == 0 ? '.' : 's.' );
		return true;
	}


	function _QrySelectFrom ( $tablename, $allfields = false ) {

		if( $allfields || $this->fields === false ) {
			$this->fields = $this->GetAllFields();
		}

		// order the field names as in the current form, but the old ones at the back
		usort( $this->fields, array( $this, '_orderLikeInForm' ) );


		$myfields = '';
		foreach( $this->fields as $fld ) {

			switch( $fld ) {

				case '_read_':
			 		$myfields .= ', (_flags_ & ' . FLAG_READ . ') <> 0 AS _read_ ';
					$addFlagRead = false;
					break;

				case '_starred_':
			 		$myfields .= ', (_flags_ & ' . FLAG_STARRED . ') <> 0 AS _starred_ ';
					$addFlagStar = false;
					break;

				case 'flags':
					// ignore internally used fields
					break;

				default:
					$myfields .= ',' . $this->_EscapeName( $fld );
			}
		}

		$qry = 'SELECT rowid'. $myfields . ' FROM ' . $this->_EscapeName( $tablename );

		return $qry;
	}


	function _GetRowIdsByWhere ( $where, $tablename = false ) {

		if( ! $tablename )		$tablename = $this->table;

		$qry = 'SELECT rowid FROM ' . $this->_EscapeName( $tablename ) . ' WHERE ' . $where . ';';
		$result = $this->db->query( $qry );

		if( $result === false )	{
			$this->error = 'Failed to execute query with this where clause: ' . $where;
			return false;
		}

		$rows = array();

		while( $r = $result->fetch( PDO::FETCH_NUM )  ) {
			$rows[] = $r[0];
		}

		return $rows;
	}


	// get a row from the key-value settings table
	function GetSetting ( $key ) {

		if( ! $this->_TableExists( FB_SETTINGS_TABLE ) )
			return '';

		$qry = 'SELECT value FROM ' . FB_SETTINGS_TABLE . ' WHERE name=?;';
		$sth = $this->db->prepare( $qry );
		
		if( $sth->execute( array( $key ) ) === false ) {
			writeErrorLog( 'Failed to get settings:', $qry);
			return '';
		}

		$row = $sth->fetch( PDO::FETCH_NUM );
		$sth->closeCursor();

		if( $row === false )		return '';
		else						return $row[0];
	}


	// usort callback that compares 2 fieldnames in the rules  	
	private function _orderLikeInForm ( $a, $b ) {

		static $keys = false;
		if( !$keys )		$keys = array_keys( $this->rules );

		foreach( $keys as $key ) {
			
			if( $key == $a )		return -1;		// a appears before b
			if( $key == $b )		return 1;		// b appears before a
		}
		return 0;									// neither a nor b is found
	}

}



