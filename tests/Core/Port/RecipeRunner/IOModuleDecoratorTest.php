<?php

/*
 * This file is part of the "Recipe Runner" project.
 *
 * (c) Víctor Puertas <http://github.com/yosymfony>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RecipeRunner\Cli\Test\Core\Port\RecipeRunner;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RecipeRunner\Cli\Core\Port\RecipeRunner\IOModuleDecorator;
use RecipeRunner\RecipeRunner\IO\IOInterface;

class IOModuleDecoratorTest extends TestCase
{
    /** @var MockObject */
    private $ioMock;

    /** @var IOModuleDecorator */
    private $ioModuleDecorator;

    private $margin = 4;

    public function setUp(): Void
    {
        $this->ioMock = $this->getMockBuilder(IOInterface::class)->getMock();
        $this->ioModuleDecorator = new IOModuleDecorator($this->ioMock, $this->margin);
    }

    /**
    * @expectedException InvalidArgumentException
    * @expectedExceptionMessage Margin cannot be a negative value. Value found: -1.
    */
    public function testMustFailWhenMarginIsANegativeValue(): void
    {
        new IOModuleDecorator($this->ioMock, -1);
    }

    /**
     * @testWith    [true, 1]
     *              [false, 2]
     */
    public function testMustCallWrite(bool $newline, int $verbosity): void
    {
        $message = 'hi';
        $expected = $this->composeExptectedText($message);
        $this->ioMock->expects($this->once())->method('write')->with(
            $this->equalTo($expected),
            $this->equalTo($newline),
            $this->equalTo($verbosity)
        );

        $this->ioModuleDecorator->write($message, $newline, $verbosity);
    }

    public function testWriteMustNotAddThePrefixTheSecondTimeWriteIsCalledWithNewlineFalse(): void
    {
        $message1 = 'hi';
        $message2 = 'you';
        $expected1 = $this->composeExptectedText($message1);
        $expected2 = $message2;
        
        $this->ioMock->expects($this->at(0))->method('write')->with(
            $this->equalTo($expected1),
            $this->equalTo(false)
        );
        $this->ioMock->expects($this->at(1))->method('write')->with(
            $this->equalTo($expected2),
            $this->equalTo(false)
        );

        $this->ioModuleDecorator->write($message1, false);
        $this->ioModuleDecorator->write($message2, false);
    }

    /**
     * @testWith    ["default1"]
     *              ["default2"]
     */
    public function testMustCallAsk(string $default): void
    {
        $message = 'Any question?';
        $expected = $this->composeExptectedText($message);
        $this->ioMock->expects($this->once())->method('ask')->with(
            $this->equalTo($expected),
            $this->equalTo($default)
        );

        $this->ioModuleDecorator->ask($message, $default);
    }

    /**
     * @testWith    [true]
     *              [false]
     */
    public function testMustCallAskConfirmation(bool $default): void
    {
        $message = 'yes or no?';
        $expected = $this->composeExptectedText($message);
        $this->ioMock->expects($this->once())->method('askConfirmation')->with(
            $this->equalTo($expected),
            $this->equalTo($default)
        );

        $this->ioModuleDecorator->askConfirmation($message, $default);
    }

    public function testMustCallAskWithHiddenResponse(): void
    {
        $message = 'your email';
        $expected = $this->composeExptectedText($message);
        $this->ioMock->expects($this->once())->method('askWithHiddenResponse')->with(
            $this->equalTo($expected)
        );

        $this->ioModuleDecorator->askWithHiddenResponse($message);
    }

    /**
     * @testWith    [[1,2,3], 1, 1]
     *              [[1,2,3], 2, 2]
     */
    public function testMustCallAskChoice(array $choices, $default, int $attempts): void
    {
        $message = 'which option do you want?';
        $expected = $this->composeExptectedText($message);
        $this->ioMock->expects($this->once())->method('askChoice')->with(
            $this->equalTo($expected),
            $this->equalTo($choices),
            $this->equalTo($default),
            $this->equalTo($attempts)
        );

        $this->ioModuleDecorator->askChoice($message, $choices, $default, $attempts);
    }

    /**
     * @testWith    [[1,2,3], [1], 1]
     *              [[1,2,3], 2, 2]
     */
    public function testMustCallAskMultiselectChoice(array $choices, $default, int $attempts): void
    {
        $message = 'which option do you want?';
        $expected = $this->composeExptectedText($message);
        $this->ioMock->expects($this->once())->method('askMultiselectChoice')->with(
            $this->equalTo($expected),
            $this->equalTo($choices),
            $this->equalTo($default),
            $this->equalTo($attempts)
        );

        $this->ioModuleDecorator->askMultiselectChoice($message, $choices, $default, $attempts);
    }

    private function composeExptectedText(string $message): string
    {
        return \str_repeat(' ', $this->margin).">{$message}";
    }
}
