<?php

/*
 * This file is part of the "Recipe Runner" project.
 *
 * (c) Víctor Puertas <http://github.com/yosymfony>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RecipeRunner\Cli\Test\Core\RecipeVariable;

use PHPUnit\Framework\TestCase;
use RecipeRunner\Cli\Core\Port\RecipeRunnerManagerInterface;
use RecipeRunner\Cli\Core\RecipeVariable\CommonRecipeVariableGenerator;
use RecipeRunner\Cli\Core\RecipeVariable\CurrentDirectoryProviderInterface;
use RecipeRunner\Cli\Core\WorkingDirectory\WorkingDirectory;
use RecipeRunner\RecipeRunner\Recipe\StandardRecipeVariables;
use Yosymfony\Collection\MixedCollection;

class CommonRecipeVariableGeneratorTest extends TestCase
{
    /** @var CommonRecipeVariableGenerator */
    private $commonRecipeVariableGenerator;
    private $workingDir = '/test';
    private $currentDir = '/home';
    private $recipeName = 'recipe.rr';

    /** @var MixedCollection */
    private $standardVariables;

    public function setUp(): void
    {
        $this->standardVariables = new MixedCollection();
       
        $workingDirectory = $this->getMockBuilder(WorkingDirectory::class)->disableOriginalConstructor()->getMock();
        $workingDirectory->method('getWorkingDirectory')
            ->willReturn($this->workingDir);

        $currentDirProviderMock = $this->createMock(CurrentDirectoryProviderInterface::class);
        $currentDirProviderMock->method('getCurrentDirectory')->willReturn($this->currentDir);
        $recipeRunnerManagerMock = $this->createMock(RecipeRunnerManagerInterface::class);
        $recipeRunnerManagerMock->method('getStandardVariables')->willReturn($this->standardVariables);

        $this->commonRecipeVariableGenerator = new CommonRecipeVariableGenerator(
            $recipeRunnerManagerMock,
            $workingDirectory,
            $currentDirProviderMock
        );
    }

    public function testMustReturnStandardVariablesFromCore(): void
    {
        $this->standardVariables->addRangeOfValues(StandardRecipeVariables::getCollectionOfVariables());

        $commonVariables = $this->commonRecipeVariableGenerator->generateVariablesForRecipe($this->recipeName);

        $this->assertArraySubset($this->standardVariables, $commonVariables);
    }

    public function testMustReturnTheRecipeName(): void
    {
        $commonVariables = $this->commonRecipeVariableGenerator->generateVariablesForRecipe($this->recipeName);

        $this->assertArraySubset([
            'recipe_name' => $this->recipeName,
        ], $commonVariables);
    }

    public function testMustReturnTheWorkingDirectory(): void
    {
        $commonVariables = $this->commonRecipeVariableGenerator->generateVariablesForRecipe($this->recipeName);

        $this->assertArraySubset([
            'recipe_dir' => $this->workingDir,
        ], $commonVariables);
    }

    public function testMustReturnTheCurrentDirectory(): void
    {
        $commonVariables = $this->commonRecipeVariableGenerator->generateVariablesForRecipe($this->recipeName);

        $this->assertArraySubset([
            'current_dir' => $this->currentDir,
        ], $commonVariables);
    }
}
