<?php

/*
 * This file is part of the "Recipe Runner" project.
 *
 * (c) Víctor Puertas <http://github.com/yosymfony>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RecipeRunner\Cli\Adapter;

use RecipeRunner\RecipeRunner\IO\IOInterface;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

final class ConsoleIO implements IOInterface
{
    /** @var InputInterface */
    protected $input;

    /** @var OutputInterface */
    protected $output;
    
    /** @var HelperSet */
    protected $helperSet;

    /** @var array<int, int> */
    private $verbosityMap;

    public function __construct(InputInterface $input, OutputInterface $output, HelperSet $helperSet)
    {
        $this->input = $input;
        $this->output = $output;
        $this->helperSet = $helperSet;

        $this->verbosityMap = array(
            self::VERBOSITY_QUIET => OutputInterface::VERBOSITY_QUIET,
            self::VERBOSITY_NORMAL => OutputInterface::VERBOSITY_NORMAL,
            self::VERBOSITY_VERBOSE => OutputInterface::VERBOSITY_VERBOSE,
            self::VERBOSITY_VERY_VERBOSE => OutputInterface::VERBOSITY_VERY_VERBOSE,
            self::VERBOSITY_VERY_VERY_VERBOSE => OutputInterface::VERBOSITY_DEBUG,
        );
    }
    
    /**
     * {@inheritdoc}
     */
    public function isInteractive(): bool
    {
        return $this->input->isInteractive();
    }

    /**
     * {@inheritdoc}
     */
    public function write($messages, bool $newline = true, int $verbosity = self::VERBOSITY_NORMAL): void
    {
        $sfVerbosity = $this->verbosityMap[$verbosity];
        
        if ($sfVerbosity > $this->output->getVerbosity()) {
            return;
        }

        $this->output->write($messages, $newline, $sfVerbosity);
    }


    /**
     * {@inheritdoc}
     */
    public function ask(string $question, string $default = ''): string
    {
        $helper = $this->getQuestionHelper();
        $question = new Question($question, $default);
        
        return $helper->ask($this->input, $this->output, $question);
    }

    /**
     * {@inheritdoc}
     */
    public function askConfirmation(string $question, bool $default = true): bool
    {
        $helper = $this->getQuestionHelper();
        $question = new ConfirmationQuestion($question, $default);

        return $helper->ask($this->input, $this->output, $question);
    }

    /**
     * {@inheritdoc}
     */
    public function askWithHiddenResponse(string $question): string
    {
        $helper = $this->getQuestionHelper();
        $question = new Question($question);
        $question->setHidden(true);

        return $helper->ask($this->input, $this->output, $question);
    }

    /**
     * {@inheritdoc}
     */
    public function askChoice(string $question, array $choices, $default, int $attempts = self::INFINITE_ATTEMPTS): string
    {
        $helper = $this->getQuestionHelper();
        $question = new ChoiceQuestion($question, $choices, $default);
        $question->setMaxAttempts($this->convertAttemptValue($attempts));
        $result = $helper->ask($this->input, $this->output, $question);

        return array_search($result, $choices, true);
    }

    /**
     * {@inheritdoc}
     */
    public function askMultiselectChoice(string $question, array $choices, $default, int $attempts = self::INFINITE_ATTEMPTS): array
    {
        $helper = $this->getQuestionHelper();
        $question = new ChoiceQuestion($question, $choices, $default);
        $question->setMaxAttempts($this->convertAttemptValue($attempts));
        $question->setMultiselect(true);
        $result = $helper->ask($this->input, $this->output, $question);

        $results = [];

        foreach ($choices as $index => $choice) {
            if (in_array($choice, $result, true)) {
                $results[] = $index;
            }
        }

        return $results;
    }

    private function getQuestionHelper(): QuestionHelper
    {
        return $this->helperSet->get('question');
    }

    private function convertAttemptValue(int $attempts): ?int
    {
        return $attempts != self::INFINITE_ATTEMPTS ?: null;
    }
}
