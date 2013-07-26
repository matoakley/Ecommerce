<?php defined('SYSPATH') or die ('No direct script access.');

class Ecommerce_Model_Role extends Model_Auth_Role
{
  public static function initialize(Jelly_Meta $meta)
	{
	  parent::initialize($meta);
	  
		$meta->name_key('name')
			->fields(array(
			'created' =>  new Field_Timestamp(array(
					'auto_now_create' => TRUE,
					'format' => 'Y-m-d H:i:s',
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
	
	public function update($data)
	{
	  if (isset($data['name']))
	  {
  	  $this->name = strtolower(str_replace(' ', '_', $data['name']));
	  }
  	$this->description = $data['description'];
  	$this->save();
    
    //remove all the rules
    AACL::revoke($this->name, '*');
      
  	//if the permissions are set then do this
  	if (isset($data['permissions']))
  	{  	
      //add them one by one
      foreach ($data['permissions'] as $array)
    	{
    	  foreach ($array as $resource => $action)
    	  {
      	  AACL::grant($this->name, $resource, $action); 
    	  }
    	}
  	}
	}
	
	//a helper to find the rules applied to this role.
	public function roles_rules()
	{
  	return Jelly::select('aacl_rule')->where('role', '=', $this->id)->execute();
	}
	
	public function match_rule($resource = NULL, $action = NULL, $condition = NULL)
	{ 
	  //get the rules for this role
  	$roles_rules = $this->roles_rules();
  	$result = FALSE;
    
    //find the one that matches this rule and return true.
  	foreach ($roles_rules as $rule)
  	{
    	if ($resource != NULL)
    	{
        if($resource == $rule->resource)
      	{
        	$result = TRUE;
      	}
      	else
      	{
        	$result = FALSE;
      	}
    	}
    	if ($action != NULL)
    	{
        if($action == $rule->action)
      	{
        	$result = TRUE;
      	}
      	else
      	{
        	$result = FALSE;
      	}
    	}
    	if ($condition != NULL)
    	{
        if($condition == $rule->condition)
      	{
        	$result = TRUE;
      	}
      	else
      	{
        	$result = FALSE;
      	}
    	}
    	//if its the one then stop the presses!
    	if ($result == TRUE)
    	{
      	return TRUE;
    	}
  	}
  	return $result;
	}

	public static function list_all()
	{
		return Jelly::select('role')->execute();
	}
}