<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Controller_Admin_Reviews extends Controller_Admin_Application
{
	function before()
	{
		if ( ! Kohana::config('ecommerce.modules.reviews'))
		{
			throw new Kohana_Exception('The Reviews module is not enabled');
		}
	
		parent::before();
	}
	
	public function action_index()
	{					
		$items = ($this->list_option != 'all') ? $this->list_option : FALSE;

		$search = Model_Review::search(array(), $items);

		// Pagination
		$this->template->pagination = Pagination::factory(array(
			'total_items' => $search['count_all'],
			'items_per_page' => ($items) ? $items : $search['count_all'],
			'auto_hide'	=> false,
			'view' => 'pagination/admin',
		));
		
		// Set URI into session for redirecting back from forms
		$this->session->set('admin.reviews.index', $_SERVER['REQUEST_URI']);
		
		$this->template->reviews = $search['results'];
		$this->template->total_reviews = $search['count_all'];
		$this->template->page = (isset($_GET['page'])) ? $_GET['page'] : 1;
		$this->template->items = $items;
	}
	
	public function action_edit()
	{
  	$review = Model_Review::load($this->request->param('id'));
	
		if ($this->request->param('id') AND ! $review->loaded())
		{
			throw new Kohana_Exception('Review could not be found.');
		}
		
		$fields = array(
		  'review' => $review->as_array(),
		);
		$errors = array();
		
		$redirect_to = $this->session->get('admin.reviews.index', '/admin/reviews');
		$this->template->cancel_url = $redirect_to;
		
		if ($_POST)
		{
  		try
  		{
        $review->validate($_POST['review']);
  		}
  		catch (Validate_Exception $e)
  		{
    		$errors['review'] = $e->array->errors();
  		}
  		
  		if (empty($errors))
  		{
    		$review->update($_POST['review']);
    		
    		// If 'Save & Exit' has been clicked then lets hit the index with previous page/filters
				if (isset($_POST['save_exit']))
				{
					$this->request->redirect($redirect_to);
				}
				else
				{
					$this->request->redirect('/admin/reviews/edit/'.$review->id);
				}
  		}
  		else
  		{
    		$fields['review'] = $_POST['review'];
  		}
		}
		
		$this->template->fields = $fields;
		$this->template->errors = $errors;
		
		$this->template->review = $review;
		$this->template->statuses = Model_Review::$statuses;
	}
	
	public function action_delete()
	{
		$this->auto_render = FALSE;
		
		$review = Model_Review::load($this->request->param('id'));
		$review->delete();
		
		$this->request->redirect($this->session->get('admin.reviews.index', 'admin/reviews'));
	}
}