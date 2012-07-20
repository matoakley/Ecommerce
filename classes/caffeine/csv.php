<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Provide helper methods when creating CSV files.
 *
 * @package    CaffeineApps
 * @author     Matt Oakley
 * @copyright  (c) 2010 - 2012 Creative Intent Ltd
 */
abstract class Caffeine_CSV
{
	/**
	 * Use fputcsv() for magic and then convert line ending to Windows.
	 */
	public static function get_csv_line($list, $seperator = ",", $enclosure = "\"", $newline = "\r\n" )
	{
    $fp = fopen('php://temp', 'r+'); 

    fputcsv($fp, $list, $seperator, $enclosure );
    rewind($fp);

    $line = fgets($fp);
    if( $newline and $newline != "\n" )
    {
      if( $line[strlen($line)-2] != "\r" and $line[strlen($line)-1] == "\n")
      {
        $line = substr_replace($line,"",-1) . $newline;
      }
      else
      {
        die( 'original csv line is already \r\n style' );
      }
    }

    return $line;
	}
}