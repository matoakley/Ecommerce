<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Model_Address extends Model_Application
{
	public static function initialize(Jelly_Meta $meta)
	{
		$meta->table('addresses')
			->fields(array(
				'id' => new Field_Primary,
				'customer' => new Field_BelongsTo,
				'is_delivery' => new Field_Boolean,
				'line_1' => new Field_String(array(
					'rules' => array(
						'not_empty' => NULL,
					),
				)),
				'line_2' => new Field_String,
				'town' => new Field_String(array(
					'rules' => array(
						'not_empty' => NULL,
					),
				)),
				'county' => new Field_String(array(
					'rules' => array(
						'not_empty' => NULL,
					),
				)),
				'postcode' => new Field_String,
				'country' => new Field_BelongsTo,
				'telephone' => new Field_String,
				'latitude' => new Field_Float(array(
					'places' => 6,
				)),
				'longitude' => new Field_Float(array(
					'places' => 6,
				)),
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
	
	public function __toString()
	{
		$address_parts = array(
			$this->line_1,
			$this->line_2,
			$this->town,
			$this->county,
			// DON'T PUT POSTCODE HERE, IT BREAKS THE GEOCODE LOOKUP
			// (apologies for shouting, but it's quite important)
		);
		
		foreach ($address_parts as $key => $part)
		{
			if (is_null($part) OR $part == '')
			{
				unset($address_parts[$key]);
			}
		}
	
		return implode(', ', $address_parts);
	}

	public static function create($data, $customer_id, $is_delivery = FALSE)
	{
		$address = Jelly::factory('address');
		$address->customer = $customer_id;
		$address->is_delivery = $is_delivery;
		$address->line_1 = $data['line_1'];
		$address->line_2 = $data['line_2'];
		$address->town = $data['town'];
		$address->county = $data['county'];
		$address->postcode = $data['postcode'];
		$address->country = $data['country'];
		
		if (isset($data['telephone']))
		{
			$address->telephone = $data['telephone'];
		}
	
		return $address->save();
	}
	
	public function update($data)
	{
		$this->line_1 = $data['line_1'];
		$this->line_2 = $data['line_2'];
		$this->town = $data['town'];
		$this->county = $data['county'];
		$this->postcode = $data['postcode'];
	
		return $this->save();
	}
	
	public function save($key = NULL)
	{
		//TODO: Only save if address has changed.
		$this->geocode();
	
		return parent::save($key);
	}
	
	private function geocode()
	{
		// Geoding service using Open Street Maps (that's how we roll) http://wiki.openstreetmap.org/wiki/Nominatim
		$base_url = 'http://nominatim.openstreetmap.org/search';
		$request_parts = array(
			'q' => $this->__toString(),
			'format' => 'json',
		);
	
		//TODO: This should be put into a queue and processed to avoid holding checkout up if unavailable
		$response = json_decode(Remote::get($base_url.'?'.http_build_query($request_parts)));
		
		if (isset($response[0]))
		{
			$this->latitude = $response[0]->lat;
			echo Kohana::debug($response[0]->lat);
			echo Kohana::debug($this->latitude);
			$this->longitude = $response[0]->lon;
		}
		return $this; // DON'T RETURN $this->save() BECAUSE IT CREATES AN INFINITE LOOP.
	}
}