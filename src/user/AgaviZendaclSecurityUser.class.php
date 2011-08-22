<?php

class AgaviZendaclSecurityUser extends AgaviSecurityUser implements Zend_Acl_Role_Interface
{
	protected $zendAcl;
	
	public function initialize(AgaviContext $context, array $parameters = array())
	{
		parent::initialize($context, $parameters);
		
		$this->zendAcl = new Zend_Acl();
	}
	
	public function getZendAcl()
	{
		return $this->zendAcl;
	}
	
	public function isAllowed($resource, $operation = null)
	{
		return $this->getZendAcl()->isAllowed($this, $resource, $operation);
	}
	
	public function hasRole($role)
	{
		// could be our role directly, could be an ancestor, so check both
		return $this->getRoleId() == $role || $this->getZendAcl()->inheritsRole($this->getRoleId(), $role);
	}
	
	public function getRoleId()
	{
		if($this->isAuthenticated() && $this->hasAttribute('acl_role')) {
			return $this->getAttribute('acl_role');
		}
		
		return $this->getParameter('default_acl_role', 'user');
	}
	
	public function hasCredential($credential)
	{
		try {
			if($credential instanceof Zend_Acl_Resource_Interface) {
				// an object instance was given; perform an access check on this (without an operation)
				return $this->isAllowed($credential);
			}
			
			if(!is_scalar($credential)) {
				// can't do much with this...
				return false;
			}
			
			$credential = explode('.', $credential, 2);
			if(count($credential) == 2) {
				// a string like "product.create"; check the ACL
				return $this->isAllowed($credential[0], $credential[1]);
			} else {
				// something like "administrator"; let's see if that's our role or an ancestor of it
				return $this->hasRole($credential[0]);
			}
		} catch(Zend_Acl_Exception $e) {
			return false;
		}
	}
}

?>