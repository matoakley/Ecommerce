<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Email module
 *
 * Ported from Kohana 2.2.3 Core to Kohana 3.0 module
 * 
 * Updated to use Swiftmailer 4.0.4
 *
 * @package    Core
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Caffeine_Email extends Email{

  //THIS WAS OVEWRITTEN BECAUSE THE ORIGINAL AUTH IS A COCK AND WE COULDN'T DO IT ANOTHER WAY.//

	/**
	 * Send an email message.
	 *
	 * @param   string|array  recipient email (and name), or an array of To, Cc, Bcc names
	 * @param   string|array  sender email (and name)
	 * @param   string        message subject
	 * @param   string        message body
	 * @param   boolean       send email as HTML
	 * @return  integer       number of emails sent
	 */
	public static function send($to, $from, $subject, $message, $html = FALSE)
	{
		// Connect to SwiftMailer
		(Email::$mail === NULL) and Email::connect();

		// Determine the message type
		$html = ($html === TRUE) ? 'text/html' : 'text/plain';

		// Create the message
		$message = Swift_Message::newInstance($subject, $message, $html, 'utf-8');

		if (is_string($to))
		{
			// Single recipient
			$message->setTo($to);
		}
		elseif (is_array($to))
		{
			if (isset($to[0]) AND isset($to[1]))
			{
				// Create To: address set
				$to = array('to' => $to);
			}

			foreach ($to as $method => $set)
			{
				if ( ! in_array($method, array('to', 'cc', 'bcc')))
				{
					// Use To: by default
					$method = 'to';
				}

				// Create method name
				$method = 'add'.ucfirst($method);

				if (is_array($set))
				{
					// Add a recipient with name
					$message->$method($set[0], $set[1]);
				}
				else
				{
					// Add a recipient without name
					$message->$method($set);
				}
			}
		}
		
		if (is_string($from))
		{
			// From without a name
			$message->setFrom($from);
			$message->setReturnPath($from);
		}
		elseif (is_array($from))
		{
			// From with a name
			$message->setFrom($from);
			
			//if its an array we need the address
			$from_email = array_keys($from);
			$message->setReturnPath($from_email[0]);
		}

		return Email::$mail->send($message);
	}

} // End email