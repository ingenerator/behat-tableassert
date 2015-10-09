Contributing
------------

Behat-TableAssert is an open source project and we welcome contributions from the community.

To help us keep the package high-quality, focused and stable we have a few simple rules:

- Always start your contribution in a *new branch* based on our current master branch
- Start by writing new unit tests to prove the bug / demonstrate how your new feature works.
- If it's a bugfix, commit your changes to the tests first and in a separate commit to your fix. This makes it easy
  for us to run the tests without your fix and check that they are catching the error you described.
- Try to use well-structured and detailed commit messages, and make sure your commits are kept logically separate. If
  you are fixing two bugs, that should usually be two separate pull requests with four separate commits.
- Follow the coding style you see in the project and try to make any new code consistent with the architecture and
  design patterns we've used elsewhere.
- Do not add any concrete dependencies on Mink, or any dependencies or code that will prevent this package working with
  both Behat v2 and v3.

Backwards compatibility
-----------------------

Unless you want your PR to start a new major version of the package (which will be very rare) you *must* take care to
make sure your changes are fully backwards compatible with the previous version - or at the very least that you
introduce some sort of backwards compatibility layer.

If you think your changes might need to break BC, please discuss with us in an issue before you do too much work.

You can read detailed guidance on what BC means in [Symfony2 BC guide](http://symfony.com/doc/current/contributing/code/bc.html).

Downloading and running tests
-----------------------------

The default distribution package provided by composer does not include the test cases. To develop on the project you
will need to install from source (eg by cloning the git repository, or deleting the package from your `vendor` directory
and running `composer install --prefer-source`).

Then, in the behat-tableassert directory, install development dependencies with `composer install`.

To run the test suite, just run `bin/phpunit`.
