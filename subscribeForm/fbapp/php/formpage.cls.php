<?php

/**
 * CoffeeCup Software's Web Form Builder.
 *
 * Base class for anything related to the page.
 *
 *
 * @version $Revision: 2456 $
 * @author Cees de Gruijter
 * @category FB
 * @copyright Copyright (c) 2011 CoffeeCup Software, Inc. (http://www.coffeecup.com/)
 */

require 'fbapp/inc/utilities.inc.php';

define( 'CONFIG_FILE', 'form.cfg.php');
define( 'CONFIG_FILE_SDRIVE', 'form.cfg.dat');

define( 'CC_FB_STORAGE_FOLDER', '/storage/' );		// where all data is stored
define( 'CC_FB_PREFIX', 'fb_' );
define( 'CC_FB_UPLOADS_DIRECTORY', 'files/' );		// where files are uploaded, need not be publicly visible
define( 'CC_FB_PUBLIC_DIRECTORY', 'public/' );		// where uploaded files are copied to for public visibility
define( 'CC_FB_DB_DIRECTORY', 'db/' );				// where sqlite stores its files
define( 'CC_FB_CSV_DIRECTORY', 'csv/' );			// where csv files are stored

define( 'HTMLENTITY_FLAGS', ENT_COMPAT );

class FormPage {

	private static $instance = null;	// me...for singleton
	private $name = '';					// name of the form
	public $source = false;				// form markup to display
	private $errors = array();			// validation errors
	public $post = false;				// any valid data the user has send
	private $config = false;			// form configuration
	public $sdrive = false;				// sdrive configuration
	private $stats = false;				// statistics reporter

	private $mysql = false;				// db instance
	private $sqlite = false;			// db instance
	private $csv = false;				// db instance
	private $email = false;				// emailer
	public $uploads = array();			// remember upload-fields relationship, info is needed for reporting


	private function __construct ( $formname ) {
		$this->name = $formname;
	}


	// singleton instance is only created when the formname is specified!
	public static function GetInstance ( $formname = false ) {

		if( ! isset( self::$instance ) && $formname !== false ) {

			$className = __CLASS__;
			self::$instance = new $className( $formname );
		}

		return self::$instance;
	}

	


	function ReadSource ( ) {

		$filename = $this->name . '.html';
		$this->source = file_get_contents( $filename , FILE_USE_INCLUDE_PATH );

		if( $this->source === false ) {
			writeErrorLog( 'Couldn\'t open or read:', $filename );
		}

		if( $this->sdrive !== false ) {

			// sdrive has the recaptcha keys if needed
			$this->source = str_replace( '_FB_RECAPTCHA_', $this->sdrive['recaptcha_public_key'], $this->source );

		}

		// changed if condition: check the URI instead of testing $this->sdrive - this should work also
		// on the sdrive version that still uses de myform.php entry point
		// match '.php' ignoring a query string that my follow it
		if( preg_match( '/\.php(?:\?.*)?$/i', $_SERVER['REQUEST_URI'] ) ) {

			// adjust paths, because source may have paths relative to its own location for stand-alone use
			// not needed for sdrive, because form is accessed like: ..../formname/ instead of ..../formname.php
				$this->source = str_replace( 'data-name=""', 'data-name="' .rawurlencode($this->name ). '/"', $this->source );
				$this->source = str_replace( 'href="theme/', 'href="' . rawurlencode($this->name) . '/theme/', $this->source );
				$this->source = str_replace( 'src="common/', 'src="' . rawurlencode($this->name) . '/common/', $this->source );
				$this->source = str_replace( 'url(common/', 'url(' . rawurlencode($this->name) . '/common/', $this->source );
				$this->source = str_replace( 'url(theme/', 'url(' . rawurlencode($this->name) . '/theme/', $this->source );
				$this->source = str_replace( '../' . rawurlencode($this->name) . '.php', rawurlencode($this->name) . '.php', $this->source );
		}
	}

	
	function SetSdriveConfig( $config ) {

		$this->sdrive =& $config;

		if( isset( $this->sdrive[ 'sdrive_account_formbuilder_stats' ] ) &&
			! empty( $this->sdrive[ 'sdrive_account_formbuilder_stats' ] ) ) {

			$this->stats = new StatsReporter( $this->sdrive['sdrive_account_id'],
											  $this->GetFormName(),
											  $this->sdrive[ 'sdrive_account_host' ],
											  $this->sdrive[ 'sdrive_account_formbuilder_stats' ] );
		}
	}


	// read form config from the json text file and
	// return the required section or the whole config if section is not given.
	function GetConfig ( $section = false ) {

		if( ! $this->config ) {
	
			// time zone must be set to avoid warnings when handling dates
			date_default_timezone_set( 'UTC' );

			$txt = file_get_contents( ($this->sdrive ? CONFIG_FILE_SDRIVE : CONFIG_FILE) , FILE_USE_INCLUDE_PATH );

			if( $txt === false ) {
				writeErrorLog( 'Couldn\'t open or read:', CONFIG_FILE );
				echo '<html><body>Configuration missing.</body></html>';
				exit();
			}

			$this->config = json_decode( substr( $txt, strpos( $txt, "{" ) ), true );

			if( $this->config == NULL ) {
				$this->SetErrors( array( array( 'err' => 'Failed to read or decode form configuration.' ) ) );
				writeErrorLog( 'Couldn\'t decode:', ($this->sdrive ? CONFIG_FILE_SDRIVE : CONFIG_FILE) );
				return false;
			}

			// move all settings that are not fields 1 level up 
			if( isset( $this->config[ 'rules' ][ '_special' ] ) ) {

					$this->config[ 'special' ] = $this->config[ 'rules' ][ '_special' ];
					unset( $this->config[ 'rules' ][ '_special' ] );
			}
		}

		if( isset( $this->config['timezone'] ) )		date_default_timezone_set( $this->config['timezone'] );	

		#print_r( $this->config );

		if( $section === false )						return $this->config;
		else if( isset( $this->config[ $section ] ) )	return $this->config[ $section ];
		else											return false;	
	}


//TODO change this once the rules are formatted like id=>rule instead of name=>rule for jquery compatability. 
	// get a validation rule property by field name
	// return false if the rule or property isn't found
	function GetRulePropertyByName ( $fieldname, $property ) {
		
		$rules =& $this->GetConfig( 'rules' );

		if( isset( $rules[ $fieldname ] ) &&
			isset( $rules[ $fieldname ][ $property ] ) ) {

			return $rules[ $fieldname ][ $property ];
		}
		
		return false;
	}


	function SetPostValues ( $post ) {

		$this->post = $post;

		// since this means the data is valid and ready to store, we might as well add 
		// ip address and timestamp
		$this->post[ '_submitted_' ] = date('Y-m-d H:i:s');

		if( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {

			list( $this->post[ '_fromaddress_' ] ) = explode( ',', $_SERVER['HTTP_X_FORWARDED_FOR'] );

		} else {

			$this->post[ '_fromaddress_' ] = $_SERVER['REMOTE_ADDR'] ;
		}
	}


	function SetErrors ( $errors ) {

		$this->errors = array_merge( $this->errors, $errors );
	}


	function GetErrors ( $assoc = false ) {

		if( !$assoc )			return $this->errors;

		$errors = array();
		foreach( $this->errors as $err ) {
			if( isset( $err['field'] ) )		$errors[ $err['field'] ] = $err['err'];
			else if( isset( $err['err'] ) )		$errors[] = $err['err'];
			else								$errors[] = $err['warn'];
		}

		return $errors;
	}


	function GetErrorCount ( ) {

		return count( $this->errors );
	}


	function Show ( ) {

		// check if this happens to be a confirmation page
		if( isset( $_GET['confirmation'] ) ) {

			session_start();
			if( isset( $_SESSION['code'] ) ) {

				// serve this contents only once
				echo unserialize( $_SESSION['code'] );
				unset( $_SESSION['code'] );
				return;

			} else {

				// a second time, redirect to the original form
				ob_end_clean();
				header( 'Location: ' . substr( $_SERVER['REQUEST_URI'], 0, strpos( $_SERVER['REQUEST_URI'], '?confirmation' ) ) );
				exit();
			}
		}
		
		if( $this->source === false ) 			$this->ReadSource();

		if( $this->source !== false ) 			echo $this->source;
		else									trigger_error( 'Read the template BEFORE trying to send it to a user.', E_USER_WARNING );
	}


	function ReportStats( $type, $param = false ) {
		
		if( ! $this->stats )		return;
		
		if( method_exists( $this->stats, $type ) ) {

			if( $param !== false )				$this->stats->$type( $param );
			else								$this->stats->$type();

		} else {

			writeErrorLog('Call to undefined statistcs reporter method:', $type );
		}
	}


	// return error count
	function ProcessPostedData ( ) {

		$cfg =& $this->GetConfig( 'settings' );

		// load the storage extensions

		// when on sdrive, never enable mysql storage, no matter what the config setting is
		if( ! $this->sdrive &&
			isset( $cfg['data_settings']['save_database'] ) &&
			$cfg['data_settings']['save_database']['is_present'] == true ) {
			
			$this->mysql = new DataSaveMySQL( $cfg['data_settings']['save_database'] );
			$this->errors = array_merge( $this->errors, $this->mysql->errors );
		}

		// when on sdrive, alway enable sqlite, no matter what the config setting is
		if( isset( $cfg['data_settings']['save_sqlite'] ) &&
			( $cfg['data_settings']['save_sqlite']['is_present'] == true || $this->sdrive ) ) {

			$this->sqlite = new DataSaveSQLite( $cfg['data_settings']['save_sqlite'] );
			$this->errors = array_merge( $this->errors, $this->sqlite->errors );
		}

		// when on sdrive, never enable csv storage, no matter what the config setting is
		if( ! $this->sdrive &&
			isset( $cfg['data_settings']['save_file'] ) &&
			$cfg['data_settings']['save_file']['is_present'] == true ) {

			$this->csv = new DataSaveCSV( $cfg['data_settings']['save_file'] );
			$this->errors = array_merge( $this->errors, $this->csv->errors );
		}

		if( ( isset( $cfg['email_settings']['auto_response_message'] ) &&
			  $cfg['email_settings']['auto_response_message']['is_present'] == true ) ||
			( isset( $cfg['email_settings']['notification_message'] ) &&
			  $cfg['email_settings']['notification_message']['is_present'] == true ) ) {

			$this->email = new DataSaveMailer( $cfg['email_settings'] );
			$this->errors = array_merge( $this->errors, $this->email->errors );
		}

		if( $this->GetErrorCount() > 0 )		return false;

		// handle the data
		$this->_ProcessFiles();
		$this->_ProcessPost();

		if( $this->GetErrorCount() > 0 )		return false;

		// anything else we need to do before returning to the user
		if( isset( $cfg[ 'mailchimp' ] ) &&
			is_array( $cfg[ 'mailchimp' ]['lists'] ) ) {
				
			foreach( $cfg[ 'mailchimp' ]['lists'] as $list ) {

				if( isset( $list[ 'is_present' ] ) && $list[ 'is_present' ] ) {

					$mc = new MailChimp();
					$mc->Dispatch( $cfg[ 'mailchimp' ] );
					break;
				}
			}
		}

		return count( $this->errors ) == 0;
	}


	function HandleErrors ( ) {

		// check the configuration to determine what to do
		$cfg =& $this->GetConfig( 'settings' );

		switch ( $cfg[ 'validation_report' ] ) {

		case 'in_line':

			// get the html file and merge the errors into it
			include 'fbapp/inc/mergeformpost.inc.php';
			$this->ReadSource();
			$this->source = MergeFormPost();
			break;

		case 'separate_page':
		default:
			ob_start();
			include 'fbapp/inc/displayerrors.inc.php';
		}
	}


	function GetFormName ( $encoded = false ) {
		return $encoded ? rawurlencode( $this->name ) : $this->name;
	}


	private function _ProcessFiles ( ) {

		if( empty( $_FILES ) )		return;

		if( $this->mysql !== false ) {
			$this->mysql->SaveUploads();
			$this->errors = array_merge( $this->errors, $this->mysql->errors );
		}

		if( $this->sqlite !== false ||
			$this->csv !== false )  {

			$this->_SaveUploadsAsFiles();

			if( $this->sqlite ) $this->sqlite->UpdatePost( $this->post );
			if( $this->csv ) $this->csv->UpdatePost( $this->post );

		} else {

			//check if there is a file upload that must be saved even without database
			$this->_SaveUploadsAsFiles();
		}
	}


	private function _SaveUploadsAsFiles ( ) {

		$dest = $this->GetStorageFolder( 1 );

		if( ! is_dir( $dest ) && !mkdir( $dest, 0755 ) ) {
			$this->errors[] = array("err" => _T('Could not create file upload directory "%s"', $dest ) );
			return;
		}

		foreach( $_FILES as $fieldname => $filedata ) {

			if( empty( $filedata['tmp_name'] ) )
				continue;

			// check if the file is mentioned in the rules
			if( $this->GetRulePropertyByName( $fieldname, 'fieldtype' ) == 'fileupload' ) {

				// check if the file must be saved on the server
				if( ! $this->GetRulePropertyByName( $fieldname, 'files' ) ) {

					// only store the name in post, but don't save the file
					$this->post[ $fieldname ] = $filedata['name'];

					continue;
				}

			} else {

				// not a upload
				continue;
			}

			$storedname = SaveUploadAsFile( $dest, $filedata );

			// add it to post, mailer needs it if the file is to be attached
			if( $storedname !== false )		$this->post[ $fieldname ] = $storedname;

			// remember which files are stored for which fields, we need that info
			// when reporting data, because in that context we don't have access to the rules
			$this->uploads[] = array( 'orgname' => $filedata['name'],
									  'storedname' => $storedname,
									  'fieldname' => $fieldname );
		}
	}
	

	private function _ProcessPost ( ) {

		if( $this->mysql !== false ) {
			$this->mysql->Save();
			$this->mysql->UpdateStoredFileIds();
			$this->mysql->SaveUploadsRef( $this->uploads );
			$this->errors = array_merge( $this->errors, $this->mysql->errors );
		}

		if( $this->sqlite ) {
			$this->sqlite->Save();
			$this->sqlite->SaveUploadsRef( $this->uploads );
			$this->errors = array_merge( $this->errors, $this->sqlite->errors );
		}

		if( $this->csv !== false ) {
			$this->csv->Save();
			$this->errors = array_merge( $this->errors, $this->csv->errors );
		}

		if( $this->email !== false ) {
			$this->email->Save();
			$this->errors = array_merge( $this->errors, $this->email->errors );
		}
	}


	// make a page based on the custom html from the user
	function PrepareConfirmPage ( ) {

		$cfg = $this->GetConfig( 'settings' );
		$this->source = $cfg['redirect_settings']['confirmpage'];
		 
		if( empty( $this->source ) ) {

			$this->source = false;
			return;

		} else if( empty( $this->post ) )
			return;

		$this->source = $this->SubstituteFieldNames ( $this->source );
	}


	// substitute form contents by the custom html from the user
	function PrepareInlineConfirm ( ) {
		 
		// get the html
		$this->ReadSource();

		if( ($dom = DOMDocument::loadHTML( $this->source )) === false ) {
			writeErrorLog('Failed to parse HTML form.');
			return false;
		}

		// get the confirm message
		$cfg =& $this->GetConfig( 'settings' );
		$html = $cfg['redirect_settings']['inline'];
		$usernode = false;

		if( ! empty( $html ) ) {

			$html = $this->SubstituteFieldNames( $html );

			// since there is no meta-tag in this html fragment, we need to tell DOMDocument
			// it with a meta-tag prefix
			$userdom = DOMDocument::loadHTML( '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">' . $html );

			if( $userdom !== false ) {

				$xpath = new DOMXpath( $userdom );
				$bodies = $xpath->query( '//body' );

				if( $bodies->length > 0 )
					$root = $bodies->item( 0 );
				else
					$root = $xpath->query( '/*' )->item( 0 );

				$usernode = $dom->importNode( $root, true );
			}
		}

		if( ! $usernode )
			$usernode = $dom->createTextNode ( "Thank your for filling in the form." );

		// find the container for the message
		$container = $dom->getElementById( 'fb_confirm_inline' );
		if( ! $container ) {
			writeErrorLog('Parsed HTML form, but can\'t locate element with id "#fb_confirm_inline".');
			return false;
		}

		// remove all child nodes 
		while( $container->hasChildNodes() ) {
			$container->removeChild( $container->firstChild );
		}

		// add our html and  change the 'display:none' style to 'display:block' 
		$container->appendChild( $usernode );
		$style = str_replace( 'none', 'block', $container->getAttribute( 'style' ) );
		
		$container->setAttribute( 'style', $style );

		// remove all siblings, go up to parent ... until we arrive at the form 
		do {

			while( $container->previousSibling ) {
				$container->parentNode->removeChild( $container->previousSibling ); 
			}

			while( $container->nextSibling ) {
				$container->parentNode->removeChild( $container->nextSibling ); 
			}

			$container = $container->parentNode;

		} while( $container->getAttribute( 'id' ) != 'docContainer' );

		$this->source = $dom->saveHTML();

		return true;
	}


	function SubstituteFieldNames ( $text, $useHtmlEntities = true ) {

		$replacements = array();

		foreach( $this->post as $field => $value ) {

			$needles[] = '[' . $field . ']';
			$replacements[] = $this->_FormatFieldValue( $field, $value, $useHtmlEntities );
		}

		foreach ( $this->uploads as $up ) {

			$needles[] = '[' . $up[ 'fieldname' ] . ']';
			$replacements[] = $up[ 'orgname' ];
		}

		$text = str_replace( $needles, $replacements, $text );

		if( strpos(	$text, '[form_results]') !== false ) {

			$text = str_replace( '[form_results]', $this->_FormatFormContents( $useHtmlEntities ), $text );

		} else {

			foreach ( $this->GetReservedFields() as $name ) {

				// only include those words that are present in post, else remove tag
				if( isset( $this->post[ $name ] ) )
					$text = str_replace( '[' . $name . ']', $this->post[ $name ], $text );
				else
					$text = str_replace( '[' . $name . ']', '', $text );
			}
		}

		// anything not substituted by now is not present in the form, let's remove the placeholders
		$fieldnames = array_keys( $this->GetConfig( 'rules' ) );
		foreach( $fieldnames as $field ) {

			if( strpos( $text, '[' . $field .']' ) !== false )
				$text = str_replace( '[' . $field .']', '', $text );
		}

		return $text;
	}


	private function _FormatFormContents ( $useHtmlEntities = true ) {
		
		$form_contents = '<table><tbody style="vertical-align: text-top;line-height: 30px;">';

		// use the rules to list output in the right order
		$cfg =& $this->GetConfig( 'rules' );

		foreach( $cfg as $name => $rule ) {
			
			// first check if the post value exist
			if( isset( $this->post[ $name ] ) ) {

				$form_contents .= '<tr><td>' . $this->_FindLabelByFieldName( $name )
							   .  ':</td><td>'. $this->_FormatFieldValue( $name, $this->post[ $name ], $useHtmlEntities ) . '</td></tr>';

			}
			// check the file uploads only if the post[] doesn't exist, 
			// this avoids listing the same file twice which may happen when
			// saving for sqlite and csv adds these fields to post, but
			// for mysql with files stored in a table this doesn't happened
			elseif( isset( $this->uploads[ $name ] ) ) {

				$form_contents .= '<tr><td>' . $this->_FindLabelByFieldName( $name )
							   .  ':</td><td>'.  $this->uploads[ $name ][ 'orgname' ] . '</td></tr>';
			}
		}

		// finally add the reserved keywords
		foreach ( $this->GetReservedFields() as $name ) {

			// only include those words that are present in post
			if( ! isset( $this->post[ $name ] ) )				continue;

			$form_contents .= '<tr><td>' . $this->_FindLabel( $name )
						   .  ':</td><td>'. $this->post[ $name ] . '</td></tr>'; 
		}

		$form_contents .= '</tbody></table>';

		return $form_contents;
	}


	private function _FormatFieldValue ( $field, $value, $useHtmlEntities ) {

		if( $this->GetRulePropertyByName( $field, 'fieldtype' ) == 'date' && ! empty( $value ) )
			return date( $this->GetDateFormatByFieldname( $field ), $value );

		if( $this->GetRulePropertyByName( $field, 'fieldtype' ) == 'textarea' )
			return nl2br( $useHtmlEntities ? htmlentities( $value, HTMLENTITY_FLAGS, 'UTF-8' ) : $value );

		if( is_array( $value ) ) $value = implode( $value, ', ');
		return $useHtmlEntities ? htmlentities( $value, HTMLENTITY_FLAGS, 'UTF-8' ) : $value;
	}


	// return path to file system storage location
	function GetStorageFolder ( $which ) {

		global $scriptpath;

		if( $this->sdrive ) {

			$storage = $this->sdrive[ 'sdrive_account_datastore_path' ] . DIRECTORY_SEPARATOR . CC_FB_PREFIX;

		} else {
	
			$storage = $scriptpath . CC_FB_STORAGE_FOLDER;
		}

		switch ( $which ) {
			case 1:				//uploaded files
				return $storage . CC_FB_UPLOADS_DIRECTORY;
			
			case 2:				// database location
				return $storage . CC_FB_DB_DIRECTORY;

			case 3:				// csv location
				return $storage . CC_FB_CSV_DIRECTORY;

			case 4:				// publicly visible uploads
				return $storage . CC_FB_PUBLIC_DIRECTORY;

			default:
				writeErrorLog( 'Storage folder ID is not defined:', $which );
		}
		return false;		
	}


	private function _FindLabelByFieldName ( $name ) {
		
		$cfg =& $this->GetConfig( 'rules' );

		$label = $cfg[ $name ]['label'];

		return empty( $label) ? $name : $label;
	}


	// used by the storage engines to create additional columns
	function GetReservedFieldTypes ( ) {

		// note: don't change the order in the array, because the CSV file goes by position!
		return array( '_submitted_' => 'datetime',		// format: YYYY-MM-DD HH:MM:SS
					  '_fromaddress_' => 'ipaddress',
					  '_flags_'	=> 'number' );
	}


	// used by the page for display purposes, exclude fields that are not for display
	function GetReservedFields ( ) {

		static $names = array();

		if( empty( $names ) ) {
			$names = array_diff( array_keys( $this->GetReservedFieldTypes() ), array( '_flags_' ) );
		}

		return $names;	
	}


	private function _FindLabel ( $key ) {

		static $labels = array( '_submitted_' => 'Submitted On',
								'_fromaddress_' => 'IP Address' );
		
		return isset( $labels[ $key ] ) ? $labels[ $key ] : $key;
	}


	// return format string suitable for date() defined for a field, default to the iso format
	function GetDateFormatByFieldname ( $fieldname ) {

		$date_formats = array(
			'US_SLASHED' => 'm/d/Y',
			'ISO_8601' => 'Y-m-d',
			'RFC_822' => 'D, j M y',
			'RFC_850' => 'l, d-M-y',
			'RFC_1036' => 'D, d M y',
			'RFC_1123' => 'D, j M Y',
			'COOKIE' => 'D, d M Y',
			'DATE_CUSTOM_1' => 'd/m/Y',
			'DATE_CUSTOM_2' => 'Y/m/d',
			'DATE_CUSTOM_3' => 'm-d-Y',
			'DATE_CUSTOM_4' => 'd-m-Y',
			'DATE_CUSTOM_5' => 'd.m.Y',		// 20.02.2012
			'DATE_CUSTOM_6' => 'd.m.y',		// 20.02.12
			'DATE_CUSTOM_7' => 'd:m:Y', 	// 20:02:2012
			'DATE_CUSTOM_8' => 'd.F y',		// 20.February 12
			'DATE_CUSTOM_9' => 'd.F'		// 20.February
			);

		$cfg =& $this->GetConfig( 'rules' );

		if( isset( $cfg[ $fieldname ] ) && $cfg[ $fieldname ]['fieldtype'] == 'date' )
			return $date_formats[ $cfg[ $fieldname ]['date_config']['dateFormat'] ];

		return $date_formats['ISO_8601'];
	}


	// return timestamp or false on failure
	function ParseDateStringOnFormatByFieldname ( $fieldname, $value ) {

		$cfg =& $this->GetConfig( 'rules' );

		if( isset( $cfg[ $fieldname ] ) && $cfg[ $fieldname ]['fieldtype'] == 'date' )
			$format = $cfg[ $fieldname ]['date_config']['dateFormat'];
		else
			return false;
		
		switch( $format ) {
			case 'US_SLASHED':
			case 'ISO_8601':
			case 'RFC_822':
			case 'RFC_850':
			case 'RFC_1036':
			case 'RFC_1123':
			case 'COOKIE':
				// formats that strtotime() understands
				return strtotime( $value );

			// formats that we need to parse, using named capture groups in pcre
			case 'DATE_CUSTOM_1':
				$pattern = '/^(\d+)\/(\d+)\/(\d+)$/';				/*'d/m/Y'*/ 
				$replacement = '$2/$1/$3';
				break;
			case 'DATE_CUSTOM_2':
				$pattern = '/^(\d+)\/(\d+)\/(\d+)$/'; 				/*'Y/m/d'*/ 
				$replacement = '$2/$3/$1';
				break;
			case 'DATE_CUSTOM_3':
				$pattern = '/-/';					 				/*'m-d-Y'*/
				$replacement = '/';
				break;
		 	case 'DATE_CUSTOM_4':
		 		$pattern = '/^(\d+)-(\d+)-(\d+)$/';			 		/*'d-m-Y'*/
				$replacement = '$2/$1/$3';
				break;
			case 'DATE_CUSTOM_5':									/*'dd.mm.yy', 20.02.2012 */
			case 'DATE_CUSTOM_7':									/*'dd:mm:yy', 20:02:2012 */
		 		$pattern = '/^(\d{2})[.:](\d{2})[.:](\d{4})$/';
				$replacement = '$2/$1/$3';
				break;
			case 'DATE_CUSTOM_6':
				$pattern = '/^(\d{2})\.(\d{2})\.(\d{2})$/';			/*'dd.mm.y', 20.02.12 */
				$replacement = '$2/$1/$3';
				break;
			case 'DATE_CUSTOM_8':
				$pattern = '/^(\d{1,2})\.([a-z]+)\w+(\d{2,4})$/i';	/*'dd.MM y', 20.February 12 */
				$replacement = '$1 $2 $3';
				break;
			case 'DATE_CUSTOM_9':
				$pattern = '/^(\d{1,2})\.([a-z]+)$/';				/*'dd.MM', 20.February */
				$replacement = '$1 $2';
				break;

			default:
				return false;
		}
		
		// strtotime should understand the US format now
		return strtotime( preg_replace( $pattern, $replacement, $value) );
	}

}


?>
