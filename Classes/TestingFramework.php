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
        $this->ensureFileExists($filePath);

        $dataSet = include $filePath;
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
        $this->ensureFileExists($filePath);

        $dataSet = include $filePath;
        $failMessages = [];

        foreach ($dataSet as $tableName => $expectedRecords) {
            $records = $this->getAllRecords($tableName, (isset($GLOBALS['TCA'][$tableName])));

            foreach ($expectedRecords as $assertion) {
                $result = $this->assertInRecords($assertion, $records);
                if ($result === false) {
                    // Handle error
                    if (isset($assertion['uid']) && empty($records[$assertion['uid']])) {
                        $failMessages[] = 'Record "' . $tableName . ':' . $assertion['uid'] . '" not found in database';
                        continue;
                    }
                    if (isset($assertion['uid'])) {
                        $recordIdentifier = $tableName . ':' . $assertion['uid'];
                        $additionalInformation = $this->renderRecords($assertion, $records[$assertion['uid']]);
                    } else {
                        $recordIdentifier = $tableName;
                        $additionalInformation = $this->arrayToString($assertion);
                    }

                    $failMessages[] = 'Assertion in data-set failed for "' . $recordIdentifier . '":' . PHP_EOL . $additionalInformation;
                    continue;
                }

                // Unset asserted record
                unset($records[$result]);
                // Increase assertion counter
                self::assertTrue($result !== false);
            }

            if (!empty($records)) {
                foreach ($records as $record) {
                    $recordIdentifier = $tableName . ':' . ($record['uid'] ?? '');
                    $failMessages[] = 'Not asserted record found for "' . $recordIdentifier . '".';
                }
            }
        }

        if (!empty($failMessages)) {
            self::fail(implode(PHP_EOL, $failMessages));
        }
    }

    private function ensureFileExists(string $filePath): void
    {
        if (file_exists($filePath) === false) {
            throw new InvalidArgumentException('The requested PHP data-set file "' . $filePath . '" does not exist.', 1681207108);
        }
    }
}
