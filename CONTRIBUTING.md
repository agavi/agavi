# Contributing

Input and contributions are very welcome! Please open issues with
improvements, feature requests or bug reports. To contribute source code,
add documentation or fix spelling mistakes try this:

1. [Fork](https://help.github.com/forking/) this repository.
1. Clone the forked repository via ```git clone git@github.com:YOURNAME/agavi.git```.
1. [Install composer](https://getcomposer.org/doc/00-intro.md) and check that
   the tests run via ```cd test && php run-tests.php```
1. Make changes and additions in specific branches and add tests where appropriate.
1. Verify changes and make sure that the tests succeed.
1. Add, commit, squash and push the changes to the forked repository.
1. Send a [pull request](https://help.github.com/pull-requests/) to Agavi.
    1. Pull requests for bugfixes should go to the `1.0` branch.
    1. Pull requests for features should go to the `master` branch.

Please provide a well written issue describing the change and why it is
necessary. This is helpful for maintainers that want to integrate your changes
into Agavi. You may add a [CHANGELOG](CHANGELOG) entry suggestion to the pull
request ticket.

## Coding styles

Please see the [coding style hints](https://github.com/agavi/agavi/wiki/CodingStyle) before making changes
and try to adhere to them as otherwise incorporating the changes into Agavi is
not easily possible and may prevent the pull request from being accepted at all.

## Commit messages

Please use [well-written commit messages](https://github.com/torvalds/subsurface/blob/master/README#L92-L112).
The basic format of git commit messages is:

- A single short summary line of not more than 70 characters.
- A blank line.
- A detailed description of the change in present tense with not more than 80
  characters per line.

## License and credits

There is no Contributor License Agreement (CLA) to sign, but you have to accept
and agree to the [LICENSE](LICENSE) to get your patches included. Make sure the
contributions do not include code from libraries or projects with incompatible
licenses. For a list of contributors see the [github contributors graph](https://github.com/agavi/agavi/graphs/contributors).

## Separate changes

Different independent logical changes should be separated into separate commits
Separate patch submissions allow each change to be considered on its own and
make reviews a lot easier.

## Tests

Commits are continously integrated via [TravisCI](https://travis-ci.org/agavi/agavi)
and failing the PHPUnit tests will fail the builds. Usually the build status
will be shown on the pull request by Github. If something fails please try to
fix the changes as otherwise they can't be easily integrated by maintainers.

