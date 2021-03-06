<?php

/*
 * This file is part of the "Recipe Runner" project.
 *
 * (c) Víctor Puertas <http://github.com/yosymfony>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RecipeRunner\Cli\CliInterface\Command;

use RecipeRunner\Cli\Adapter\ConsoleIO;
use RecipeRunner\Cli\Adapter\CurrentDirectoryProvider;
use RecipeRunner\Cli\Adapter\Filesystem;
use RecipeRunner\Cli\Adapter\Process;
use RecipeRunner\Cli\Adapter\RecipeRunner\RecipeRunnerManager;
use RecipeRunner\Cli\Application\RunRecipe\RecipeNameExtractor;
use RecipeRunner\Cli\Application\RunRecipe\RunRecipeCommand;
use RecipeRunner\Cli\Core\DependencyManager\DependencyManager;
use RecipeRunner\Cli\Core\RecipeVariable\CommonRecipeVariableGenerator;
use RecipeRunner\Cli\Core\WorkingDirectory\WorkingDirectory;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Yosymfony\Collection\MixedCollection;

/**
 * Run command for Symfony console.
 *
 * @author Víctor Puertas <vpgugr@gmail.com>
 */
class RunCommand extends Command
{
    protected static $defaultName = 'run';

    protected function configure()
    {
        $this
            ->setDescription('Run a recipe.')
            ->setHelp('The run command executes a recipe.');
        
        $this->addArgument('filename', InputArgument::REQUIRED, 'The recipe YAML filename.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new ConsoleIO($input, $output, new HelperSet([
            new QuestionHelper(),
        ]));
        
        $recipeFilename = $input->getArgument('filename');
        $runRecipeCommand = $this->makeRunRecipeCommand($io, $this->getWorkingDirFromPath($recipeFilename));
        $runRecipeCommand->execute($this->getRecipeFilenameFromPath($recipeFilename), new MixedCollection());
    }

    private function makeRunRecipeCommand(ConsoleIO $io, string $workingDir): RunRecipeCommand
    {
        $process = new Process($io);
        $fs = new Filesystem();
        $workingDirectory = new WorkingDirectory($workingDir, $fs);
        $recipeRunner = new RecipeRunnerManager($workingDirectory, $io);
        $dependencyManager = new DependencyManager($process, $workingDirectory);
        $recipeNameExtractor = new RecipeNameExtractor();
        $currentDirectoryProvider = new CurrentDirectoryProvider();
        $commonRecipeVariableGenerator = new CommonRecipeVariableGenerator($recipeRunner, $workingDirectory, $currentDirectoryProvider);

        return new RunRecipeCommand($dependencyManager, $recipeRunner, $recipeNameExtractor, $commonRecipeVariableGenerator, $io);
    }

    private function getRecipeFilenameFromPath(string $recipeFilename): string
    {
        $fileInfo = new \SplFileInfo($recipeFilename);

        return $fileInfo->getFilename();
    }

    private function getWorkingDirFromPath(string $recipeFilename): string
    {
        $fileInfo = new \SplFileInfo($recipeFilename);
        $directory = $fileInfo->getPath();

        if ($directory === "") {
            $currentDirectoryProvider = new CurrentDirectoryProvider();
            $directory = $currentDirectoryProvider->getCurrentDirectory();
        }

        return $directory;
    }
}
