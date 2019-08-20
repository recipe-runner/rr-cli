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

use RecipeRunner\Cli\Core\RecipeVariable\CurrentDirectoryProviderInterface;

/**
 * Current directory provider.
 *
 * @author Víctor Puertas <vpgugr@gmail.com>
 */
class CurrentDirectoryProvider implements CurrentDirectoryProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getCurrentDirectory(): string
    {
        return \getcwd();
    }
}
