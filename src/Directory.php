<?php
/**
 * sFire Framework (https://sfire.io)
 *
 * @link      https://github.com/sfire-framework/ for the canonical source repository
 * @copyright Copyright (c) 2014-2020 sFire Framework.
 * @license   http://sfire.io/license BSD 3-CLAUSE LICENSE
 */

declare(strict_types=1);

namespace sFire\FileControl;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use sFire\FileControl\Exception\RuntimeException;


/**
 * Class Directory
 * @package sFire\FileControl
 */
class Directory {


    /**
     * Contains the types of elements that should be returned
     * @var string
     */
	public const TYPE_ARRAY   = 'array';
    public const TYPE_JSON 	  = 'json';
    public const TYPE_OBJECT  = 'object';
    public const TYPE_DEFAULT = 'default';


    /**
     * Contains the current directory path
     * @var null|string
     */
    private ?string $directory = null;


	/**
	 * Constructor
	 * @param string $directory The path to the directory
	 */
	public function __construct(string $directory) {
	    $this -> directory = rtrim($directory, DIRECTORY_SEPARATOR);
	}


    /**
     * Sets a new name (renaming) for the current directory
     * @param string $name The new name for the directory
     * @return bool Returns true on success, false otherwise
     */
    public function setName(string $name): bool {
        return $this -> rename($name);
    }


    /**
     * Returns the directory name
     * @return null|string
     */
    public function getName(): ?string {

        $name = explode(DIRECTORY_SEPARATOR, $this -> directory);
        $name = end($name);

        return $name;
    }


    /**
     * Returns the path of the directory
     * @return string The path of the directory
     */
    public function getPath(): ?string {
        return rtrim($this -> directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }


    /**
     * Returns the parent path of a directory
     * @return null|string The parent path of a directory
     */
    public function getBasePath(): ?string {
        return dirname($this -> directory) . DIRECTORY_SEPARATOR;
    }


    /**
     * Sets a new parent path for the current directory
     * @param string $path The new parent path
     * @return bool
     */
    public function setBasePath(string $path): bool {
        return $this -> move($path);
    }


    /**
     * Sets the modification time
     * @param int $time A unix timestamp
     * @return bool Returns true on success, false otherwise
     */
    public function setModificationTime(int $time): bool {

        $touched = false;

        if(true === $this -> exists()) {

            $touched = touch($this -> directory, $time, $this -> getAccessTime());
            clearstatcache();
        }

        return $touched;
    }


    /**
     * Returns the modification time
     * @return bool|int False if modification time could not be read, Unix timestamp as modification time if modification could be read
     */
    public function getModificationTime() {
        return @filemtime($this -> directory);
    }


    /**
     * Sets the id of the owner of a directory
     * @param int $id The id of the user
     * @return bool Returns true on success, false otherwise
     */
    public function setOwnerId(int $id): bool {
        return $this -> chown($id);
    }


    /**
     * Returns the owner id of the current directory
     * @return bool|int False if directory owner could not be read. The id of the owner as an Int if directory owner could be read.
     */
    public function getOwnerId() {

        $owner = @fileowner($this -> directory);

        if(false !== $owner) {
            return (int) $owner;
        }

        return false;
    }


    /**
     * Sets the id of the owner of a directory
     * @param string $name The name of the user
     * @return bool Returns true on success, false otherwise
     */
    public function setOwner(string $name): bool {

        if(false !== $this -> exists()) {
            return chown($this -> getPath(), $name);
        }

        return false;
    }


    /**
     * Returns the owner id of the current directory
     * @return bool|string False if directory owner could not be read. The name of the owner as a string if directory owner could be read.
     */
    public function getOwner() {

        $id = $this -> getOwnerId();

        if(false !== $id) {

            if($owner = @posix_getpwuid($id)) {
                return $owner['name'] ?? false;
            }
        }

        return false;
    }


    /**
     * Sets the access time of a directory
     * @param int $time Unix timestamp as time
     * @return bool Returns true on success, false otherwise
     */
    public function setAccessTime(int $time): bool {

        $touched = false;

        if(true === $this -> exists()) {

            $touched = touch($this -> directory, $this -> getModificationTime(), $time);
            clearstatcache();
        }

        return $touched;
    }


    /**
     * Returns the last access time of a directory
     * @return bool|int The last access time as a Unix Timestamp
     */
    public function getAccessTime() {
        return @fileatime($this -> directory);
    }


    /**
     * Returns the group id of the current directory
     * @return bool|int
     */
    public function getGroupId() {
        return @filegroup($this -> directory);
    }


    /**
     * Sets the group id of a directory
     * @param int $id The group id for the directory
     * @return bool Returns true on success, false otherwise
     */
    public function setGroupId(int $id): bool {
        return true === @chgrp($id, $this -> directory);
    }


    /**
     * Returns total size in bytes of all the files (recursive) in the current directory
     * @return int The size of the directory contents
     */
    public function getSize(): int {

        $total = 0;
        $path  = realpath($this -> getPath());

        if($path !== false) {

            foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS)) as $object) {
                $total += $object -> getSize();
            }
        }

        return $total;
    }


    /**
     * Returns if current directory is readable
     * @return bool Returns true if the current directory is readable, false if not
     */
    public function isReadable(): bool {

        if(false !== $this -> exists()) {
            return true === is_readable($this -> directory);
        }

        return false;
    }


    /**
     * Returns if current directory is writable
     * @return bool Returns true if the current directory is writable, false if not
     */
    public function isWritable(): bool {

        if(false !== $this -> exists()) {
            return true === is_writable($this -> directory);
        }

        return false;
    }


    /**
     * Returns files and folders about the current directory as array, stdClass or JSON string
     * @param string $type Can be either array, object or default
     * @return array|object|string
     * @throws RuntimeException
     */
    public function getContent(?string $type = self::TYPE_DEFAULT) {

        if(false === is_dir($this -> getPath())) {
            throw new RuntimeException(sprintf('Directory "%s" does not exists', $this -> getPath()));
        }

        $files = array_values(array_diff(scandir($this -> getPath()), ['.', '..']));

        switch($type) {

            case self::TYPE_ARRAY :
                return $files;

            case self::TYPE_OBJECT :
                return (object) $files;

            case self::TYPE_JSON :
                return json_encode($files, JSON_INVALID_UTF8_IGNORE);

            default :

                $content = [];

                foreach($files as $file) {

                    if(is_dir($this -> getPath() . $file)) {

                        $content[] = new Directory($file);
                        continue;
                    }

                    $content[] = new File($this -> getPath() . $file);
                }

                return $content;
        }
    }


    /**
     * Returns if directory exists or not
     * @return bool Returns true if directory exists, false otherwise
     */
    public function exists(): bool {
        return true === is_dir($this -> directory);
    }


    /**
     * Moves the current directory to a new directory including its content
     * @param string $directory The path of the new parent of the current directory
     * @return bool Returns true on success, false otherwise
     * @throws RuntimeException
     */
	public function move(string $directory): bool {

        //Check if new directory exists and is a directory and not a file
		if(false === is_dir($directory)) {
			throw new RuntimeException(sprintf('Directory "%s" passed to %s is not an existing directory', $directory, __METHOD__));
		}

		//Check if new directory is writable
		if(false === is_writable($directory)) {
			throw new RuntimeException(sprintf('Directory "%s" passed to %s is not writable', $directory, __METHOD__));
		}

		//Add directory separator to directory
		$directory = rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

		if(false !== $this -> exists()) {

			if(@rename($this -> getPath(), $directory . $this -> getName())) {

			    $this -> directory = $directory . $this -> getName();
			    return true;
			}
		}

		return false;
	}


	/**
	 * Deletes the current directory with content
	 * @return self
	 */
	public function delete(): self {

		if(false !== $this -> exists()) {

		    $this -> purge($this -> getPath());
            @rmdir($this -> getPath());
		}

		return $this;
	}


    /**
     * Copies the content of the current directory to a new directory
     * @param string $directory A new parent directory for the content of the current directory
     * @return self
     */
	public function copy(string $directory): self {

		//Copy recursively the directory and files
		if(false !== $this -> exists()) {
			$this -> recursiveCopy($this -> directory, $directory);
		}

		return $this;
	}


	/**
	 * Renames the current directory
	 * @param string $name The new name for the current directory
	 * @return bool Returns true on success, false otherwise
	 */
	public function rename(string $name): bool {

		if(false !== $this -> exists()) {

		    $path = $this -> getBasePath() . DIRECTORY_SEPARATOR . $name;

			if(@rename($this -> directory, $path)) {

			    $this -> directory = $path;
                return true;
			}
		}

		return false;
	}


    /**
     * Removes all files and directories within the current directory, but leaves the current directory intact
     * @return self
     */
    public function clear(): self {

        $this -> purge($this -> directory);
	    return $this;
    }


	/**
	 * Creates recursively (if necessary) new directory
	 * @param int $mode The permission (chmod) level. Will be ignored on Windows
	 * @return bool True if all directories could be created, false if not
	 */
	public function create(int $mode = 0777): bool {

		$paths = explode(DIRECTORY_SEPARATOR, $this -> directory);
		$build = '';

		foreach($paths as $path) {

			$build .= $path . DIRECTORY_SEPARATOR;

			if(false === @is_dir($build)) {

			    if(false === @mkdir($build, $mode)) {
			        return false;
                }
			}
		}

		return true;
	}


	/**
	 * Changes the current directory permissions level
	 * @param int|string $mode i.e. 755, 0755, u+rwx,go+rx
	 * @return bool Returns true if current directory has successfully changed file mode, otherwise false
	 */
	public function chmod($mode): bool {

		if(false !== $this -> exists()) {
			return chmod($this -> getPath(), $mode);
		}

		return false;
	}


	/**
	 * Changes the owner of the current directory
	 * @param int $userId The id of the user
	 * @return bool Returns true if current directory has successfully changed owner, otherwise false
	 */
	public function chown(int $userId): bool {

		if(false !== $this -> exists()) {
			return chown($this -> directory, (int) $userId);
		}

		return false;
	}


	/**
	 * Copies all contents of directory to an existing directory
	 * @param string $source The source directory
	 * @param string $destination The destination directory
	 * @return void
	 */
	private function recursiveCopy(string $source, string $destination): void {

		if(false === is_dir($destination)) {
			(new Directory($destination)) -> create();
		}

	    $directory = opendir($source);
	    
	    while(false !== ($file = readdir($directory))) {

	        if(false === in_array($file, ['.', '..'])) {

	            if(is_dir($source . DIRECTORY_SEPARATOR . $file) ) { 
	                $this -> recursiveCopy($source . DIRECTORY_SEPARATOR . $file, $destination . DIRECTORY_SEPARATOR . $file);
	            } 
	            else { 
	                copy($source . DIRECTORY_SEPARATOR . $file, $destination . DIRECTORY_SEPARATOR . $file); 
	            } 
	        } 
	    } 

	    closedir($directory); 
	}


    /**
     * Removes all files and directories within a directory
     * @param string $path The directory which should be purged
     * @return void
     */
    private function purge($path): void {

        $files = glob(rtrim($path,DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '{,.}*', GLOB_BRACE);

        foreach($files as $file) {

            if(substr($file, -1, 1) === '.' || substr($file, -2, 2) === '..') {
                continue;
            }

            if(true === is_file($file)) {
                @unlink($file);
            }
            elseif(true === is_dir($file)) {

                $this -> purge($file);
                @rmdir($file);
            }
        }
    }
}