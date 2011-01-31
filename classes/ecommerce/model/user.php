<?php defined('SYSPATH') or die ('No direct script access.');

class Ecommerce_Model_User extends Model_Auth_User
{
	public static function initialize(Jelly_Meta $meta)
    {
		$meta->name_key('username')
			->sorting(array('username' => 'ASC'))
			->fields(array(
			'id' => new Field_Primary,
			'firstname' => new Field_String,
			'lastname' => new Field_String,
			'username' => new Field_Email(array(
				'unique' => TRUE,
				'rules' => array(
						'not_empty' => NULL,
					)
				)),
			'password' => new Field_Password(array(
				'hash_with' => array(Auth::instance(), 'hash_password'),
				'rules' => array(
					'not_empty' => NULL,
					'max_length' => array(50),
					'min_length' => array(6)
				)
			)),
			'password_confirm' => new Field_Password(array(
				'in_db' => FALSE,
				'callbacks' => array(
					'matches' => array('Model_Auth_User', '_check_password_matches')
				),
				'rules' => array(
					'not_empty' => NULL,
					'max_length' => array(50),
					'min_length' => array(6)
				)
			)),
			'email' => new Field_Email(array(
				'unique' => TRUE
			)),
			'logins' => new Field_Integer(array(
				'default' => 0
			)),
			'last_login' => new Field_Timestamp(array(
				'pretty_format' => 'D M Y H:i',
			)),
			'tokens' => new Field_HasMany(array(
				'foreign' => 'user_token'
			)),
			'roles' => new Field_ManyToMany,
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

	public static function load($id = FALSE)
	{
		return Jelly::select(get_called_class(), $id);
	}
	
	public static function search($conditions = array(), $items = FALSE)
	{
		$data = array();
		
		$query_string = (isset($_GET['q'])) ? explode(' ', $_GET['q']) : array();
		
		$query_string = array_merge($query_string, $conditions);
		
		$class = get_called_class();
		
		$results = Jelly::select($class);
		
		$filters = array();
		
		if ( ! empty($query_string))
		{
			// Extract the filters from the query string if they are listed in the Model.
			foreach ($query_string as $key => $qs)
			{
				if (strpos($qs, ':'))
				{
					$filter = explode(':', $qs);
				
					if (array_key_exists($filter[0], $class::$searchable_fields['filtered']))
					{
						$filters[$filter[0]] = $filter[1];
						unset($query_string[$key]);
					}
				}
			}
		}
		
		if ( ! empty($query_string))
		{
			$results->and_where_open();
			foreach ($query_string as $value)
			{
				foreach ($class::$searchable_fields['search'] as $field)
				{
					$results->or_where($field, 'LIKE', '%'.$value.'%');
				}
			}
			$results->and_where_close();
		}
		
		// Implement the filters based on rules in the Model.
		foreach ($filters as $field => $value)
		{
			if (array_key_exists('join', $class::$searchable_fields['filtered'][$field]))
			{
				foreach ($class::$searchable_fields['filtered'][$field]['join'] as $table => $on)
				{
					$results->join($table);
					$results->on($on[0], '=', $on[1]);
				}
			}
			
			$results->where($class::$searchable_fields['filtered'][$field]['field'], '=' , $value);
		}
		
		if ($items)
		{
			$page = (isset($_GET['page'])) ? $_GET['page'] : 1;		
			$results->limit($items)->offset(($page - 1) * $items);
		}
		
		$data['count_all'] = $results->count();
		$data['results'] = $results->execute();
		
		return $data;
	}
	
	public function update($data)
	{		
		$this->username = $data['email'];
		$this->email = $data['email'];
		$this->password = $data['password'];
		$this->password_confirm = $data['password'];
		
		$this->firstname = $data['firstname'];
		$this->lastname = $data['lastname'];
		
		foreach ($this->roles as $role)
		{
			$this->remove('roles', $role->id);
		}
		
		if (isset($data['roles']))
		{
			$this->add('roles', $data['roles']);
		}
		
		return $this->save();
	}
	
}