<?php defined('SYSPATH') or die ('No direct script access.');

/**
 * Another ACL
 * 
 * @see			http://github.com/banks/aacl
 * @package		AACL
 * @uses		Auth
 * @uses		Jelly
 * @author		Paul Banks
 * @copyright	(c) Paul Banks 2010
 * @license		MIT
 */
class AACL
{
	/**
	 * All rules that apply to the currently logged in user
	 * 
	 * @var	array	contains Model_AACL_Rule objects
	 */
	protected static $_rules;
	
	/**
	 * Grant access to $role for resource
	 * 
	 * @param	string|Model_Role	string role name or Model_Role object [optional]
	 * @param	string	resource identifier [optional]
	 * @param	string	action [optional]
	 * @param	string	condition [optional]
	 * @return 	void
	 */
	public static function grant($role = NULL, $resource = NULL, $action = NULL, $condition = NULL)
	{
      // if $role is null — we grant this to everyone
      if( is_null($role) )
      {
         // Create rule
         Jelly::factory('aacl_rule', array(
            'role' => null,
            'resource' => $resource,
            'action' => $action,
            'condition' => $condition,
         ))->create();
      }
      else
      {
         // Normalise $role
         if ( ! $role instanceof Model_Role)
         {
            $role = Jelly::select('role')->where('name', '=', $role)->limit(1)->execute();
         }
         // Check role exists
         if ( ! $role->loaded())
         {
            throw new AACL_Exception('Unknown role :role passed to AACL::grant()',
               array(':role' => $role->name));
         }

         // Create rule
         Jelly::factory('aacl_rule', array(
            'role' => $role,
            'resource' => $resource,
            'action' => $action,
            'condition' => $condition,
         ))->create();
      }
	}
	
	/**
	 * Revoke access to $role for resource
    * CHANGED: now accepts NULL role
	 * 
	 * @param	string|Model_Role role name or Model_Role object [optional]
	 * @param	string	resource identifier [optional]
	 * @param	string	action [optional]
	 * @param	string	condition [optional]
	 * @return 	void
	 */
	public static function revoke($role = NULL, $resource = NULL, $action = NULL, $condition = NULL)
	{
      if( is_null($role) )
      {
         $model = Jelly::factory('aacl_rule', array(
            'role' => NULL,
         ));
      }
      else
      {
         // Normalise $role
         if ( ! $role instanceof Model_Role)
         {
            $role = Jelly::select('role')->where('name', '=', $role)->load();
         }

         // Check role exists
         if ( ! $role->loaded())
         {
            // Just return without deleting anything
            return;
         }
         
         $models = Jelly::select('aacl_rule')
                    ->where('role', '=', $role->id);
         
          if ($resource == '*')
          {
            $rules = $models->execute();
          }
          else
          {
            if ( ! is_null($resource) && $resource != '*')
            {
              $models->where('resource', '=', $resource);
            }
            
            if ( ! is_null($resource) && ! is_null($action))
            {
              $models->where('action', '=', $action);
            }
            
            if ( ! is_null($resource) && ! is_null($condition))
            {
              $models->where('condition', '=', $condition);
            }
            
            if (! is_null($resource) )
            {
              $rules = $models->execute();
            }
          }
          
          //delete each rule
          foreach ($rules as $rule)
          {
            $rule->delete();
          }
  
      }
	}

   /**
	 * Checks user has permission to access resource
    * CHANGED: now works with unauthorized users
	 *
	 * @param	AACL_Resource	AACL_Resource object being requested
	 * @param	string			action identifier [optional]
	 * @throw	AACL_Exception	To identify permission or authentication failure
	 * @return	void
	 */
	public static function check(AACL_Resource $resource, $action = NULL)
	{
		$user = Auth::instance()->get_user();

      // User is logged in, check rules
		$rules = self::_get_rules($user);

		foreach ($rules as $rule)
		{
			if ($rule->allows_access_to($resource, $action))
			{
				// Access granted, just return
				return true;
			}
		}

      // No access rule matched
      if( $user )
   		throw new AACL_Exception_403;
		else
			throw new AACL_Exception_401;
	}
	
	/**
	 * Get all rules that apply to user
    * CHANGED
	 * 
	 * @param 	Model_User|Model_Role|bool 	User, role or everyone
	 * @param 	bool		[optional] Force reload from DB default FALSE
	 * @return 	array
	 */
	protected static function _get_rules( $user = false, $force_load = FALSE)
	{
      if ( ! isset(self::$_rules) || $force_load)
      {
         $select_query = Jelly::select('aacl_rule')->where('deleted', '=', NULL);
         // Get rules for user
         if( $user instanceof Model_User && !is_null($user->id))
         {
            self::$_rules = $select_query->where('role','IN', $user->roles->as_array(NULL, 'id'));
         }
         // Get rules for role
         else if( $user instanceof Model_Role && !is_null($user->id))
         {
            self::$_rules = $select_query->where('role','=', $user->id);
         }
         // User is guest
         else
         {
            self::$_rules = $select_query->where('role','=', null);                  
         }

         self::$_rules = $select_query
                           ->order_by('LENGTH("resource")', 'ASC')
                           ->execute();
      }

      return self::$_rules;
	}
	
	protected static $_resources;
	
	/**
	 * Returns a list of all valid resource objects based on the filesstem adn
    * FIXED
	 * 
	 * @param	string|bool	string resource_id [optional] if provided, the info for that specific resource ID is returned,
	 * 					if TRUE a flat array of just the ids is returned
	 * @return	array 
	 */
	public static function list_resources($resource_id = FALSE)
	{		
		if ( ! isset(self::$_resources))
		{
			// Find all classes in the application and modules
			$classes = self::_list_classes();
      
			// Loop throuch classes and see if they implement AACL_Resource
			foreach ($classes as $i => $class_name)
			{
				$class = new ReflectionClass($class_name);

				if ($class->implementsInterface('AACL_Resource'))
				{
					// Ignore interfaces and abstract classes
					if ($class->isInterface() || $class->isAbstract())
					{
						continue;
					}
	
					// Create an instance of the class
					$resource = $class->getMethod('acl_instance')->invoke($class_name, $class_name);
					
               // Get resource info
					self::$_resources[$resource->acl_id()] = array(
						'actions' 		=> $resource->acl_actions(),
						'conditions'	=> $resource->acl_conditions(),
					);
					
				}
				
				unset($class);
			}			
		}
		
		if ($resource_id === TRUE)
		{
			return array_keys(self::$_resources);
		}
		elseif ($resource_id)
		{
			return isset(self::$_resources[$resource_id]) ? self::$_resources[$resource_id] : NULL;
		}
		
		return self::$_resources;
	}

   /**
    * FIXED
    */
	protected static function _list_classes($files = NULL)
	{
		if (is_null($files))
		{
			// Remove core module paths form search
			$loaded_modules = Kohana::modules();
			
			$exclude_modules = array(
               'database',
               'orm',
               'jelly',
               'auth',
               'jelly-auth',
               'userguide',
               'image',
               'codebench',
               'unittest',
               'pagination',
               'migration'
            );

      /*   'firephp' => MODPATH.'firephp',
        'dbforge' => MODPATH.'dbforge',
        'database'   => MODPATH.'database',   // Database access
        'migration' => MODPATH.'migration',
        'formo'        => MODPATH.'formo',
        'formo-jelly'        => MODPATH.'formo-jelly',
        'jelly'        => MODPATH.'jelly',        // Object Relationship Mapping
        'jelly-auth'        => MODPATH.'jelly-auth',
        'auth'       => MODPATH.'auth',       // Basic authentication
        'aacl'       => MODPATH.'aacl',       // Roles, rules, resources
        // 'oauth'      => MODPATH.'oauth',      // OAuth authentication
        // 'pagination' => MODPATH.'pagination', // Paging of results
        'archive' => MODPATH.'archive',
        'unittest'   => MODPATH.'unittest',   // Unit testing
        'userguide'  => MODPATH.'userguide',  // User guide and API documentation
        //'debug-toolbar'        => MODPATH.'debug-toolbar',
        'notices'        => MODPATH.'notices',
        //'editor' => MODPATH.'editor',
        'article' => MODPATH.'article',*/
				
			$paths = Kohana::include_paths();
         
         // Remove known core module paths
			foreach ($loaded_modules as $module => $path)
			{
            if (in_array($module, $exclude_modules))
				{
               // Doesn't works properly — double slash on the end
               //	unset($paths[array_search($path.DIRECTORY_SEPARATOR, $paths)]);
               unset($paths[array_search($path, $paths)]);
				}
			}	

			// Remove system path
			unset($paths[array_search(SYSPATH, $paths)]);
			$files = Kohana::list_files('classes', $paths);
		}
		
		$classes = array();
		
		foreach ($files as $name => $path)
		{
			if (is_array($path))
			{
				$classes = array_merge($classes, self::_list_classes($path));
			}
			else
			{
				// Strip 'classes/' off start
				$name = substr($name, 8);
				
				// Strip '.php' off end
				$name = substr($name, 0, 0 - strlen(EXT));
				
				// Convert to class name
				$classes[] = str_replace(DIRECTORY_SEPARATOR, '_', $name);
			}
		}
		
		return $classes;
	}

   /**
    * Method, that allows to check any rule from database in any place of project.
    * Works with string presentations of resources, actions, roles and conditions
    * @todo: support conditions
    *
    * @param string $role
    * @param string $resource
    * @param string $action
    * @param string $condition
    * @return bool
    */
   public static function granted($role = NULL, $resource = NULL, $action = NULL, $condition = NULL)
   {
      $role = Jelly::select('role')->where('name','=',$role)->limit(1)->execute();
      $rules = self::_get_rules($role);
      
      foreach( $rules as $rule )
      {
         if( $rule->allows_access_to($resource,$action)
                 && $rule->role == $role )
         {
            return true;
         }
      }

      return false;
   }
	
	/**
	 * Force static access
	 * 
	 * @return	void 
	 */
	protected function __construct() {}
	
	/**
	 * Force static access
	 * 
	 * @return	void 
	 */
	protected function __clone() {}
	
} // End  AACL