<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Twig Template extension to include the directory in template names.
 *
 * @package 	Ecommerce
 * @category	Front End
 * @author		Alex Gisby
 */

class Controller_Template_Twig extends Kohana_Controller_Template_Twig
{
	/**
	 * Quick overload to make sure that directories are respected in template names.
	 */
	public function before()
	{
		if (empty($this->template))
		{
			// Generate a template name if one wasn't set.
			$template_file 	= ($this->request->directory != '')?	$this->request->directory . '/' : '';
			$template_file 	.= str_replace('_', '/', $this->request->controller).'/'.$this->request->action;
			$this->template = $template_file;
		}

		return parent::before();
	}
}