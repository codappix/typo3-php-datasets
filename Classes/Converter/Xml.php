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

namespace Codappix\Typo3PhpDatasets\Converter;

use InvalidArgumentException;
use SimpleXMLElement;
use SplFileObject;

class Xml implements Converter
{
    public function convert(string $xmlFileName): string
    {
        if (file_exists($xmlFileName) === false) {
            throw new InvalidArgumentException('Given file "' . $xmlFileName . '" does not exist.', 1681283739);
        }

        $phpFileName = $this->getNewFileName($xmlFileName);
        $xmlContent = file_get_contents($xmlFileName);
        if ($xmlContent === false) {
            throw new \Exception('Could not read content of file "' . $xmlFileName . '".', 1681287782);
        }

        try {
            file_put_contents($phpFileName, $this->buildContent($xmlContent));
        } catch (\Exception $e) {
            throw new \Exception('Could not generate new file.', 1681287881, $e);
        }

        return $phpFileName;
    }

    private function getNewFileName(string $xmlFileName): string
    {
        $file = new SplFileObject($xmlFileName);
        return str_replace(
            $file->getBasename(),
            $file->getBasename($file->getExtension()) . 'php',
            $file->getRealPath()
        );
    }

    /**
     * Adapted from TYPO3 testing framework XML import.
     */
    private function buildContent(string $xmlContent): string
    {
        $phpArray = [];
        $xml = simplexml_load_string($xmlContent);
        if ($xml === false) {
            throw new \Exception('Could not parse XML content.', 1681287859);
        }
        foreach ($xml->children() as $table) {
            if (!$table instanceof SimpleXMLElement) {
                continue;
            }

            $insertArray = [];
            foreach ($table->children() as $column) {
                if (!$column instanceof SimpleXMLElement) {
                    continue;
                }

                $columnName = $column->getName();
                $columnValue = (string)$table->$columnName;

                if ((string)($column['is-NULL'] ?? '') === 'yes') {
                    $columnValue = null;
                }

                $insertArray[$columnName] = $columnValue;
            }

            $phpArray[$table->getName()][] = $insertArray;
        }

        return implode(PHP_EOL, [
            '<?php',
            '',
            'return ' . var_export($phpArray, true) . ';',
            '',
        ]);
    }
}
