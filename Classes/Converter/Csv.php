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
use SplFileObject;

class Csv implements Converter
{
    public function convert(string $fileName): string
    {
        if (file_exists($fileName) === false) {
            throw new InvalidArgumentException('Given file "' . $fileName . '" does not exist.', 1681283739);
        }

        $phpFileName = $this->getNewFileName($fileName);
        $incomingFile = new SplFileObject($fileName);

        try {
            file_put_contents($phpFileName, $this->buildContent($incomingFile));
        } catch (\Exception $e) {
            throw new \Exception('Could not generate new file.', 1681287881, $e);
        }

        return $phpFileName;
    }

    private function getNewFileName(string $fileName): string
    {
        $file = new SplFileObject($fileName);
        return str_replace(
            $file->getBasename(),
            $file->getBasename($file->getExtension()) . 'php',
            $file->getRealPath()
        );
    }

    private function buildContent(SplFileObject $incomingFile): string
    {
        $phpArray = [];
        $tableName = '';
        $columns = [];

        $incomingFile->setFlags(SplFileObject::READ_CSV);
        $incomingFile->setCsvControl(',', '"', '"');

        foreach ($incomingFile as $line) {
            if (is_array($line) && count($line) === 1 && is_null($line[0])) {
                // End of file
                break;
            }

            if (is_array($line) && count($line) === 1 && is_string($line[0])) {
                // Line is a new table, introducing also new columns o next row
                $tableName = $line[0];
                $columns = [];
                continue;
            }

            if ($columns === [] && is_array($line)) {
                $columns = array_slice($line, 1);
                continue;
            }

            if (is_array($line)) {
                $values = array_slice($line, 1);
                $phpArray[$tableName][] = array_combine($columns, $values);
            }
        }

        return implode(PHP_EOL, [
            '<?php',
            '',
            'return ' . var_export($phpArray, true) . ';',
            '',
        ]);
    }
}
