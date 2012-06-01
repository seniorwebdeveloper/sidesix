<?php

/**
 * CoffeeCup Software's Web Form Builder.
 *
 * Methods to handle data mails.
 *
 *
 * @version $Revision: 2456 $
 * @author Cees de Gruijter
 * @category FB
 * @copyright Copyright (c) 2011 CoffeeCup Software, Inc. (http://www.coffeecup.com/)
 */


define( 'CC_USERCONFIG', 'user.cfg.php');


class DataSaveMailer extends DataSave {

	var $settings;
	var $mailer;
	var $default_from;

	function DataSaveMailer ( $settings ) {

		parent::DataSave( $settings );

		$this->mailer = new Mailer();

		if( isset($page->sdrive) && $page->sdrive ) {

			// use sdrive config if available
			$config['service'] = 'smtp';
			$config['smtp']['auth'] = false;
			$config['smtp']['user'] = '';
			$config['smtp']['password'] = '';
			$config['smtp']['host'] = $page->sdrive['smtp_server'];
			$config['smtp']['port'] = $page->sdrive['smtp_port'];
			$config['smtp']['secure'] = '';
			$this->mailer->SetConfig( $config );

			// stop our newsletter processing system from rewrite all URLs and adding the tracking image.
			$this->mailer->extra_header ='X-GreenArrow-MailClass: noclick';

		} else {

			// check if the user config exists in include path
			$handle = @fopen( CC_USERCONFIG, 'r', 1 );
			if( $handle ) {
				fclose( $handle );
				include CC_USERCONFIG;
				if( isset( $user_config['mailer'] ) ) {
					$this->mailer->SetConfig( $user_config['mailer'] );
				}
			}
		}

		// generate default from address, remove all characters that aren't alpha numerical to avoid problems
		global $myName;
		$name = preg_replace( '/[^a-z0-9]/i', '', $myName );

		// get a server name, we need HTTP_X_FORWARDED_HOST when on sdrive
		if( isset( $_SERVER['HTTP_X_FORWARDED_HOST'] ) ) {

			list( $server ) = explode( ',', $_SERVER['HTTP_X_FORWARDED_HOST'] );

		} else {

			$server = $_SERVER['SERVER_NAME'] ;
		}

		// set the default
		$this->default_from = $name . '@' . $server;
	}


	// save really means "send" in this context.
	// notification messages are sent with a from address using the form and server names 
	// auto-response message are sent with the from address defined, or auto generated one if it's missing 
	function Save ( ) {
		if( $this->settings['auto_response_message']['is_present'] ) {
			$this->_Send( $this->settings['auto_response_message'] );	
		}
		
		if( $this->settings['notification_message']['is_present'] ) {
			$this->_HandleAttachedFiles();
			$this->_Send( $this->settings['notification_message'] );
		}
	}


	function _HandleAttachedFiles ( ) {

		$attachments = array();

		// check the rules for any files that must be attached
		foreach( $this->rules as $fieldname => $rule ) {

			if( $rule['fieldtype'] != 'fileupload' ||
				$rule['attach'] == false ) {

				continue;					
			}

			// find out if the file is in the temp space else look for it in the uploads table
			if( isset( $_FILES[ $fieldname ] ) &&
				file_exists( $_FILES[ $fieldname ][ 'tmp_name' ] ) ) {

				$attachments[ $_FILES[ $fieldname ][ 'name' ] ] = $_FILES[$fieldname]['tmp_name'];

			} else {
				
				foreach( $this->page->uploads as $up ) {

					if( $up['fieldname'] != $fieldname )		continue;

					$filepath = $this->page->GetStorageFolder( 1 ) . $up['storedname'];

					if( file_exists( $filepath ) ) {

						$attachments[ $up['storedname'] ] = $filepath;
						break;

					} else {

						writeErrorLog( 'Attachment file not found in tmp storage nor in :', $filepath );
					}
				}
			}
		}

		if( empty( $attachments ) ) 
			$this->mailer->Attach( false );
		else
			$this->mailer->Attach( $attachments );
	}


	function _Send( $spec ) {

		if( $spec['is_present'] == false )			return;

		$this->mailer->ResetFields();

		$to = $this->_SubstituteAddress( $spec[ 'to' ] );

		if( $to == '' && ! empty( $spec[ 'to' ] ) ) {

			// happens when the to field is a magic field, but the user didn't fill it in
			// return without an error
			return;

		} else if( $to == '' || ! $this->mailer->SetRecipients( $to ) ) {

			$this->errors[] = array( 'err' => _T('Could not send an email because the recipient isn\'t defined.') );
			writeErrorLog('Can\'t send a mail message when the "to:" field is empty.');
			return;
		}

		// set default and update with settings if available
		$this->mailer->SetFrom( $this->default_from );

		if( isset( $spec['from'] ) ) {		

			$from = $this->_SubstituteAddress( $spec['from'] );
			if( ! empty( $from ) ) 		$this->mailer->SetFrom( $from );
		}

		if( isset( $spec['replyto'] ) ) {		

			$replyto = $this->_SubstituteAddress( $spec['replyto'] );
			if( ! empty( $replyto ) ) 		$this->mailer->SetReplyTo( $replyto );
		}

		if( isset( $spec[ 'cc'] ) ) {

			$cc = $this->_SubstituteAddress( $spec['cc'] );
			if( ! empty( $cc ) ) 		$this->mailer->SetCC( $cc );
		}

		if( isset( $spec[ 'bcc'] ) ) {

			$bcc = $this->_SubstituteAddress( $spec['bcc'] );
			if( ! empty( $bcc ) ) 		$this->mailer->SetBCC( $bcc );
		}

		// subject should not be html-encoded, that is done by the mailer
		$this->mailer->SetSubject( $this->page->SubstituteFieldNames( $spec['custom']['subject'], false ) );
		$this->mailer->SetMessage( $this->page->SubstituteFieldNames( $spec['custom']['body'], false ) );
		
		if( ! $this->mailer->Send() ) {

			$this->errors[] = array( 'err' => $this->mailer->error );
		}
	}


	function _SubstituteAddress ( $name ) {

		$matches = array();
		$r = preg_match_all( '\'\[([^\]]+)\]\'', $name, $matches, PREG_PATTERN_ORDER );

		if( $r === false )				writeErrorLog( 'Error in regex parsing:', $name );
		if( ! $r )						return trim( $name );

		foreach( $matches[1] as $match ) {

			// check if this is an email field and get its value if it is
			$cfg = $this->page->GetConfig( 'rules' );

			if( isset( $cfg[ $match ] ) &&
				( $cfg[ $match ]['fieldtype'] == 'email' || $cfg[ $match ]['contactList'] == true ) &&
				isset( $this->page->post[ $match ] ) ) {

				$name = str_replace( '[' . $match . ']', $this->page->post[ $match ] , $name);
			}
		}

		return trim( $name );
	}
}

?>