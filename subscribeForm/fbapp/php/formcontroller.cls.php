<?php

/**
 * CoffeeCup Software's Web Form Builder.
 *
 * Form controller, routes POST/GET requests to the appropriate scripts.
 *
 *
 * @version $Revision: 2456 $
 * @author Cees de Gruijter
 * @category FB
 * @copyright Copyright (c) 2011 CoffeeCup Software, Inc. (http://www.coffeecup.com/)
 */

class FormController  {

	private $settings;
	private $page;

	public function __construct ( )
	{
		$this->page = & FormPage::GetInstance();
		$this->settings =& $this->page->GetConfig( 'settings' );
	}


	public function Dispatch ( ) {

		#print_r( $_POST );
		#print_r( $_GET );
		#print_r( $_FILES );

		// validate the input
		include 'fbapp/inc/validator.inc.php';

		// validate $_POST and process input if 0 errors encountered
		$r = ValidateInput( $this->page );
			
		$this->page->ReportStats( 'NotifyFormSubmit', $r );

		if( $r == 0 ) {

			// save validated data and proceed if no errors encountered
			if( $this->page->ProcessPostedData() ) {

				// return to the user according to the settings
				$action = isset( $this->settings['redirect_settings'] ) ? $this->settings['redirect_settings']['type'] : 'default';

				switch( $action ) {

				case 'gotopage':

					$this->Redirect();
					break;
				
				case 'inline':
					$this->_ShowInlinePage();
					break;

				case 'confirmpage':
				default:
		
					$this->_ShowConfirmPage();

					exit( 0 );
				}
			}

		}

		// show whatever result there is
		$this->page->HandleErrors();
	}


	function Redirect ( ) {

		$url = $this->settings['redirect_settings']['gotopage'];

		if( empty($url) ) {

			$url = "http://www.coffeecup.com/form-builder/";

		} elseif( ! url( $url) ) {

			isset( $_SERVER['HTTPS'] ) ? $proto = "https" : $proto = "http";

			$uri_parts = parse_url( $_SERVER['REQUEST_URI'] );
			$path = $uri_parts['path'];
			$path_segments = explode( '/', $path );
			$path = implode( '/', array_slice($path_segments,0, count( $path_segments ) - 1 ) );

			$url = sprintf( '%s://%s%s/%s', $proto, $_SERVER['HTTP_HOST'], $path, $url );
		}

		ob_end_clean();

		// if this came from an iframe, we must force the browser to break out to the parent frame
		if( isset( $_POST[ 'fb_form_embedded' ] ) ) {

			echo '<html><script type="text/javascript">'
			   . 'top.location.href = "' . $url . '";'
			   . '</script></html>';

		}  else {

			header( "Location: " . $url );
		}

		exit( 0 );
	}


	function _ShowConfirmPage ( ) {

		$this->page->PrepareConfirmPage();

		session_start();
		$_SESSION['code'] = serialize( $this->page->source );
		ob_end_clean();

		// redirect to the user's site if the hidden field is present in post
		// requires the confirm.html file to be on the user's server and the confirm.js.php on ours
		if( isset( $_POST[ 'fb_form_custom_html' ] ) &&
			! empty( $_POST[ 'fb_form_custom_html' ] ) ) {

			$url = preg_replace( '/[^\/]*?$/', $this->page->GetFormName(true) . '/confirm.html', $_POST[ 'fb_form_custom_html' ] );
			header( 'Location: ' .  $url );

		} else {

			$relpath = preg_match( '/\.php$/', $_SERVER['REQUEST_URI'] ) ? $this->page->GetFormName(true) . '/' : '';
			header( 'Location: ' .  $relpath . 'confirm.php' );
		}	

		exit( 0 );
	}


	function _ShowInlinePage ( ) {

		$this->page->PrepareInlineConfirm();

		session_start();
		$_SESSION['code'] = serialize( $this->page->source );
		@ob_end_clean();				// make this fail silently, to prevent a notice if there is no buffer

		header( 'Location: ' .  $_SERVER['REQUEST_URI'] . '?confirmation' );

		exit( 0 );
	}

}


?>
