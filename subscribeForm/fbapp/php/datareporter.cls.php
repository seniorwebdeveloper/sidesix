<?php

/**
 * CoffeeCup Software's Web Form Builder.
 *
 * Class for data reporting. Used for S-Drive control panel. 
 *
 *
 * @version $Revision: 2456 $
 * @author Cees de Gruijter
 * @category FB
 * @copyright Copyright (c) 2011 CoffeeCup Software, Inc. (http://www.coffeecup.com/)
 */


// bit masks for the _flags_ field
define ( 'FLAG_READ', 1 );
define ( 'FLAG_STARRED', 2 );



class DataReporter {

	private $where = false;
	private $fields = false;				// array of fields to use for select query
	private $maxwidth = -1;
	private $data_settings;
	private $page;
	private $db = false;
	public $error = '';
	public $data = array();

	function __construct( $page ) {
		$this->page =& FormPage::GetInstance();

		$cfg =& $page->GetConfig( 'settings' );
		$this->data_settings =& $cfg[ 'data_settings' ];

		$this->_Connect();
	}


	function SetSelection ( $selection ) {

		if( $selection === false ) 		return true;

		// check the syntax and build a where clause
		// possible formats are:
		//		3days						last 3 days
		//		from20to40					from row 20 to row 40
		//		from2011-07-29 10:27:18to2011-07-30 10:27:18
		//									from date_time to date_time
		//		new							rows that don't have the _read_ flag set 
		//		starred						rows that have the _starred_ flag set 
		//		1,2,3						rows with ids 1, 2 and 3 

		$matches = array();
		if( $selection == 'new' ) {

			$this->where = '(_flags_ & ' . FLAG_READ . ')== 0';

		} else if( $selection == 'starred') {

			$this->where = '_flags_ & ' . FLAG_STARRED;

		} else if( $selection == 'all') {

			$this->where = '1=1';

		} else if( preg_match( '/(\d+)days?/', $selection, $matches ) == 1 ) {
			
			$start = time() - ( $matches[1] * 24 * 60 * 60);
			$this->where = '_submitted_>=\'' . date( 'Y-m-d', $start ) . ' 00:00:00\'';

		} else if( preg_match( '/from(\d+)to(\d+)/', $selection, $matches ) == 1 ) {

			$this->where = '_rowid_>=' . $matches[1] . ' AND ' . '_rowid_<=' . $matches[2];  

		} else if( preg_match( '/from([ \d-:]+)to([ \d-:]+)/', $selection, $matches ) == 1 ) {

			$this->where = '_submitted_>=\'' . $matches[1] . '\' AND ' . '_submitted_<\'' . $matches[2] . '\'';

		} else if( preg_match( '/[\d,]/', $selection ) ) {

			$this->where = 'rowid IN (' . $selection . ')';

		} else {

			writeErrorLog( 'Failed to interpret record selector:', $selection );
			$this->error = 'Failed to interpret record selector. Allowed formats are: "all", "4,6,7", "new", "starred", "3days", "from20to40" and "from2011-07-29 10:27:18to2011-07-30 10:27:18"';
		
			return false;
		}

		return true;
	}


	function GetPagedData ( ) {

		// define some sensible defaults
		$input = array();
		$input['page'] = ( isset( $_GET['page'] ) ? $_GET['page'] : 1 ); 
		$input['pagelength'] = ( isset( $_GET['items_per_page'] ) ? $_GET['items_per_page'] : 20 ); 

		if( $this->fields !== false ) {
			$this->db->SetFieldClause( $this->fields ); 
		}

		if( $this->where !== false ) {
			$this->db->SetWhereClause( $this->where ); 
		}

		if( ! $this->_SetSortOrder() ) {
			return false;
		}

		$this->data['data'] =& $this->db->GetPage( $input['page'], $input['pagelength'] );

		if( $this->data['data'] === false ) {
			$this->data['error'] = 'Failed to get data, check the log.';
			return false;
		}

		$this->_PostProcessData();
		$this->data['pagination'] = $this->db->GetPagination();

		return $this->data;
	}


	function GetAllData( ) {

		if( $this->where !== false ) {
			$this->db->SetWhereClause( $this->where ); 
		}

		if( $this->fields !== false ) {
			$this->db->SetFieldClause( $this->fields ); 
		}

		if( ! $this->_SetSortOrder() ) {
			return false;
		}

		$this->data['data'] =& $this->db->GetAllRows();

		if( $this->data['data'] === false ) {
			$this->data['error'] = 'Failed to get data.';
			return false;
		}

		$this->_PostProcessData();

		return true;
	}


	function GetItemData ( ) {

		if( ! isset( $_GET['rowid'] ) ||
			! is_numeric( $_GET['rowid'] ) ) {
			
			$this->data[ 'error' ] = 'Row ID must be numeric.';
			return false;
		}

		$this->data['data'] =& $this->db->GetItemData( $_GET['rowid'] );

		if( $this->data['data'] === false ) {
			$this->data['error'] = $this->db->error;
			return false;
		}

		// findout if there is any date fromatting to be done
		$formatfields = $this->_GetDateFieldNamesInOutput();
		foreach( $formatfields as $name ) {
			$this->data['data'][$name] = date( $this->page->GetDateFormatByFieldname( $name ),
											   strtotime( $this->data['data'][ $name] ) );
		}

		$this->data['files'] =& $this->db->GetItemFiles( $_GET['rowid'] );

		if( $this->data['files'] === false ) {
			$this->data['error'] = $this->db->error;
		}

		return true;
	}


	function UpdateItem ( ) {

		if( ! isset( $_GET['rowid'] ) ||
			! is_numeric( $_GET['rowid'] ) ) {

			$this->data[ 'error' ] = 'Row ID must be numeric.';
			return false;
		}

		$read = false;
		$starred = false;

		if( ! $this->_GetUpdateData( $read, $starred ) ) {
			// nothing to update
			return true;;
		}

		if( $this->db->UpdateItem( $_GET['rowid'], $read, $starred ) )

			$this->data['result'] = $this->db->result;
		else
			$this->data['error'] = $this->db->error;

		return true;
	}


	// limits the number of columns to return and possibly the max width of the text for each column
	// get the first x columns from the database, respecting any columns selected by the user	
	function SetSummaryMode ( $maxcolumns, $maxwidth ) {

		$specials = array( '_fromaddress_', '_submitted_', '_read_', '_starred_' );

		if( $maxwidth != -1 )			$this->maxwidth = $maxwidth;

		if( ! $this->db->HasSetting( 'columns' ) ) {

			// get all available columns from the database because no user settings available
			$cols = $this->db->GetAllFields();
			if( $maxcolumns == -1 ) {

				$this->fields = $cols;
				
			} else {

				$this->fields = array();

				foreach( $cols as $fld ) {

					if( $maxcolumns-- <= 0 )		break;

					if( ! in_array( $fld, $specials ) ) {
	
						$this->fields[] = $fld;
					}

				}
			}

		} else {

			// get the columns from the db + the user settings
			$cols =& $this->_GetColumnsData();

			// order the values, maintaining the keys 
			asort( $cols );

			// build the fields string
			$this->fields = array( 'rowid' );

			foreach( $cols as $name => $state ) {

				// skip these names
				if( $state == 0 || in_array( $name, array( 'rowid' ) ) ) {
					
					continue;
				}

				// include these names
				if( $state != 0 )			$this->fields[] = $name;
			}			
		}

		// always include the special fields
		$this->fields = array_merge( $this->fields , $specials );
		$this->fields = array_unique( $this->fields );

		return true;
	}


	// returns an array of all available columns and if they are selected for the summary display
	// format: array( field_name => 0/1,....), 0/1 being no/yes included in summary
	function _GetColumnsData ( ) {

		// get all available columns from the database
		$cols = $this->db->GetAllFields();

		// get any user preference for a column
		$tmp = $this->db->GetSetting( 'columns' );
		$prefs = empty( $tmp ) ? array() : json_decode( $tmp, true );
		$columns = array();

		foreach( $cols as $name ) {

			if( ! isset( $prefs[ $name ] ) )		$columns[ $name ] = 1;
			else									$columns[ $name ] = $prefs[ $name ];
		}

		return $columns;
	}

	
	// value 0 -> don't include
	// value 1 -> include, lower numbers come first
	function UpdateColumns ( $columns ) {

		// merge the input with the stored data
		$this->data['data'] =& $this->_GetColumnsData();

		$count = 0;
		foreach( $this->data['data'] as $name => $state ) {

			if( ! isset( $columns[ $name ] ) )			continue;
			
			if( ! is_numeric( $columns[ $name ] ) ) {

				$this->data[ 'error' ] = 'columns must have a numeric value, columns are ordered ascending'; 
				return false;
			}

			$this->data['data'][ $name ] = (int)$columns[ $name ];
			$count++;
		}

		if( ! $count && count( $columns) > 0 )	{
			$this->data[ 'error' ] = 'None of the specified columns was found in the database.';
			return false;
		}
		 
		if(	! $this->db->SetSetting( array( 'columns' => json_encode( $this->data['data'] ) ) ) ) {

				$this->data[ 'error' ] = $this->db->errors[0]['err'];
				return false;
		}

		if( $count )
			$this->data[ 'result' ] = 'Stored column configuration, ' . $count . ' column state(s) updated.';

		return true;
	}


	function UpdateRows ( ) {

		$read = false;
		$starred = false;

		if( ! $this->_GetUpdateData( $read, $starred ) ) {
			// nothing to update
			return true;;
		}

		if( $this->where !== false ) {

			if( $this->db->UpdateWhere( $this->where, $read, $starred ) )
				$this->data['result'] = $this->db->result;
			else
				$this->data['error'] = $this->db->error;

		} else {

			$this->data[ 'error' ] = 'Specify a selection criteria and try again.';
			return false;
		}

		return true;
	}


	function DeleteRows ( ) {

		if( $this->where !== false ) {

			if( $this->db->DeleteWhere( $this->where ) )
				$this->data['result'] = $this->db->result;
			else
				$this->data['error'] = $this->db->error;

		} else {

			$this->data[ 'error' ] = 'Specify a selection criteria and try again.';
			return false;
		}

		return true;
	}


	function _Connect ( ) {

		// this should work on s-drive only
		if( ! isset( $_GET['secret'] ) ||
			! class_exists( 'SdriveACL' ) ||
			! SdriveACL::isAllowed( $_SERVER['REMOTE_ADDR'], $_GET['secret'] ) )
		{
			header("HTTP/1.1 401 Unauthorized");
			exit('Access denied.');
		}

		// connect to the database
		if( $this->db === false ) {
			$this->db = new DataAccessSQLite( $this->data_settings['save_sqlite'] );
		}
	}


	function _SetSortOrder ( ) {

		if( isset( $_GET['sort_field'] ) && $_GET['sort_field'] != '' ) {

			$order_desc = ( isset( $_GET['order_desc'] ) && $_GET['order_desc'] );

			if( ! $this->db->SetSortOrder( $_GET['sort_field'], ! $order_desc ) ) {

				$this->data['error'] = 'Failed to set sort order.';
				return false;
			}
		}

		return true;
	}


	function _PostProcessData ( ) {

		// add the fields to the output first, we'll use this info below
		$this->_SetFields();

		// findout if there is any date fromatting to be done
		$formatfields = $this->_GetDateFieldNamesInOutput();

		// check if there is anything to be done before iterating over the data
		if( ! is_array( $this->data['data'] ) ||
			(empty( $formatfields ) && $this->maxwidth == -1 ) )	return;

		for( $i = 0; $i < count( $this->data['data'] ); $i++ ) {

			foreach( $this->data['data'][ $i ] as $key => &$value ) {

				if( in_array( $key, $formatfields ) && ! empty( $value ) ) {
					
					// this only works if $value is by reference in the foreach clause
					$value = date( $this->page->GetDateFormatByFieldname( $key ) , strtotime( $value ) );
				}

				if( $this->maxwidth != -1 &&
					strlen( $value ) > $this->maxwidth &&
					! in_array( $key, array('_submitted_', 'rowid', '_fromaddress_' ) ) ) {

					$this->data['data'][ $i ][ $key ] = substr( $value, 0, $this->maxwidth ) . '...';
				}
			}
		}
	}


	function _GetUpdateData ( &$read, &$starred ) {

		if( isset( $_GET['read'] ) )		$read = $_GET['read'] ? 1 : 0;
		else								$read = false;

		if( isset( $_GET['starred'] ) )		$starred = $_GET['starred'] ? 1 : 0;
		else								$starred = false;

		// return false if nothing defined
		if( $read === false && $starred === false )			return false;
		else 												return true;
	}


	function _SetFields ( ) {
		
		if( $this->fields !== false && 					// add the used output fields when in summary mode
			is_array( $this->data['data'] ) &&
			isset( $this->data['data'][0] ) )			// use the keys of the data map if it is available
			$this->data['fields'] = array_keys( $this->data['data'][0] );
		
		// always include all fields in the output
		$this->data['all_fields'] = $this->db->GetAllFields();

		// add the field that is used for sorting
		if( $this->db->sort !== false ) {
			$this->data['sort_field'] = $this->db->sort['field'];
		}
	}


	// only call this AFTER a call to _SetFields()
	function _GetDateFieldNamesInOutput ( ) {

		$cfg =& $this->page->GetConfig( 'rules' );
		$datefields = array();

		// which fields are being used?
		if( isset( $this->data['fields'] ) )
		   	$outputfields = $this->data['fields'];
		else if( isset( $this->data['all_fields'] ) )
		   	$outputfields = $this->data['all_fields'];
		else
			$outputfields = array_keys( $this->data['data'] );
		
		if( ! is_array( $outputfields ) ) 	return array();

		foreach( $outputfields as $fn ) {

			if( isset( $cfg[ $fn ] ) && $cfg[ $fn ]['fieldtype'] == 'date' )
				$datefields[] = $fn;
		}

		// add them to the output, it might be needed for column alignment
		if( ! empty( $datefields ) )		$this->data['date_fields'] = $datefields;

		return $datefields;
	}
}

?>