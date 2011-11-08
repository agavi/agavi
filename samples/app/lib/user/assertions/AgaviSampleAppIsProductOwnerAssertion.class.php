<?php

class AgaviSampleAppIsProductOwnerAssertion implements Zend_Acl_Assert_Interface
{
	public function assert(Zend_Acl $acl, Zend_Acl_Role_Interface $role = null, Zend_Acl_Resource_Interface $resource = null, $privilege = null)
	{
		if(!($resource instanceof ProductModel)) {
			// in case the check is performed without a specific product instance:
			// let's assume that the user can edit a generic product
			return true;
		}
		
		if(!($role instanceof AgaviUser)) {
			// in case the check is performed without a specific user instance:
			// let's assume that any generic user cannot edit this product
			return false;
		}
		
		return $resource->getOwner() == $role->getAttribute('username');
	}
}

?>