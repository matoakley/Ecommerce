<?php defined('SYSPATH') or die('No direct script access.');

/**
 *  Currency helper.
 *
 * @author     Matt Oakley http://www.creativeintent.co.uk/
 */
class Kohana_Currency
{
	/**
	 * Deducts tax from the price provided.
	 *
	 * @param   float  $price
	 * @param		float  $tax_rate
	 * @return  float
	 */
	public static function deduct_tax($price, $tax_rate)
	{
		return number_format($price / (($tax_rate / 100) + 1), 2);
	}
	
	/**
	 * Adds tax to the price provided.
	 *
	 * @param   float  $price
	 * @param		float  $tax_rate
	 * @return  float
	 */
	public static function add_tax($price, $tax_rate)
	{
		return number_format($price + ($price * ($tax_rate / 100)), 2);
	}
}