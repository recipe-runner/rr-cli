<?php

/*
 * This file is part of the "Recipe Runner" project.
 *
 * (c) Víctor Puertas <http://github.com/yosymfony>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RecipeRunner\Cli\Test\Application\RunRecipe;

use PHPUnit\Framework\TestCase;
use RecipeRunner\Cli\Application\RunRecipe\RecipeNameExtractor;

class RecipeNameExtractorTest extends TestCase
{
    public function testExtractRecipeNameMustReturnTheNameOfTheRecipe(): void
    {
        $extractor = new RecipeNameExtractor();

        $this->assertEquals('example', $extractor->extractNameFromFilename('example.yml'));
    }

    public function testExtractRecipeNameMustReturnTheNameOfTheRecipeWhenThereIsAComposedExtension(): void
    {
        $extractor = new RecipeNameExtractor();

        $this->assertEquals('example.rr', $extractor->extractNameFromFilename('example.rr.yml'));
    }

    /**
    * @expectedException InvalidArgumentException
    * @expectedExceptionMessage Only YAML recipes are allowed.
    */
    public function testExtractNameFromFilenameMustFailWhenTheExtensionIsNoYAML(): void
    {
        $extractor = new RecipeNameExtractor();
        $extractor->extractNameFromFilename('example.txt');
    }
}
