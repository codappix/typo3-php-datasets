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

use InvalidArgumentException;
use PHPUnit\Framework\AssertionFailedError;

/**
 * @covers \Codappix\Typo3PhpDatasets\PhpDataSet
 * @covers \Codappix\Typo3PhpDatasets\TestingFramework
 * @testdox The Testing Framework trait
 */
class AssertTest extends AbstractFunctionalTestCase
{
    /**
     * @test
     */
    public function canAssertAgainstSimpleSet(): void
    {
        $this->importPHPDataSet(__DIR__ . '/Fixtures/SimpleSet.php');
        $this->assertPHPDataSet(__DIR__ . '/Fixtures/SimpleSet.php');
    }

    /**
     * @test
     */
    public function canAssertAgainstNullValue(): void
    {
        $this->importPHPDataSet(__DIR__ . '/Fixtures/WithNull.php');
        $this->assertPHPDataSet(__DIR__ . '/Fixtures/WithNull.php');
    }

    /**
     * @test
     */
    public function canAssertAgainstDifferentSetOfColumns(): void
    {
        $this->importPHPDataSet(__DIR__ . '/Fixtures/WithDifferentColumns.php');
        $this->assertPHPDataSet(__DIR__ . '/Fixtures/WithDifferentColumns.php');
    }

    /**
     * @test
     */
    public function canAssertMmRelation(): void
    {
        $this->importPHPDataSet(__DIR__ . '/Fixtures/MmRelation.php');
        $this->assertPHPDataSet(__DIR__ . '/Fixtures/MmRelation.php');
    }

    /**
     * @test
     */
    public function failsForMissingAssertionWithUid(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Record "pages:1" not found in database');
        $this->assertPHPDataSet(__DIR__ . '/Fixtures/AssertSimpleMissingUidSet.php');
    }

    /**
     * @test
     */
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

    /**
     * @test
     */
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

    /**
     * @test
     */
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
            'Not asserted record found for "sys_category_record_mm:".',
        ]));
        $this->assertPHPDataSet(__DIR__ . '/Fixtures/MmRelationBroken.php');
    }

    /**
     * @test
     */
    public function failsForAdditionalNoneAssertedRecords(): void
    {
        $this->importPHPDataSet(__DIR__ . '/Fixtures/WithDifferentColumns.php');

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Not asserted record found for "pages:2".');
        $this->assertPHPDataSet(__DIR__ . '/Fixtures/AssertAdditionalRecords.php');
    }

    /**
     * @test
     */
    public function throwsExceptionIfFileDoesNotExist(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The requested PHP data-set file "' . __DIR__ . '/Fixtures/DoesNotExist.php' . '" does not exist.');
        $this->assertPHPDataSet(__DIR__ . '/Fixtures/DoesNotExist.php');
    }
}
