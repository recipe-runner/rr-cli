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

class RunRecipeCommand
{
    /** @var DependencyManager */
    private $dependencyManager;

    /** @var RecipeRunnerManagerInterface */
    private $recipeRunnerManager;

    /** @var RecipeNameExtractor */
    private $recipeNameExtractor;

    public function __construct(DependencyManager $dependencyManager, RecipeRunnerManagerInterface $recipeRunnerManager, RecipeNameExtractor $recipeNameExtractor)
    {
        $this->dependencyManager = $dependencyManager;
        $this->recipeRunnerManager = $recipeRunnerManager;
        $this->recipeNameExtractor = $recipeNameExtractor;
    }

    /**
     * Executes a recipe.
     *
     * @param string $recipeFilename The filename with the recipe. e.g: my-recipe.yml
     * @param array $recipeVariables Set of variables available for the recipe.
     *
     * @return void
     */
    public function execute(string $recipeFilename, array $recipeVariables = []): void
    {
        $moduleClassNames = [];
        $recipeName = $this->recipeNameExtractor->extractNameFromFilename($recipeFilename);
        $dependencies = $this->recipeRunnerManager->getDependenciesFromRecipe($recipeFilename);
        
        if (count($dependencies) > 0) {
            if ($this->dependencyManager->isNecessaryUpdate($recipeName, $dependencies)) {
                $this->dependencyManager->generateManifestFile($recipeName, $dependencies);
                $this->dependencyManager->update($recipeName);
            }

            $moduleClassNames = $this->dependencyManager->getModuleClassNamesInstalled($recipeName);
            $this->dependencyManager->loadAutoloader($recipeName);
        }

        $this->recipeRunnerManager->executeRecipe($recipeFilename, $recipeVariables, $moduleClassNames);
    }
}
