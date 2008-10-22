<?php

class NoHeadersAgaviWebResponse extends AgaviWebResponse
{
	protected function sendHttpResponseHeaders(AgaviOutputType $outputType = null)
	{
		// don't send headers, it won't work on the command line
		return;
	}
}

?>