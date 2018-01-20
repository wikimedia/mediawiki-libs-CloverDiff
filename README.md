clover-diff
===========

A PHP tool to diff two clover.xml files.

To install:
```
composer require-dev legoktm/clover-diff
```

Usage:
```
./vendor/bin/clover-diff old-clover.xml new-clover.xml
```

It will return with a failure status code of 1 if any file in the new report
has less coverage than before.

If you want to integrate this into your application, the `Differ` class takes
the filename of two XML files, and will generate a `Diff` for you.

clover-diff is (C) 2018 Kunal Mehta under the terms of the GPL v3, or any later
version. See COPYING for more details.