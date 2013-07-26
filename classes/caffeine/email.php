<?php defined('SYSPATH') or die('No direct script access.');

class Caffeine_Email extends Email
{
  public static function send($to, $from, $subject, $message, $html = FALSE)
  {    
    if (is_string($from))
    {
        // From without a name
        $message->setReturnPath($from);
    }
    elseif (is_array($from))
    {
        // From with a name
        $message->setReturnPath($from);
    }
    
    return self::parent($to, $from, $subject, $message, $html);
  }
}