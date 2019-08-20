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

use RecipeRunner\Cli\Core\Port\RecipeRunner\RecipeRunnerManagerInterface;
use RecipeRunner\Cli\Core\WorkingDirectory\WorkingDirectory;
use Yosymfony\Collection\CollectionInterface;
use Yosymfony\Collection\MixedCollection;

class CommonRecipeVariableGenerator implements RecipeVariableGeneratorInterface
{
    /** @var RecipeRunnerManagerInterface */
    private $recipeRunnerManager;

    /** @var WorkingDirectory */
    private $workingDir;

    /** @var CurrentDirectoryProviderInterface */
    private $currentDirectoryProvider;

    public function __construct(RecipeRunnerManagerInterface $recipeRunnerManager, WorkingDirectory $workingDir, CurrentDirectoryProviderInterface $currentDirProvider)
    {
        $this->recipeRunnerManager = $recipeRunnerManager;
        $this->workingDir = $workingDir;
        $this->currentDirectoryProvider = $currentDirProvider;
    }

    public function generateVariablesForRecipe(string $recipeName): CollectionInterface
    {
        $variableCollection = $this->recipeRunnerManager->getStandardVariables();
        $variableCollection->add('recipe_name', $recipeName)
            ->add('recipe_dir', $this->workingDir->getWorkingDirectory())
            ->add('current_dir', $this->currentDirectoryProvider->getCurrentDirectory());

        return $variableCollection;
    }
}
