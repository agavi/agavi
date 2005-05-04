<?php
/**
 * An Agavi Database driver for Propel, derived from the native Creole driver. 
 * 
 * @since 1.0 
 * @author Dusty Matthews (dustym@agavi.org)
 */
class PropelDatabase extends Database
{
	/**
	 * Connect to the database. 
	 * 
	 * @since 1.0
	 * @access public
	 * @return void
	 * @throws <b>DatabaseException</b> If a connection could not be created.
	 * @author Dusty Matthews (dustym@agavi.org)
	 */
	public function connect ()
	{
		try {
			// determine how to get our settings
			$method = $this->getParameter('method', 'normal');
			switch ($method) {
				case 'normal':
					$runtime = ConfigHandler::replaceConstants($this->getParameter('runtime',null));
					break;
				case 'server':
					$runtime =& $_SERVER['runtime'];
					break;
				case 'env':
					$runtime =& $_ENV['runtime'];
					break;
				default:
					$error = 'Invalid PropelDatabase parameter retrieval method "%s"';
					$error = sprintf($error, $method);
					throw new DatabaseException($error);
			}
			// get propel class path
			$classPath = ConfigHandler::replaceConstants($this->getParameter('classpath',null));
			// set the include path to our Propel generated classes
			if (!is_null($classPath)) {
				set_include_path(get_include_path().PATH_SEPARATOR.$classPath);
			}

			require_once('creole/SQLException.php');
			require_once('propel/Propel.php');
			require_once('propel/util/Criteria.php');
			require_once('propel/map/DatabaseMap.php');

			// Everything looks good. Off to the races.
			Propel::init($runtime);
			$this->connection = Propel::getConnection();
			$this->resource =& $this->connection->getResource();
		} catch (SQLException $e) {
			// the connection's foobar'd
			throw new DatabaseException($e->toString());
		}
	}
	
	/**
	 * Execute the shutdown procedure. 
	 * 
	 * @since 1.0
	 * @access public
	 * @return void
	 * @throws <b>DatabaseException</b> If an error occurs while shutting down this database.
	 * @author Dusty Matthews (dustym@agavi.org)
	 */
	public function shutdown ()
	{
		if ($this->connection !== null) {
			@$this->connection->close();
		}
	}
}
?>