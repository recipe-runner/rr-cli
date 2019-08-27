<?php

/*
 * This file is part of the "Recipe Runner" project.
 *
 * (c) Víctor Puertas <http://github.com/yosymfony>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RecipeRunner\Cli\Adapter\RecipeRunner;

use RecipeRunner\RecipeRunner\Block\Step\StepParserInterface;
use RecipeRunner\RecipeRunner\Definition\StepDefinition;
use RecipeRunner\RecipeRunner\IO\IOInterface;
use RecipeRunner\RecipeRunner\RecipeVariablesContainer;
use Yosymfony\Collection\CollectionInterface;

/**
 * Decorator for IO operations.
 *
 * @author Víctor Puertas <vpgugr@gmail.com>
 */
final class IOStepParserDecorator implements StepParserInterface
{
    /** @var IOInterface */
    private $io;

    /** @var StepParserInterface */
    private $stepParser;

    public function __construct(IOInterface $io, StepParserInterface $stepParser)
    {
        $this->io = $io;
        $this->stepParser = $stepParser;
    }
    
    public function parse(StepDefinition $step, RecipeVariablesContainer $recipeVariables): CollectionInterface
    {
        $this->io->write("- Running step <info>\"{$step->getName()}\"</info>");

        return $this->stepParser->parse($step, $recipeVariables);
    }
}
