<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Controller_Stockists extends Controller_Application
{
	public function before()
	{
		if ( ! Kohana::config('ecommerce.modules.stockists'))
		{
			throw new Kohana_Exception('This module is not enabled');
		}
		
		parent::before();
		
		$this->add_breadcrumb(URL::site(Route::get('stockists')->uri()), 'Stockists');
	}

	public function action_index()
	{	
		// If we are sent a postcode query then we'll search for stockists around it
		if (isset($_GET['postcode']))
		{
			$this->template->postcode = $_GET['postcode'];
			$response = json_decode(Remote::get('http://maps.googleapis.com/maps/api/geocode/json?address='.urlencode($_GET['postcode'].',UK').'&sensor=false&region=uk'));
			if (count($response->results) > 0)
			{
				$lat = $response->results[0]->geometry->location->lat;
				$lng = $response->results[0]->geometry->location->lng;
			}
			$this->template->map_centre = array(
				'lat' => $lat, 
				'lng' => $lng,
			);
			
			$distance = isset($_GET['distance']) ? $_GET['distance'] : 20;
			$bounding_box = Geolocation::get_bounding_box($lat, $lng, $distance);
			$this->template->stockists = Jelly::select('stockist')
																				->join('addresses')->on('stockists.address_id', '=', 'addresses.id')
																				->where('status', '=', 'active')
																				->where('latitude', '>',  $bounding_box[0])->where('latitude', '<',  $bounding_box[1])
																				->where('longitude', '>',  $bounding_box[2])->where('longitude', '<',  $bounding_box[3])
																				->execute();
		}
		else
		{
			$stockists_search = Model_Stockist::search(array('status:active'));
			$this->template->stockists = $stockists_search['results'];	
		}
	}
	
	public function action_view()
	{
		$stockist = Model_Stockist::load($this->request->param('slug'));
		
		if ( ! $stockist->loaded())
		{
			throw new Kohana_Exception('The stockist that you are searching for could not be found.');
		}
		
		$this->template->stockist = $stockist;
		
		$this->add_breadcrumb(URL::site(Route::get('view_stockist')->uri(array('slug' => $stockist->slug))), $stockist->name.', '.$stockist->address->town);
	}
	
}