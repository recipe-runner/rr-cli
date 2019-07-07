<?php

/*
 * This file is part of the "Recipe Runner" project.
 *
 * (c) Víctor Puertas <http://github.com/yosymfony>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RecipeRunner\Cli\Core\DependencyManager;

use RecipeRunner\Cli\Core\Process\ProcessInterface;
use RecipeRunner\Cli\Core\WorkingDirectory\WorkingDirectory;
use RuntimeException;

class DependencyManager
{
    private const COMPOSER_JSON_FILE = 'composer.json';
    private const COMPOSER_LOCK_FILE = 'composer.lock';
    
    /** @var ProcessInterface */
    private $process;

    /** @var WorkingDirectory */
    private $workingDirectory;

    private $composerPath;

    /**
     * Constructor.
     *
     * @param ProcessInterface $process
     * @param WorkingDirectory $workingDirectory
     */
    public function __construct(ProcessInterface $process, WorkingDirectory $workingDirectory)
    {
        $this->process = $process;
        $this->workingDirectory = $workingDirectory;
    }

    /**
     * Updates dependencies presents in the directory.
     *
     * @param string $dir
     *
     * @return void
     */
    public function update(string $recipeName): void
    {
        $dir = $this->workingDirectory->getRecipeInternalDirectory($recipeName);
        $composerCommand = $this->guessComposerExecutablePath();
        $this->process->runPHPScript($composerCommand, ['update', '--no-dev', '--prefer-dist', '--no-suggest'], $dir);
    }

    /**
     * Generates the manifest file with the packages passes.
     *
     * @param string $recipeName The name of the recipe.
     * @param array $nameVersionPairs Key-value list with the package name as key and the version as value.
     * E.g: ['recipe-runner/io-module' => '1.0.x-dev']
     *
     * @return void
     */
    public function generateManifestFile(string $recipeName, array $nameVersionPairs): void
    {
        $composerFileContent = $this->generateManifestContent($nameVersionPairs);
        $this->workingDirectory->writeRecipeInternalFile($recipeName, self::COMPOSER_JSON_FILE, $composerFileContent);
    }

    /**
     * Indicates if is necessary to update the dependencies of a recipe.
     *
     * @param string $recipeName The name of the recipe.
     * @param array $nameVersionPairs Key-value list with the package name as key and the version as value.
     * E.g: ['recipe-runner/io-module' => '1.0.x-dev']
     */
    public function isNecessaryUpdate(string $recipeName, array $nameVersionPairs): bool
    {
        if (!$this->workingDirectory->existsRecipeInternalFile($recipeName, self::COMPOSER_LOCK_FILE)) {
            return true;
        }

        $packagesAlreadyInstalled = $this->getPackagesAlreadyInstalled($recipeName);
        
        if (count($nameVersionPairs) != count($packagesAlreadyInstalled)) {
            return true;
        }
        
        \ksort($nameVersionPairs);
        \ksort($packagesAlreadyInstalled);

        foreach ($packagesAlreadyInstalled as $package => $version) {
            if (isset($nameVersionPairs[$package]) === false || $nameVersionPairs[$package] != $version) {
                return true;
            }
        }

        return false;
    }

    public function getModuleClassNamesInstalled(string $recipeName): array
    {
        $composerLockStructure = $this->readComposerLockContent($recipeName);
        $packages = $this->extractPackagesFromComposerLockStructure($composerLockStructure);
        $moduleClassNames = [];

        foreach ($packages as $package) {
            $modules = $this->extractClassNamesFromPackageStructure($package);
            $moduleClassNames = \array_merge($moduleClassNames, $modules);
        }

        return \array_unique($moduleClassNames);
    }

    public function loadAutoloader(string $recipeName): void
    {
        $baseDir = $this->workingDirectory->getRecipeInternalDirectory($recipeName);
        $autoloadFile = $baseDir.'/vendor/autoload.php';

        $composerClassLoader = require_once $autoloadFile;
        $this->registerRecipeDependenciesClassLoaderAtTheEnd($composerClassLoader);
    }

    private function registerRecipeDependenciesClassLoaderAtTheEnd($composerClassLoader): void
    {
        $composerClassLoader->unregister();
        $composerClassLoader->register(false);
    }

    private function generateManifestContent(array $nameVersionPairs): string
    {
        $composerArray = ['require' => $nameVersionPairs, 'minimum-stability' => 'dev'];

        return \json_encode($composerArray);
    }

    private function guessComposerExecutablePath(): string
    {
        if ($this->composerPath !== null) {
            return $this->composerPath;
        }

        $candidateNames = ['composer', 'composer.phar'];

        foreach ($candidateNames as $name) {
            $this->composerPath = $this->process->findExecutable($name);

            if ($this->composerPath === null) {
                throw new RuntimeException('Composer command not found.');
            }

            return $this->composerPath;
        }
    }

    private function getPackagesAlreadyInstalled(string $recipeName): array
    {
        $composerJsonContent = $this->workingDirectory->readRecipeInternalFile($recipeName, self::COMPOSER_JSON_FILE);
        $composerArrayContent = \json_decode($composerJsonContent, true);
        
        return $composerArrayContent['require'];
    }

    private function readComposerLockContent(string $recipeName): array
    {
        $composerLockFileContent = $this->workingDirectory->readRecipeInternalFile($recipeName, self::COMPOSER_LOCK_FILE);

        return \json_decode($composerLockFileContent, true);
    }

    private function extractPackagesFromComposerLockStructure(array $composerLockStructure): array
    {
        if (!isset($composerLockStructure['packages'])) {
            return [];
        }
        
        return $composerLockStructure['packages'];
    }

    private function extractClassNamesFromPackageStructure(array $package): array
    {
        if (isset($package['extra'], $package['extra']['recipe-runner'], $package['extra']['recipe-runner']['modules'])) {
            return $package['extra']['recipe-runner']['modules'];
        }

        return [];
    }
}
