<?php

/**
 * CoffeeCup Software's Web Form Builder.
 *
 * Validates posted data against the rules.
 *
 *
 * @version $Revision: 2456 $
 * @author Cees de Gruijter
 * @category FB
 * @copyright Copyright (c) 2011 CoffeeCup Software, Inc. (http://www.coffeecup.com/)
 */

// copy anything valid from $_POST to $post
// return error count if the input doesn't pass the tests

function ValidateInput ( ) {

	if( $_SERVER['CONTENT_LENGTH'] > getPhpConfBytes() ) {

		$page->SetErrors( array( array( 'field' => 'Form',
								 		'err' => _T('The form is attempting to send more data than the server allows. Please check that you are not uploading too many large files.' ) ) ) );

		return 1;
	}

	$cfg =& FormPage::GetInstance()->GetConfig( 'rules' );
	if( $cfg === false ) 		return 1;

	$validator = new Validator();

	foreach( $cfg as $name => $rules ) {

		// skip all rules that have a name with a _ prefix
		if( $name[0] == '_' )		continue;

		$fieldtype = $rules['fieldtype'];

		if( method_exists( 'Validator', $fieldtype ) ) {

			if( ! $validator->required( $name, $rules ) )
				continue;
			else
				$validator->$fieldtype( $name, $rules );

		} else {
			writeErrorLog( 'Validation handler missing for fieldtype: ', $fieldtype );
		}
	}

	$errcount = count( $validator->errors );

	// ready, assign the result to the page instance
	if( $errcount > 0 ) 		FormPage::GetInstance()->SetErrors( $validator->errors );
	else 						FormPage::GetInstance()->SetPostValues( $validator->post );

	return $errcount;
}


/******************* validation handlers *******************/

class Validator {

	public $errors = array();
	public $post;
	private $m_noSlashes;				// posted string with slashes stripped
	private $m_noLinebreaks;			// posted string with slashes and line breaks stripped
	private $page;

	public function __construct ( ){
		$this->page =& FormPage::GetInstance();
	}

	public function text ( $name, $rules ) {

		if(	! $this->_checklength( $name, $rules ) )		return;

		$this->post[ $name ] = $this->m_noLinebreaks;
	}


	public function hidden ( $name, $rules ) {

		// hidden fields are validated silently, thus don't call _checklength()
		if( ! isset( $rules[ 'database' ] ) ||
			! $rules[ 'database' ] ) 						return;
	
		if( isset( $_POST[ $name ] ) ) {

			$tmp = stripslashes_deep( trim( $_POST[ $name ] ) );
			if(	strlen( $tmp ) > $rules['maxbytes'] )		return;

		} else {

			$tmp = '';
		}

		$this->post[ $name ] = $tmp;
	}


	public function textarea ( $name, $rules ) {

		if(	! $this->_checklength( $name, $rules ) )		return;

		$this->post[ $name ] = $this->m_noSlashes;
	}


	public function number ( $name, $rules) {

		// set empty number to null to avoid confusion with allowed values for none required fields
		if( ! isset( $_POST[ $name ] ) || $_POST[ $name ] == '' ) {
			$this->post[ $name ] = null;
			return;
		}

		$label = empty($rules['label']) ? $name : $rules['label'];

		if( ! is_numeric( $_POST[ $name ] ) )
		{
			$this->_errormsg( $name, $rules, _T( '"%s" must be a number.', $name ) );
			return;
		}

		$num = (int) $_POST[ $name ];

		if( isset( $rules['range'] ) && is_array( $rules['range'] ) ) {

			list( $min, $max, $step ) = array( false, false, false );

			if( count($rules['range'] ) == 3 )	list( $min, $max, $step ) = $rules['range'];
			else								list( $min, $max ) = $rules['range'];

			if( $min !== false && $num < $min ) {
				$this->_errormsg( $name, $rules, _T( '"%s" must be larger than %s.', array( $label, $min ) ) );
			} else if( $max !== false && $num > $max ) {
				$this->_errormsg( $name, $rules, _T( '"%s" must be smaller than %d.', array( $label, $max ) ) );
			} else if( $step !== false )  {

				$remainder = ($num - $min) % $step;

				if( $remainder != 0 )
					$this->_errormsg( $name, $rules, _T( '"%s" doesn\'t have an allowed value. Closest allowed values are %s or %s',
														array( $label, ($num - $remainder), ($num - $remainder + $step) ) ) );
			}
		}

		$this->post[ $name ] = $num;		
	}


	public function date ( $name, $rules ) {

		$value = isset( $_POST[ $name ] ) ? trim( $_POST[ $name ] ) : '' ;

		if( strlen( $value ) == 0 ) {
			$this->post[ $name ] = '';
			return;
		}
	
		$postedtime = $this->page->ParseDateStringOnFormatByFieldname( $name, $value );
		$label = empty($rules['label']) ? $name : $rules['label'];

		if( isset( $rules['date_config'] ) ) {

			if( $postedtime === false ||
				isset( $rules['date_config']['dateFormat'] ) &&
				($tmp = date( $this->page->GetDateFormatByFieldname( $name ), $postedtime )) != $value &&
				preg_replace( '/\b0/', '', $tmp) != $value )			// repeat test without leading 0's for day and month

			{
				$this->_errormsg( $name, $rules, _T( '"%s" must be a correctly formatted and valid date.', $name ) );
			}

			if( $rules['date_config']['minDate'] > 0 && $postedtime < $rules['date_config']['minDate'] ) {

				$this->_errormsg( $name, $rules, _T( '"%s" must be a date later than %s.',
													array( $label, date( $this->page->GetDateFormatByFieldname( $name ), $rules['date_config']['minDate'] ) ) ) );
			}
			if( $rules['date_config']['maxDate'] > 0 && $postedtime > $rules['date_config']['maxDate'] ) {

				$this->_errormsg( $name, $rules, _T( '"%s" must be a date before %s.',
													array( $label, date( $this->page->GetDateFormatByFieldname( $name ), $rules['date_config']['maxDate'] ) ) ) );
			}
		}
		$this->post[ $name ] = $postedtime;
	}


	public function email ( $name, $rules ) {

		$addr = isset( $_POST[ $name ] ) ? trim( $_POST[ $name ] ) : '';

		if( strlen( $addr ) == 0 ) {

			$this->post[ $name ] = '';			// empty email
			return;
		}

		$label = empty($rules['label']) ? $name : $rules['label'];

		if( ! email( $addr ) ) {

			$this->_errormsg( $name, $rules, _T( '"%s" is not a valid email address.', $label ) );
			return;
		}

		if( isset( $rules['equalTo'] ) ) {

			if( ! isset( $_POST[ $rules['equalTo'] ] ) ||
				$addr != trim(	$_POST[ $rules['equalTo'] ] ) ) {

				$this->_errormsg( $name, $rules, _T( 'The addresses in the fields "%s" and "%s" must match.',
													 array( $label, $rules['label_equal'] ) ) );
				return;
			}
		} 

		$this->post[ $name ] = $addr;
	}


	public function password ( $name, $rules ) {

		if(	! $this->_checklength( $name, $rules ) )		return;

		if( isset( $rules['equalTo'] ) && $rules['equalTo'] != '' ) {

			if( ! isset( $_POST[ $rules['equalTo'] ] ) ||
				$this->m_noSlashes != stripslashes_deep( $_POST[ $rules['equalTo'] ] )  ) {

				$this->_errormsg( $name, $rules, _T( '"%s" and "%s" must match.', array( $rules['label'], $rules['label_equal'] ) ) );
				return;
					
			}
		}
		$this->post[ $name ] = $this->m_noSlashes;
	}


	public function url ( $name, $rules ) {

		$url = isset( $_POST[ $name ] ) ? trim( $_POST[ $name ] ) : '';

		if( strlen( $url ) == 0 ) {

			$this->post[ $name ] = '';			// empty url
			return;
		}

		// lets relax the test a little bit and prefix http if it isn't there
		$tmp = preg_match( '/^(?:ftp|https?):\/\//xi' , $url ) ? $url : 'http://' . $url; 

		if( ! url( $tmp ) ) {

			$this->_errormsg( $name, $rules, _T( '"%s" is not a valid web address.', empty($rules['label']) ? $name : $rules['label'] ) );
			return;
		}

		if( isset( $rules['equalTo'] ) ) {

			if( ! isset( $_POST[ $rules['equalTo'] ] ) ||
				$url != trim(	$_POST[ $rules['equalTo'] ] ) ) {

				$this->_errormsg( $name, $rules, _T( 'The URLs in the fields "%s" and "%s" must match.',
														empty($rules['label']) ? $name : $rules['label'], $rules['label_equal'] ) );
				return;
			}
		}

		$this->post[ $name ] = $url;		
	}


	public function checkbox ( $name, $rules ) {

		$values = isset( $_POST[ $name ] ) ? stripslashes_deep( $_POST[ $name ] ) : array();

		if( isset( $rules['number_required'] ) && $rules['number_required'] > 0 ) {

			if( ! is_array( $values ) || count( $values ) < $rules['number_required'] )
			{
				$this->_errormsg( $name, $rules, _T( '"%s" must have at least %d checkboxes checked.',
														array( (empty($rules['label']) ? $name : $rules['label']),
																$rules['number_required'] ) ) );
				return;
			}
		}

		$this->post[ $name ] = $values;		
	}


	public function dropdown ( $name, $rules ) {

		// dropdown is like a listbox
		$this->listbox( $name, $rules );

		// but can have only 1 value
		if( isset( $_POST[ $name ] ) && is_array( $_POST[ $name ] ) ) {

			$this->_errormsg( $name, $rules, _T( '"%s" can\'t have more than 1 value.', empty($rules['label']) ? $name : $rules['label'] ) );

		} else if( is_array( $this->post[ $name ] ) && count( $this->post[ $name ] ) > 0 ) {

			// flatten the array to the first element
			$this->post[ $name ] = $this->post[ $name ][ 0 ];

		} else {

			$this->post[ $name ] = '';
		}
	}


	public function listbox ( $name, $rules ) {

		if( isset( $_POST[ $name ] ) ) {

			// listboxes can be single and multiple select, unify input to array
			if( is_array( $_POST[ $name ] ) )			$values = stripslashes_deep($_POST[ $name ]);
			else										$values = array( stripslashes_deep( $_POST[ $name ] ) );
	
			if( isset( $rules['values'] ) && is_array( $rules['values'] ) ) {

				foreach( $values as $value ) {	

					if( ! in_array( trim( $value ), $rules['values'] ) ) {

						$this->_errormsg( $name, $rules, _T( '"%s" doesn\'t have a valid value.',
															  empty($rules['label']) ? $name : $rules['label'] ) );
						return;
					}
				}
	
			} else {
	
				writeErrorLog( 'Validation rules for a listbox lacks values array.' );
			}

			$this->post[ $name ] = $values;

		} else {

			$this->post[ $name ] = '';
		}
	}


	public function radiogroup ( $name, $rules ) {
		
		if( isset( $_POST[ $name ] ) ) {

			$value = stripslashes_deep( trim( $_POST[ $name ] ) );

			if( isset( $rules['values'] ) && is_array( $rules['values'] ) ) {
	
				if( ! in_array( $value, $rules['values'] ) ) {

					$this->_errormsg( $name, $rules, _T( '"%s" doesn\'t have a valid value.',
														  empty($rules['label']) ? $name : $rules['label'] ) );
					return;
				}
	
			} else {
	
				writeErrorLog( 'Validation rules for a radio group lacks values array.' );
			}

			$this->post[ $name ] = $value;

		} else {

			$this->post[ $name ] = '';
		}
	}


	public function fileupload ( $name, $rules ) {

		if( $rules['accept'] && isset( $_FILES[ $name ] ) ) {

			$uploaded_file = $_FILES[ $name ];

			if( $uploaded_file['error'] ) {

				switch( $uploaded_file['error'] ) {

				case UPLOAD_ERR_INI_SIZE:
				case UPLOAD_ERR_FORM_SIZE:
					$this->_errormsg( $name, $rules, _T( 'The file "%s" exceeds the maximum size that can be uploaded by this form.',
														  $uploaded_file[ 'name' ] ) );
				break;

				case UPLOAD_ERR_NO_FILE:
					// handled by required()
				break;

				default:
					$this->_errormsg( $name, $rules, _T( 'The file "%s" was not uploaded; error code: %d.',
														  array( $uploaded_file[ 'name' ], $uploaded_file['error'] ) ) );
				}

			} else {

				// json formatted validation rule: "accept":"txt|jpg|png|gif"
				if( ! empty( $uploaded_file[ 'name' ] ) &&
					! preg_match( '/\.(' . $rules['accept'] . ')$/is', $uploaded_file[ 'name' ] ) ) {

					$this->_errormsg( $name, $rules, _T( 'The file "%s" is not an allowed file type.',
														  $uploaded_file[ 'name' ] ) );
				}

			 	// test against scripts diguised as an image (exploit-db.php.jpg) that hurt default apache config
				if( preg_match( '/[\d\W]php\d?\./i', $uploaded_file[ 'name' ] ) ||
					strpos( $uploaded_file[ 'name' ], "\0" ) !== false ) {

					$this->_errormsg( $name, $rules, _T( 'The filename "%s" is not allowed.', $uploaded_file[ 'name' ] ) );
				}

				if( isset( $rules['maxbytes'] ) && $uploaded_file[ 'size' ] > $rules['maxbytes'] ) {

					$this->_errormsg( $name, $rules, _T( 'The file "%s" is larger than the maximum file size allowed.',
															  $uploaded_file[ 'name' ] ) );
				}
			}
		}
	}


	public function tel ( $name, $rules ) {

		$telnum = isset( $_POST[ $name ] ) ? trim( $_POST[ $name ] ) : '';

		if( strlen( $telnum ) == 0 ) {

			$this->post[ $name ] = '';			// empty tel. number
			return;
		}

		$method = '_' . strtolower( $rules['phone'] );
		if( ! method_exists( $this, $method ) ) {

			writeErrorLog( 'No format defined for telephone number type:', $rules['phone'] );
			$this->errors[] = array( 'field' => $name, 'err' =>  _T( 'No format specifier found for "%s".', $rules['phone'] ) );

		} else if( $this->$method( $telnum ) ) {

			$this->post[ $name ] = $telnum;

		} else {

			$this->_errormsg( $name, $rules, _T( '"%s" isn\'t recognized as a valid telephone number format.',
												  empty($rules['label']) ? $name : $rules['label'] ) );
		}
	}


	public function captcha ( $name, $rules ){

		$private_key = '';

		if( $rules['captcha'] == 'automatic' && $this->page->sdrive !== false ) {

			$private_key = $this->page->sdrive[ 'recaptcha_private_key' ];

		} else if( $rules['captcha'] == 'manual' && isset( $rules['private_key'] ) && $rules['private_key'] != '' ) {

			$private_key = $rules['private_key'];

		} else {

			$this->_errormsg( $name, $rules, _T('Please configure valid public and private reCaptcha keys or use CoffeeCup S-Drive\'s automatic reCaptcha processing.' ) );
			return;
		}

		if( ! isset( $_POST[ 'recaptcha_challenge_field' ] ) ||
			! isset( $_POST[ 'recaptcha_response_field' ] ) ) {

			$this->_errormsg( $name, $rules, _T( 'The form post is missing reCaptcha fields.' ) );
		}


		include 'fbapp/inc/recaptchalib.php';

		$resp = recaptcha_check_answer( $private_key,
										$_SERVER[ 'REMOTE_ADDR' ],
										$_POST[ 'recaptcha_challenge_field' ],
										$_POST[ 'recaptcha_response_field' ] );

		if( ! $resp->is_valid ) {
			$this->_errormsg( $name, $rules, _T( 'Please enter the correct words in the Captcha box.' ) );
		}
	}


	public function regex ( $name, $rules ) {

		$val = isset( $_POST[ $name ] ) ? trim( $_POST[ $name ] ) : ''; 

		// allow an empty field
		if( strlen( $val ) == 0 ) {

			$this->post[ $name ] = '';
			return;
		}

		if( preg_match( $rules['regex_config'], $val ) == 0 ) {

			$this->_errormsg( $name, $rules, _T( '"%s" must match the format defined for this field.' ) , empty($rules['label']) ? $name : $rules['label'] );
			return;
		}

		$this->post[ $name ] = $val;
	}



	/*** generic functions ***/

	public function required ( $name, $rules ) {

		if( ! isset( $rules['required'] ) || ! $rules['required'] )
			return true;

		$error = false;

		if( $rules['fieldtype'] == 'fileupload' ) {

			$error = ! isset( $_FILES[ $name ] ) ||
					 $_FILES[ $name ]['size'] == 0 ||
					 $_FILES[ $name ]['error'] == UPLOAD_ERR_NO_FILE;

		} else {

			$tmp = isset( $_POST[ $name ] ) ? $_POST[ $name ] : '';	
			if( is_array( $tmp ) ) {
				$error = empty( $tmp );
			} else {
				$tmp = trim( $tmp );
				$error = empty( $tmp ) && strlen( $tmp ) == 0;
			}
		}

		if( $error ) {

			$this->_errormsg( $name, $rules, _T( '"%s" is a required field and cannot be empty.',
									 			  empty($rules['label']) ? $name : $rules['label'] ) );
		}

		return ! $error;
	}



	/*** private functions ***/

	// check length, ignoring charriage returns and leading/trailing spaces
	// as a side effect, it sets the private properties $this->m_noSlashes and $this->m_noLinebreaks
	// which the caller may use if true is returned
	private function _checklength ( $name, $rules ) {

		$input = isset( $_POST[ $name ] ) ? trim( $_POST[ $name ] ) : ''; 

		if( strlen( $input ) == 0 ) {

			$this->m_noLinebreaks = '';
			$this->m_noSlashes = '';
			return true;
		}	

		// prepare the string for counting
		$this->m_noSlashes = stripslashes_deep( $input );

		// strips the carriage returns for character count
		// Processes \r\n's first so they aren't converted twice.
		$this->m_noLinebreaks = str_replace( array( "\r\n", "\n", "\r" ), ' ', $this->m_noSlashes );

		$label = empty($rules['label']) ? $name : $rules['label'];

		if( isset( $rules['maxlength'] ) && strlen( $this->m_noLinebreaks ) > $rules['maxlength'] ) {

			$this->_errormsg( $name, $rules, _T( '"%s" must be less than %d characters.',
									 			  array( $label, $rules['maxlength'] ) ) );
			return false;

		} else if( isset( $rules['minlength'] ) && strlen( $this->m_noLinebreaks ) < $rules['minlength'] ) {

			$this->_errormsg( $name, $rules, _T( '"%s" must be at least %d characters.',
									 			  array( $label, $rules['minlength'] ) ) );						 				   
			return false;
			
		}

		return true;
	}

	
	private function _errormsg ( $name, $rules, $default ) {

		if( isset( $rules[ 'messages' ] ) && ! empty( $rules[ 'messages' ] ) ) {
			$this->errors[] = array( 'field' => $name, 'err' => $rules[ 'messages' ] );
		} else {
			$this->errors[] = array( 'field' => $name, 'err' => $default );
		}
	}


	// return true if valid
	// International
	// <= 15 digits, may have leading + and contain ().- (according to Wikipedia)
	private function _international ( $number ) {

		//ignoring all non-digits makes counting easier
		$tmp = preg_replace('/[^\d+]/', '', RemoveExtensionAndSpaces( $number ) );
		return preg_match( '/^\+?\d{9,15}$/', $tmp );

	}

	// (111) 111-1111
	// 1-222-222-2222
	// 111-111-1111
	// 111.111.1111
	// possibly with x or ext. 123 added
	private function _phoneus ( $number ) {

		$tmp = RemoveExtensionAndSpaces( $number );

		return preg_match( '/^\(\d{3}\)\d{3}-\d{4}$/', $tmp ) ||
			   preg_match( '/^1-[\d-]{12}$/', $tmp ) ||
			   preg_match( '/^[\d-.]{12}$/', $tmp);
	}

	// (02x) AAAA AAAA
	// (01xx) AAA BBBB
	// (01xxx) AAAAAA
	// (01AAA) BBBBB
	// (01AA AA) BBBBB
	// (01AA AA) BBBB
	// 0AAA BBB BBBB
	// 0AAA BBB BBB
	private function _phoneuk ( $number ) {

		$tmp = RemoveExtensionAndSpaces( $number );

		return preg_match( '/^\(0\d{2}\)\d{8}$/', $tmp ) ||
			   preg_match( '/^\(0\d{3}\)\d{7}$/', $tmp ) ||
			   preg_match( '/^\(0\d{4}\)\d{5,6}$/', $tmp) ||
			   preg_match( '/^\(0\d{5}\)\d{4,5}$/', $tmp) ||
			   preg_match( '/^0\d{9,10}$/', $tmp);
	}

	// 07AAA BBBBBB
	private function _mobileuk ( $number ) {

		return preg_match( '/^07\d{9}$/', str_replace( ' ', '', $number ) );
	}

}

/**
* Removes the backslashes in case their are set in the post
* of the fields from a form.
* 
* @param  $value could be an array or a string where the backslashes will be removed
* @return the element passed as parameter with no backslashed 
*/
function stripslashes_deep ( $value )
{
	if( ! get_magic_quotes_gpc() )		return $value;
	if( is_array( $value ) )			return array_map( 'stripslashes_deep', $value );
	else								return stripslashes($value);
}

/**
* Checks if a given value is a valid email address.
*
* @access  public
* @param   mixed $value  value to check
* @return  boolean
* @static
*/
function email( $value )
{
	if( ! is_string($value) )		return false;

	if( (strpos($value, '..') !== false) ||
		(!preg_match('/^(.+)@([^@]+)$/', $value, $matches)))
	{
		return false;
	}

	$localpart = $matches[1];
	$hostname  = $matches[2];

	if((strlen($localpart) > 64) || (strlen($hostname) > 255))
	{
		return false;
	}

	$atext = 'a-zA-Z0-9\x21\x23\x24\x25\x26\x27\x2a\x2b\x2d\x2f\x3d\x3f\x5e\x5f\x60\x7b\x7c\x7d\x7e';
	if(!preg_match('/^[' . $atext . ']+(\x2e+[' . $atext . ']+)*$/', $localpart))
	{
		// Try quoted string format

		// Quoted-string characters are: DQUOTE *([FWS] qtext/quoted-pair) [FWS] DQUOTE
		// qtext: Non white space controls, and the rest of the US-ASCII characters not
		// including "\" or the quote charadcter
		$noWsCtl = '\x01-\x08\x0b\x0c\x0e-\x1f\x7f';
		$qtext = $noWsCtl . '\x21\x23-\x5b\x5d-\x7e';
		$ws = '\x20\x09';
		if(!preg_match('/^\x22([' . $ws . $qtext . '])*[$ws]?\x22$/', $localpart))
		{
			return false;
		}
	}

	return (bool) preg_match("/^(?:[A-Z0-9]+(?:-*[A-Z0-9]+)*\.)+[A-Z]{2,6}$/i", $hostname);
}


/**
* Checks if a given value is a valid URL
*
* @access  public
* @param   mixed $value  value(s) to check
* @return  boolean
*/
function url( $value )
{
	// Thanks to drupal for this
	return (bool) preg_match("
		/^														# Start at the beginning of the text
		(?:ftp|https?):\/\/										# Look for ftp, http, or https schemes
		(?:														# Userinfo (optional) which is typically
		(?:(?:[\w\.\-\+!$&'\(\)*\+,;=]|%[0-9a-f]{2})+:)*		# a username or a username and password
		(?:[\w\.\-\+%!$&'\(\)*\+,;=]|%[0-9a-f]{2})+@			# combination
		)?
		(?:
		(?:[a-z0-9\-\.]|%[0-9a-f]{2})+							# A domain name or a IPv4 address
		|(?:\[(?:[0-9a-f]{0,4}:)*(?:[0-9a-f]{0,4})\])			# or a well formed IPv6 address
		)
		(?::[0-9]+)?											# Server port number (optional)
		(?:[\/|\?]
		(?:[\w#!:\.\?\+=&@$'~*,;\/\(\)\[\]\-]|%[0-9a-f]{2})		# The path and query (optional)
		*)?
		$/xi", $value);
}


function getPhpConfBytes ( ) {

	$value = trim( ini_get('post_max_size') );

	switch( strtolower( substr($value, -1) ) ) {
		case 'g':
			$value *= 1024;
		case 'm':
			$value *= 1024;
		case 'k':
			$value *= 1024;
	}

	return $value;
}

//ignoring extension and spaces of phone numbers
function RemoveExtensionAndSpaces( $number ) {
	return str_replace( ' ', '', preg_replace( '/[ext]{1,3}\.?\s*[\d]+$/', '', $number ) );
}
?>
