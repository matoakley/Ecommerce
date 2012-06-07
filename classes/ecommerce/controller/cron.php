<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Controller_Cron extends Controller_Application
{
	function before()
	{
		$this->auto_render = FALSE;
		
		// Check that customer accounts module is enabled
		if ( ! Kohana::$is_cli)
		{
			throw new Kohana_Exception('Action only available from command line.', NULL, 401);
		}
				
		parent::before();
	}

	// - Add cron trigger 'php /path/to/install/index.php --uri=/cron/index' every minute
	public function action_index()
	{
		// Called every minute
		if ($this->modules['geocoded_addresses'])
		{
			$requests = Jelly::select('address_geocode_request')->where('status', '=', 'queued')->limit(10)->execute();
			foreach ($requests as $request)
			{
				$request->process();
			}
		}
		exit();
	}
}