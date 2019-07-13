<?php

/*
 * This file is part of the "Recipe Runner" project.
 *
 * (c) Víctor Puertas <http://github.com/yosymfony>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RecipeRunner\Cli\Core\RecipeRunner;

use RecipeRunner\RecipeRunner\Block\Action\ActionParserInterface;
use RecipeRunner\RecipeRunner\Block\BlockResult;
use RecipeRunner\RecipeRunner\Definition\ActionDefinition;
use RecipeRunner\RecipeRunner\IO\IOInterface;
use RecipeRunner\RecipeRunner\RecipeVariablesContainer;

/**
 * Decorator for IO operations.
 *
 * @author Víctor Puertas <vpgugr@gmail.com>
 */
final class IOActionParserDecorator implements ActionParserInterface
{
    /** @var IOInterface */
    private $io;

    /** @var ActionParserInterface */
    private $actionParser;

    final public function __construct(IOInterface $io, ActionParserInterface $actionParser)
    {
        $this->io = $io;
        $this->actionParser = $actionParser;
    }

    public function parse(ActionDefinition $action, RecipeVariablesContainer $recipeVariables): BlockResult
    {
        $this->io->write("  + Running action <info>\"{$action->getName()}\"</info>");

        return $this->actionParser->parse($action, $recipeVariables);
    }
}
