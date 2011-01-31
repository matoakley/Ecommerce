<?php defined('SYSPATH') or die('No direct script access.');

abstract class Jelly extends Jelly_Core
{

	// Fixed this function so that you can call using get_called_class() if necessary.
	public static function class_name($model)
	{
		if ($model instanceof Jelly_Model)
		{
			$model = strtolower(get_class($model));
		}
		else
		{
			$prefix_length = strlen(Jelly::model_prefix());

			// Compare the first parts of the names and chomp if they're the same
			if (strtolower(substr($model, 0, $prefix_length)) !== strtolower(Jelly::model_prefix()))
			{
				$model = strtolower(Jelly::model_prefix().$model);
			}
		}

		return $model;
	}

}