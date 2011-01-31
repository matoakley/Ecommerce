<?php defined('SYSPATH') or die('No direct script access.');

class Jelly_Model extends Jelly_Model_Core
{	
	public function delete($key = NULL)
	{
		$result = FALSE;

		// Are we loaded? Then we're just deleting this record
		if ($this->_loaded OR $key)
		{
			if ($this->_loaded)
			{
				$key = $this->id();
			}

			$result = Jelly::factory($this)
		               ->set(array('deleted' => time()))
		               ->save($key);
		}

		// Clear the object so it appears deleted anyway
		$this->clear();

		return (boolean) $result;
	}
}
