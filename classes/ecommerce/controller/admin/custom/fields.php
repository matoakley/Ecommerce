<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Controller_Admin_Custom_Fields extends Controller_Admin_Application
{
	function before()
	{
		if ( ! Kohana::config('ecommerce.modules.custom_fields'))
		{
			throw new Kohana_Exception('This module is not enabled');
		}
	
		parent::before();
	}

	public function action_index()
	{
		$items = ($this->list_option != 'all') ? $this->list_option : FALSE;

		$search = Model_Custom_Field::search(array(), $items);

		// Pagination
		$this->template->pagination = Pagination::factory(array(
			'total_items'  => $search['count_all'],
			'items_per_page' => ($items) ? $items : $search['count_all'],
			'auto_hide'	=> false,
			'view' => 'pagination/admin',
		));
		
		// Set URI into session for redirecting back from forms
		$this->session->set('admin.custom_fields.index', $_SERVER['REQUEST_URI']);
		
		$this->template->custom_fields = $search['results'];
		$this->template->total_custom_fields = $search['count_all'];
		$this->template->page = (isset($_GET['page'])) ? $_GET['page'] : 1;
		$this->template->items = $items;
	}
	
	public function action_edit()
	{
		$custom_field_id = $this->request->param('id');
		
		$custom_field = Model_Custom_Field::load($custom_field_id);
	
		if ($custom_field_id AND ! $custom_field->loaded())
		{
			throw new Kohana_Exception('Custom Field could not be found.');
		}
		
		$redirect_to = $this->session->get('admin.custom_fields.index', '/admin/custom_fields');
		$this->template->cancel_url = $redirect_to;
		
		$fields = array(
			'custom_field' => $custom_field->as_array(),
		);
		$errors = array();
		
		if ($_POST)
		{
			try
			{
				$custom_field->validate($_POST['custom_field']);
			}
			catch (Validate_Exception $e)
			{
				$errors['custom_field'] = $e->array->errors();
			}
			
			if ( ! $errors)
			{
				$custom_field->update($_POST['custom_field']);
				
				// If 'Save & Exit' has been clicked then lets hit the index with previous page/filters
				if (isset($_POST['save_exit']))
				{
					$this->request->redirect($redirect_to);
				}
				else
				{
					$this->request->redirect('/admin/custom_fields/edit/' . $custom_field->id);
				}
			}
			else
			{
				$fields = $_POST;
			}
		}
		
		$this->template->fields = $fields;
		$this->template->errors = $errors;
		
		$this->template->custom_field = $custom_field;
		$this->template->objects = Model_Custom_Field::$objects;
	}
	
	public function action_delete()
	{
		$this->auto_render = FALSE;
		
		$custom_field = Model_Custom_Field::load($this->request->param('id'));
		$custom_field->delete();
		
		$this->request->redirect($this->session->get('admin.custom_fields.index', 'admin/custom_fields'));
	}
	
	public function action_delete_document()
	{
	 $custom_field_id = $this->request->param('field_id');
	 $object_id = $this->request->param('object_id');
	 $document = Jelly::select('custom_field_value')->where('custom_field_id', '=', $custom_field_id)->where('object_id', '=', $object_id)->load();
	 
	 $object = array( 'id' => $object_id);
	 $custom_field = array( 'id' => $custom_field_id);
	 
	 $data = array();
	 
		$data['html'] = Twig::factory('/admin/snippets/_upload.html', array(
			'object' => $object,
			'custom_field' => $custom_field,
			'inputs' => Model_Product::$inputs,
		))->render();
		
	 $document->delete();
		
		echo json_encode($data);
		
	}
}
