<?php

/**
 * CoffeeCup Software's Web Form Builder.
 *
 * Some tools that are used throughtout the app.
 *
 *
 * @version $Revision: 2456 $
 * @author Cees de Gruijter
 * @category FB
 * @copyright Copyright (c) 2011 CoffeeCup Software, Inc. (http://www.coffeecup.com/)
 */

// load PHP4 compatibility functions (from the PEAR::PHP_Compat package) if needed
// 5.1 might not have the json extension installed!
if( strcmp( PHP_VERSION, '5.2' ) < 0 ) {
	include 'fbapp/inc/php4compat.inc.php';
}


// write to error log in storage folder
// $text1 or 2 are flattened if they are simple arrays.
// $text1 and $text2 are concatenated with a space
function writeErrorLog ( $text1, $text2 = false ) {

	global $scriptpath;
	global $errorLoggingType;
	
	$log = '';
	$prefix = '';
	$postfix = '';
	
	if( $errorLoggingType == 3 ) {

		if( ! file_exists( $scriptpath . '/storage' ) ) {

			// don't log if the target folder doesn't exist
			return;	

		} else {
			
			// create empty log with some access protection if it doesn't exist yet 
			$log = $scriptpath . '/storage/fb_error.log.php';
			if( ! file_exists( $log ) )		@error_log( "<?php echo 'Access denied.'; exit(); ?>\n", 3, $log );
			
			// in a file, we need to add a timestamp and a new line
			$prefix = date( 'r');
			$postfix = "\n";
		}
	} else {
		
		// in the hosted environment, we should add a userid to the log
		global $sdrive_config;
		if( isset( $sdrive_config['sdrive_account_id'] ) )
			$prefix = 'sdrive_account=' . $sdrive_config['sdrive_account_id'];
	}

	if( empty( $text1 ) ) $text1 = 'Error logger was called with empty text.';

	$text = '';
	foreach( func_get_args() as $arg ) {

		$text .= ' ';

		if( is_array( $arg ) ) {

			foreach( $arg as $key => $value ) {

				if( is_array( $value ) ) $value = implode( ',', $value );

				$text .= '[' . $key . '] ' . $value . '   ';
			}

		} else {

			$text .= $arg;
		}
	}

	// if it fails, it should fail silently
	@error_log( $prefix . ': ' . trim( $text ) . $postfix, $errorLoggingType, $log );
}


function getFileLock ( &$handle, $lockType ) {

	$retries = 0;
    $max_retries = 50;

    do {
        if ($retries > 0) {
            usleep( rand(5, 1000) );
        }
        $retries += 1;
    } while( ! flock( $handle, $lockType) && $retries <= $max_retries );

    if( $retries == $max_retries )
    	return false;
    else
    	return true;
}


function makeRandomString ( $length = 6 ) {

	$data = '0123456789abcdefghijklmnopqrstuvwxyz';
	$txt = '';

	for( $i = 0; $i < $length; $i++ ) {
		$txt .= $data[ rand(0,35) ];
	}
	return $txt;
}


function SaveUploadAsFile ( $dest, $filedata ) {

	if( ! is_dir( $dest ) && !mkdir( $dest, 0755 ) ) {
		writeErrorLog( 'Could not create file upload directory \'' . $dest . '\'' );
		return false;
	}

	// filename may or may not have an extension that must be preserved
	$pos = strrpos( $filedata['name'], '.' );

	// try the org name first, only if it exists add the random string
	$uploadname = $filedata['name'];

	// replace any dots left with a _ for scripts diguised as an image (e.g. exploit-db.php.jpg)
	if( $pos !== false ) { 
		$tmp = substr( $filedata['name'], 0, $pos );
		$uploadname = str_replace( '.', '_', $tmp ) . substr( $filedata['name'], $pos );	
	}

	while( file_exists( $dest . $uploadname ) ) {

		$rand = makeRandomString();

		if( $pos === false ) {

			$uploadname = $filedata['name']  . '_' . $rand;

		} else {

			$uploadname = substr( $filedata['name'], 0, $pos )  . '_' . $rand . substr( $filedata['name'], $pos );

		}
	}

	if( empty( $filedata['tmp_name']) ) {

		writeErrorLog( 'Could not move uploaded file because the tmp_name is empty.' );
		return false;
	}

	$rc = move_uploaded_file( $filedata["tmp_name"], $dest. $uploadname );
	if( $rc ) {

		return $uploadname;
	}

	writeErrorLog( 'Moving file ' . $filedata['tmp_name'] . ' to ' . $uploadname . ' failed.' );
	return false;
}


// GetText-like translator
function _T( $text, $vars = false ) {

	static $lang = false;

	// load language table if necessary
	if( $lang === false ) {
		$file = 'fbapp/inc/language.dat.php';
		@$handle = fopen( $file, "r", true );
		if( $handle !==false ) {
			$sdat = fread( $handle, filesize( $file ) );
			fclose( $handle );
			$lang = unserialize( $sdat );
		} else {
			$lang = '';
		}
	}

	if( ! empty( $lang ) && isset($lang[$text]) ) {
		$translated = $lang[$text];
	} else {
		$translated =  $text;
	}

	// replace %s markers with values in vars
	if( $vars ) {

		if( is_string( $vars ) )		$vars = array( $vars );

		foreach( $vars as $var ) {

			$pos = strpos( $translated, '%s' );

			if( $pos !== false ) {
				$translated = substr( $translated, 0, $pos )
							. $var
							. substr( $translated, $pos + 2 );
			}
		}
	}

	return $translated;
}


?>