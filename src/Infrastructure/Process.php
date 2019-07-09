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

use RecipeRunner\Cli\Core\Process\ProcessInterface;
use RecipeRunner\RecipeRunner\IO\IOInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process as SymfonyProcess;

/**
 * Process runner.
 *
 * @author Víctor Puertas <vpgugr@gmail.com>
 */
class Process implements ProcessInterface
{
    /** @var IOInterface */
    private $io;

    private $phpBinaryPath;

    public function __construct(IOInterface $io)
    {
        $this->io = $io;
    }

    /**
     * {@inheritdoc}
     */
    public function runPHPScript(string $command, array $arguments = [], string $workingDir = null): void
    {
        $finalCommand = \array_merge([$command], $arguments);
        $process = new SymfonyProcess($finalCommand, $workingDir);
        $process->start();

        foreach ($process as $type => $data) {
            if ($process::OUT === $type) {
                $this->io->write($data, true, IOInterface::VERBOSITY_VERBOSE);
                continue;
            }

            $this->io->write($data, true, IOInterface::VERBOSITY_VERBOSE);
        }

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function findExecutable(string $name): string
    {
        $executableFinder = new ExecutableFinder();
        
        return $executableFinder->find($name);
    }
}
