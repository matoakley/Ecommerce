<?php defined('SYSPATH') or die ('No direct script access.');

/**
 * Access rule model
 * 
 * @see			http://github.com/banks/aacl
 * @package		AACL
 * @uses		Auth
 * @uses		Jelly
 * @author		Paul Banks
 * @copyright	(c) Paul Banks 2010
 * @license		MIT
 */
class Model_AACL_Rule extends Jelly_AACL
{
	public static function initialize(Jelly_Meta $meta)
	{
		$meta->table('rules')
           ->fields(array(
              'id' => new Field_Primary(array(
                 'editable' => false,
              )),
              'role' => new Field_BelongsTo(array(
                 'label' => 'Role',
                 'null'=>true,
              )),
              'resource' => new Field_String(array(
                 'label' => 'Controlled resource',
                 'null'=>true,
                 'rules' => array(
                     'max_length' 	=> array(45),
                 ),
              )),
              'action' => new Field_String(array(
                 'label' => 'Controlled action',
                 'null'=>true,
                 'rules' => array(
                     'max_length' 	=> array(25),
                 ),
              )),
              'condition' => new Field_String(array(
                 'label' => 'Access condition',
                 'null'=>true,
                 'rules' => array(
                     'max_length' 	=> array(25),
                 ),
              )),
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
	
	/**
	 * Check if rule matches current request
    * CHANGED: allows_access_to accepts now resource_id
	 * 
	 * @param string|AACL_Resource	AACL_Resource object or it's id that user requested access to
	 * @param string        action requested [optional]
	 * @return 
	 */
	public function allows_access_to($resource, $action = NULL)
	{
      if (is_null($this->resource))
      {
         // No point checking anything else!
         return TRUE;
      }

      if( $resource instanceof AACL_Resource)
      {
         if (is_null($action))
         {
            // Check to see if Resource whats to define it's own action
            $action = $resource->acl_actions(TRUE);
         }

         // Get string id
         $resource_id = $resource->acl_id();
      }
      else
      {
         // $resource should be valid resource id

         // TODO: here could be some buggy stuff
         /*if (is_null($action))
         {
            // Check to see if Resource whats to define it's own action
            $action = $resource->acl_actions(TRUE);
         }*/

         // Get string id
         $resource_id = $resource;
      }
      
      // Make sure action matches
      if ( ! is_null($action) AND ! is_null($this->action) AND $action !== $this->action)
      {
         // This rule has a specific action and it doesn't match the specific one passed
         return FALSE;
      }

      $matches = FALSE;

      // Make sure rule resource is the same as requested resource, or is an ancestor
      while( ! $matches)
      {
         // Attempt match
         if ($this->resource === $resource_id)
         {
            // Stop loop
            $matches = TRUE;
         }
         else
         {
            // Find last occurence of '.' separator
            $last_dot_pos = strrpos($resource_id, '.');

            if ($last_dot_pos !== FALSE)
            {
               // This rule might match more generally, try the next level of specificity
               $resource_id = substr($resource_id, 0, $last_dot_pos);
            }
            else
            {
               // We can't make this any more general as there are no more dots
               // And we haven't managed to match the resource requested
               return FALSE;
            }
         }
      }

      // Now we know this rule matches the resource, check any match condition
      if ( ! is_null($this->condition) AND ! $resource->acl_conditions(Auth::instance()->get_user(), $this->condition))
      {
         // Condition wasn't met (or doesn't exist)
         return FALSE;
      }

      // All looks rosy!
      return TRUE;
	}
	
	/**
	 * Override create to remove less specific rules when creating a rule
	 * 
	 * @return $this
	 */
	public function create()
	{
      $meta = $this->meta();
      $fields = $meta->fields();
		// Delete all more specifc rules for this role
      if( isset($this->_changed['role']))
         $delete = Jelly::delete($this)
            ->where( $fields['role']->column, '=', $this->_changed['role'] );
      else
         $delete = Jelly::delete($this)
            ->where( $fields['role']->column, '=', NULL );
		
		// If resource is NULL we don't need any more rules - we just delete every rule for this role
		if ( ! is_null($this->resource) )
		{
			// Need to restrict to roles with equal or more specific resource id
			$delete->where_open()
				->where('resource', '=', $this->resource)
				->or_where('resource', 'LIKE', $this->resource.'.%')
				->where_close();
		}
		
		if ( ! is_null($this->action))
		{
			// If this rule has an action, only remove other rules with the same action
			$delete->where('action', '=', $this->action);
		}
		
		if ( ! is_null($this->condition))
		{
			// If this rule has a condition, only remove other rules with the same condition
			$delete->where('condition', '=', $this->condition);
		}		
		
		// Do the delete
		$delete->execute();
		
		// Create new rule
		parent::save();
	}
	
	/**
	 * Override Default model actions
	 * 
	 * @param	bool	$return_current [optional]
	 * @return	mixed
	 */
	public function acl_actions($return_current = FALSE)
	{
		if ($return_current)
		{
			// We don't know anything about what the user intends to do with us!
			return NULL;
		}
		
		// Return default model actions
		return array('grant', 'revoke');
	}
	
} // End  Model_AACL_Rule