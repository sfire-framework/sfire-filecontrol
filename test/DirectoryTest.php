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
use sFire\FileControl\Directory;

final class DirectoryTest extends TestCase {


	/**
	 * The path to the assets folder
	 * @var string
	 */
	private string $path;


    /**
     * A Directory instance
     * @var Directory
     */
    private Directory $directory;


	/**
     * Constructor
     */
    public function setUp(): void {

        $this -> path = getcwd() . DIRECTORY_SEPARATOR . 'test' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'directory'. DIRECTORY_SEPARATOR;
        $this -> directory = new Directory($this -> path . 'test');
    }


	/**
	 * Testing creating a new directory
	 * Testing if directory is readable
	 * Testing if directory is writable
	 * Testing if directory exists
	 * @return void
	 */
    public function testCreatingDirectory(): void {

    	$this -> assertFalse($this -> directory -> isReadable());
        $this -> assertFalse($this -> directory -> isWritable());
        $this -> assertFalse($this -> directory -> exists());

    	$this -> directory -> create();
    	$this -> directory -> setOwner('administrator');
    	$this -> directory -> setGroupId(1000);

        $this -> assertTrue($this -> directory -> isReadable());
        $this -> assertTrue($this -> directory -> isWritable());
        $this -> assertTrue($this -> directory -> exists());

        $this -> directory -> delete();
    }


    /**
     * Testing changing the name of a directory
     * @return void
     */
    public function testChangingName(): void {

        $this -> directory -> create();
        $this -> directory -> setName('new-name');

        $this -> assertEquals($this -> directory -> getName(), 'new-name');
        $this -> assertTrue(is_dir($this -> path . 'new-name'));

        $this -> directory -> delete();
    }


    /**
     * Testing changing the extension of a directory
     * Testing appending text to a directory
     * @return void
     */
    public function testFileSize(): void {

        $this -> directory -> create();
        file_put_contents($this -> directory -> getPath() . 'test.txt', 'test');
        $this -> assertEquals($this -> directory -> getSize(), 4);
        $this -> assertEquals($this -> directory -> getContent(Directory::TYPE_ARRAY), ['test.txt']);
        $this -> assertEquals($this -> directory -> getContent(Directory::TYPE_JSON), '["test.txt"]');
        $this -> assertIsObject($this -> directory -> getContent(Directory::TYPE_OBJECT));
        $this -> assertIsArray($this -> directory -> getContent(Directory::TYPE_DEFAULT));
        $this -> directory -> delete();
    }


    /**
     * Testing changing the modification time of a directory
     * @return void
     */
    public function testModificationTime(): void {

        $this -> directory -> create();
        $this -> assertEquals($this -> directory -> getModificationTime(), time());

        $this -> directory -> setModificationTime(time() - 1000);
        $this -> assertEquals($this -> directory -> getModificationTime(), time() - 1000);

        $this -> directory -> delete();
    }


    /**
     * Testing retrieving the parent directory
     * @return void
     */
    public function testBasePath(): void {

        $this -> directory -> create();
        $this -> assertEquals($this -> directory -> getBasePath(), $this -> path);

        $directory = new Directory($this -> path . 'parent');
        $directory -> create();

        $this -> directory -> setBasePath($directory -> getPath());
        $this -> assertEquals($this -> directory -> getBasePath(), $this -> path . 'parent' . DIRECTORY_SEPARATOR);

        $directory -> delete();
    }


    /**
     * Testing changing the access time of a directory
     * @return void
     */
    public function testAccessTime(): void {

        $this -> directory -> create();
        $this -> assertEquals($this -> directory -> getAccessTime(), time());

        $this -> directory -> setAccessTime(time() - 1000);
        $this -> assertEquals($this -> directory -> getAccessTime(), time() - 1000);

        $this -> directory -> delete();
    }


    /**
     * Testing clearing a directory
     * @return void
     */
    public function testClear(): void {

        $this -> directory -> create();
        touch($this -> directory -> getPath() . 'test.txt');
        $this -> assertEquals($this -> directory -> getContent(Directory::TYPE_ARRAY), ['test.txt']);
        $this -> directory -> clear();
        $this -> assertEquals($this -> directory -> getContent(Directory::TYPE_ARRAY), []);

        $this -> directory -> delete();
    }


    /**
     * Testing changing owner id of a directory
     * @return void
     */
    public function testChmod(): void {

        $this -> directory -> create();
        $this -> assertTrue($this -> directory -> chmod($this -> directory -> getOwnerId()));
        $this -> directory -> delete();
    }


    /**
     * Testing changing the owner of a directory
     * @return void
     */
    public function testChangingOwner(): void {

        $this -> directory -> create();

        $this -> directory -> setOwnerId($this -> directory -> getOwnerId());
        $this -> assertEquals($this -> directory -> getOwnerId(), $this -> directory -> getOwnerId());

        $this -> directory -> setOwner($this -> directory -> getOwner());
        $this -> assertEquals($this -> directory -> getOwner(), $this -> directory -> getOwner());

        $this -> directory -> delete();
    }


    /**
     * Testing changing the group of a directory
     * @return void
     */
    public function testChangingGroup(): void {

        $this -> directory -> create();

        $this -> directory -> setGroupId($this -> directory -> getGroupId());
        $this -> assertEquals($this -> directory -> getGroupId(), $this -> directory -> getGroupId());

        $this -> directory -> delete();
    }


    /**
     * Testing retrieving image dimensions
     * @return void
     */
    public function testCopy(): void {

        $directory = new Directory($path = $this -> path . 'copy');
        $directory -> create();

        $this -> directory -> create();
        touch($this -> directory -> getPath() . 'test.txt');

        $this -> directory -> copy($directory -> getPath());
        $this -> assertTrue(is_file($directory -> getPath() . 'test.txt'));

        $this -> directory -> delete();
        $directory -> delete();
    }
}