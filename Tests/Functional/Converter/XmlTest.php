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

use Codappix\Typo3PhpDatasets\Converter\Xml;
use GlobIterator;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Xml::class)]
#[TestDox('The XML converter')]
class XmlTest extends TestCase
{
    protected function tearDown(): void
    {
        $filesToDelete = new GlobIterator(__DIR__ . '/Fixtures/Xml/*Incoming.php');
        foreach ($filesToDelete as $file) {
            unlink((string)$file);
        }

        parent::tearDown();
    }

    #[Test]
    public function canBeCreated(): void
    {
        $subject = new Xml();

        self::assertInstanceOf(
            Xml::class,
            $subject
        );
    }

    #[Test]
    public function throwsExceptionForNoneExistingFile(): void
    {
        $subject = new Xml();
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(1681283739);
        $this->expectExceptionMessage('Given file "NoneExistingFile.xml" does not exist.');
        $subject->convert('NoneExistingFile.xml');
    }

    #[Test]
    #[TestDox('Converts $_dataName XML to PHP')]
    #[DataProvider('possibleXmlFiles')]
    public function convertsXmlFileToPhpFile(
        string $incomingXmlFile,
        string $expectedResultFile
    ): void {
        $subject = new Xml();
        $result = $subject->convert($incomingXmlFile);

        self::assertFileEquals($expectedResultFile, $result);
    }

    public static function possibleXmlFiles(): array
    {
        return [
            'Simple' => [
                'incomingXmlFile' => __DIR__ . '/Fixtures/Xml/SimpleIncoming.xml',
                'expectedResultFile' => __DIR__ . '/Fixtures/Xml/SimpleAssert.php',
            ],
            'Multiple records in single table' => [
                'incomingXmlFile' => __DIR__ . '/Fixtures/Xml/MultipleRecordsInSingleTableIncoming.xml',
                'expectedResultFile' => __DIR__ . '/Fixtures/Xml/MultipleRecordsInSingleTableAssert.php',
            ],
            'Records in different tables' => [
                'incomingXmlFile' => __DIR__ . '/Fixtures/Xml/RecordsInDifferentTablesIncoming.xml',
                'expectedResultFile' => __DIR__ . '/Fixtures/Xml/RecordsInDifferentTablesAssert.php',
            ],
        ];
    }
}
