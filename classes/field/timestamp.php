<?php defined('SYSPATH') or die('No direct script access.');

class Field_Timestamp extends Jelly_Field_Timestamp
{
 	public function set($value)
	{
		if ($value === NULL OR ($this->null AND empty($value)))
		{
			return NULL;
		}

		if (is_numeric($value))
		{
			return (int) $value;
		}
		elseif (FALSE !== strtotime($value))
		{
			return strtotime($value);
		}

		return $value;
	}
}