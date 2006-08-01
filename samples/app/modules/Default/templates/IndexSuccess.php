Agavi has debugging mode on by default, which means it's reparsing every
single configuration file on each request. You may experience a slow loading
page while this is happening. Debugging mode should stay on if you plan on
developing for this installation. Otherwise you can turn it off.

<br/><br/>

<strong>NOTE:</strong> It's ok to manually delete all files and sub directories
in your Agavi cache directory.

<br/><br/>

<strong>NOTE:</strong> If you're developing with debugging mode off, a lot of
your errors may be traced back to invalid configuration cache files, which is
why it's suggested that you keep debugging on until your application is fully
functioning.
