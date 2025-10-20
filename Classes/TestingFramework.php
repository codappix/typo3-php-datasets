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

namespace Codappix\Typo3PhpDatasets;

use Exception;
use InvalidArgumentException;

/**
 * @api Only use within `TYPO3\TestingFramework\Core\Functional\FunctionalTestCase`
 */
trait TestingFramework
{
    /**
     * @api
     */
    protected function importPHPDataSet(string $filePath): void
    {
        $dataSet = $this->getDataSet($filePath);
        try {
            (new PhpDataSet())->import($dataSet);
        } catch (Exception $e) {
            self::fail('Error for PHP data-set "' . $filePath . '":' . PHP_EOL . $e->getMessage());
        }
    }

    /**
     * Highly inspired by TYPO3 testing framework.
     * @api
     */
    protected function assertPHPDataSet(string $filePath): void
    {
        if (is_array($GLOBALS['TCA'] ?? null) === false) {
            throw new \RuntimeException('TYPO3 GLOBALS["TCA"] is not defined.', 1760942400);
        }

        $failMessages = [];
        foreach ($this->getDataSet($filePath) as $tableName => $expectedRecords) {
            $records = $this->getAllRecords($tableName, (isset($GLOBALS['TCA'][$tableName])));

            foreach ($expectedRecords as $assertion) {
                $result = $this->assertInRecords($assertion, $records);
                if ($result === false) {
                    $failMessages[] = $this->getAssertionErrorMessageForNoneMatchingRecord($assertion, $records, $tableName);
                    continue;
                }

                // Unset already asserted record to only keep unexpected records.
                unset($records[$result]);

                // Increase assertion counter
                self::assertTrue(true);
            }

            foreach ($records as $record) {
                if (is_array($record) === false) {
                    throw new \RuntimeException('Something went horribly wrong while fetching records, record was not an array.', 1760943536);
                }

                $failMessages[] = $this->getAssertionErrorMessageForUnexpectedRecord($record, $tableName);
            }
        }

        $failMessages = array_filter($failMessages);

        if (!empty($failMessages)) {
            self::fail(implode(PHP_EOL, $failMessages));
        }
    }

    /**
     * @return array<string, array<string, string>[]>
     */
    private function getDataSet(string $filePath): array
    {
        $this->ensureFileExists($filePath);

        $dataSet = require $filePath;
        if (is_array($dataSet) === false) {
            throw new \RuntimeException('Given file did not return an array: ' . $filePath, 1760942255);
        }

        return $dataSet;
    }

    private function ensureFileExists(string $filePath): void
    {
        if (file_exists($filePath) === false) {
            throw new InvalidArgumentException('The requested PHP data-set file "' . $filePath . '" does not exist.', 1681207108);
        }
    }

    /**
     * @param array{uid: int|string|null} $assertion
     * @param array<string|int, mixed[]> $records
     */
    private function getAssertionErrorMessageForNoneMatchingRecord(array $assertion, array $records, string $tableName): string
    {
        // Handle error
        if (isset($assertion['uid']) && empty($records[$assertion['uid']])) {
            return 'Record "' . $tableName . ':' . $assertion['uid'] . '" not found in database';
        }

        if (isset($assertion['uid'])) {
            $record = $records[$assertion['uid']] ?? null;
            if (is_array($record) === false) {
                return 'Assertion in data-set failed for "' . $tableName . ':' . $assertion['uid'] . '": Uid missing in database' . PHP_EOL;
            }

            return 'Assertion in data-set failed for "' . $tableName . ':' . $assertion['uid'] . '":' . PHP_EOL . $this->renderRecords($assertion, $record);
        }

        return 'Assertion in data-set failed for "' . $tableName . '":' . PHP_EOL . $this->arrayToString($assertion);
    }

    /**
     * @param mixed[] $record
     */
    private function getAssertionErrorMessageForUnexpectedRecord(array $record, string $tableName): string
    {
        if (is_numeric($record['uid'] ?? null)) {
            return sprintf(
                'Not asserted record with uid "%s" found for table "%s".',
                $record['uid'],
                $tableName
            );
        }

        return sprintf(
            'Not asserted record found for table "%s": %s',
            $tableName,
            PHP_EOL . var_export($record, true)
        );
    }
}
