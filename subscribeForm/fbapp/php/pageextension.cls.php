<?php
/**
 * CoffeeCup Software's Web Form Builder.
 *
 * Base class for Page Extension modules. It's purpose is to supply a stable interface
 * to any function the derived classes may need from Page.  
 *
 *
 * @version $Revision: 2456 $
 * @author Cees de Gruijter
 * @category FB
 * @copyright Copyright (c) 2012 CoffeeCup Software, Inc. (http://www.coffeecup.com/)
 */

class PageExtension {

	protected $page;

	function __construct ( ) {

		$this->page =& FormPage::GetInstance();
	}


	// if $fieldname is an array, the corresponding map with post values is returned
	// returns false if key(s) not found in post
	protected function getPost ( $fieldname = false ) {

		if( $fieldname === false )		return $this->page->post;

		if( is_array( $fieldname ) ) {

			$output = array();
			foreach( $fieldname as $mcname => $postname ) {

				if( isset( $this->page->post[ $postname ] ) )
					$output[ $mcname ] =  $this->page->post[ $postname ];
			}
			return empty( $output ) ? false : $output;
		}

		return isset( $this->page->post[ $fieldname ] ) ? $this->page->post[ $fieldname ] : false;
	}


	protected function getConfig ( $section = false ) {
		return $this->page->GetConfig( $section  );
	}


	protected function setError ( $msg ) {

		// follow the same format that validator is using, thus errors 
		// are an array of key-value pair maps. 
		$this->page->SetErrors( array( array( 'err' => $msg ) ) );
	}
}


?>