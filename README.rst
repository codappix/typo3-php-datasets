PHP DataSets for TYPO3
======================

Provides APIs to use data sets written as PHP arrays with TYPO3.

Why
===

We don't like the approach of TYPO3 Testing Framework regarding DataSets.

We have the following issues:

#. XML is only supported for imports, not for assertions

#. CSV is a bad format that already got hacked, e.g. ``#`` to indicate comments.
   We consider it bad as one needs special toolings in order to properly write CSV files, they are not human readable.

That's why we prefer PHP files instead. That way developers are free to use whatever
they want. Either plain PHP or even YAML or other formats. They are not forced to
anything but can stick to their known tooling.

We also have situations where we wanna have static database records on production
that are maintained by PHP data sets and update wizards.

So this package should in general handle PHP data sets for TYPO3.
It should ease the abstract usage by providing convenient integrations for general
use cases, like the testing framework.

Usage
=====

See our own tests for how to use, as they do nothing else.

Within testing framework
------------------------

#. Create data set

   A data set is a PHP file that returns an array of tables with their records.
   Format is:

   .. code-block:: php

      return [
          'table_name' => [
              // Records
              [
                  // column_name => value
                  'uid' => 1,
              ],
          ],
      ];

#. Add API

   .. code-block:: php

      use Codappix\Typo3PhpDatasets\TestingFramework;

#. Use API

   Import:

   .. code-block:: php

      $this->importPHPDataSet(__DIR__ . '/Fixtures/SimpleSet.php');

   Assert:

   .. code-block:: php

      $this->assertPHPDataSet(__DIR__ . '/Fixtures/SimpleSet.php');

TODO
====

#. Implement use case to check for necessary updates and allow updates.
   Use for static data during deployment within update wizards or other scripts.
