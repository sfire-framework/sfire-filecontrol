<?php
/**
 * sFire Framework (https://sfire.io)
 *
 * @link      https://github.com/sfire-framework/ for the canonical source repository
 * @copyright Copyright (c) 2014-2020 sFire Framework.
 * @license   http://sfire.io/license BSD 3-CLAUSE LICENSE
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use sFire\FileControl\File;

final class FileTest extends TestCase {


    /**
     * The path to the assets folder
     * @var string
     */
    private string $path;


    /**
     * A File instancew
     * @var File
     */
    private File $file;


    /**
     * Constructor
     */
    public function setUp(): void {

        $this -> path = getcwd() . DIRECTORY_SEPARATOR . 'test' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'file'. DIRECTORY_SEPARATOR;
        $this -> file = new File($this -> path . 'file.txt');
        $this -> file -> delete();
    }


    /**
     * Testing creating a new file
     * Testing if file is readable
     * Testing if file is writable
     * Testing if file exists
     * @return void
     */
    public function testCreatingFile(): void {

        $this -> assertFalse($this -> file -> isReadable());
        $this -> assertFalse($this -> file -> isWritable());
        $this -> assertFalse($this -> file -> exists());

        $this -> file -> create();
        $this -> file -> setOwner('administrator');
        $this -> file -> setGroupId(1000);

        $this -> assertTrue($this -> file -> isReadable());
        $this -> assertTrue($this -> file -> isWritable());
        $this -> assertTrue($this -> file -> exists());

        $this -> file -> delete();
    }


    /**
     * Testing changing the base name of a file
     * @return void
     */
    public function testChangingBaseName(): void {

        $this -> file -> create();
        $this -> file -> setBaseName('new-name.txt');
        $this -> assertEquals($this -> file -> getBaseName(), 'new-name.txt');
        $this -> assertTrue(file_exists($this -> path . 'new-name.txt'));

        $this -> file -> delete();
    }


    /**
     * Testing changing the name of a file
     * @return void
     */
    public function testChangingName(): void {

        $this -> file -> create();
        $this -> file -> setName('new-name');

        $this -> assertEquals($this -> file -> getName(), 'new-name');
        $this -> assertTrue(file_exists($this -> path . 'new-name.txt'));

        $this -> file -> delete();
    }


    /**
     * Testing the mime type of a file
     * @return void
     */
    public function testMime(): void {

        $this -> file -> create();
        $this -> assertEquals($this -> file -> getMimeType(), 'text/plain');
        $this -> file -> delete();
    }


    /**
     * Testing changing the parent path of a file (moving the file)
     * @return void
     */
    public function testChangingBasePath(): void {

        $this -> file -> create();
        $this -> file -> setBasePath($this -> path);

        $this -> assertEquals($this -> file -> getBasePath(), $this -> path);
        $this -> assertTrue(file_exists($this -> path . 'file.txt'));

        $this -> file -> delete();
    }


    /**
     * Testing changing the extension of a file
     * @return void
     */
    public function testChangingExtension(): void {

        $this -> file -> create();
        $this -> file -> setExtension('.tmp');
        $this -> assertEquals($this -> file -> getExtension(), 'tmp');
        $this -> assertTrue(file_exists($this -> path . 'file.tmp'));
        $this -> file -> delete();

        $this -> file -> create();
        $this -> file -> setExtension('tmp');
        $this -> assertEquals($this -> file -> getExtension(), 'tmp');
        $this -> assertTrue(file_exists($this -> path . 'file.tmp'));
        $this -> file -> delete();
    }


    /**
     * Testing changing the extension of a file
     * Testing appending text to a file
     * @return void
     */
    public function testFileSize(): void {

        $this -> file -> create();
        $this -> file -> append('test');
        $this -> assertEquals($this -> file -> getFileSize(), 4);
        $this -> assertEquals($this -> file -> getContent(), 'test');
        $this -> file -> delete();
    }


    /**
     * Testing changing the modification time of a file
     * @return void
     */
    public function testModificationTime(): void {

        $this -> file -> create();
        $this -> assertEquals($this -> file -> getModificationTime(), time());

        $this -> file -> setModificationTime(time() - 1000);
        $this -> assertEquals($this -> file -> getModificationTime(), time() - 1000);

        $this -> file -> delete();
    }


    /**
     * Testing changing the access time of a file
     * @return void
     */
    public function testAccessTime(): void {

        $this -> file -> create();
        $this -> assertEquals($this -> file -> getAccessTime(), time());

        $this -> file -> setAccessTime(time() - 1000);
        $this -> assertEquals($this -> file -> getAccessTime(), time() - 1000);

        $this -> file -> delete();
    }


    /**
     * Testing changing the owner of a file
     * @return void
     */
    public function testChangingOwner(): void {

        $this -> file -> create();

        $this -> file -> setOwnerId($this -> file -> getOwnerId());
        $this -> assertEquals($this -> file -> getOwnerId(), $this -> file -> getOwnerId());

        $this -> file -> setOwner($this -> file -> getOwner());
        $this -> assertEquals($this -> file -> getOwner(), $this -> file -> getOwner());

        $this -> file -> delete();
    }


    /**
     * Testing changing the group of a file
     * @return void
     */
    public function testChangingGroup(): void {

        $this -> file -> create();

        $this -> file -> setGroupId($this -> file -> getGroupId());
        $this -> assertEquals($this -> file -> getGroupId(), $this -> file -> getGroupId());

        $this -> file -> delete();
    }


    /**
     * Testing retrieving image dimensions
     * @return void
     */
    public function testRetrievingDimensions(): void {

        $file = new File($this -> path . 'image.png');
        $this -> assertEquals($file -> getWidth(), 120);
        $this -> assertEquals($file -> getHeight(), 120);
    }


    /**
     * Testing retrieving camera info
     * @return void
     */
    public function testCameraInfo(): void {

        $info = [
            'camera' => [
                'Make' => null,
                'Model' => null,
                'Orientation' => null,
                'XResolution' => null,
                'YResolution' => null,
                'ResolutionUnit' => null,
                'Software' => null,
                'ExposureTime' => null,
                'FNumber' => null,
                'ISOSpeedRatings' => null,
                'ShutterSpeedValue' => null,
                'ApertureValue' => null,
                'BrightnessValue' => null,
                'ExposureBiasValue' => null,
                'MaxApertureValue' => null,
                'MeteringMode' => null,
                'Flash' => null,
            ],
            'created' => null,
            'mime' => 'image/jpeg'
        ];

        $file = new File($this -> path . 'image.jpg');
        $this -> assertEquals($file -> getCameraInfo(), $info);
    }


    /**
     * Testing retrieving image dimensions
     * @return void
     */
    public function testCopy(): void {

        $this -> file -> create();
        $this -> file -> copy($this -> path, 'copy.txt');
        $this -> assertTrue(file_exists($this -> path . 'copy.txt'));

        $file = new File($this -> path . 'copy.txt');
        $file -> delete();
        $this -> file -> delete();
    }


    /**
     * Testing append text to a file
     * Testing prepend text to a file
     * Testing flushing text to a file
     * @return void
     */
    public function testSettingText(): void {

        $this -> file -> create();
        $this -> file -> append('World!');
        $this -> file -> prepend('Hello ');
        $this -> assertEquals($this -> file -> getContent(), 'Hello World!');
        $this -> file -> flush();
        $this -> assertEquals($this -> file -> getContent(), null);
        $this -> file -> delete();
    }
}