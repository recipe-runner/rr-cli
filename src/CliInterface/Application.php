<?php

/*
 * This file is part of the "Recipe Runner" project.
 *
 * (c) Víctor Puertas <http://github.com/yosymfony>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RecipeRunner\Cli\CliInterface;

use RecipeRunner\Cli\CliInterface\Command;
use Symfony\Component\Console\Application as BaseApplication;

class Application extends BaseApplication
{
    const VERSION = '0.0.1';

    private static $logo = '
    ____     ___    __  ____  ____   ___      ____  __ __  ____   ____     ___  ____      
    |    \   /  _]  /  ]|    ||    \ /  _]    |    \|  |  ||    \ |    \   /  _]|    \     
    |  D  ) /  [_  /  /  |  | |  o  )  [_     |  D  )  |  ||  _  ||  _  | /  [_ |  D  )    
    |    / |    _]/  /   |  | |   _/    _]    |    /|  |  ||  |  ||  |  ||    _]|    /     
    |    \ |   [_/   \_  |  | |  | |   [_     |    \|  :  ||  |  ||  |  ||   [_ |    \     
    |  .  \|     \     | |  | |  | |     |    |  .  \     ||  |  ||  |  ||     ||  .  \    
    |__|\_||_____|\____||____||__| |_____|    |__|\_|\__,_||__|__||__|__||_____||__|\_|    
                                                                                           
    by Víctor Puertas.
    
    ';

    public function __construct()
    {
        parent::__construct('RR - Recipe Runner', self::VERSION);
    }

    public function getHelp()
    {
        return self::$logo.parent::getHelp();
    }

    protected function getDefaultCommands()
    {
        $commands = array_merge(parent::getDefaultCommands(), [
            new Command\RunCommand(),
        ]);
        
        return $commands;
    }
}
