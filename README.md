# Agavi

- License: LGPL
- Latest Version: [![Latest Stable Version](https://poser.pugx.org/agavi/agavi/version.png)](https://packagist.org/packages/agavi/agavi)
- Build: [![Build Status](https://secure.travis-ci.org/agavi/agavi.png)](http://travis-ci.org/agavi/agavi)
- Homepage: [http://www.agavi.org/](http://www.agavi.org/)
- Releases: see [downloads page] or [github releases]

## Purpose

Agavi is a *powerful, scalable PHP5 application framework* that follows the MVC
paradigm. It enables developers to write clean, maintainable and extensible
code. Agavi puts choice and freedom over limiting conventions, and focuses on
sustained quality rather than short-sighted decisions.

Agavi is designed for serious development. It is not a complete website
construction kit but rather a skeleton over which you build your application.
The architecture of Agavi allows developers to retain very fine control over
their code.

Agavi strives to leave most implementational choices to the developers. Agavi's
components are inherently extensible, and the framework itself is designed
around a XML-based configuration system that provides a very flexible
environment.

The framework works for almost all kinds of applications but excels most in
large codebases, long-term projects, extreme cases of integration and other
special situations. Creating an application that is accessible not only as
a standard web application but also via a commandline interface or standards
like HTTP, SOAP or even XML-RPC is a perfectly valid use case.

## Requirements and installation

- PHP v5.2.0+ (recommended is 5.2.8 or higher)
- required: `libxml`, `dom`, `SPL`, `Reflection` and `PCRE`
- optional: `xsl`, `tokenizer`, `session`, `xmlrpc`, `soap`, `PDO`, `iconv`, `gettext`, `phing`

See the [installation guide](http://www.agavi.org/documentation/tutorial/agavi-installation.html)
in the tutorial for some details. Installation via [Composer](http://getcomposer.org/)/[Packagist](http://packagist.com/)
and git clone is not mentioned there, but available by typing ```composer
require agavi/agavi [optional version]```. Adding Agavi manually as a vendor
library requirement to the `composer.json` file of your project works as well:

```json
{
    "require": {
        "agavi/agavi": "~1.0.0"
    }
}
```

Alternatively, you can download a release archive from the [github releases]
page and extract it or see the [downloads page] on the homepage.

## Documentation

An introduction into Agavi can be found in form of a [tutorial](http://www.agavi.org/documentation/tutorial)
for a blog application. There are [API docs](http://www.agavi.org/apidocs/)
and an [official FAQ](https://github.com/agavi/agavi/wiki/FAQ) as well as slightly outdated [WTF](https://github.com/agavi/agavi/wiki/WTF)
and [blog](http://blog.agavi.org/). A [useful FAQ for developers](http://mivesto.de/agavi/agavi-faq.html)
may help with common questions while browsing the [source files](src) with their docs is always an option.

## Support

To get support have a look at the [support page](http://www.agavi.org/support) on the homepage.
There are mailing lists to join and a helpful [freenode IRC channel](https://github.com/agavi/agavi/wiki/IRC)
named `#agavi` to get you up to speed (```irc://irc.freenode.org/agavi```).
The [IRC channel logs](http://www.agavi.org/irclogs/) are available for the
curious that are interested in past conversations.

## Contribution

Discussing issues on the mailing lists or in github issues as well as talking
about problems and features in the IRC channel is always of good help to
everyone. If you want to do more please contribute by [forking](https://help.github.com/forking/)
and sending a [pull request](https://help.github.com/pull-requests/). More
information can be found in the [CONTRIBUTING.md](CONTRIBUTING.md) file.

## Changelog

See the latest changes in the [repository CHANGELOG](CHANGELOG) or on the [homepage](http://www.agavi.org/download/1.0.7/changelog).
The [1.0 release notes](RELEASE_NOTES-1.0) or [upcoming release notes](RELEASE_NOTES)
may be helpful as well.

## License

Agavi is licensed under the <a rel="license" href="https://en.wikipedia.org/wiki/GNU_Lesser_General_Public_License">LGPL 2.1</a>.
See the [Open Source Initiative](http://opensource.org/licenses/LGPL-2.1)
and [this FAQ entry](https://github.com/agavi/agavi/wiki/FAQ#wiki-can-i-use-agavi-in-a-proprietary-commercial-application)
for details. All relevant licenses and details can be found in the [LICENSE](LICENSE) file.

- Total Composer downloads: [![Composer Downloads](https://poser.pugx.org/agavi/agavi/d/total.png)](https://packagist.org/packages/agavi/agavi)

[downloads page]: http://www.agavi.org/download
[github releases]: https://github.com/agavi/agavi/releases