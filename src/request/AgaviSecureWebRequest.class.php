<?php
// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2011 the Agavi Project.                                |
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
 * AgaviSecureWebRequest provides additional support for HTTPS client requests
 * such as SSL certificate inspection.
 *
 * @package    agavi
 * @subpackage request
 *
 * @author     Markus Lervik <markus.lervik@necora.fi>
 * @author     David Zülke <dz@bitxtender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.10.0.0
 *
 * @deprecated To be removed in Agavi 1.1
 *
 * @version    $Id$
 */
class AgaviSecureWebRequest extends AgaviWebRequest {

	/**
	 * Check whether or not the current request is over a secure connection
	 * (HTTPS)
	 *
	 * @return     bool true if HTTPS is on, false otherwise
	 *
	 * @author     Markus Lervik <markus.lervik@necora.fi>
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.10.0
	 */
	public function isHTTPS()
	{

		return (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on');

	}

	/**
	 * Check if the client certificate is a valid one
	 *
	 * @return     bool true if the certificate is valid, false otherwise
	 *
	 * @author     Markus Lervik <markus.lervik@necora.fi>
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.10.0
	 */
	public function hasValidClientCert()
	{

		return (isset($_SERVER['SSL_CLIENT_VERIFY']) && strtoupper($_SERVER['SSL_CLIENT_VERIFY']) == 'SUCCESS');

	}

// -------------------------------------------------------------------------

	/**
	 * Get the client CN (Common Name) field from the client X.509 certificate
	 * if one is available
	 *
	 * @return     mixed the CN field if it's available, otherwise null
	 *
	 * @author     Markus Lervik <markus.lervik@necora.fi>
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.10.0
	 */
	public function getClientCertCN()
	{

		if(isset($_SERVER['SSL_CLIENT_S_DN_CN'])) {
			return $_SERVER['SSL_CLIENT_S_DN_CN'];
		}

	}

// -------------------------------------------------------------------------

	/**
	 * Get the client DN (Distinguished Name) field from the client X.509
	 * certificate if one is available
	 *
	 * @return     mixed the DN field if it's available, otherwise null
	 *
	 * @author     Markus Lervik <markus.lervik@necora.fi>
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.10.0
	 */
	public function getClientCertDN()
	{

		if(isset($_SERVER['SSL_CLIENT_S_DN'])) {
			return $_SERVER['SSL_CLIENT_S_DN'];
		}

	}

	/**
	 * Get the client GN (General Name) field from the client X.509 certificate
	 * if one is available
	 *
	 * @return     mixed the GN field if it's available, otherwise null
	 *
	 * @author     Markus Lervik <markus.lervik@necora.fi>
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.10.0
	 */
	public function getClientCertGN()
	{

		if(isset($_SERVER['SSL_CLIENT_S_DN_G'])) {
			return $_SERVER['SSL_CLIENT_S_DN_G'];
		}

	}

	/**
	 * Get the client SN (Subject Name) field from the client X.509 certificate
	 * if one is available
	 *
	 * @return     mixed the SN field if it's available, otherwise null
	 *
	 * @author     Markus Lervik <markus.lervik@necora.fi>
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.10.0
	 */
	public function getClientCertSN()
	{

		if(isset($_SERVER['SSL_CLIENT_S_DN_S'])) {
			return $_SERVER['SSL_CLIENT_S_DN_S'];
		}

	}

	/**
	 * Get the client O (Organisation) field from the client X.509 certificate
	 * if one is available
	 *
	 * @return     mixed the O field if it's available, otherwise null
	 *
	 * @author     Markus Lervik <markus.lervik@necora.fi>
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.10.0
	 */
	public function getClientCertO()
	{

		if(isset($_SERVER['SSL_CLIENT_S_DN_O'])) {
			return $_SERVER['SSL_CLIENT_S_DN_O'];
		}

	}

	/**
	 * Get the client OU (Organisation Unit) field from the client X.509
	 * certificate if one is available
	 *
	 * @return     mixed the OU field if it's available, otherwise null
	 *
	 * @author     Markus Lervik <markus.lervik@necora.fi>
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.10.0
	 */
	public function getClientCertOU()
	{

		if(isset($_SERVER['SSL_CLIENT_S_DN_OU'])) {
			return $_SERVER['SSL_CLIENT_S_DN_OU'];
		}

	}

	/**
	 * Get the date from which the client certificate is valid
	 * if one is available
	 *
	 * @return     mixed the date field if it's available, otherwise null
	 *
	 * @author     Markus Lervik <markus.lervik@necora.fi>
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.10.0
	 */
	public function getClientCertValidityStart()
	{

		if(isset($_SERVER['SSL_CLIENT_V_START'])) {
			return $_SERVER['SSL_CLIENT_V_START'];
		}

	}

	/**
	 * Get the date until which the client certificate is valid
	 * if one is available
	 *
	 * @return     mixed the date field if it's available, otherwise null
	 * @author     Markus Lervik <markus.lervik@necora.fi>
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.10.0
	 */
	public function getClientCertValidityEnd()
	{

		if(isset($_SERVER['SSL_CLIENT_V_END'])) {
			return $_SERVER['SSL_CLIENT_V_END'];
		}

	}

	/**
	 * Get the cipher type used for this connection
	 * if one is available
	 *
	 * @return     mixed the cipher type if it's available, otherwise null
	 * @author     Markus Lervik <markus.lervik@necora.fi>
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.10.0
	 */
	public function getSSLChipherType()
	{

		if(isset($_SERVER['SSL_CIPHER'])) {
			return $_SERVER['SSL_CIPHER'];
		}

	}

	/**
	 * Get the issuer DN (Distinguished Name) field from the issuer X.509
	 * certificate if one is available
	 *
	 * @return     mixed the DN field if it's available, otherwise null
	 * @author     Markus Lervik <markus.lervik@necora.fi>
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.10.0
	 */
	public function getIssuerCertDN()
	{

		if(isset($_SERVER['SSL_CLIENT_I_DN'])) {
			return $_SERVER['SSL_CLIENT_I_DN'];
		}

	}

	/**
	 * Get the issuer CN (Common Name) field from the issuer X.509 certificate
	 * if one is available
	 *
	 * @return     mixed the CN field if it's available, otherwise null
	 * @author     Markus Lervik <markus.lervik@necora.fi>
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.10.0
	 */
	public function getIssuerCertCN()
	{

		if(isset($_SERVER['SSL_CLIENT_I_CN'])) {
			return $_SERVER['SSL_CLIENT_I_CN'];
		}

	}

	/**
	 * Get the issuer C (Country) field from the issuer X.509 certificate
	 * if one is available
	 *
	 * @return     mixed the C field if it's available, otherwise null
	 * @author     Markus Lervik <markus.lervik@necora.fi>
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.10.0
	 */
	public function getIssuerCertC()
	{

		if(isset($_SERVER['SSL_CLIENT_I_C'])) {
			return $_SERVER['SSL_CLIENT_I_C'];
		}

	}

	/**
	 * Get the issuer O (Organisation) field from the issuer X.509 certificate
	 * if one is available
	 *
	 * @return     mixed the O field if it's available, otherwise null
	 * @author     Markus Lervik <markus.lervik@necora.fi>
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.10.0
	 */
	public function getIssuerCertO()
	{

		if(isset($_SERVER['SSL_CLIENT_I_O'])) {
			return $_SERVER['SSL_CLIENT_I_O'];
		}

	}

	/**
	 * Get the issuer OU (Organisation Unit) field from the issuer X.509
	 * certificate if one is available
	 *
	 * @return     mixed the OU field if it's available, otherwise null
	 * @author     Markus Lervik <markus.lervik@necora.fi>
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.10.0
	 */
	public function getIssuerCertOU()
	{

		if(isset($_SERVER['SSL_CLIENT_I_OU'])) {
			return $_SERVER['SSL_CLIENT_I_OU'];
		}

	}

	/**
	 * Get the issuer ST (State) field from the issuer X.509 certificate
	 * if one is available
	 *
	 * @return     mixed the ST field if it's available, otherwise null
	 * @author     Markus Lervik <markus.lervik@necora.fi>
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.10.0.0
	 */
	public function getIssuerCertST()
	{

		if(isset($_SERVER['SSL_CLIENT_I_ST'])) {
			return $_SERVER['SSL_CLIENT_I_ST'];
		}

	}

}

?>