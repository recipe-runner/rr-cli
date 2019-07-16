<?php

/*
 * This file is part of the "Recipe Runner" project.
 *
 * (c) Víctor Puertas <http://github.com/yosymfony>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RecipeRunner\Cli\Application\RunRecipe;

use RecipeRunner\Cli\Application\RunRecipe\RecipeNameExtractor;
use RecipeRunner\Cli\Core\DependencyManager\DependencyManager;
use RecipeRunner\Cli\Core\RecipeRunner\RecipeRunnerManagerInterface;
use RecipeRunner\Cli\Core\RecipeVariable\RecipeVariableGeneratorInterface;
use RecipeRunner\RecipeRunner\IO\IOInterface;
use Yosymfony\Collection\CollectionInterface;

/**
 * Run recipe command.
 *
 * @author Víctor Puertas <vpgugr@gmail.com>
 */
class RunRecipeCommand
{
    /** @var DependencyManager */
    private $dependencyManager;

    /** @var RecipeRunnerManagerInterface */
    private $recipeRunnerManager;

    /** @var RecipeNameExtractor */
    private $recipeNameExtractor;

    /** @var RecipeVariableGeneratorInterface */
    private $commonRecipeVariablesGenerator;

    /** @var IOInterface */
    private $io;

    public function __construct(DependencyManager $dependencyManager, RecipeRunnerManagerInterface $recipeRunnerManager, RecipeNameExtractor $recipeNameExtractor, RecipeVariableGeneratorInterface $commonRecipeVariablesGenerator, IOInterface $io)
    {
        $this->dependencyManager = $dependencyManager;
        $this->recipeRunnerManager = $recipeRunnerManager;
        $this->recipeNameExtractor = $recipeNameExtractor;
        $this->commonRecipeVariablesGenerator = $commonRecipeVariablesGenerator;
        $this->io = $io;
    }

    /**
     * Executes a recipe.
     *
     * @param string $recipeFilename The filename with the recipe. e.g: my-recipe.yml
     * @param CollectionInterface $executionVariables Set of variables available for recipe.
     *
     * @return void
     */
    public function execute(string $recipeFilename, CollectionInterface $executionVariables): void
    {
        $moduleClassNames = [];
        $recipeName = $this->recipeNameExtractor->extractNameFromFilename($recipeFilename);
        $dependencies = $this->recipeRunnerManager->getDependenciesFromRecipe($recipeFilename);
        
        if (count($dependencies) > 0) {
            if ($this->dependencyManager->isNecessaryUpdate($recipeName, $dependencies)) {
                $this->dependencyManager->generateManifestFile($recipeName, $dependencies);
                $this->io->write('Resolving dependencies...');
                $this->dependencyManager->update($recipeName);
            }

            $moduleClassNames = $this->dependencyManager->getModuleClassNamesInstalled($recipeName);
            $this->dependencyManager->loadAutoloader($recipeName);
        }

        $finalRecipeVariables = $this->commonRecipeVariablesGenerator->generateVariablesForRecipe($recipeName)->union($executionVariables);

        $this->recipeRunnerManager->executeRecipe($recipeFilename, $finalRecipeVariables, $moduleClassNames);
    }
}
