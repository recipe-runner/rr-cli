<?php

/*
 * This file is part of the "Recipe Runner" project.
 *
 * (c) Víctor Puertas <http://github.com/yosymfony>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RecipeRunner\Cli\Test\Adapter\RecipeRunner;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RecipeRunner\Cli\Adapter\RecipeRunner\IORecipeParserDecorator;
use RecipeRunner\RecipeRunner\Definition\RecipeDefinition;
use RecipeRunner\RecipeRunner\IO\IOInterface;
use RecipeRunner\RecipeRunner\Recipe\RecipeParserInterface;
use Yosymfony\Collection\CollectionInterface;

class IORecipeParserDecoratorTest extends TestCase
{
    /** @var MockObject */
    private $ioMock;

    /** @var MockObject */
    private $recipeParserMock;

    /** @var MockObject */
    private $recipeDefinitionMock;
    
    /** @var MockObject */
    private $recipeVariablesMock;
    
    /** @var IORecipeParserDecorator */
    private $ioRecipeParserDecorator;

    public function setUp(): Void
    {
        $this->ioMock = $this->getMockBuilder(IOInterface::class)->getMock();
        $this->recipeParserMock = $this->getMockBuilder(RecipeParserInterface::class)->getMock();
        $this->ioRecipeParserDecorator = new IORecipeParserDecorator($this->ioMock, $this->recipeParserMock);
        $this->recipeDefinitionMock = $this->getMockBuilder(RecipeDefinition::class)->disableOriginalConstructor()->getMock();
        $this->recipeVariablesMock = $this->getMockBuilder(CollectionInterface::class)->getMock();
    }

    public function testMustCallOriginalActionParser(): void
    {
        $this->recipeParserMock->expects($this->once())->method('parse')
            ->with($this->equalTo($this->recipeDefinitionMock), $this->equalTo($this->recipeVariablesMock));

        $this->ioRecipeParserDecorator->parse($this->recipeDefinitionMock, $this->recipeVariablesMock);
    }

    public function testMustCallIOWriteMethod(): void
    {
        $this->ioMock->expects($this->atLeastOnce())->method('write');

        $this->ioRecipeParserDecorator->parse($this->recipeDefinitionMock, $this->recipeVariablesMock);
    }
}
