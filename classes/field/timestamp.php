<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Overload Jelly_Timestamp since it doesn't do pretty date outputs :(
 *
 * @package 	v4 Jelly Fields
 * @author		Alex Gisby
 */

class Field_Timestamp extends Jelly_Field_Timestamp
{
	
	/**
	 * Get the date in a pretty format
	 *
	 * @param   Jelly_Model  $model
	 * @param   mixed        $value
	 * @return  mixed
	 */
	public function get($model, $value)
	{
		if(!is_numeric($value))
		{
			$value = strtotime($value);
		}
		
		return ($value != null) ? date($this->pretty_format, $value) : null;
	}
	
}