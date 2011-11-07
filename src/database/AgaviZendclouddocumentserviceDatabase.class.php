<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2011 the Agavi Project.                                |
// | Based on the Mojavi3 MVC Framework, Copyright (c) 2003-2005 Sean Kerr.    |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE   |
// | file that was distributed with this source code. You can also view the    |
// | LICENSE file online at http://www.agavi.org/LICENSE.txt                   |
// |   vi: set noexpandtab:                                                    |
// |   Local Variables:                                                        |
// |   indent-tabs-mode: t                                                     |
// |   End:                                                                    |
// +---------------------------------------------------------------------------+

/**
 * AgaviZendclouddocumentserviceDatabase provides connectivity to databases
 * using Zend Framework's Zend_Cloud_DocumentService functionality.
 *
 * Parameters:
 *   'factory_class'    Name of the factory class to use; the default value is
 *                      "Zend_Cloud_DocumentService_Factory".
 *   'factory_options'  Array of options for Zend_Cloud_DocumentService_Factory,
 *                      must at least contain one sub-element with the key
 *                      Zend_Cloud_DocumentService_Factory::DOCUMENT_ADAPTER_KEY
 *                      (or just "document_adapter"). This class must either be
 *                      already loaded with include() or be autoloadable.
 *   'collection'       Optional name of a default collection; if this is set,
 *                      then convenience methods defined directly in this class
 *                      can be used to perform operations without having to
 *                      specify the name of the collection/domain every time.
 *
 * @package    agavi
 * @subpackage database
 *
 * @author     David Zülke <david.zuelke@bitextender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      1.0.5
 *
 * @version    $Id$
 */
class AgaviZendclouddocumentserviceDatabase extends AgaviDatabase
{
	/**
	 * Initialize this Database.
	 *
	 * @param      AgaviDatabaseManager The database manager of this instance.
	 * @param      array                An assoc array of initialization params.
	 *
	 * @throws     <b>AgaviInitializationException</b> If an error occurs while
	 *                                                 initializing this Database.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.5
	 */
	public function initialize(AgaviDatabaseManager $databaseManager, array $parameters = array())
	{
		parent::initialize($databaseManager, $parameters);
		
		if(!$this->hasParameter('factory_class')) {
			$this->setParameter('factory_class', 'Zend_Cloud_DocumentService_Factory');
		}
		
		if(!class_exists($this->getParameter('factory_class'))) {
			if(!class_exists('Zend_Loader')) {
				require('Zend/Loader.php');
			}
			Zend_Loader::loadClass($this->getParameter('factory_class'));
		}
		
		$factoryOptions = array();
		foreach((array)$this->getParameter('factory_options', array()) as $name => $value) {
			// resolve constants like "Zend_Cloud_DocumentService_Factory::DOCUMENT_ADAPTER_KEY"
			if(strpos($name, '::') && defined($name)) {
				$name = constant($name);
			}
			
			$factoryOptions[$name] = $value;
		}
		
		$this->setParameter('factory_options', $factoryOptions);
	}
	
	/**
	 * Connect to the database.
	 * If a default "collection" is configured, this collection will be created.
	 *
	 * @throws     <b>AgaviDatabaseException</b> If a connection could not be 
	 *                                           created.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.5
	 */
	public function connect()
	{
		try {
			$this->connection = call_user_func(array($this->getParameter('factory_class'), 'getAdapter'), $this->getParameter('factory_options'));
		} catch(Zend_Exception $e) {
			throw new AgaviDatabaseException(sprintf("Caught exception of type %s while creating adapter instance; details:\n\n%s", get_class($e), $e->getMessage()));
		}
	}
	
	/**
	 * Retrieve the underlying implementation used by the adapter.
	 *
	 * @return     mixed The service implementation.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.5
	 */
	public function getResource()
	{
		return $this->getConnection()->getClient();
	}
	
	/**
	 * Shut down this database connection.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.5
	 */
	public function shutdown()
	{
		$this->connection = $this->resource = null;
	}
	
	/**
	 * List all documents in a collection.
	 * This is a convenience function using the configured "collection" parameter.
	 *
	 * @see        Zend_Cloud_DocumentService_Adapter::listDocuments()
	 *
	 * @param      array An array of options.
	 *
	 * @return     Zend_Cloud_DocumentService_DocumentSet A list of documents.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.5
	 */
	public function listDocuments(array $options = null)
	{
		if(!$this->hasParameter('collection')) {
			throw new AgaviDatabaseException('Convenience functions require configuration parameter "collection".');
		}
		
		return $this->getConnection()->listDocuments($this->getParameter('collection'), $options);
	}
	
	/**
	 * Insert document.
	 * This is a convenience function using the configured "collection" parameter.
	 *
	 * @see        Zend_Cloud_DocumentService_Adapter::insertDocument()
	 *
	 * @param      array|Zend_Cloud_DocumentService_Document Document to insert.
	 * @param      array                                     An array of options.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.5
	 */
	public function insertDocument($document, $options = null)
	{
		if(!$this->hasParameter('collection')) {
			throw new AgaviDatabaseException('Convenience functions require configuration parameter "collection".');
		}
		
		return $this->getConnection()->insertDocument($this->getParameter('collection'), $document, $options);
	}
	
	/**
	 * Replace document.
	 * The new document replaces the existing document with the same ID.
	 * This is a convenience function using the configured "collection" parameter.
	 *
	 * @see        Zend_Cloud_DocumentService_Adapter::replaceDocument()
	 *
	 * @param      array|Zend_Cloud_DocumentService_Document The document.
	 * @param      array                                     An array of options.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.5
	 */
	public function replaceDocument($document, $options = null)
	{
		if(!$this->hasParameter('collection')) {
			throw new AgaviDatabaseException('Convenience functions require configuration parameter "collection".');
		}
		
		return $this->getConnection()->replaceDocument($this->getParameter('collection'), $document, $options);
	}
	
	/**
	 * Update document.
	 * The fields of the existing documents will be updated.
	 * Fields not specified in the set will be left as-is.
	 * This is a convenience function using the configured "collection" parameter.
	 *
	 * @see        Zend_Cloud_DocumentService_Adapter::updateDocument()
	 *
	 * @param      mixed|Zend_Cloud_DocumentService_Document The Document ID or an
	 *                                                       instance with updates
	 * @param      array|Zend_Cloud_DocumentService_Document The fields to update.
	 * @param      array                                     An array of options.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.5
	 */
	public function updateDocument($documentID, $fieldset = null, $options = null)
	{
		if(!$this->hasParameter('collection')) {
			throw new AgaviDatabaseException('Convenience functions require configuration parameter "collection".');
		}
		
		return $this->getConnection()->updateDocument($this->getParameter('collection'), $documentID, $fieldset, $options);
	}
	
	/**
	 * Delete document.
	 * This is a convenience function using the configured "collection" parameter.
	 *
	 * @see        Zend_Cloud_DocumentService_Adapter::deleteDocument()
	 *
	 * @param      mixed ID of the document to delete.
	 * @param      array An array of options.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.5
	 */
	public function deleteDocument($documentID, $options = null)
	{
		if(!$this->hasParameter('collection')) {
			throw new AgaviDatabaseException('Convenience functions require configuration parameter "collection".');
		}
		
		return $this->getConnection()->deleteDocument($this->getParameter('collection'), $documentID, $options);
	}
	
	/**
	 * Fetch single document by ID.
	 * This is a convenience function using the configured "collection" parameter.
	 *
	 * @see        Zend_Cloud_DocumentService_Adapter::fetchDocument()
	 *
	 * @param      mixed ID of the document to fetch.
	 * @param      array An array of options.
	 *
	 * @return     Zend_Cloud_DocumentService_Document The document or bool false.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.5
	 */
	public function fetchDocument($documentID, $options = null)
	{
		if(!$this->hasParameter('collection')) {
			throw new AgaviDatabaseException('Convenience functions require configuration parameter "collection".');
		}
		
		return $this->getConnection()->fetchDocument($this->getParameter('collection'), $documentID, $options);
	}
	
	/**
	 * Query for documents stored in the document service. If a string is given
	 * as the query, the query string will be passed directly to the service.
	 * This is a convenience function using the configured "collection" parameter.
	 *
	 * @see        Zend_Cloud_DocumentService_Adapter::query()
	 *
	 * @param      Zend_Cloud_DocumentService_Query The query to perform.
	 * @param      array                            An array of options.
	 *
	 * @return     Zend_Cloud_DocumentService_DocumentSet The query result.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.5
	 */
	public function query($query, $options = null)
	{
		if(!$this->hasParameter('collection')) {
			throw new AgaviDatabaseException('Convenience functions require configuration parameter "collection".');
		}
		
		return $this->getConnection()->query($this->getParameter('collection'), $query, $options);
	}
	
	/**
	 * Create query statement.
	 * This is a convenience function; unlike the other convenience functions, it
	 * does not use the configured "collection" parameter, but it included here to
	 * make the service interface complete.
	 *
	 * @see        Zend_Cloud_DocumentService_Adapter::select()
	 *
	 * @param      string The fields to select.
	 *
	 * @return     Zend_Cloud_DocumentService_Query An initialized query instance.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.5
	 */
	public function select($fields = null)
	{
		return $this->getConnection()->select($fields);
	}
}

?>