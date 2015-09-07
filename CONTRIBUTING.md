# Contributing

`Crunch\FastCGI` is an open-source project. If you want to contribute, don't hesitate
to do so.

## Report bugs

* Use the [Issue-Tracker](https://github.com/KingCrunch/FastCGI) to file a bug, or feature
  request.
  
## Submit a pull-request

* Fork the project
* Make your changes and add tests
* Push your changes
* Create a pull request
* Describe what the PR fixes and what it affects.

For a pull-request to get accepted, it should fulfill some basic expectations

* The code must fulfill the PSR-2 coding styles
* The code should contain a test showing, what it fixes. The general rule: The test should
  fail as long as the actual code change isn't in place, and should be successful, when
  with the code change
* If you change an existing test, describe why it was needed.
* The library follows the rules of [Semver](http://semver.org/). This means the merge
  and therefore the release of a specific PR may be delayed, when it breaks forward
  or backward compatibility. This is probably the case, when an existing test
  needs adjustments.
  
Please accept, that I'll review the code and decide for each PR separately. When I
decline a PR it doesn't mean, that it is bad, but maybe it just exceeds the scope
of the library, or there are better suitable solutions available. Every pull request
is welcome, even the declined one.
