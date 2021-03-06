<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Ecommerce Address Model
 * @package Ecommerce
 * @author	Matt Oakley
 */
class Ecommerce_Model_Address extends Model_Application
{
	public static function initialize(Jelly_Meta $meta)
	{
		$meta->table('addresses')
			->fields(array(
				'id' => new Field_Primary,
				'customer' => new Field_BelongsTo,
				'is_delivery' => new Field_Boolean,
				'house_name' => new Field_String,
				'line_1' => new Field_String,
				'line_2' => new Field_String,
				'line_3' => new Field_String,
				'town' => new Field_String,
				'county' => new Field_String,
				'postcode' => new Field_String,
				'country' => new Field_BelongsTo,
				'telephone' => new Field_String,
				'latitude' => new Field_Float(array(
					'places' => 6,
				)),
				'longitude' => new Field_Float(array(
					'places' => 6,
				)),
				'archived' => new Field_Timestamp(array(
					'format' => 'Y-m-d H:i:s',
				)),
				'name' => new Field_String,
				'notes' => new Field_String,
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
	
	public static function customer_address_validator($data)
	{
		$validator = Validate::factory($data)
											->filter(TRUE, 'trim')
											->rule('line_1', 'not_empty')
											->rule('town', 'not_empty')
											->rule('postcode', 'not_empty');
		
		if ( ! $validator->check())
		{
			throw new Validate_Exception($validator);
		}
		
		return TRUE;
	}
	
	/**
	 * Find addresses with specified postcode
	 * @param string $postcode		Postcode string to search for
	 * @return Jelly_Collection		Iterable collection of matching addresses
	 */
	public static function find_by_postcode($postcode)
	{
		return Jelly::select('address')->where(DB::expr('REPLACE(postcode, \' \', \'\')'), 'LIKE', '%'.$postcode.'%')->execute();
	}
	
	/**
	 * Find a geocoded address with specified postcode
	 * @param string $postcode		Postcode string to search for
	 * @return Ecommerce_Model_Address		Iterable collection of matching addresses
	 */
	public static function find_lat_lng_by_postcode($postcode)
	{
		$lat_lng = array();
		$address = Jelly::select('address')->where(DB::expr('REPLACE(postcode, \' \', \'\')'), 'LIKE', str_replace(' ', '', $postcode))->where('latitude', '<>', '')->where('longitude', '<>', '')->load();
		if ($address->loaded())
		{
			$lat_lng = array(
				'lat' => $address->latitude,
				'lng' => $address->longitude,
			);
		}
		return $lat_lng;
	}
	
	public function create_for_new_customer($customer, $data)
	{
		$this->customer = $customer;
		$this->line_1 = $data['line_1'];
		$this->line_2 = $data['line_2'];
		$this->line_3 = $data['line_3'];
		$this->town = $data['town'];
		$this->county = $data['county'];
		$this->postcode = $data['postcode'];
		$this->country = $data['country'];
		$this->telephone = $data['telephone'];
		$this->name = $data['name'];
		$this->notes = $data['notes'];
		$this->save();
	
		$customer->set_default_billing_address($this);
		$customer->set_default_shipping_address($this);
		
		return $this;
	}
	
	public function __toString()
	{
		$address_parts = array(
			$this->line_1,
			$this->line_2,
			$this->line_3,
			$this->town,
			$this->county,
			// DON'T PUT POSTCODE HERE, IT BREAKS THE GEOCODE LOOKUP
			// (apologies for shouting, but it's quite important)
		);
		
		if ($this->country->loaded())
		{
			$address_parts[] = $this->country->name;
		}
		
		foreach ($address_parts as $key => $part)
		{
			if (is_null($part) OR $part == '')
			{
				unset($address_parts[$key]);
			}
		}
	
		return implode(', ', $address_parts);
	}
	
	public function human_string()
	{
		$address_parts = array(
			$this->line_1,
			$this->line_2,
			$this->line_3,
			$this->town,
			$this->county,
			$this->postcode,
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

	public static function create($data, $customer_id = NULL, $is_delivery = FALSE)
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
		if (isset($data['name']))
		{
			$address->name = $data['name'];
		}
		
		if (isset($data['latitude']))
		{
			$address->latitude = $data['latitude'];
		}
		if (isset($data['longitude']))
		{
			$address->longitude = $data['longitude'];
		}

		if (isset($data['line_3']))
		{
			$address->line_3 = $data['line_3'];
		}
		
		if (isset($data['telephone']))
		{
			$address->telephone = $data['telephone'];
		}
		
		if (isset($data['notes']))
		{
			$address->notes = $data['notes'];
		}
	
		return $address->save();
	}
		
	public function update($data)
	{ 
	 if (isset($data['line_1']))
		{
		$this->line_1 = $data['line_1'];
		}
		if (isset($data['line_2']))
		{
		$this->line_2 = $data['line_2'];
		}
		if (isset($data['town']))
		{
		$this->town = $data['town'];
		}
		if (isset($data['county']))
		{
		$this->county = $data['county'];
		}
		if (isset($data['postcode']))
		{
		$this->postcode = $data['postcode'];
		}
		if (isset($data['telephone']))
		{
			$this->telephone = $data['telephone'];
		}
		if (isset($data['line_3']))
		{
			$this->line_3 = $data['line_3'];
		}
		if (isset($data['notes']))
		{
			$this->notes = $data['notes'];
		}
		if (isset($data['name']))
		{
		  $this->name = $data['name'];
		}
		if (isset($data['latitude']))
		{
			$this->latitude = $data['latitude'];
		}
		if (isset($data['longitude']))
		{
			$this->longitude = $data['longitude'];
		}
		if (isset($data['address']))
		{
    $address = explode(", ", $_POST['address']);
    $this->line_1 = $address[0];
    $this->line_2 = $address[1];
    $this->line_3 = $address[2];
    $this->town = $address[3];
    $this->county = $address[4];
    $this->postcode = $address[5];
    
		}
		return $this->save();
	}
	
	public function save($key = NULL, $geocode = TRUE)
	{
		$has_changed = $this->changed();
		
		if (get_class($this->meta()->fields('county')) == 'Field_String')
		{		
			// Sometimes, lazy ass people like to shorten the county name and 
			// this breaks the postcode lookup. This is where we try and put
			// the correct county name back in.
			switch (strtolower($this->county))
			{
				case 'bucks':
					$this->county = 'Buckinghamshire';
					break;
			
				case 'cambs':
					$this->county = 'Cambridgeshire';
					break;
			
				case 'herts':
					$this->county = 'Hertfordshire';
					break;
					
				case 'northants':
					$this->county = 'Northamptonshire';
					break;
				
				case 'staffs':
					$this->county = 'Staffordshire';
					break;
					
				case 'wilts':
					$this->county = 'Wiltshire';
					break;
					
				default:
					break;
			}
		}
		
		parent::save($key);
		
		// Only queue for geocoding if the address has been changed
		// We must call this after save so that we have an id
		if ($has_changed AND $geocode)
		{
			$this->geocode();
		}
	
		return $this;
	}
	
	public function geocode()
	{
		Model_Address_Geocode_Request::queue($this);
		return $this;
	}
	
	public function set_lat_lng($lat, $lng)
	{
		$this->latitude = $lat;
		$this->longitude = $lng;
		$this->save(NULL, FALSE);
	}
	
	public function generic_string()
	{
		$address_parts = array(
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
	
	public function archive()
	{
		$this->archived = time();
		return $this->save();
	}
}