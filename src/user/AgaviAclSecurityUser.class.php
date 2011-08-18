<?php

abstract class AgaviAclSecurityUser extends AgaviSecurityUser
{
	abstract public function isAllowed($resource, $operation = null);
}

?>