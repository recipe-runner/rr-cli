<?php

/*
 * This file is part of the "Recipe Runner" project.
 *
 * (c) Víctor Puertas <http://github.com/yosymfony>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RecipeRunner\Cli\Core\Port;

/**
 * Interface for accessing filesystem.
 */
interface FilesystemInterface
{
    /**
     * Dumps the content into a file. If a part of the path does not exist it will be create.
     *
     * @param string $filename The file to be written to.
     * @param string $content The data to write into the file.
     */
    public function dumpFile(string $filename, string $content): void;

    /**
     * Reads the content of a filename.
     *
     * @param string $filename The file of which to read the content.
     *
     * @return string The content of the filename.
     */
    public function readFile(string $filename): string;

    /**
     * Checks if a file exists.
     *
     * @param bool $filename
     *
     * @return bool
     */
    public function exists(string $filename): bool;
}
