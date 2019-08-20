<?php

/*
 * This file is part of the "Recipe Runner" project.
 *
 * (c) Víctor Puertas <http://github.com/yosymfony>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RecipeRunner\Cli\Test\Core\WorkingDirectory;

use PHPUnit\Framework\TestCase;
use RecipeRunner\Cli\Core\Port\FilesystemInterface;
use RecipeRunner\Cli\Core\WorkingDirectory\WorkingDirectory;

class WorkingDirectoryTest extends TestCase
{
    public $filesystemMock;

    public function setUp(): void
    {
        $this->filesystemMock = $this->getMockBuilder(FilesystemInterface::class)->getMock();
    }

    public function testGetWorkingDirMustReturnTheWorkingDirectory(): void
    {
        $workingDir = '/mypath';
        $wd = new WorkingDirectory($workingDir, $this->filesystemMock);

        $this->assertEquals($workingDir, $wd->getWorkingDirectory());
    }

    public function testWriteRecipeInternalFileMustCreateTheFileWithTheContent(): void
    {
        $recipeName = 'myrecipe';
        $workingDir = '/mypath';

        $this->filesystemMock
            ->expects($this->once())
            ->method('dumpFile')
            ->with(
                $this->equalTo("/mypath/.rr/{$recipeName}/myfile.json"),
                $this->equalTo('content')
            );

        $wd = new WorkingDirectory($workingDir, $this->filesystemMock);
        $wd->writeRecipeInternalFile($recipeName, 'myfile.json', 'content');
    }

    /**
    * @expectedException InvalidArgumentException
    * @expectedExceptionMessage The param "recipeName" must not be empty.
    */
    public function testWriteRecipeInternalFileMustThrownAnExceptionWhenRecipeParamIsEmpty(): void
    {
        $workingDir = '/mypath';
        $wd = new WorkingDirectory($workingDir, $this->filesystemMock);
        $wd->writeRecipeInternalFile('', 'myfile.json', 'content');
    }

    /**
    * @expectedException InvalidArgumentException
    * @expectedExceptionMessage The param "filename" must not be empty.
    */
    public function testWriteRecipeInternalFileMustThrownAnExceptionWhenFilenameParamIsEmpty(): void
    {
        $workingDir = '/mypath';
        $wd = new WorkingDirectory($workingDir, $this->filesystemMock);
        $wd->writeRecipeInternalFile('myrecipe', '', 'content');
    }

    /**
    * @expectedException InvalidArgumentException
    * @expectedExceptionMessage The param "workingDir" must not be empty.
    */
    public function testConstructorMustThrownExceptionWhenWorkingDirIsEmpty(): void
    {
        $workingDir = '';
        $wd = new WorkingDirectory($workingDir, $this->filesystemMock);
    }

    public function testGetRecipeInternalFolderMustReturnThePath(): void
    {
        $recipeName = 'myrecipe';
        $workingDir = '/mypath';

        $wd = new WorkingDirectory($workingDir, $this->filesystemMock);
        $path = $wd->getRecipeInternalDirectory($recipeName);

        $this->assertEquals("/mypath/.rr/{$recipeName}", $path);
    }

    public function testReadRecipeInternalFileMustReturnTheContent(): void
    {
        $recipeName = 'myrecipe';
        $fileContent = 'content';
        $workingDir = '/mypath';
        $this->filesystemMock
            ->expects($this->once())
            ->method('readFile')
            ->with(
                $this->equalTo("/mypath/.rr/{$recipeName}/myfile.txt")
            )->will($this->returnValue($fileContent));

        $wd = new WorkingDirectory($workingDir, $this->filesystemMock);

        $this->assertEquals($fileContent, $wd->readRecipeInternalFile($recipeName, 'myfile.txt'));
    }

    /**
    * @expectedException InvalidArgumentException
    * @expectedExceptionMessage The param "filename" must not be empty.
    */
    public function testReadRecipeInternalFileMustThrownAnExceptionWhenFilenameParamIsEmpty(): void
    {
        $workingDir = '/mypath';
        $wd = new WorkingDirectory($workingDir, $this->filesystemMock);
        $wd->readRecipeInternalFile('myrecipe', '');
    }

    /**
    * @expectedException InvalidArgumentException
    * @expectedExceptionMessage The param "recipeName" must not be empty.
    */
    public function testReadRecipeInternalFileMustThrownAnExceptionWhenRecipeNameParamIsEmpty(): void
    {
        $workingDir = '/mypath';
        $wd = new WorkingDirectory($workingDir, $this->filesystemMock);
        $wd->readRecipeInternalFile('', 'myfile.txt');
    }

    public function testReadFileMustReturnTheContent(): void
    {
        $filename = 'myRecipe.yml';
        $fileContent = 'content';
        $workingDir = '/mypath';
        $this->filesystemMock
            ->expects($this->once())
            ->method('readFile')
            ->with(
                $this->equalTo("/mypath/myRecipe.yml")
            )->will($this->returnValue($fileContent));

        $wd = new WorkingDirectory($workingDir, $this->filesystemMock);

        $this->assertEquals($fileContent, $wd->readFile($filename));
    }

    /**
    * @expectedException InvalidArgumentException
    * @expectedExceptionMessage The param "filename" must not be empty.
    */
    public function testReadFileMustThrownAnExceptionWhenFilenameParamIsEmpty(): void
    {
        $workingDir = '/mypath';
        $wd = new WorkingDirectory($workingDir, $this->filesystemMock);
        $wd->readFile('');
    }

    public function testExistsRecipeInternalFileMustReturnTrueWhenTheFileExists(): void
    {
        $recipeName = 'myrecipe';
        $workingDir = '/mypath';
        $filename = 'myfile.txt';
        $this->filesystemMock
            ->expects($this->once())
            ->method('exists')
            ->with(
                $this->equalTo("/mypath/.rr/{$recipeName}/{$filename}")
            )->will($this->returnValue(true));

        $wd = new WorkingDirectory($workingDir, $this->filesystemMock);

        $this->assertTrue($wd->existsRecipeInternalFile($recipeName, $filename));
    }

    public function testExistsRecipeInternalFileMustReturnFalseWhenTheFileExists(): void
    {
        $recipeName = 'myrecipe';
        $workingDir = '/mypath';
        $filename = 'myfile.txt';
        $this->filesystemMock
            ->expects($this->once())
            ->method('exists')
            ->with(
                $this->equalTo("/mypath/.rr/{$recipeName}/{$filename}")
            )->will($this->returnValue(false));

        $wd = new WorkingDirectory($workingDir, $this->filesystemMock);

        $this->assertFalse($wd->existsRecipeInternalFile($recipeName, $filename));
    }

    /**
    * @expectedException InvalidArgumentException
    * @expectedExceptionMessage The param "filename" must not be empty.
    */
    public function testExistsRecipeInternalFileMustThrownAnExceptionWhenFilenameParamIsEmpty(): void
    {
        $workingDir = '/mypath';
        $wd = new WorkingDirectory($workingDir, $this->filesystemMock);
        $wd->existsRecipeInternalFile('myrecipe', '');
    }

    /**
    * @expectedException InvalidArgumentException
    * @expectedExceptionMessage The param "recipeName" must not be empty.
    */
    public function testExistsRecipeInternalFileMustThrownAnExceptionWhenRecipeNameParamIsEmpty(): void
    {
        $workingDir = '/mypath';
        $wd = new WorkingDirectory($workingDir, $this->filesystemMock);
        $wd->existsRecipeInternalFile('', 'myfile.txt');
    }
}
