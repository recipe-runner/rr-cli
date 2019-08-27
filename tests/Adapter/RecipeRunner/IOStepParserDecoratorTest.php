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
use RecipeRunner\Cli\Adapter\RecipeRunner\IOStepParserDecorator;
use RecipeRunner\RecipeRunner\Block\Step\StepParserInterface;
use RecipeRunner\RecipeRunner\Definition\StepDefinition;
use RecipeRunner\RecipeRunner\IO\IOInterface;
use RecipeRunner\RecipeRunner\RecipeVariablesContainer;

class IOStepParserDecoratorTest extends TestCase
{
    /** @var MockObject */
    private $ioMock;

    /** @var MockObject */
    private $stepParserMock;

    /** @var MockObject */
    private $stepDefinitionMock;

    /** @var MockObject */
    private $recipeVariablesContainerMock;

    /** @var IOStepParserDecorator */
    private $ioStepParserDecorator;

    public function setUp(): Void
    {
        $this->ioMock = $this->getMockBuilder(IOInterface::class)->getMock();
        $this->stepParserMock = $this->getMockBuilder(StepParserInterface::class)->getMock();
        $this->ioStepParserDecorator = new IOStepParserDecorator($this->ioMock, $this->stepParserMock);
        $this->stepDefinitionMock = $this->getMockBuilder(StepDefinition::class)->disableOriginalConstructor()->getMock();
        $this->recipeVariablesContainerMock = $this->getMockBuilder(RecipeVariablesContainer::class)->disableOriginalConstructor()->getMock();
    }

    public function testMustCallOriginalStepParser(): void
    {
        $this->stepParserMock->expects($this->once())->method('parse')
            ->with($this->equalTo($this->stepDefinitionMock), $this->equalTo($this->recipeVariablesContainerMock));

        $this->ioStepParserDecorator->parse($this->stepDefinitionMock, $this->recipeVariablesContainerMock);
    }

    public function testMustCallIOWriteMethod(): void
    {
        $this->ioMock->expects($this->once())->method('write');

        $this->ioStepParserDecorator->parse($this->stepDefinitionMock, $this->recipeVariablesContainerMock);
    }
}
