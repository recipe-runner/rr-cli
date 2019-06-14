<?php

/*
 * This file is part of the "Recipe Runner" project.
 *
 * (c) Víctor Puertas <http://github.com/yosymfony>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RecipeRunner\Cli\Test\Infrastructure;

use PHPUnit\Framework\TestCase;
use RecipeRunner\Cli\Infrastructure\ConsoleIO;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class ConsoleIOTest extends TestCase
{
    /** @var InputInterface */
    public $inputMock;

    /** @var OutputInterface */
    public $outputMock;

    /** @var HelperSet */
    public $helperMock;

    public function setUp(): void
    {
        $this->inputMock = $this->getMockBuilder(InputInterface::class)->getMock();
        $this->outputMock = $this->getMockBuilder(OutputInterface::class)->getMock();
        $this->helperMock = $this->getMockBuilder(HelperSet::class)->getMock();
    }
    
    public function testIsInteractiveMustReturnTrue()
    {
        $this->inputMock->expects($this->at(0))
            ->method('isInteractive')
            ->will($this->returnValue(true));
        
        $consoleIO = new ConsoleIO($this->inputMock, $this->outputMock, $this->helperMock);
        
        $this->assertTrue($consoleIO->isInteractive());
    }

    public function testIsInteractiveMustReturnFalse()
    {
        $this->inputMock->expects($this->at(0))
            ->method('isInteractive')
            ->will($this->returnValue(false));
        
        $consoleIO = new ConsoleIO($this->inputMock, $this->outputMock, $this->helperMock);
        
        $this->assertFalse($consoleIO->isInteractive());
    }

    public function testWriteMustOutputAMessageWhenMessageVerbosityLevelIsEqualToCurrentVerbosityLevel()
    {
        $message = 'hi user';
        $this->outputMock->expects($this->once())
            ->method('getVerbosity')
            ->willReturn(OutputInterface::VERBOSITY_NORMAL);
        $this->outputMock->expects($this->once())
            ->method('write')
            ->with($this->equalTo($message), $this->equalTo(false));

        $consoleIO = new ConsoleIO($this->inputMock, $this->outputMock, $this->helperMock);

        $consoleIO->write($message, false);
    }

    public function testWriteMustNotOutputAMessageWhenCurrentVerbosityLevelIsQuiet()
    {
        $this->outputMock->expects($this->once())
            ->method('getVerbosity')
            ->willReturn(OutputInterface::VERBOSITY_QUIET);
        $this->outputMock->expects($this->never())
            ->method('write');

        $consoleIO = new ConsoleIO($this->inputMock, $this->outputMock, $this->helperMock);

        $consoleIO->write('hi', false);
    }

    public function testWriteMustNotOutputAMessageWhenCurrentVerbosityLevelIsMinor()
    {
        $this->outputMock->expects($this->once())
            ->method('getVerbosity')
            ->willReturn(OutputInterface::VERBOSITY_NORMAL);
        $this->outputMock->expects($this->never())
            ->method('write');

        $consoleIO = new ConsoleIO($this->inputMock, $this->outputMock, $this->helperMock);

        $consoleIO->write('hi', false, ConsoleIO::VERBOSITY_VERBOSE);
    }

    public function testAskMustReturnTheAnswer(): void
    {
        $questionMock = $this->getMockBuilder(QuestionHelper::class)->getMock();
        $questionMock
            ->expects($this->once())
            ->method('ask')
            ->with(
                $this->isInstanceOf(InputInterface::class),
                $this->isInstanceOf(OutputInterface::class),
                $this->isInstanceOf(Question::class)
            )
            ->willReturn('');

        $this->helperMock
            ->expects($this->once())
            ->method('get')
            ->with($this->equalTo('question'))
            ->will($this->returnValue($questionMock));
            
        $consoleIO = new ConsoleIO($this->inputMock, $this->outputMock, $this->helperMock);
        $consoleIO->ask('Say something');
    }

    public function testAskConfirmationMustReturnTheAnswer(): void
    {
        $default = true;
        $questionMock = $this->getMockBuilder(QuestionHelper::class)->getMock();
        $questionMock
            ->expects($this->once())
            ->method('ask')
            ->with(
                $this->isInstanceOf(InputInterface::class),
                $this->isInstanceOf(OutputInterface::class),
                $this->isInstanceOf(ConfirmationQuestion::class)
            )
            ->willreturn($default);

        $this->helperMock
            ->expects($this->once())
            ->method('get')
            ->with($this->equalTo('question'))
            ->will($this->returnValue($questionMock))
        ;
        $consoleIO = new ConsoleIO($this->inputMock, $this->outputMock, $this->helperMock);
        $consoleIO->askConfirmation('sure?', $default);
    }
    
    public function testAskChoiceMustReturnTheSelectedAnswer(): void
    {
        $selectedValue = 'b';
        $questionMock = $this->getMockBuilder(QuestionHelper::class)->getMock();
        $questionMock
            ->expects($this->once())
            ->method('ask')
            ->with(
                $this->isInstanceOf(InputInterface::class),
                $this->isInstanceOf(OutputInterface::class),
                $this->isInstanceOf(Question::class)
            )
            ->willReturn($selectedValue);

        $this->helperMock
            ->expects($this->once())
            ->method('get')
            ->with($this->equalTo('question'))
            ->will($this->returnValue($questionMock));
            
        $consoleIO = new ConsoleIO($this->inputMock, $this->outputMock, $this->helperMock);
        
        $selectedItem = $consoleIO->askChoice('Please, select an item', ['a', $selectedValue], $selectedValue);

        $expectedIndex = '1';
        
        $this->assertEquals($expectedIndex, $selectedItem);
    }

    public function testAskMultiselectChoiceMustReturnTheSelectedAnswers(): void
    {
        $questionMock = $this->getMockBuilder(QuestionHelper::class)->getMock();
        $questionMock
            ->expects($this->once())
            ->method('ask')
            ->with(
                $this->isInstanceOf(InputInterface::class),
                $this->isInstanceOf(OutputInterface::class),
                $this->isInstanceOf(Question::class)
            )
            ->willReturn(['b', 'c']);

        $this->helperMock
            ->expects($this->once())
            ->method('get')
            ->with($this->equalTo('question'))
            ->will($this->returnValue($questionMock));
            
        $consoleIO = new ConsoleIO($this->inputMock, $this->outputMock, $this->helperMock);
        
        $selectedItems = $consoleIO->askMultiselectChoice('Please, select an item', ['a', 'b', 'c'], 'c');

        $expectedIndexes = ['1', '2'];
        
        $this->assertEquals($expectedIndexes, $selectedItems);
    }
}
