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
    private $margin;

    /** @var IOInterface */
    private $io;

    public function __construct(IOInterface $io, int $margin = 5)
    {
        if ($margin < 0) {
            throw new InvalidArgumentException("Margin cannot be a negative value. Value found: {$margin}.");
        }

        $this->io = $io;
        $this->margin = \str_repeat(' ', $margin);
    }

    public function isInteractive(): bool
    {
        return $this->io->isInteractive();
    }

    public function write($messages, bool $newline = true, int $verbosity = self::VERBOSITY_NORMAL): void
    {
        $messages = (array) $messages;
        $messagesWithMargin = [];

        foreach ($messages as $value) {
            $messagesWithMargin[] = $this->formatMessage($value);
        }

        $this->io->write($messagesWithMargin, $newline, $verbosity);
    }

    public function ask(string $question, string $default = ''): string
    {
        return $this->io->ask($this->formatMessage($question), $default);
    }

    public function askConfirmation(string $question, bool $default = true): bool
    {
        return $this->io->askConfirmation($this->formatMessage($question), $default);
    }

    public function askWithHiddenResponse(string $question): string
    {
        return $this->io->askWithHiddenResponse($this->formatMessage($question));
    }

    public function askChoice(string $question, array $choices, $default, int $attempts = self::INFINITE_ATTEMPTS): string
    {
        return $this->io->askChoice($this->formatMessage($question), $choices, $default, $attempts);
    }

    public function askMultiselectChoice(string $question, array $choices, $default, int $attempts = self::INFINITE_ATTEMPTS): array
    {
        return $this->io->askMultiselectChoice($this->formatMessage($question), $choices, $default, $attempts);
    }

    private function formatMessage(string $message): string
    {
        return "{$this->margin}>{$message}";
    }
}
