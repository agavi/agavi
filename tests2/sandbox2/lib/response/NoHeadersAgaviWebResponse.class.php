<?php

class NoHeadersAgaviWebResponse extends AgaviWebResponse
{
	public function sendHttpResponseHeaders()
	{
		// don't send headers, it won't work on the command line
		return;
	}
}

?>