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
use RecipeRunner\Cli\Adapter\RecipeRunner\IOActionParserDecorator;
use RecipeRunner\RecipeRunner\Block\Action\ActionParserInterface;
use RecipeRunner\RecipeRunner\Definition\ActionDefinition;
use RecipeRunner\RecipeRunner\IO\IOInterface;
use RecipeRunner\RecipeRunner\RecipeVariablesContainer;

class IOActionParserDecoratorTest extends TestCase
{
    /** @var MockObject */
    private $ioMock;

    /** @var MockObject */
    private $actionParserMock;

    /** @var MockObject */
    private $actionDefinitionMock;
    
    /** @var MockObject */
    private $recipeVariablesContainerMock;
    
    /** @var IOActionParserDecorator */
    private $ioActionParserDecorator;

    public function setUp(): Void
    {
        $this->ioMock = $this->getMockBuilder(IOInterface::class)->getMock();
        $this->actionParserMock = $this->getMockBuilder(ActionParserInterface::class)->getMock();
        $this->ioActionParserDecorator = new IOActionParserDecorator($this->ioMock, $this->actionParserMock);
        $this->actionDefinitionMock = $this->getMockBuilder(ActionDefinition::class)->disableOriginalConstructor()->getMock();
        $this->recipeVariablesContainerMock = $this->getMockBuilder(RecipeVariablesContainer::class)->disableOriginalConstructor()->getMock();
    }

    public function testMustCallOriginalActionParser(): void
    {
        $this->actionParserMock->expects($this->once())->method('parse')
            ->with($this->equalTo($this->actionDefinitionMock), $this->equalTo($this->recipeVariablesContainerMock));

        $this->ioActionParserDecorator->parse($this->actionDefinitionMock, $this->recipeVariablesContainerMock);
    }

    public function testMustCallIOWriteMethod(): void
    {
        $this->ioMock->expects($this->exactly(2))->method('write');

        $this->ioActionParserDecorator->parse($this->actionDefinitionMock, $this->recipeVariablesContainerMock);
    }
}
