<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Model_Address_Geocode_Request extends Model_Application
{
	public static function initialize(Jelly_Meta $meta)
	{
		$meta->fields(array(
			'id' => new Field_Primary,
			'address' => new Field_BelongsTo,
			'status' => new Field_String,
			'request' => new Field_String,
			'response' => new Field_Serialized,
			'created' =>  new Field_Timestamp(array(
				'auto_now_create' => TRUE,
				'format' => 'Y-m-d H:i:s',
				'pretty_format' => 'd/m/Y H:i',
			)),
			'modified' => new Field_Timestamp(array(
				'auto_now_update' => TRUE,
				'format' => 'Y-m-d H:i:s',
			)),
			'deleted' => new Field_Timestamp(array(
				'format' => 'Y-m-d H:i:s',
			)),
		));
	}
	
	public static $statuses = array(
		'queued', 'completed', 'failed',
	);
	
	public static function queue($address)
	{
		// Check this address isn't already queued
		$requests = Jelly::select('address_geocode_request')->where('address_id', '=', $address->id)->where('status', '=', 'queued')->count();
		if ($requests == 0)
		{
			$request = Jelly::factory('address_geocode_request');
			$request->address = $address;
			$request->status = 'queued';
			$request->save();
		}
		return TRUE;
	}
	
	public function process()
	{
		// Geoding service using Open Street Maps (that's how we roll) http://wiki.openstreetmap.org/wiki/Nominatim
		$base_url = 'http://nominatim.openstreetmap.org/search';
		$request_parts = array(
			'q' => (string)$this->address,
			'format' => 'json',
			'countrycodes' => strtolower($this->address->country->iso_3_code),
		);
	
		try
		{
			$request_url = $base_url.'?'.http_build_query($request_parts);
			$response = json_decode(Remote::get($request_url));
			
			$this->request = $request_url;
			$this->response = $response;
			
			if (isset($response[0]))
			{
				$this->address->set_lat_lng($response[0]->lat, $response[0]->lon);
				$this->status = 'completed';
			}
			else
			{
				$request_parts = array(
					'q' => (string)$this->address->generic_string(),
					'format' => 'json',
					'countrycodes' => strtolower($this->address->country->iso_3_code),
				);
				$request_url = $base_url.'?'.http_build_query($request_parts);
				$response = json_decode(Remote::get($request_url));
				
				$this->request = $request_url;
				$this->response = $response;
				
				if (isset($response[0]))
				{
					$this->address->set_lat_lng($response[0]->lat, $response[0]->lon);
					$this->status = 'completed';
				}
				else
				{
					$this->status = 'failed';
				}
			}
		}
		catch (Kohana_Exception $e)
		{
			$this->status = 'failed';
		}
		
		return $this->save();
	}
}