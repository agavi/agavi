<?php

/*

This is an example cli-config.php for Doctrine 2 that can be used in applications.
Adjust paths and other details in this file as necessary, and put it into a folder somewhere in your project (e.g. dev/doctrine)
From this folder, you may then run the "doctrine" command line tool.
As always with files that require modifications for each developer (like index.php), it is advisable to only have a template file and ask each developer to copy this file to "cli-config.php" and insert their respective environment name so the correct database configuration is used.

factories.xml needs a configuration section similar to this:
<ae:configuration context="doctrine-cli">
	<request class="AgaviConsoleRequest">
		<!-- the important bit: don't clear argc and argv -->
		<ae:parameter name="unset_input">false</ae:parameter>
	</request>
	<response class="AgaviConsoleResponse" />
	<routing class="AgaviConsoleRouting" />
	<storage class="AgaviNullStorage" />
	<user class="AgaviSecurityUser" />
</ae:configuration>

output_types.xml also needs an output type declared for this context, of course ("text" with no further settings will do).

*/

require('path/to/src/agavi.php');

require('path/to/app/config.php');

Agavi::bootstrap('development-yourname');

$em = AgaviContext::getInstance('doctrine-cli')->getDatabaseConnection(); // fetches default connection; pass a connection name if necessary

$helperSet = new \Symfony\Component\Console\Helper\HelperSet(array(
	'db' => new \Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper($em->getConnection()),
	'em' => new \Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper($em)
));

?>