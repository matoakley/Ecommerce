<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Model_Application extends Jelly_Model
{
  public function display_meta_description()
  { 
    if ($this->_meta->columns('meta_description') AND $this->meta_description AND $this->meta_description != '')
    {
      return $this->meta_description;
    }
    elseif ($this->_meta->columns('description'))
    {
      return Text::limit_chars(strip_tags($this->description), 160, NULL, TRUE);
    }
    elseif ($this->_meta->columns('body'))
    {
      return Text::limit_chars(strip_tags($this->body), 160, NULL, TRUE);
    }
    elseif ($this->_meta->columns('text'))
    {
      return Text::limit_chars(strip_tags($this->text), 160, NULL, TRUE);
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
	
	public static function search($conditions = array(), $items = FALSE, $order = FALSE, $include_archived = FALSE)
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
	
	/**
	* Returns a collection of custom fields for the object.
	*/
	public function custom_fields()
	{
		if ( ! Kohana::config('ecommerce.modules.custom_fields'))
		{
			throw new Kohana_Exception('The custom fields module is not enabled.');
		}
	
		// Trim the 'Model_'part from the start of the class name and convert to lowercase for DB query
		$class = get_called_class();
		$object = strtolower(substr($class, 6));
		
		if ( ! in_array($object, Model_Custom_Field::$objects))
		{
			throw new Kohana_Exception('The type of object does not comply to Custom Field pattern.');
		}
		
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
		
		// Trim the 'Model_'part from the start of the class name and convert to lowercase for DB query
		$class = get_called_class();
		$object = strtolower(substr($class, 6));
		
		if ( ! in_array($object, Model_Custom_Field::$objects))
		{
			throw new Kohana_Exception('The type of object does not comply to Custom Field pattern.');
		}
		
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
		
		// Trim the 'Model_'part from the start of the class name and convert to lowercase for DB query
		$class = get_called_class();
		$object = strtolower(substr($class, 6));
		
		if ( ! in_array($object, Model_Custom_Field::$objects))
		{
			throw new Kohana_Exception('The type of object does not comply to Custom Field pattern.');
		}
	
		// Save custom fields
		foreach ($fields as $key => $value)
		{
			Model_Custom_Field_Value::update($key, $this->id, $value);
		}

	}
}