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

namespace Codappix\Typo3PhpDatasets\Tests\Functional\Converter;

use Codappix\Typo3PhpDatasets\Converter\Csv;
use GlobIterator;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Csv::class)]
#[TestDox('The CSV converter')]
class CsvTest extends TestCase
{
    protected function tearDown(): void
    {
        $filesToDelete = new GlobIterator(__DIR__ . '/Fixtures/Csv/*Incoming.php');
        foreach ($filesToDelete as $file) {
            unlink((string)$file);
        }

        parent::tearDown();
    }

    #[Test]
    public function canBeCreated(): void
    {
        $subject = new Csv();

        self::assertInstanceOf(
            Csv::class,
            $subject
        );
    }

    #[Test]
    public function throwsExceptionForNoneExistingFile(): void
    {
        $subject = new Csv();
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(1681283739);
        $this->expectExceptionMessage('Given file "NoneExistingFile.csv" does not exist.');
        $subject->convert('NoneExistingFile.csv');
    }

    #[Test]
    #[TestDox('Converts $_dataName CSV to PHP')]
    #[DataProvider('possibleCsvFiles')]
    public function convertsCsvFileToPhpFile(
        string $incomingCsvFile,
        string $expectedResultFile
    ): void {
        $subject = new Csv();
        $result = $subject->convert($incomingCsvFile);

        self::assertFileEquals($expectedResultFile, $result);
    }

    public static function possibleCsvFiles(): array
    {
        return [
            'Simple' => [
                'incomingCsvFile' => __DIR__ . '/Fixtures/Csv/SimpleIncoming.csv',
                'expectedResultFile' => __DIR__ . '/Fixtures/Csv/SimpleAssert.php',
            ],
            'Multiple records in single table' => [
                'incomingCsvFile' => __DIR__ . '/Fixtures/Csv/MultipleRecordsInSingleTableIncoming.csv',
                'expectedResultFile' => __DIR__ . '/Fixtures/Csv/MultipleRecordsInSingleTableAssert.php',
            ],
            'Records in different tables' => [
                'incomingCsvFile' => __DIR__ . '/Fixtures/Csv/RecordsInDifferentTablesIncoming.csv',
                'expectedResultFile' => __DIR__ . '/Fixtures/Csv/RecordsInDifferentTablesAssert.php',
            ],
            'Extra columns' => [
                'incomingCsvFile' => __DIR__ . '/Fixtures/Csv/ExtraColumnsIncoming.csv',
                'expectedResultFile' => __DIR__ . '/Fixtures/Csv/ExtraColumnsAssert.php',
            ],
        ];
    }
}
