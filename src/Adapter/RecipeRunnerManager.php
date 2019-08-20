<?php

/*
 * This file is part of the "Recipe Runner" project.
 *
 * (c) Víctor Puertas <http://github.com/yosymfony>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RecipeRunner\Cli\Adapter;

use InvalidArgumentException;
use RecipeRunner\Cli\Core\RecipeRunner\IOActionParserDecorator;
use RecipeRunner\Cli\Core\RecipeRunner\IOModuleDecorator;
use RecipeRunner\Cli\Core\RecipeRunner\IORecipeParserDecorator;
use RecipeRunner\Cli\Core\RecipeRunner\IOStepParserDecorator;
use RecipeRunner\Cli\Core\RecipeRunner\RecipeRunnerManagerInterface;
use RecipeRunner\Cli\Core\WorkingDirectory\WorkingDirectory;
use RecipeRunner\RecipeRunner\Adapter\Expression\SymfonyExpressionLanguage;
use RecipeRunner\RecipeRunner\Block\Action\ActionParser;
use RecipeRunner\RecipeRunner\Block\Action\ActionParserInterface;
use RecipeRunner\RecipeRunner\Block\BlockCommonOperation;
use RecipeRunner\RecipeRunner\Block\Step\StepParser;
use RecipeRunner\RecipeRunner\Block\Step\StepParserInterface;
use RecipeRunner\RecipeRunner\Definition\RecipeDefinition;
use RecipeRunner\RecipeRunner\Definition\RecipeMaker\YamlRecipeMaker;
use RecipeRunner\RecipeRunner\IO\IOInterface;
use RecipeRunner\RecipeRunner\Module\BuiltIn\EssentialModule;
use RecipeRunner\RecipeRunner\Module\ModuleMethodExecutor;
use RecipeRunner\RecipeRunner\Recipe\RecipeParser;
use RecipeRunner\RecipeRunner\Recipe\RecipeParserInterface;
use RecipeRunner\RecipeRunner\Recipe\StandardRecipeVariables;
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
        $recipeDefinition = $this->createRecipeDefinitionFromFilename($recipeFilename);

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
        $recipe = $this->createRecipeDefinitionFromFilename($recipeFilename);
        $recipeParser = $this->createRecipeRunner($moduleCollection);
        
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

    private function createRecipeDefinitionFromFilename(string $recipeFilename): RecipeDefinition
    {
        $ymlRecipeMaker = new YamlRecipeMaker();

        return $ymlRecipeMaker->makeRecipeFromString($this->workingDirectory->readFile($recipeFilename));
    }

    private function createRecipeRunner(CollectionInterface $modules = null): RecipeParserInterface
    {
        $io = new IOModuleDecorator($this->io);
        $finalModules = $this->composeListOfModules($modules);
        $expressionResolver = new SymfonyExpressionLanguage();
        $blockCommonOperation = new BlockCommonOperation($expressionResolver);
        $methodExecutor = new ModuleMethodExecutor($finalModules, $expressionResolver, $io);
        $actionParser = $this->createActionParser($blockCommonOperation, $methodExecutor);
        $stepParser = $this->createStepParser($actionParser, $blockCommonOperation);
        $recipeParser = $this->createRecipeParser($stepParser);

        return $recipeParser;
    }

    private function composeListOfModules(?CollectionInterface $moduleCollection): CollectionInterface
    {
        $finalModuleCollection = new MixedCollection([new EssentialModule()]);
        
        if ($moduleCollection !== null) {
            $finalModuleCollection->addRangeOfValues($moduleCollection);
        }
        return $finalModuleCollection;
    }

    private function createStepParser(ActionParserInterface $actionParser, BlockCommonOperation $blockCommonOperation): StepParserInterface
    {
        $stepParser = new StepParser($actionParser, $blockCommonOperation);

        return new IOStepParserDecorator($this->io, $stepParser);
    }

    private function createActionParser(BlockCommonOperation $blockCommonOperation, ModuleMethodExecutor $methodExecutor): ActionParserInterface
    {
        $actionParser = new ActionParser($blockCommonOperation, $methodExecutor);

        return new IOActionParserDecorator($this->io, $actionParser);
    }

    private function createRecipeParser(StepParserInterface $stepParser): RecipeParserInterface
    {
        $recipeParser = new RecipeParser($stepParser);

        return new IORecipeParserDecorator($this->io, $recipeParser);
    }
}
