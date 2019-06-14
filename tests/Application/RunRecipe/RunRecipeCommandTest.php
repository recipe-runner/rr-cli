<?php

/*
 * This file is part of the "Recipe Runner" project.
 *
 * (c) Víctor Puertas <http://github.com/yosymfony>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RecipeRunner\Cli\Test\Application\RunRecipe;

use PHPUnit\Framework\TestCase;
use RecipeRunner\Cli\Application\RunRecipe\RecipeNameExtractor;
use RecipeRunner\Cli\Application\RunRecipe\RunRecipeCommand;
use RecipeRunner\Cli\Core\DependencyManager\DependencyManager;
use RecipeRunner\Cli\Core\RecipeRunner\RecipeRunnerManagerInterface;

class RunRecipeCommandTest extends TestCase
{
    /** @var RunRecipeCommand */
    private $runRecipeCommand;

    /** @var DependencyManager */
    private $dependencyManagerMock;

    /** @var RecipeRunnerManagerInterface */
    private $recipeRunnerManagerMock;

    public function setUp(): void
    {
        $this->dependencyManagerMock = $this->getMockBuilder(DependencyManager::class)->disableOriginalConstructor()->getMock();
        $this->recipeRunnerManagerMock = $this->getMockBuilder(RecipeRunnerManagerInterface::class)->getMock();
        $recipeNameExtractor = new RecipeNameExtractor();
        $this->runRecipeCommand = new RunRecipeCommand(
            $this->dependencyManagerMock,
            $this->recipeRunnerManagerMock,
            $recipeNameExtractor
        );
    }

    public function testExecuteMustPassTheVariablesToRecipeRunner(): void
    {
        $recipeName = 'myRecipe';
        $recipeFilename = "{$recipeName}.yml";
        $packages = [];
        $modules = [];

        $this->recipeRunnerManagerMock
            ->method('getDependenciesFromRecipe')
            ->willReturn($packages);

        $variables = [
            'recipe-path' => '/test',
        ];

        $this->recipeRunnerManagerMock
            ->expects($this->once())
            ->method('executeRecipe')
            ->with(
                $this->equalTo($recipeFilename),
                $this->equalTo($variables),
                $this->equalTo($modules)
            );

        $this->runRecipeCommand->execute($recipeFilename, $variables);
    }

    public function testExecuteMustInstallDependenciesOfARecipeWhenTheRecipeHaveDependencies(): void
    {
        $recipeName = 'myRecipe';
        $recipeFilename = "{$recipeName}.yml";
        $packages = ['vendorPackage1/module1'];
        $modules = ['Vendor/ModuleClass1'];
        $this->recipeRunnerManagerMock
            ->method('getDependenciesFromRecipe')
            ->willReturn($packages);
        
        $this->dependencyManagerMock
            ->expects($this->once())
            ->method('isNecessaryUpdate')
            ->with(
                $this->equalTo($recipeName),
                $this->equalTo($packages)
            )->willReturn(true);

        $this->dependencyManagerMock
            ->expects($this->once())
            ->method('generateManifestFile')
            ->with(
                $this->equalTo($recipeName)
            );

        $this->dependencyManagerMock
            ->expects($this->once())
            ->method('update')
            ->with(
                $this->equalTo($recipeName)
            );

        $this->dependencyManagerMock
            ->expects($this->once())
            ->method('getModuleClassNamesInstalled')
            ->with(
                $this->equalTo($recipeName)
            )
            ->willReturn($modules);

        $this->dependencyManagerMock
            ->expects($this->once())
            ->method('loadAutoloader')
            ->with(
                $this->equalTo($recipeName)
            );

        $this->recipeRunnerManagerMock
            ->expects($this->once())
            ->method('executeRecipe')
            ->with(
                $this->equalTo($recipeFilename),
                $this->equalTo([]),
                $this->equalTo($modules)
            );

        $this->runRecipeCommand->execute($recipeFilename);
    }

    public function testExecuteRecipeMustOnlyLoadAutoloaderAndGetModuleClassNamesInstalledWhenThereAreDependenciesButTheyDoNotNeedToBeUpdated(): void
    {
        $recipeName = 'myRecipe';
        $recipeFilename = "{$recipeName}.yml";
        $packages = ['vendorPackage1/module1'];
        $modules = ['Vendor/ModuleClass1'];
        $this->recipeRunnerManagerMock
            ->method('getDependenciesFromRecipe')
            ->willReturn($packages);
        
        $this->dependencyManagerMock
            ->expects($this->once())
            ->method('isNecessaryUpdate')
            ->with(
                $this->equalTo($recipeName),
                $this->equalTo($packages)
            )->willReturn(false);

        $this->dependencyManagerMock
            ->expects($this->never())
            ->method('generateManifestFile');

        $this->dependencyManagerMock
            ->expects($this->never())
            ->method('update');

        $this->dependencyManagerMock
            ->expects($this->once())
            ->method('getModuleClassNamesInstalled')
            ->with(
                $this->equalTo($recipeName)
            )
            ->willReturn($modules);

        $this->dependencyManagerMock
            ->expects($this->once())
            ->method('loadAutoloader')
            ->with(
                $this->equalTo($recipeName)
            );

        $this->recipeRunnerManagerMock
            ->expects($this->once())
            ->method('executeRecipe')
            ->with(
                $this->equalTo($recipeFilename),
                $this->equalTo([]),
                $this->equalTo($modules)
            );

        $this->runRecipeCommand->execute($recipeFilename);
    }

    public function testExecuteMustNotInstallAnyDependencyWhenTheRecipeDoesNotHaveDependencies(): void
    {
        $recipeName = 'myRecipe';
        $recipeFilename = "{$recipeName}.yml";
        $packages = [];
        $modules = [];
        $this->recipeRunnerManagerMock
            ->method('getDependenciesFromRecipe')
            ->willReturn($packages);
       
        $this->dependencyManagerMock
            ->expects($this->never())
            ->method('isNecessaryUpdate');

        $this->dependencyManagerMock
            ->expects($this->never())
            ->method('generateManifestFile');

        $this->dependencyManagerMock
            ->expects($this->never())
            ->method('update');

        $this->dependencyManagerMock
            ->expects($this->never())
            ->method('loadAutoloader');

        $this->recipeRunnerManagerMock
            ->expects($this->once())
            ->method('executeRecipe')
            ->with(
                $this->equalTo($recipeFilename),
                $this->equalTo([]),
                $this->equalTo($modules)
            );

        $this->runRecipeCommand->execute($recipeFilename);
    }
}
