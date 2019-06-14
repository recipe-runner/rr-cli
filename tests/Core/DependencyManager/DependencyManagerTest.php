<?php

/*
 * This file is part of the "Recipe Runner" project.
 *
 * (c) Víctor Puertas <http://github.com/yosymfony>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RecipeRunner\Cli\Test\Core\DependencyManager;

use PHPUnit\Framework\TestCase;
use RecipeRunner\Cli\Core\DependencyManager\DependencyManager;
use RecipeRunner\Cli\Core\Process\ProcessInterface;
use RecipeRunner\Cli\Core\WorkingDirectory\WorkingDirectory;

class DependencyManagerTest extends TestCase
{
    private $processMock;

    /** @var WorkingDirectory */
    private $workingDirectoryMock;

    /** @var DependencyManager */
    private $dependencyManager;

    public function setUp(): void
    {
        $this->processMock = $this->getMockBuilder(ProcessInterface::class)->getMock();
        $this->workingDirectoryMock = $this->getMockBuilder(WorkingDirectory::class)->disableOriginalConstructor()->getMock();
        $this->dependencyManager = new DependencyManager($this->processMock, $this->workingDirectoryMock);
    }

    public function testRunMustExecuteComposerProcess(): void
    {
        $workingPath = '/myFolder';
        $recipeName = 'myRecipe';
        $composerExecutablePath = '/usr/local/bin/composer';
        $composerDir = "{$workingPath}/.rr/{$recipeName}";

        $this->workingDirectoryMock
            ->method('getRecipeInternalDirectory')
            ->willReturn($composerDir);
        $this->processMock
            ->expects($this->once())
            ->method('runPHPScript')
            ->with(
                $this->equalTo($composerExecutablePath),
                $this->equalTo(['update', '--no-dev', '--prefer-dist', '--no-suggest']),
                $this->equalTo($composerDir)
            );

        $this->processMock
            ->expects($this->once())
            ->method('findExecutable')
            ->with(
                $this->equalTo('composer')
            )
            ->willReturn($composerExecutablePath);

        $this->dependencyManager->update($recipeName);
    }

    public function testGenerateManifestFile(): void
    {
        $recipeName = 'myRecipe';
        $this->workingDirectoryMock
            ->expects($this->once())
            ->method('writeRecipeInternalFile')
            ->with(
                $this->equalTo($recipeName),
                $this->equalTo("composer.json"),
                $this->equalTo('{"require":{"vendorName\/package1":"1.*"},"minimum-stability":"dev"}')
            );

        $packages = [
            'vendorName/package1' => '1.*',
        ];
        
        $content = $this->dependencyManager->generateManifestFile($recipeName, $packages);
    }

    public function testIsNecessaryUpdateMustReturnTruenWhenComposerLockFileDoesNoExist(): void
    {
        $recipeName = 'myRecipe';
        $packages = [
            'vendorName/package1' => '1.*',
        ];

        $this->workingDirectoryMock
            ->expects($this->once())
            ->method('existsRecipeInternalFile')
            ->with(
                $this->equalTo($recipeName),
                $this->equalTo("composer.lock")
            )
            ->willReturn(false);

        $this->assertTrue($this->dependencyManager->isNecessaryUpdate($recipeName, $packages));
    }

    public function testIsNecessaryUpdateMustReturnTruenWhenThereAreDifferencesBetweenInstelledPackagesAndTheOnesPassedAsArgument(): void
    {
        $recipeName = 'myRecipe';
        $packages = [
            'vendorName/package1' => '1.*',
        ];
        $composerContentArray = [
            'require' => [
                'vendorName/package2' => '*',
            ],
        ];

        $this->workingDirectoryMock
            ->expects($this->once())
            ->method('existsRecipeInternalFile')
            ->with(
                $this->equalTo($recipeName),
                $this->equalTo("composer.lock")
            )
            ->willReturn(true);
        $this->workingDirectoryMock
            ->expects($this->once())
            ->method('readRecipeInternalFile')
            ->with(
                $this->equalTo($recipeName),
                $this->equalTo("composer.json")
            )
            ->willReturn(\json_encode($composerContentArray));

        $this->assertTrue($this->dependencyManager->isNecessaryUpdate($recipeName, $packages));
    }

    public function testIsNecessaryUpdateMustReturnFalseWhenThereAreNoDifferencesBetweenInstelledPackagesAndTheOnesPassedAsArgument(): void
    {
        $recipeName = 'myRecipe';
        $packages = [
            'vendorName/package1' => '1.*',
        ];
        $composerContentArray = [
            'require' => [
                'vendorName/package1' => '1.*',
            ],
        ];

        $this->workingDirectoryMock
            ->expects($this->once())
            ->method('existsRecipeInternalFile')
            ->with(
                $this->equalTo($recipeName),
                $this->equalTo("composer.lock")
            )
            ->willReturn(true);
        $this->workingDirectoryMock
            ->expects($this->once())
            ->method('readRecipeInternalFile')
            ->with(
                $this->equalTo($recipeName),
                $this->equalTo("composer.json")
            )
            ->willReturn(\json_encode($composerContentArray));

        $this->assertFalse($this->dependencyManager->isNecessaryUpdate($recipeName, $packages));
    }

    public function testIsNecessaryUpdateMustReturnTruenWhenThereAreDifferencesBetweenVersionOfInstelledPackagesAndTheOnesPassedAsArgument(): void
    {
        $recipeName = 'myRecipe';
        $packages = [
            'vendorName/package1' => '1.*',
        ];
        $composerContentArray = [
            'require' => [
                'vendorName/package1' => '2.*',
            ],
        ];

        $this->workingDirectoryMock
            ->expects($this->once())
            ->method('existsRecipeInternalFile')
            ->with(
                $this->equalTo($recipeName),
                $this->equalTo("composer.lock")
            )
            ->willReturn(true);

        $this->workingDirectoryMock
            ->expects($this->once())
            ->method('readRecipeInternalFile')
            ->with(
                $this->equalTo($recipeName),
                $this->equalTo("composer.json")
            )
            ->willReturn(\json_encode($composerContentArray));

        $this->assertTrue($this->dependencyManager->isNecessaryUpdate($recipeName, $packages));
    }

    public function testIsNecessaryUpdateMustReturnTruenWhenThereAreDifferencesBetweenTheNumberOfInstelledPackagesAndTheNumberOfPassedAsArgument(): void
    {
        $recipeName = 'myRecipe';
        $packages = [
            'vendorName/package1' => '1.*',
        ];
        $composerContentArray = [
            'require' => [
                'vendorName/package1' => '1.*',
                'vendorName/package2' => '1.*',
            ],
        ];

        $this->workingDirectoryMock
            ->expects($this->once())
            ->method('existsRecipeInternalFile')
            ->with(
                $this->equalTo($recipeName),
                $this->equalTo("composer.lock")
            )
            ->willReturn(true);
        $this->workingDirectoryMock
            ->expects($this->once())
            ->method('readRecipeInternalFile')
            ->with(
                $this->equalTo($recipeName),
                $this->equalTo("composer.json")
            )
            ->willReturn(\json_encode($composerContentArray));

        $this->assertTrue($this->dependencyManager->isNecessaryUpdate($recipeName, $packages));
    }

    public function testGetModuleClassNamesInstalledMustReturnTheNamespaceOfTheModuleInstalled(): void
    {
        $recipeName = 'myRecipe';
        $composerLockContent = <<<json
        {
            "packages": [
                {
                    "type": "library",
                    "extra": {
                        "branch-alias": {
                            "dev-master": "1.0-dev"
                        },
                        "recipe-runner": {
                            "modules": [
                                "VendorName\\\\Module"
                            ]
                        }
                    }
                }
            ]
        }
json;

        $this->workingDirectoryMock
            ->expects($this->once())
            ->method('readRecipeInternalFile')
            ->with(
                $this->equalTo($recipeName),
                $this->equalTo("composer.lock")
            )
            ->willReturn($composerLockContent);

        $this->assertEquals([
            'VendorName\Module',
        ], $this->dependencyManager->getModuleClassNamesInstalled($recipeName));
    }

    public function testGetModuleClassNamesInstalledMustReturnTheNamespaceOfModulesInstalled(): void
    {
        $recipeName = 'myRecipe';
        $composerLockContent = <<<json
        {
            "packages": [
                {
                    "type": "library",
                    "extra": {
                        "branch-alias": {
                            "dev-master": "1.0-dev"
                        },
                        "recipe-runner": {
                            "modules": [
                                "VendorName1\\\\Module",
                                "VendorName2\\\\Module"
                            ]
                        }
                    }
                }
            ]
        }
json;
            
        $this->workingDirectoryMock
            ->expects($this->once())
            ->method('readRecipeInternalFile')
            ->with(
                $this->equalTo($recipeName),
                $this->equalTo("composer.lock")
            )
            ->willReturn($composerLockContent);

        $this->assertEquals([
            'VendorName1\Module',
            'VendorName2\Module',
        ], $this->dependencyManager->getModuleClassNamesInstalled($recipeName));
    }

    public function testGetModuleClassNamesInstalledMustReturnEmptyArrayWhenThereAreNoModules(): void
    {
        $recipeName = 'myRecipe';
        $composerLockContent = <<<json
        {
            "packages": [
                {
                    "type": "library",
                    "extra": {
                        "branch-alias": {
                            "dev-master": "1.0-dev"
                        }
                    }
                }
            ]
        }        
json;
            
        $this->workingDirectoryMock
            ->expects($this->once())
            ->method('readRecipeInternalFile')
            ->with(
                $this->equalTo($recipeName),
                $this->equalTo("composer.lock")
            )
            ->willReturn($composerLockContent);

        $this->assertEquals([], $this->dependencyManager->getModuleClassNamesInstalled($recipeName));
    }
}
