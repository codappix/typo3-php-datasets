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

use RuntimeException;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class PhpDataSet
{
    public function import(array $dataSet): void
    {
        foreach ($dataSet as $tableName => $records) {
            $connection = $this->getConnectionPool()->getConnectionForTable($tableName);

            if (method_exists($connection, 'getSchemaManager')) {
                // <= 12
                $tableDetails = $connection->getSchemaManager()->listTableDetails($tableName);
            } elseif (method_exists($connection, 'getSchemaInformation')) {
                // >= 13
                $tableDetails = $connection->getSchemaInformation()->introspectTable($tableName);
            } else {
                throw new RuntimeException('Could not check the schema for table: ' . $tableName, 1707144020);
            }

            foreach ($records as $record) {
                $types = [];
                foreach (array_keys($record) as $columnName) {
                    $types[] = $tableDetails->getColumn((string)$columnName)->getType()->getBindingType();
                }

                $connection->insert($tableName, $record, $types);
            }
        }
    }

    private function getConnectionPool(): ConnectionPool
    {
        return GeneralUtility::makeInstance(ConnectionPool::class);
    }
}
