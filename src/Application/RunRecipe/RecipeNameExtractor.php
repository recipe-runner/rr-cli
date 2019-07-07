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

/**
 * Recipe name extractor.
 *
 * @author Víctor Puertas <vpgugr@gmail.com>
 */
class RecipeNameExtractor
{
    /**
     * Returns the recipe name without extension.
     *
     * @param string $recipeFilename The recipe filename. e.g: "myrecipe.rr.yml"
     *
     * @return string Recipe name. e.g: "myrecipe.rr"
     */
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
