<?php

/*
 * This file is part of the "Recipe Runner" project.
 *
 * (c) Víctor Puertas <http://github.com/yosymfony>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RecipeRunner\Cli\Core\RecipeVariable;

use Yosymfony\Collection\CollectionInterface;

/**
 * Interface for variables that depend on recipe name.
 *
 * @author Víctor Puertas <vpgugr@gmail.com>
 */
interface RecipeVariableGeneratorInterface
{
    /**
     * Generate a collection of variables for a recipe.
     *
     * @param string $recipeName The name of the recipe. E.g: "myrecipe.rr"
     *
     * @return CollectionInterface
     */
    public function generateVariablesForRecipe(string $recipeName): CollectionInterface;
}
