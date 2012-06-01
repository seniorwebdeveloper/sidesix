<?php

/**
 * CoffeeCup Software's Web Form Builder.
 *
 * Methods to handle CSV data files.
 *
 *
 * @version $Revision: 2456 $
 * @author Cees de Gruijter
 * @category FB
 * @copyright Copyright (c) 2011 CoffeeCup Software, Inc. (http://www.coffeecup.com/)
 */


class DataSaveCSV extends DataSave {

	var $fp;			// file handle
	var $outfile;

	function DataSaveCSV ( $settings ) {
		parent::DataSave( $settings );
	}


	function Save ( ) {

		if( $this->_GetFileHandle() ) {

			$data = array();
			$this->_FlattenPost();

			foreach( $this->rules as $field => $rule ) {

				if( ! isset( $this->post[ $field  ] ) ) {

					// it could still be an uploaded file
					$notfound = true;
					foreach( $this->page->uploads as $up ) {

						if( $up[ 'fieldname' ] == $field ) {
							$data[] = $up[ 'storedname' ];
							$notfound = false;
							break;
						}
					}
					if( $notfound )		$data[] = '';

				} else if( $rule['fieldtype'] == 'date' && ! empty( $this->post[ $field ] ) ) {
					$data[] = date( $this->page->GetDateFormatByFieldname( $field ), $this->post[ $field ] );
				} else {
					$data[] = $this->post[ $field ];
				}
			}

			foreach( $this->page->GetReservedFields() as $name ) {
				$data[] = isset( $this->post[ $name ] ) ? $this->post[ $name ] : '';
			}

			fputcsv( $this->fp, $data );
			fclose( $this->fp );

		} else {

			$this->errors[] = array( 'err' => _T('Failed to record the data because the server is too busy or doesn\'t have write permission.') );			
		} 
	}


	// First line in the output file is the field list.
	// Archive the file if this doesn't coincide with the current field list
	function _GetFileHandle ( ) {

		// where to save
		$this->output_file = $this->page->GetStorageFolder( 3 ) . $this->settings['filename'];

		if( ! file_exists( $this->output_file) ) {

			return $this->_MakeNew();

		} else if( ! $this->_CheckFields() ) {
			
			return $this->_Archive() && $this->_MakeNew();

		} else {

			if( ! is_writable( $this->output_file ) ) {
				writeErrorLog( 'Output file is not writable:', $this->output_file );
				return false;	
			}
			$this->fp = fopen( $this->output_file, 'a' );
			return $this->_GetLock( LOCK_EX );
		}
	}


	function _MakeNew ( ) {

		if( ! is_writable( dirname( $this->output_file ) ) ) {
			writeErrorLog( 'Output folder is not writable:', $this->output_file );
			return false;	
		}

		$this->fp = fopen( $this->output_file, 'a' );
		if( ! $this->_GetLock( LOCK_EX ) )		return false;

		$columns = array_keys( $this->rules );

		foreach( $this->page->GetReservedFields() as $name ) {
			$columns[] = $name;
		}

		fputcsv( $this->fp, $columns );

		// leave file open for next write operation
		return true;
	}


	function _GetLock ( $locktype ) {

		if( ! $this->fp || ! getFileLock( $this->fp, $locktype ) ) {

			writeErrorLog( 'Failed to obtain lock on:', $this->output_file );
			if( $this->fp !== false )		fclose( $this->fp );
			return false;
		}

		return true;
	}


	function _CheckFields ( ) {

		$this->fp = fopen( $this->output_file, 'r' );

		if( ! $this->_GetLock( LOCK_SH ) ) {

			fclose( $this->fp );
			return false;
		}

		$fields = fgetcsv( $this->fp );
		fclose( $this->fp );

		// fields must be the same as rules + reserved_fields_count	
		$delta = array_diff( $fields ,array_keys( $this->rules ));

		// use array_values because array_diff respects the keys, thus those will be different
		return array_values( $delta) == array_values( $this->page->GetReservedFields() );

		// field count must be at least: rules_count + reserved_fields_count
		return count( array_diff( $fields, array_keys( $this->rules ) ) ) == count( $this->page->GetReservedFields() );
	}
			
	
	function _Archive( ) {

		$i = 1;
		do {
			$newname = str_replace( '.', $i . '.', $this->output_file );
			$i++;
		} while ( file_exists( $newname ) );

		if( ! rename( $this->output_file, $newname ) ) {

			writeErrorLog( 'Failed to archive ' . $this->output_file . ' to:', $newname );
			$this->errors[] = array( 'err' => _T('Failed to archive data file.') );
			return false;
		}
		return true;
	}

}



?>