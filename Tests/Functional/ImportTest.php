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
class ImportTest extends AbstractFunctionalTestCase
{
    /**
     * @test
     */
    public function canImportSimpleSet(): void
    {
        $this->importPHPDataSet(__DIR__ . '/Fixtures/SimpleSet.php');

        $records = $this->getAllRecords('pages', true);
        self::assertCount(1, $records);
        self::assertIsArray($records[1]);
        self::assertSame('Rootpage', $records[1]['title']);
        self::assertSame('Some text', $records[1]['description']);
    }

    /**
     * @test
     */
    public function canImportWithNullValue(): void
    {
        $this->importPHPDataSet(__DIR__ . '/Fixtures/WithNull.php');

        $records = $this->getAllRecords('pages', true);
        self::assertNull($records[1]['description']);
    }

    /**
     * @test
     */
    public function canImportRecordsWithDifferentSetOfColumns(): void
    {
        $this->importPHPDataSet(__DIR__ . '/Fixtures/WithDifferentColumns.php');

        $records = $this->getAllRecords('pages', true);
        self::assertCount(2, $records);
        self::assertIsArray($records[1]);
        self::assertSame('Rootpage', $records[1]['title']);
        self::assertIsArray($records[2]);
        self::assertSame('Some other text', $records[2]['description']);
    }

    /**
     * @test
     */
    public function failsIfSqlError(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage(
            'SQL Error for PHP data-set "' . __DIR__ . '/Fixtures/WithBrokenSql.php":'
            . PHP_EOL
            . 'There is no column with name \'none_existing_column\' on table \'pages\'.'
        );
        $this->importPHPDataSet(__DIR__ . '/Fixtures/WithBrokenSql.php');
    }

    /**
     * @test
     */
    public function throwsExceptionIfFileDoesNotExist(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The requested PHP data-set file "' . __DIR__ . '/Fixtures/DoesNotExist.php' . '" does not exist.');
        $this->importPHPDataSet(__DIR__ . '/Fixtures/DoesNotExist.php');
    }
}
