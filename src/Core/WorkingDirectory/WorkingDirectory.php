<?php

/*
 * This file is part of the "Recipe Runner" project.
 *
 * (c) Víctor Puertas <http://github.com/yosymfony>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RecipeRunner\Cli\Core\WorkingDirectory;

use InvalidArgumentException;
use RecipeRunner\Cli\Core\Port\FilesystemInterface;

class WorkingDirectory
{
    /** @var FilesystemInterface */
    private $fs;

    private $recipeInternalDir = '.rr';
    private $workingDir;

    /**
     * Constructor.
     *
     * @param string $workingDir Absolute path canonized. eg: /my-path/recipes
     * or in case of Windows: c:/my-path/recipes
     * @param FilesystemInterface $fs Interface for accessing to the filesystem.
     */
    public function __construct(string $workingDir, FilesystemInterface $fs)
    {
        $this->assertFieldIsNotEmpty($workingDir, 'workingDir');
        $this->fs = $fs;
        $this->workingDir = $workingDir;
    }

    /**
     * Returns the working directory.
     *
     * @return string
     */
    public function getWorkingDirectory(): string
    {
        return $this->workingDir;
    }

    /**
     * Write a file in the recipe internal directory.
     *
     * @param string $recipeName The name of the recipe.
     * @param string $filename
     * @param string $content The content of the file.
     */
    public function writeRecipeInternalFile(string $recipeName, string $filename, string $content): void
    {
        $this->assertFieldIsNotEmpty($recipeName, 'recipeName');
        $this->assertFieldIsNotEmpty($filename, 'filename');
        
        $recipeFilename = $this->composeRecipeInternalDirWithFile($recipeName, $filename);

        $this->fs->dumpFile($recipeFilename, $content);
    }

    /**
     * Reads a file in the recipe internal directory.
     *
     * @param string $recipeName The name of the recipe.
     * @param string $filename
     *
     * @return string The content of the file.
     */
    public function readRecipeInternalFile(string $recipeName, string $filename): string
    {
        $this->assertFieldIsNotEmpty($recipeName, 'recipeName');
        $this->assertFieldIsNotEmpty($filename, 'filename');

        $recipeFilename = $this->composeRecipeInternalDirWithFile($recipeName, $filename);

        return $this->fs->readFile($recipeFilename);
    }

    /**
     * Reads a file.
     *
     * @param string $recipeName The name of the recipe.
     * @param string $filename
     *
     * @return string The content of the file.
     */
    public function readFile(string $filename): string
    {
        $this->assertFieldIsNotEmpty($filename, 'filename');

        $filename = "{$this->workingDir}/{$filename}";

        return $this->fs->readFile($filename);
    }

    /**
     * Check if a file exists in the recipe internal directory.
     *
     * @param string $recipeName The name of the recipe.
     * @param string $filename
     *
     * @return bool
     */
    public function existsRecipeInternalFile(string $recipeName, string $filename): bool
    {
        $this->assertFieldIsNotEmpty($recipeName, 'recipeName');
        $this->assertFieldIsNotEmpty($filename, 'filename');

        $recipeFilename = $this->composeRecipeInternalDirWithFile($recipeName, $filename);

        return $this->fs->exists($recipeFilename);
    }

    /**
     * Returns the recipe internal directory.
     *
     * @param string $recipeName The name of the recipe.
     *
     * @return string Path canonized.
     */
    public function getRecipeInternalDirectory(string $recipeName): string
    {
        return $this->composeRecipeInternalDir($recipeName);
    }

    private function assertFieldIsNotEmpty(string $value, string $fieldName): void
    {
        if (\trim($value) == '') {
            throw new InvalidArgumentException("The param \"{$fieldName}\" must not be empty.");
        }
    }

    private function composeRecipeInternalDirWithFile(string $recipeName, string $filename): string
    {
        $baseDir = $this->composeRecipeInternalDir($recipeName);

        return "{$baseDir}/{$filename}";
    }

    private function composeRecipeInternalDir(string $recipeName): string
    {
        return "{$this->workingDir}/{$this->recipeInternalDir}/{$recipeName}";
    }
}
