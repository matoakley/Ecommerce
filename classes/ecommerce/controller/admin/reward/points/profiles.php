<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Controller_Admin_Reward_Points_Profiles extends Controller_Admin_Application {

	function before()
	{
		if ( ! Kohana::config('ecommerce.modules.reward_points'))
		{
			throw new Kohana_Exception('This module is not enabled');
		}
	
		parent::before();
	}

	function action_index()
	{		
		$items = ($this->list_option != 'all') ? $this->list_option : FALSE;

		$search = Model_Reward_Points_Profile::search(array(), $items);

		// Pagination
		$this->template->pagination = Pagination::factory(array(
			'total_items' => $search['count_all'],
			'items_per_page' => ($items) ? $items : $search['count_all'],
			'auto_hide'	=> false,
			'view' => 'pagination/admin',
		));
		
		// Set URI into session for redirecting back from forms
		$this->session->set('admin.reward_points_profiles.index', $_SERVER['REQUEST_URI']);
		
		$this->template->profiles = $search['results'];
		$this->template->total_profiles = $search['count_all'];
		$this->template->page = (isset($_GET['page'])) ? $_GET['page'] : 1;
		$this->template->items = $items;
	}
	
	function action_edit($id = FALSE)
	{
		$profile = Model_Reward_Points_Profile::load($id);
				
		$fields = array();
		$fields['reward_points'] = $profile->as_array();
		
		$redirect_to = $this->session->get('admin.reward_points_profiles.index', '/admin/reward_points_profiles');
		
		$errors = array();
		
		if ($_POST)
		{
			try
			{
				$profile->validate($_POST['reward_points']);
				
			}
			catch (Validate_Exception $e)
			{
				$errors['reward_points'] = $e->array->errors();
				
			}
					
			if (empty($errors))
			{
				$profile->update($_POST['reward_points']);
			
				
				// If 'Save & Exit' has been clicked then lets hit the index with previous page/filters
				if (isset($_POST['save_exit']))
				{
					$this->request->redirect($redirect_to);
					
				}
				else
				{
					$this->request->redirect('/admin/reward_points_profiles/edit/' . $id);
					
				}
			}
			else
			{
				$fields['reward_points'] = $_POST['reward_points'];
				
			}
		}
		
		$this->template->fields = $fields;
		$this->template->errors = $errors;
		$this->template->reward_points_profile = $profile;
		
		
	}

	public function action_auto_generate()
	{
		$this->auto_render = FALSE;
		echo Model_Reward_Points_Profile::generate_unique_code();
		exit;
	}
	
	public function action_delete($id = NULL)
	{
		$this->auto_render = FALSE;
		
		$promotion_code = Model_Promotion_Code::load($id);
		$promotion_code->delete();
		
		$this->request->redirect($this->session->get('admin.promotion_codes.index', 'admin/promotion_codes'));
	}
		
	public function action_delete_reward()
	{
		if ( ! Request::$is_ajax)
		{
			throw new Kohana_Exception('Must be accessed via AJAX', NULL, 404);
		}
		
		$promotion_code = Model_Promotion_Code::load($this->request->param('promotion_code_id'));
		
		if ( ! $promotion_code->loaded())
		{
			throw new Kohana_Exception('Promotion code could not be found');
		}
		
		$this->auto_render = FALSE;
		
		$promotion_code_reward = Model_Promotion_Code_Reward::load($this->request->param('promotion_code_reward_id'));
		
		$promotion_code_reward->delete();
		
		echo 'okay';
	}
}