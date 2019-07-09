<?php

/*
 * This file is part of the "Recipe Runner" project.
 *
 * (c) Víctor Puertas <http://github.com/yosymfony>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RecipeRunner\Cli\Infrastructure;

use InvalidArgumentException;
use RecipeRunner\Cli\Core\RecipeRunner\RecipeRunnerManagerInterface;
use RecipeRunner\Cli\Core\WorkingDirectory\WorkingDirectory;
use RecipeRunner\RecipeRunner\Definition\RecipeDefinition;
use RecipeRunner\RecipeRunner\Definition\RecipeMaker\YamlRecipeMaker;
use RecipeRunner\RecipeRunner\IO\IOInterface;
use RecipeRunner\RecipeRunner\Recipe\StandardRecipeVariables;
use RecipeRunner\RecipeRunner\Setup\QuickStart;
use Yosymfony\Collection\CollectionInterface;
use Yosymfony\Collection\MixedCollection;

/**
 * Represent the actions availables with Recipe Runner core.
 *
 * @author Víctor Puertas <vpgugr@gmail.com>
 */
class RecipeRunnerManager implements RecipeRunnerManagerInterface
{
    /** @var IOInterface */
    private $io;

    /** @var WorkingDirectory */
    private $workingDirectory;

    public function __construct(WorkingDirectory $workingDirectory, IOInterface $io)
    {
        $this->io = $io;
        $this->workingDirectory = $workingDirectory;
    }

    /**
     * {@inheritdoc}
     */
    public function getDependenciesFromRecipe(string $recipeFilename): array
    {
        $recipeDefinition = $this->makeRecipeDefinitionFromFilename($recipeFilename);

        $result = $recipeDefinition->getExtra()->getDot('rr.packages', []);

        if (!\is_array($result)) {
            throw new InvalidArgumentException('Invalid value: extra value "rr.packages" must be an array.');
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function executeRecipe(string $recipeFilename, CollectionInterface $recipeVariables = null, array $classNameModules = []): void
    {
        $moduleCollection = $this->createModuleInstances($classNameModules);
        $recipe = $this->makeRecipeDefinitionFromFilename($recipeFilename);
        $recipeParser = QuickStart::Create($moduleCollection, $this->io);
        
        $recipeParser->parse($recipe, $recipeVariables ?? new MixedCollection());
    }

    /**
     * {@inheritdoc}
     */
    public function getStandardVariables(): CollectionInterface
    {
        return StandardRecipeVariables::getCollectionOfVariables();
    }

    private function createModuleInstances(array $classNameModules): MixedCollection
    {
        $moduleInstances = [];

        foreach ($classNameModules as $moduleClassName) {
            $moduleInstances[] = new $moduleClassName();
        }

        return new MixedCollection($moduleInstances);
    }

    private function makeRecipeDefinitionFromFilename(string $recipeFilename): RecipeDefinition
    {
        $ymlRecipeMaker = new YamlRecipeMaker();

        return $ymlRecipeMaker->makeRecipeFromString($this->workingDirectory->readFile($recipeFilename));
    }
}