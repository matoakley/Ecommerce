<?php defined('SYSPATH') or die('No direct script access.');

class Model_Delivery_Option extends Jelly_Model
{
	public static function initialize(Jelly_Meta $meta)
    {
        $meta->table('delivery_options')
            ->fields(array(
                'id' => new Field_Primary,
				'name' => new Field_String,
				'price' => new Field_Float(array('places' => 2)),
    	));
    }

	public static function available_options()
	{
		return Jelly::select('delivery_option')->execute();
	}

	/**
	 * Returns the Retail Price of a product after adding VAT.
	 *
	 * @return  float
	 */
	public function retail_price()
	{
		return number_format($this->price + ($this->price * (Kohana::config('ecommerce.vat_rate') / 100)), 2);
	}
}