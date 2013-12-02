<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Model_Application extends Jelly_Model
{
  public static $objects = array();

  // Gets the model that is calling the function and
  // rips the Model_ from the beginning ready for DB queries
	protected function get_object($parent_class = NULL)
	{
  	// Trim the 'Model_'part from the start of the class name and convert to lowercase for DB query
		$class = get_called_class();
		$object = strtolower(substr($class, 6));

		// If a class is passed then we need to check that this
		// object is a valid option within the static $objects array
		if ($parent_class AND ! empty($parent_class::$objects))
		{
  		if ( ! in_array($object, $parent_class::$objects))
  		{
  			throw new Kohana_Exception('The type of object does not comply to module pattern.');
  		}
    }

		return $object;
	}

  public function display_meta_description($length = 160)
  {
    if ($this->_meta->columns('meta_description') AND $this->meta_description AND $this->meta_description != '')
    {
      return $this->meta_description;
    }
    elseif ($this->_meta->columns('description'))
    {
      return Text::limit_chars(strip_tags($this->description), $length, NULL, TRUE);
    }
    elseif ($this->_meta->columns('body'))
    {
      return Text::limit_chars(strip_tags($this->body), $length, NULL, TRUE);
    }
    elseif ($this->_meta->columns('text'))
    {
      return Text::limit_chars(strip_tags($this->text), $length, NULL, TRUE);
    }

    return FALSE;
  }

	public static function load($id = FALSE)
	{
		$model_meta = Jelly::meta(get_called_class());

		if (is_numeric($id))
		{
			return Jelly::select(get_called_class(), $id);
		}
		else if ($model_meta->columns('slug'))
		{
			return Jelly::select(get_called_class())
							->where('slug', '=', $id)
							->where('status', '=', 'active')
							->limit(1)
							->execute();
		}
		else return Jelly::factory(get_called_class());
	}

	/**
	* Soft delete if deleted column exists.
	*
	**/
	public function delete($key = NULL)
	{
		$result = FALSE;

		if (is_object($this->_meta) && $this->_meta->columns('deleted'))
		{
			$this->deleted = time();
			$result = $this->save($key);

			// Clear the object so it appears deleted anyway
			$this->clear();

			$result = (bool) $result;
		}
		else
		{
			$result = parent::delete();
		}

		return $result;
	}

	// If we are updating the status then check that it
	// exisits in the array of valid statuses
	public function update_status($status)
	{
  	if (isset(self::$statuses))
  	{
    	if ( ! in_array($status, self::$statuses))
    	{
      	throw new Kohana_Exception('Invalid status.');
    	}
  	}

  	$this->status = $status;
  	return $this->save();
	}

	public static function search($conditions = array(), $items = FALSE, $order = FALSE, $include_archived = FALSE)
	{
		$data = array();

		//see if the query string is a definitive phrase;
		//if phrase, use the phrase as 0 in array, otherwise split it up.
		$query_string = (isset($_GET['q'])) ? preg_match('#^(\'|").+\1$#', $_GET['q']) == 1 ? array(str_replace(array('"',"'"), '', $_GET['q'])) : explode(' ', $_GET['q']) : array();

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
				//filter backwards, if the operand is != then get the filters and set $not to true to use later
				elseif (strpos($qs, '!='))
				  {
  				  $filter = explode('!=', $qs);
  				  $not = TRUE;

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
			//look here for $not to see if it should be filtering backwards
			if (isset($not) && $not == TRUE)
  			{
    			$results->where($class::$searchable_fields['filtered'][$field]['field'], '!=' , $value);
  			}
  		//otherwise do the usual gubbins.
			else
  			{
    		  $results->where($class::$searchable_fields['filtered'][$field]['field'], '=' , $value);
  			}
		}

		$model = new $class;

		if ( ! $include_archived AND $model->meta()->fields('status'))
		{
			$results->where('status', '<>', 'archived');
		}

		$data['count_all'] = $results->count();

		if ($order)
		{
			foreach ($order as $key => $value)
			{
				$results->order_by($key, $value);
			}
		}

		if ($items)
		{
			$page = (isset($_GET['page'])) ? $_GET['page'] : 1;
			$results->limit($items)->offset(($page - 1) * $items);
		}

		$data['results'] = $results->execute();

		$data['query_string'] = $query_string;

		return $data;
	}


/************************************************
*
* CUSTOM FIELDS
*
*************************************************/

	/**
	* Returns a collection of custom fields for the object.
	*/
	public function custom_fields()
	{
		if ( ! Kohana::config('ecommerce.modules.custom_fields'))
		{
			throw new Kohana_Exception('The custom fields module is not enabled.');
		}

		$object = $this->get_object('Model_Custom_Field');

		return Jelly::select('custom_field')->where('object', '=', $object)->execute();
	}

	/**
	* Returns the value for the custom_field with matching tag for calling object.
	*/
	public function custom_field($tag)
	{
		if ( ! Kohana::config('ecommerce.modules.custom_fields'))
		{
			throw new Kohana_Exception('The custom fields module is not enabled.');
		}

		$object = $this->get_object('Model_Custom_Field');

		return Jelly::select('custom_field_value')
							->join('custom_fields')->on('custom_field_values.custom_field_id', '=', 'custom_fields.id')
							->where('custom_fields.object', '=', $object)->where('custom_fields.tag', '=', $tag)->where('object_id', '=', $this->id)
							->load()->value;
	}

	public function update_custom_field_values($fields)
	{
		if ( ! Kohana::config('ecommerce.modules.custom_fields'))
		{
			throw new Kohana_Exception('The custom fields module is not enabled.');
		}

		$object = $this->get_object('Model_Custom_Field');

		// Save custom fields
		foreach ($fields as $key => $value)
		{
			Model_Custom_Field_Value::update($key, $this->id, $value);
		}
	}

/************************************************
*
* REVIEWS
*
*************************************************/

  public function reviews()
  {
    Model_Review::check_module();

    $object = $this->get_object('Model_Review');

		return Jelly::select('review')
		          ->where('object', '=', $object)
		          ->where('object_id', '=', $this->id)
 		          ->where('status', '=', 'active')
		          ->execute();
  }

	public function is_reviewed_by_user($user = NULL)
	{
  	Model_Review::check_module();

  	if ( ! $user)
    {
      $user = Auth::instance()->get_user();

      if ( ! $user OR ! $user->loaded())
      {
        return FALSE;
      }
    }

    $object = $this->get_object('Model_Review');

    return (bool) $user->get('reviews')
                        ->where('object', '=', $object)
                        ->where('object_id', '=', $this->id)
                        ->count();
	}

	public function average_rating()
	{
    Model_Review::check_module();
  	return Model_Review::get_average_rating($this);
	}

/************************************************
*
* COMMENTS
*
*************************************************/

  public function comments()
  {
    Model_Comment::check_module();

    $object = $this->get_object('Model_Comment');

		return Jelly::select('comment')
		          ->where('object', '=', $object)
		          ->where('object_id', '=', $this->id)
		          ->where('comment', 'IS NOT', NULL)
		          ->execute();
  }

  public function up_votes()
  {
    Model_Comment::check_module();

    $object = $this->get_object('Model_Comment');

    return Jelly::select('comment')
              ->where('object', '=', $object)
              ->where('object_id', '=', $this->id)
              ->where('up_vote', '=', 1)
              ->execute();
  }

  public function down_votes()
  {
    Model_Comment::check_module();

    $object = $this->get_object('Model_Comment');

    return Jelly::select('comment')
              ->where('object', '=', $object)
              ->where('object_id', '=', $this->id)
              ->where('up_vote', '=', 0)
              ->execute();
  }

  public function is_up_voted_by_user($user = NULL)
  {
    Model_Comment::check_module();

    if ( ! $user)
    {
      $user = Auth::instance()->get_user();

      if ( ! $user OR ! $user->loaded())
      {
        return FALSE;
      }
    }

    $object = $this->get_object('Model_Comment');

    return (bool) $user->get('comments')
                        ->where('up_vote', '=', 1)
                        ->where('object', '=', $object)
                        ->where('object_id', '=', $this->id)
                        ->count();
  }

  public function is_down_voted_by_user($user = NULL)
  {
    Model_Comment::check_module();

    if ( ! $user)
    {
      $user = Auth::instance()->get_user();

      if ( ! $user OR ! $user->loaded())
      {
        return FALSE;
      }
    }

    $object = $this->get_object('Model_Comment');

    return (bool) $user->get('comments')
                        ->where('down_vote', '=', 1)
                        ->where('object', '=', $object)
                        ->where('object_id', '=', $this->id)
                        ->count();
  }
}
