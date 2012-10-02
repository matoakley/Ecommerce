<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Controller_Admin_Promotion_Codes extends Controller_Admin_Application {

	function before()
	{
		if ( ! Kohana::config('ecommerce.modules.promotion_codes'))
		{
			throw new Kohana_Exception('This module is not enabled');
		}
	
		parent::before();
	}

	function action_index()
	{		
		$items = ($this->list_option != 'all') ? $this->list_option : FALSE;

		$search = Model_Promotion_Code::search(array(), $items);

		// Pagination
		$this->template->pagination = Pagination::factory(array(
			'total_items' => $search['count_all'],
			'items_per_page' => ($items) ? $items : $search['count_all'],
			'auto_hide'	=> false,
			'view' => 'pagination/admin',
		));
		
		// Set URI into session for redirecting back from forms
		$this->session->set('admin.promotion_codes.index', $_SERVER['REQUEST_URI']);
		
		$this->template->promotion_codes = $search['results'];
		$this->template->total_promotion_codes = $search['count_all'];
		$this->template->page = (isset($_GET['page'])) ? $_GET['page'] : 1;
		$this->template->items = $items;
	}
	
	function action_edit($id = FALSE)
	{
		$promotion_code = Model_Promotion_Code::load($id);
	
		if ($id AND ! $promotion_code->loaded())
		{
			throw new Kohana_Exception('Promotion Code could not be found.');
		}
		
		$redirect_to = $this->session->get('admin.promotion_codes.index', '/admin/promotion_codes');
		$this->template->cancel_url = $redirect_to;
		
		$fields = array();
		$fields['promotion_code'] = $promotion_code->as_array();
		$fields['rewards'] = array();
		foreach ($promotion_code->rewards as $reward)
		{
			$fields['rewards'][] = Arr::merge($reward->as_array(), array('sku_reward_retail_price' => $reward->sku_reward_retail_price()));
		}
		
		$errors = array();
		
		if ($_POST)
		{
			if ( ! isset($_POST['promotion_code']['run_indefinitely']))
			{
				$start_date = DateTime::CreateFromFormat('d/m/Y H:i', $_POST['valid_from_date'].' '.$_POST['valid_from_hour'].':'.$_POST['valid_from_minute']);
				$end_date = DateTime::CreateFromFormat('d/m/Y H:i', $_POST['valid_to_date'].' '.$_POST['valid_to_hour'].':'.$_POST['valid_to_minute']);
				$_POST['promotion_code']['start_date'] = $start_date->format('U');
				$_POST['promotion_code']['end_date'] = $end_date->format('U');
			}
			
			try
			{
				$promotion_code->validate($_POST['promotion_code']);
			}
			catch (Validate_Exception $e)
			{
				$errors['promotion_code'] = $e->array->errors();
			}
					
			if (empty($errors))
			{
				$promotion_code->update($_POST['promotion_code']);
				
				// If 'Save & Exit' has been clicked then lets hit the index with previous page/filters
				if (isset($_POST['save_exit']))
				{
					$this->request->redirect($redirect_to);
				}
				else
				{
					$this->request->redirect('/admin/promotion_codes/edit/' . $promotion_code->id);
				}
			}
			else
			{
				$fields['promotion_code'] = $_POST['promotion_code'];
			}
		}
		
		$this->template->fields = $fields;
		$this->template->errors = $errors;
		
		$all_skus = Model_Sku::search();
		
		$this->template->promotion_code = $promotion_code;
		$this->template->all_skus = $all_skus['results'];
		$this->template->statuses = Model_Promotion_Code::$statuses;
		$this->template->promotion_types = Model_Promotion_Code::$types;
		$this->template->reward_types = Model_Promotion_Code_Reward::$reward_types;
	}

	public function action_auto_generate()
	{
		$this->auto_render = FALSE;
		echo Model_Promotion_Code::generate_unique_code();
		exit;
	}
	
	public function action_delete($id = NULL)
	{
		$this->auto_render = FALSE;
		
		$promotion_code = Model_Promotion_Code::load($id);
		$promotion_code->delete();
		
		$this->request->redirect($this->session->get('admin.promotion_codes.index', 'admin/promotion_codes'));
	}
	
	/** AJAX FUNCTIONS **/
	
	public function action_edit_reward()
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
		
		$promotion_code_reward = Model_Promotion_Code_Reward::load($this->request->param('promotion_code_reward_id'));
		
		if ( $promotion_code_reward->loaded())
		{
			$this->template->promotion_code_reward = $promotion_code_reward;
		}
		
		$fields = array(
			'reward' => $promotion_code_reward->as_array(),
		);
		$fields['reward']['sku_reward_retail_price'] = $promotion_code_reward->sku_reward_retail_price();
		$errors = array();
		
		$all_skus = Model_Sku::search();		
		$all_rewards = array();
		if ($_POST)
		{	
			try
			{
				$promotion_code_reward->validate($_POST['reward']);
			}
			catch (Validate_Exception $e)
			{
				$errors['reward'] = $e->array->errors();
			}
			
			if (empty($errors))
			{
				$promotion_code_reward->update($promotion_code, $_POST['reward']);
				
				foreach ($promotion_code->rewards as $reward)
				{
					$all_rewards[] = Arr::merge($reward->as_array(), array('sku_reward_retail_price' => $reward->sku_reward_retail_price()));
				}
				
				$template_data = array(
					'promotion_code' => $promotion_code,
					'fields' => array(
						'rewards' => $all_rewards,
					),
					'reward_types' => Model_Promotion_Code_Reward::$reward_types,
					'all_skus' => $all_skus['results'],
				);
				
				$view = Twig::factory('admin/promotion/codes/_promotion_code_rewards.html', $template_data, $this->environment)->render();
				
				$data = array(
					'data' => $promotion_code_reward->as_array(),
					'view' => $view,
				);
				
			echo json_encode($data);
				exit;	
			}
			else
			{
				echo json_encode($errors);
				exit;
			}
		}

		$template_data = array(
					'promotion_code' => $promotion_code,
					'fields' => array(
						'reward' => $promotion_code_reward,
					),
					'reward_types' => Model_Promotion_Code_Reward::$reward_types,
					'all_skus' => $all_skus['results'],
				);
				$view = Twig::factory('admin/promotion/codes/edit_reward.html', $template_data, $this->environment)->render();
				
				echo $view;
		
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