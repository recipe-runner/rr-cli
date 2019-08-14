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

use InvalidArgumentException;
use RecipeRunner\RecipeRunner\IO\IOInterface;

/**
 * Decorator for IO operations.
 *
 * @author Víctor Puertas <vpgugr@gmail.com>
 */
final class IOModuleDecorator implements IOInterface
{
    /** @var int */
    private $margin;

    /** @var bool */
    private $isTheBeginningOfLine = true;

    /** @var IOInterface */
    private $io;

    /**
     * Constructor.
     *
     * @param IOInterface $io
     * @param int $margin Number of spaces before the text begin.
     */
    public function __construct(IOInterface $io, int $margin = 5)
    {
        if ($margin < 0) {
            throw new InvalidArgumentException("Margin cannot be a negative value. Value found: {$margin}.");
        }

        $this->io = $io;
        $this->margin = \str_repeat(' ', $margin);
    }

    /**
     * {@inheritdoc}
     */
    public function isInteractive(): bool
    {
        return $this->io->isInteractive();
    }

    /**
     * {@inheritdoc}
     */
    public function write($messages, bool $newline = true, int $verbosity = self::VERBOSITY_NORMAL): void
    {
        if (\is_string($messages)) {
            $messages = $this->formatMessage($messages);
            $this->isTheBeginningOfLine = $newline;
            $this->io->write($messages, $newline, $verbosity);

            return;
        }

        $this->isTheBeginningOfLine = true;
        $messagesWithMargin = [];

        foreach ($messages as $value) {
            $messagesWithMargin[] = $this->formatMessage($value);
        }

        $this->io->write($messagesWithMargin, $newline, $verbosity);
    }

    /**
     * {@inheritdoc}
     */
    public function ask(string $question, string $default = ''): string
    {
        $this->isTheBeginningOfLine = true;

        return $this->io->ask($this->formatMessage($question), $default);
    }

    /**
     * {@inheritdoc}
     */
    public function askConfirmation(string $question, bool $default = true): bool
    {
        $this->isTheBeginningOfLine = true;

        return $this->io->askConfirmation($this->formatMessage($question), $default);
    }

    /**
     * {@inheritdoc}
     */
    public function askWithHiddenResponse(string $question): string
    {
        $this->isTheBeginningOfLine = true;

        return $this->io->askWithHiddenResponse($this->formatMessage($question));
    }

    /**
     * {@inheritdoc}
     */
    public function askChoice(string $question, array $choices, $default, int $attempts = self::INFINITE_ATTEMPTS): string
    {
        $this->isTheBeginningOfLine = true;

        return $this->io->askChoice($this->formatMessage($question), $choices, $default, $attempts);
    }

    /**
     * {@inheritdoc}
     */
    public function askMultiselectChoice(string $question, array $choices, $default, int $attempts = self::INFINITE_ATTEMPTS): array
    {
        $this->isTheBeginningOfLine = true;

        return $this->io->askMultiselectChoice($this->formatMessage($question), $choices, $default, $attempts);
    }

    private function formatMessage(string $message): string
    {
        if ($this->isTheBeginningOfLine) {
            return "{$this->margin}>{$message}";
        }

        return $message;
    }
}
