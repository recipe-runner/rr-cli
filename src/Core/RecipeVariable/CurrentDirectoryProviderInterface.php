<?php

/*
 * This file is part of the "Recipe Runner" project.
 *
 * (c) Víctor Puertas <http://github.com/yosymfony>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RecipeRunner\Cli\Core\RecipeVariable;

/**
 * Interface for current directory providers.
 *
 * @author Víctor Puertas <vpgugr@gmail.com>
 */
interface CurrentDirectoryProviderInterface
{
    /**
     * Returns the current directory.
     *
     * @return string The current directory.
     */
    public function getCurrentDirectory(): string;
}
