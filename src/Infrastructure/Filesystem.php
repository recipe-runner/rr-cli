<?php

/*
 * This file is part of the "Recipe Runner" project.
 *
 * (c) Víctor Puertas <http://github.com/yosymfony>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RecipeRunner\Cli\Infrastructure;

use RecipeRunner\Cli\Core\Filesystem\FilesystemInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem as FilesystemBase;

/**
 * Filesystem operations.
 *
 * @author Víctor Puertas <vpgugr@gmail.com>
 */
class Filesystem implements FilesystemInterface
{
    /** @var FilesystemBase */
    private $fs;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->fs = new FilesystemBase();
    }

    /**
     * {@inheritdoc}
     */
    public function dumpFile(string $filename, string $content): void
    {
        $this->fs->dumpFile($filename, $content);
    }

    /**
     * {@inheritdoc}
     */
    public function readFile(string $filename): string
    {
        if (is_file($filename) === false) {
            throw new FileNotFoundException("File \"{$filename}\" does not exist.");
        }
        
        if (is_readable($filename) === false) {
            throw new IOException("File \"{$filename}\" cannot be read.");
        }
        
        $content = file_get_contents($filename);
        
        if ($content === false) {
            throw new IOException("Error reading the content of the file \"{$filename}\".");
        }

        return $content;
    }

    public function exists(string $filename): bool
    {
        return $this->fs->exists($filename);
    }
}
