<?php

/*
 * This file is part of the "Recipe Runner" project.
 *
 * (c) Víctor Puertas <http://github.com/yosymfony>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RecipeRunner\Cli\Core\Process;

/**
 * Interface for executing shell commands.
 */
interface ProcessInterface
{
    /**
     * Run a PHP command (executable or phar file) in a independent process.
     * An exception should be thrown in case of the command couldn't be executed successfully.
     * E.g: command exited with non-zero code.
     *
     * @param string $command The command to run.
     * @param array $arguments List of argument.
     * @param string $workingDir The working directory or null to use the working dir of the current PHP process.
     */
    public function runPHPScript(string $command, array $arguments = [], string $workingDir = null): void;

    /**
     * Finds an executable by name.
     *
     * @param string $name The executable name (without the extension).
     *
     * @return string The executable path or default value.
     */
    public function findExecutable(string $name): string;
}
