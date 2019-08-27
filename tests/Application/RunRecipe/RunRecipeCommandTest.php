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

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RecipeRunner\Cli\Application\RunRecipe\RecipeNameExtractor;
use RecipeRunner\Cli\Application\RunRecipe\RunRecipeCommand;
use RecipeRunner\Cli\Core\DependencyManager\DependencyManager;
use RecipeRunner\Cli\Core\Port\RecipeRunnerManagerInterface;
use RecipeRunner\Cli\Core\RecipeVariable\RecipeVariableGeneratorInterface;
use RecipeRunner\RecipeRunner\IO\IOInterface;
use Yosymfony\Collection\MixedCollection;

class RunRecipeCommandTest extends TestCase
{
    /** @var RunRecipeCommand */
    private $runRecipeCommand;

    /** @var MockObject */
    private $dependencyManagerMock;

    /** @var MockObject */
    private $recipeRunnerManagerMock;

    /** @var MixedCollection */
    private $variables;

    /** @var MixedCollection */
    private $commonVariables;

    /** @var MockObject */
    private $IOInterfaceMock;

    private $recipeName;
    private $recipeFilename;

    public function setUp(): void
    {
        $this->recipeName = 'myRecipe';
        $this->recipeFilename = "{$this->recipeName}.yml";
        $this->commonVariables = new MixedCollection();
        $this->variables = new MixedCollection();
        $this->dependencyManagerMock = $this->getMockBuilder(DependencyManager::class)->disableOriginalConstructor()->getMock();
        $this->recipeRunnerManagerMock = $this->getMockBuilder(RecipeRunnerManagerInterface::class)->getMock();
        $recipeNameExtractor = new RecipeNameExtractor();
        $recipeVariableGeneratorInterfaceMock = $this->createMock(RecipeVariableGeneratorInterface::class);
        $recipeVariableGeneratorInterfaceMock->method('generateVariablesForRecipe')->willReturn($this->commonVariables);
        $this->IOInterfaceMock = $this->getMockBuilder(IOInterface::class)->getMock();
        $this->runRecipeCommand = new RunRecipeCommand(
            $this->dependencyManagerMock,
            $this->recipeRunnerManagerMock,
            $recipeNameExtractor,
            $recipeVariableGeneratorInterfaceMock,
            $this->IOInterfaceMock
        );
    }

    public function testExecuteMustPassTheVariablesToRecipeRunner(): void
    {
        $packages = [];
        $modules = [];

        $this->recipeRunnerManagerMock
            ->method('getDependenciesFromRecipe')
            ->willReturn($packages);

        $this->variables->add('recipe-path', '/test');

        $this->recipeRunnerManagerMock
            ->expects($this->once())
            ->method('executeRecipe')
            ->with(
                $this->equalTo($this->recipeFilename),
                $this->equalTo($this->commonVariables->union($this->variables)),
                $this->equalTo($modules)
            );

        $this->runRecipeCommand->execute($this->recipeFilename, $this->variables);
    }

    public function testExecuteMustPassTheCommonVariablesPlusRecipeVariablesToRecipeRunner(): void
    {
        $packages = [];
        $modules = [];

        $this->recipeRunnerManagerMock
            ->method('getDependenciesFromRecipe')
            ->willReturn($packages);

        $this->variables->add('recipe-path', '/test');
        $this->commonVariables->add('os_family', 'myOS');

        $this->recipeRunnerManagerMock
            ->expects($this->once())
            ->method('executeRecipe')
            ->with(
                $this->equalTo($this->recipeFilename),
                $this->equalTo($this->commonVariables->union($this->variables)),
                $this->equalTo($modules)
            );

        $this->runRecipeCommand->execute($this->recipeFilename, $this->variables);
    }

    public function testExecuteMustInstallDependenciesOfARecipeWhenTheRecipeHaveDependencies(): void
    {
        $packages = ['vendorPackage1/module1'];
        $modules = ['Vendor/ModuleClass1'];
        $this->recipeRunnerManagerMock
            ->method('getDependenciesFromRecipe')
            ->willReturn($packages);
        
        $this->dependencyManagerMock
            ->expects($this->once())
            ->method('isNecessaryUpdate')
            ->with(
                $this->equalTo($this->recipeName),
                $this->equalTo($packages)
            )->willReturn(true);

        $this->dependencyManagerMock
            ->expects($this->once())
            ->method('generateManifestFile')
            ->with(
                $this->equalTo($this->recipeName)
            );

        $this->IOInterfaceMock
            ->expects($this->once())
            ->method('write')
            ->with(
                $this->equalTo('Resolving dependencies...')
            );

        $this->dependencyManagerMock
            ->expects($this->once())
            ->method('update')
            ->with(
                $this->equalTo($this->recipeName)
            );

        $this->dependencyManagerMock
            ->expects($this->once())
            ->method('getModuleClassNamesInstalled')
            ->with(
                $this->equalTo($this->recipeName)
            )
            ->willReturn($modules);

        $this->dependencyManagerMock
            ->expects($this->once())
            ->method('loadAutoloader')
            ->with(
                $this->equalTo($this->recipeName)
            );

        $this->recipeRunnerManagerMock
            ->expects($this->once())
            ->method('executeRecipe')
            ->with(
                $this->equalTo($this->recipeFilename),
                $this->equalTo($this->variables),
                $this->equalTo($modules)
            );

        $this->runRecipeCommand->execute($this->recipeFilename, $this->variables);
    }

    public function testExecuteRecipeMustOnlyLoadAutoloaderAndGetModuleClassNamesInstalledWhenThereAreDependenciesButTheyDoNotNeedToBeUpdated(): void
    {
        $packages = ['vendorPackage1/module1'];
        $modules = ['Vendor/ModuleClass1'];
        $this->recipeRunnerManagerMock
            ->method('getDependenciesFromRecipe')
            ->willReturn($packages);
        
        $this->dependencyManagerMock
            ->expects($this->once())
            ->method('isNecessaryUpdate')
            ->with(
                $this->equalTo($this->recipeName),
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
                $this->equalTo($this->recipeName)
            )
            ->willReturn($modules);

        $this->dependencyManagerMock
            ->expects($this->once())
            ->method('loadAutoloader')
            ->with(
                $this->equalTo($this->recipeName)
            );

        $this->recipeRunnerManagerMock
            ->expects($this->once())
            ->method('executeRecipe')
            ->with(
                $this->equalTo($this->recipeFilename),
                $this->equalTo($this->variables),
                $this->equalTo($modules)
            );

        $this->runRecipeCommand->execute($this->recipeFilename, $this->variables);
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
                $this->equalTo($this->variables),
                $this->equalTo($modules)
            );

        $this->runRecipeCommand->execute($recipeFilename, $this->variables);
    }
}
