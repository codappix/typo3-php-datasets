<?php

declare(strict_types=1);

/*
 * Copyright (C) 2023 Daniel Siepmann <coding@daniel-siepmann.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301, USA.
 */

namespace Codappix\Typo3PhpDatasets\Tests\Functional;

use Codappix\Typo3PhpDatasets\PhpDataSet;
use Codappix\Typo3PhpDatasets\TestingFramework;
use InvalidArgumentException;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversClass(PhpDataSet::class)]
#[CoversClass(TestingFramework::class)]
#[TestDox('The Testing Framework trait')]
class AssertTest extends AbstractFunctionalTestCase
{
    #[Test]
    public function canAssertAgainstSimpleSet(): void
    {
        $this->importPHPDataSet(__DIR__ . '/Fixtures/SimpleSet.php');
        $this->assertPHPDataSet(__DIR__ . '/Fixtures/SimpleSet.php');
    }

    #[Test]
    public function canAssertAgainstNullValue(): void
    {
        $this->importPHPDataSet(__DIR__ . '/Fixtures/WithNull.php');
        $this->assertPHPDataSet(__DIR__ . '/Fixtures/WithNull.php');
    }

    #[Test]
    public function canAssertAgainstDifferentSetOfColumns(): void
    {
        $this->importPHPDataSet(__DIR__ . '/Fixtures/WithDifferentColumns.php');
        $this->assertPHPDataSet(__DIR__ . '/Fixtures/WithDifferentColumns.php');
    }

    #[Test]
    public function canAssertMmRelation(): void
    {
        $this->importPHPDataSet(__DIR__ . '/Fixtures/MmRelation.php');
        $this->assertPHPDataSet(__DIR__ . '/Fixtures/MmRelation.php');
    }

    #[Test]
    public function failsForMissingAssertionWithUid(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Record "pages:1" not found in database');
        $this->assertPHPDataSet(__DIR__ . '/Fixtures/AssertSimpleMissingUidSet.php');
    }

    #[Test]
    public function failsForDifferingAssertionWithUid(): void
    {
        $this->importPHPDataSet(__DIR__ . '/Fixtures/SimpleSet.php');

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage(implode(PHP_EOL, [
            'Assertion in data-set failed for "pages:1":',
            'Fields|Assertion             |Record  ',
            'title |Rootpage without match|Rootpage',
        ]));
        $this->assertPHPDataSet(__DIR__ . '/Fixtures/AssertDifferingWithUid.php');
    }

    #[Test]
    public function failsForAssertionWithoutUid(): void
    {
        $this->importPHPDataSet(__DIR__ . '/Fixtures/SimpleSet.php');

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage(implode(PHP_EOL, [
            'Assertion in data-set failed for "pages":',
            'array(',
            '   \'pid\' => \'0\', ',
            '   \'title\' => \'Rootpage without match\'',
            ')',
            '',
        ]));
        $this->assertPHPDataSet(__DIR__ . '/Fixtures/AssertDifferingWithoutUid.php');
    }

    #[Test]
    public function failsForAssertionForMmRelation(): void
    {
        $this->importPHPDataSet(__DIR__ . '/Fixtures/MmRelation.php');

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage(implode(PHP_EOL, [
            'Assertion in data-set failed for "sys_category_record_mm":',
            'array(',
            '   \'uid_local\' => \'1\', ',
            '   \'uid_foreign\' => \'2\', ',
            '   \'tablenames\' => \'pages\', ',
            '   \'fieldname\' => \'categories\', ',
            '   \'sorting\' => \'0\', ',
            '   \'sorting_foreign\' => \'3\'',
            ')',
            '',
            'Not asserted record found for table "sys_category_record_mm": ',
            'array (',
            '  \'uid_local\' => 1,',
            '  \'uid_foreign\' => 2,',
            '  \'sorting\' => 0,',
            '  \'sorting_foreign\' => 2,',
            '  \'tablenames\' => \'pages\',',
            '  \'fieldname\' => \'categories\',',
            ')',
        ]));
        $this->assertPHPDataSet(__DIR__ . '/Fixtures/MmRelationBroken.php');
    }

    #[Test]
    public function failsForAdditionalNoneAssertedRecords(): void
    {
        $this->importPHPDataSet(__DIR__ . '/Fixtures/WithDifferentColumns.php');

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Not asserted record with uid "2" found for table "pages".');
        $this->assertPHPDataSet(__DIR__ . '/Fixtures/AssertAdditionalRecords.php');
    }

    #[Test]
    public function throwsExceptionIfFileDoesNotExist(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The requested PHP data-set file "' . __DIR__ . '/Fixtures/DoesNotExist.php' . '" does not exist.');
        $this->assertPHPDataSet(__DIR__ . '/Fixtures/DoesNotExist.php');
    }
}
