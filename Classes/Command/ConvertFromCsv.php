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

namespace Codappix\Typo3PhpDatasets\Command;

use Codappix\Typo3PhpDatasets\Converter\Csv;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConvertFromCsv extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'convert-from-csv';

    /**
     * @var string
     */
    protected static $defaultDescription = 'Converts CSV data-sets to PHP data-sets.';

    protected function configure(): void
    {
        $this->addArgument('file', InputArgument::IS_ARRAY, 'The file(s) to convert.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $files = $input->getArgument('file');
        if (is_array($files) === false) {
            $output->writeln('File needs to be an array.');
            return Command::INVALID;
        }

        $converter = new Csv();
        foreach ($files as $index => $file) {
            if (is_string($file) === false) {
                $output->writeln(sprintf('File at index "%s" needs to be a string.', $index));
                return Command::INVALID;
            }

            try {
                $converter->convert(realpath($file) ?: $file);
            } catch (\Exception $e) {
                $output->writeln($e->getMessage());
                return Command::INVALID;
            }
        }

        return Command::SUCCESS;
    }
}
