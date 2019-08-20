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

use RecipeRunner\RecipeRunner\Definition\RecipeDefinition;
use RecipeRunner\RecipeRunner\IO\IOInterface;
use RecipeRunner\RecipeRunner\Recipe\RecipeParserInterface;
use Yosymfony\Collection\CollectionInterface;

/**
 * Decorator for IO operations.
 *
 * @author Víctor Puertas <vpgugr@gmail.com>
 */
final class IORecipeParserDecorator implements RecipeParserInterface
{
    /** @var IOInterface */
    private $io;

    /** @var RecipeParserInterface */
    private $recipeParser;

    public function __construct(IOInterface $io, RecipeParserInterface $recipeParser)
    {
        $this->io = $io;
        $this->recipeParser = $recipeParser;
    }
    
    public function parse(RecipeDefinition $recipe, CollectionInterface $recipeVariables): CollectionInterface
    {
        $this->io->write("Running recipe \"{$recipe->getName()}\"");
        $this->io->write('');
        $result = $this->recipeParser->parse($recipe, $recipeVariables);
        $this->io->write('');
        $this->io->write("Execution finished.");

        return $result;
    }
}
