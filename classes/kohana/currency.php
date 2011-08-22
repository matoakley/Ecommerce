<?php defined('SYSPATH') or die('No direct script access.');

/**
 *  Currency helper.
 *
 * @author     Matt Oakley http://creativeintent.co.uk/
 */
class Kohana_Currency
{
	/**
	 * Deducts tax from the price provided.
	 * NOTE: Don't be tempted to perform number_format here as numbers > 1000 contain thousand separator and become a string
	 *
	 * @param   float  $price
	 * @param		float  $tax_rate
	 * @return  float
	 */
	public static function deduct_tax($price, $tax_rate)
	{
		return $price / (($tax_rate / 100) + 1);
	}
	
	/**
	 * Adds tax to the price provided.
	 * NOTE: Don't be tempted to perform number_format here as numbers > 1000 contain thousand separator and become a string
	 *
	 * @param   float  $price
	 * @param		float  $tax_rate
	 * @return  float
	 */
	public static function add_tax($price, $tax_rate)
	{
		return $price + ($price * ($tax_rate / 100));
	}
	
	/**
	 * Calculates the amount of tax for the item.
	 * NOTE: Don't be tempted to perform number_format here as numbers > 1000 contain thousand separator and become a string
	 *
	 * @param   float  	$price
	 * @param		float  	$tax_rate
	 * @param		boolean $price_includes_tax
	 * @return  float
	 */
	public static function calculate_tax($price, $tax_rate, $price_includes_tax = TRUE)
	{
		return ($price_includes_tax) ? $price - self::deduct_tax($price, $tax_rate) : self::add_tax($price, $tax_rate) - $price;
	}
}