<?php defined('SYSPATH') or die('No direct script access.');

class Jelly_Builder extends Jelly_Builder_Core
{
	public function execute($db = 'default')
	{
		if (is_object($this->_meta) AND $this->_meta->columns('deleted'))
		{
			$this->where('deleted', 'IS', NULL);	
		}
		
		return parent::execute($db);
	}
	
	public function count($db = 'default')
	{
		if (is_object($this->_meta) AND $this->_meta->columns('deleted'))
		{
			$this->where('deleted', 'IS', NULL);	
		}
		
		return parent::count($db);
	}
	
}