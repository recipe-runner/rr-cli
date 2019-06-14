<?php

/*
 * This file is part of the "Recipe Runner" project.
 *
 * (c) Víctor Puertas <http://github.com/yosymfony>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RecipeRunner\Cli\Application\RunRecipe;

use InvalidArgumentException;
use SplFileInfo;

class RecipeNameExtractor
{
    public function extractNameFromFilename(string $recipeFilename): string
    {
        $fileInfo = new SplFileInfo($recipeFilename);
        $extension = $fileInfo->getExtension();

        if ($extension != 'yml') {
            throw new InvalidArgumentException('Only YAML recipes are allowed.');
        }

        return $fileInfo->getBasename(".{$extension}");
    }
}
