<?php defined('SYSPATH') or die('No direct script access.');

class Pagination extends Kohana_Pagination
{
	/**
	 * Generates the full URL for a certain page.
	 *
	 * @param   integer  page number
	 * @return  string   page URL
	 */
	public function url($page = 1)
	{
		// Clean the page number
		$page = max(1, (int) $page);

		// No page number in URLs to first page
		if ($page === 1)
		{
			$page = NULL;
		}

		switch ($this->config['current_page']['source'])
		{
			case 'query_string':
				return URL::site(Request::current()->uri, Request::$protocol).URL::query(array($this->config['current_page']['key'] => $page));

			case 'route':
				return URL::site(Request::current()->uri(array($this->config['current_page']['key'] => $page)), Request::$protocol).URL::query();
		}

		return '#';
	}
}