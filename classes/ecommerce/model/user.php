<?php defined('SYSPATH') or die ('No direct script access.');

class Ecommerce_Model_User extends Model_Auth_User
{
	public static function initialize(Jelly_Meta $meta)
	{
		$meta->name_key('username')
			->fields(array(
				'id' => new Field_Primary,
				'customer' => new Field_HasOne,
				'firstname' => new Field_String,
				'lastname' => new Field_String,
				'username' => new Field_Email(array(
					'unique' => TRUE,
					'rules' => array(
						'not_empty' => NULL,
					),
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
				  'format' => 'U',
					'pretty_format' => 'D M Y H:i',
				)),
				'short_bio' => new Field_Text,
				'tokens' => new Field_HasMany(array(
					'foreign' => 'user_token'
				)),
				'roles' => new Field_ManyToMany,
				'avatar' => new Field_String(array(
					'in_db' => FALSE,
				)),
				'comments' => new Field_HasMany,
				'reviews' => new Field_HasMany,
				'wish_list_id' => new Field_String,
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

	public static $searchable_fields = array(
		'filtered' => array(
			'role' => array(
				'join' => array(
					'roles_users' => array('user.id', 'roles_users.user_id'),
					'roles' => array('role.id', 'roles_users.role_id'),
				),
				'field' => 'role.id',
			),
		),
		'search' => array(
			'firstname',
			'lastname',
			'email',
		),
	);

	public static function _email_is_unique(Validate $array, $field, $params = NULL)
	{
		$is_duplicate = Jelly::select('user')->where('email', '=', $array['email'])->where('deleted', 'IS', NULL);
		
		if (isset($params['id']))
		{
			$is_duplicate->where('id', '<>', $params['id']);
		}
		
		$is_duplicate = (bool) $is_duplicate->count();
		
		if ($is_duplicate)
		{
			$array->error('email', 'unique');
		}
	}

	public function __get($field)
	{
		if ($field == 'avatar')
		{
			return $this->get_avatar();
		}
	
		return parent::__get($field);
	}
	
	public static function create_for_customer($customer, $password, $username = NULL)
	{
		$user = Jelly::factory('user');
		$user->username = $username ? $username : $customer->email;
		$user->email = $customer->email;
		$user->password = $password;
		$user->password_confirm = $password;
		$user->add('roles', array(1,3));
		return $user->save();
	}

	public static function load($id = FALSE)
	{
		return Jelly::select(get_called_class(), $id);
	}
	
	public static function search($conditions = array(), $items = FALSE, $order = FALSE)
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
		
		if ($order)
		{
			foreach ($order as $key => $value)
			{
				$results->order_by($key, $value);
			}
		}
		
		$data['results'] = $results->execute();
		
		return $data;
	}
	
	public function get_avatar()
	{
		$file_path = '/images/users/' . $this->id . '.jpg';
		
		if ( ! file_exists(DOCROOT . $file_path))
		{
			$file_path = '/images/users/default.jpg';
		}
		
		return $file_path;
	}
	
	public function update($data)
	{	
		$this->username = $data['email'];
		$this->email = $data['email'];
		$this->password = $data['password'];
		$this->password_confirm = $data['password'];
		
		$this->firstname = $data['firstname'];
		$this->lastname = $data['lastname'];
		
		$this->short_bio = Text::auto_p($data['short_bio']);
		
		foreach ($this->roles as $role)
		{
			$this->remove('roles', $role->id);
		}
		
		// Always set Login and Admin roles
		$data['roles'][] = 1;
		$data['roles'][] = 2;
		
		if (isset($data['roles']))
		{
			$this->add('roles', $data['roles']);
		}
		
		return $this->save();
	}
	
	public function upload_image($tmp_file)
	{
		// Let's get to work on resizing this image
		$image = Image::factory($tmp_file);
		
		// Full Size first
		$image_size = Kohana::config('ecommerce.image_sizing.thumbnail');
		$image->resize($image_size['width'], $image_size['height'], Image::INVERSE);
		
		// Crop it for good measure
		$image->crop($image_size['width'], $image_size['height']);
		
		$directory = DOCROOT . '/images/users';
		if ( ! is_dir($directory))
		{
			mkdir($directory);
		}
		
		$image->save($directory . DIRECTORY_SEPARATOR . $this->id . '.jpg');
	}
	
	public function delete_image()
	{	
		$directory = DOCROOT . '/images/users';
		
		try
		{
			unlink($directory . DIRECTORY_SEPARATOR . $this->id . '.jpg');
		}
		catch (Exception $e)
		{}		
	}
	
	public function change_password($new_password)
	{
		$this->password = $new_password;
		return $this->save();
	}
	
	public function name()
	{
		return $this->firstname.' '.$this->lastname;
	}
	
	public function update_email($email)
	{
		$this->email = $email;
		$this->username = $email;
		return $this->save();
	}
	
	public function watch_item($item)
	{
	  $wish_list = Model_Wish_List::load();
	  $wish_list->add_watch_item($this, $item);

	}
	
	public function unwatch_item($item)
	{
		$wish_list = Model_Wish_List::load();
	  $wish_list->remove_watch_item($this, $item);
	}
	
	public function generate_wish_list_id()
	{
    $user = Jelly::select('user')->where('id', '=', $this->id)->load();
    
		$length = 16;
	
		$code = FALSE;
		
		while ( ! $code OR Jelly::select('user')->where('wish_list_id', '=', $code)->count() > 0)
		{
			$code = Text::random('distinct', $length);
		}
		
		$user->wish_list_id = $code;
		$user->save();
	
	}
}