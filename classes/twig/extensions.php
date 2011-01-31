<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Loads a default set of filters and extensions for 
 * Twig based on Kohana helpers
 *
 * @package kohana-twig
 * @author Jonathan Geiger
 */
class Twig_Extensions extends Kohana_Twig_Extensions
{
	/**
	 * Returns the added filters
	 *
	 * @return array
	 * @author Jonathan Geiger
	 */
	public function getFilters()
	{
		$array = array(
			'debug' => new Twig_Filter_Function('Kohana::debug'),
		);
		
		return array_merge(parent::getFilters(), $array);
	}
}