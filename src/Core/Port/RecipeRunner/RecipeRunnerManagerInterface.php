<?php

/*
 * This file is part of the "Recipe Runner" project.
 *
 * (c) Víctor Puertas <http://github.com/yosymfony>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RecipeRunner\Cli\Core\Port\RecipeRunner;

use Yosymfony\Collection\CollectionInterface;

/**
 * Interface for handling Recipe Runner Core
 */
interface RecipeRunnerManagerInterface
{
    /**
     * Returns a list of dependencies required for executing a recipe.
     *
     * @param string $recipeName The name of the recipe. e.g: recipe.yml
     *
     * @return array Key-value list with the package name as key and the version as value.
     * E.g: ['recipe-runner/io-module' => '1.0.x-dev']
     */
    public function getDependenciesFromRecipe(string $recipeName): array;

    /**
     * Executes a recipe.
     *
     * @param string $recipeName
     * @param CollectionInterface $recipeVariables
     * @param array $classNameModules
     *
     * @return void
     */
    public function executeRecipe(string $recipeName, CollectionInterface $recipeVariables = null, array $classNameModules = []): void;

    /**
     * Returns the collection of standard variables.
     *
     * @return CollectionInterface
     */
    public function getStandardVariables(): CollectionInterface;
}
