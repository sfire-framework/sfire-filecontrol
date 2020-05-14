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

use sFire\FileControl\Exception\RuntimeException;


/**
 * Class File
 * @package sFire\FileControl
 */
class File {


    /**
     * Contains the file as a string
     * @var null|string
     */
    private ?string $file = null;


    /**
     * Constructor
     * @param string $file
     */
    public function __construct(string $file = null) {
        $this -> file = $file;
    }


    public function setFile(string $file): self {

        $this -> file = $file;
        return $this;
    }


    /**
     * Returns if the current file is readable
     * @return bool
     */
    public function isReadable(): bool {
        return true === is_readable($this -> file);
    }


    /**
     * Returns if the current file is writable
     * @return bool
     */
    public function isWritable(): bool {
        return true === is_writable($this -> file);
    }


    /**
     * Returns mime type of current file
     * @return string
     */
    public function getMimeType(): string {
        return MimeType :: getInstance() -> get($this -> getExtension());
    }


    /**
     * Sets a new file name without extension
     * @param string $name The new name without extension
     * @return bool
     */
    public function setName(string $name): bool {
        return $this -> rename($name . ($this -> getExtension() ? '.' . $this -> getExtension() : null));
    }


    /**
     * Returns the name of the file without extension
     * @return string
     */
    public function getName(): ?string {

        $info = (object) pathinfo($this -> file);
        return $info -> filename;
    }


    /**
     * Sets a new file name with extension
     * @param string $name The new name with extension
     * @return bool
     */
    public function setBaseName(string $name): bool {
        return $this -> rename($name);
    }


    /**
     * Returns the name of the file with extension
     * @return string The name of the file with extension
     */
    public function getBaseName(): ?string {
        return pathinfo($this -> file, PATHINFO_BASENAME);
    }


    /**
     * Sets the extension of the file
     * @param string $extension
     * @return bool Returns true on success, false otherwise
     */
    public function setExtension(string $extension): bool {

        $name = substr($this -> getBaseName(), 0, 0 - strlen($this -> getExtension())) . ltrim($extension, '.');
        return $this -> rename($name);
    }


    /**
     * Returns the extension of the file
     * @return string The extension of the file without leading dot
     */
    public function getExtension(): ?string {

        $info = (object) pathinfo($this -> file);
        return $info -> extension ?? null;
    }


    /**
     * Returns the file size in bytes
     * @return int File size in bytes
     */
    public function getFileSize(): ?int {

        if(true === $this -> exists()) {
            return @filesize($this -> file);
        }

        return 0;
    }


    /**
     * Returns the path of the file
     * @return string The path of the file
     */
    public function getPath(): ?string {
        return $this -> file;
    }


    /**
     * Returns the parent directory of the file
     * @return string The parent directory of the file
     */
    public function getBasePath(): ?string {
        return dirname($this -> file) . DIRECTORY_SEPARATOR;
    }


    /**
     * Sets the path of the file
     * @param string $path The new path to the file
     * @return bool True if file is moved and path is set, false if not
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

            $touched = touch($this -> file, $time, $this -> getAccessTime());
            clearstatcache();
        }

        return $touched;
    }


    /**
     * Returns the modification time
     * @return bool|int False if modification time could not be read, Unix timestamp as modification time if modification could be read
     */
    public function getModificationTime() {
        return @filemtime($this -> file);
    }


    /**
     * Sets the id of the owner of a file
     * @param int $id The id of the user
     * @return bool Returns true on success, false otherwise
     */
    public function setOwnerId(int $id): bool {
        return $this -> chown($id);
    }


    /**
     * Returns the owner id of the current file
     * @return bool|int Returns false if file owner could not be read. The id of the owner as an integer if file owner could be read.
     */
    public function getOwnerId() {

        $owner = @fileowner($this -> file);

        if(false !== $owner) {
            return (int) $owner;
        }

        return false;
    }


    /**
     * Sets the id of the owner of a file
     * @param string $name The name of the user
     * @return bool
     */
    public function setOwner(string $name): bool {

        if(false !== $this -> exists()) {
            return chown($this -> file, $name);
        }

        return false;
    }


    /**
     * Returns the owner id of the current file
     * @return bool|string Returns false if file owner could not be read. The name of the owner as a string if file owner could be read.
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
     * Returns the group id of the current file
     * @return bool|int
     */
    public function getGroupId() {
        return @filegroup($this -> file);
    }


    /**
     * Sets the group id of a file
     * @param int $id The group id for the file
     * @return bool
     */
    public function setGroupId(int $id): bool {
        return true === @chgrp($id, $this -> file);
    }


    /**
     * Sets the access time of a file
     * @param int $time Unix timestamp as time
     * @return bool
     */
    public function setAccessTime(int $time): bool {

        $touched = false;

        if(true === $this -> exists()) {

            $touched = touch($this -> file, $this -> getModificationTime(), $time);
            clearstatcache();
        }

        return $touched;
    }


    /**
     * Returns the last access time of a file
     * @return bool|int The last access time as a Unix Timestamp
     */
    public function getAccessTime() {
        return @fileatime($this -> file);
    }


    /**
     * Returns the width of the file (i.e. image)
     * @return null|int If the width of the file could not be determined, this will return null. Otherwise it will returns the width of the file (image) as an integer
     */
    public function getWidth(): ?int {

        if(@exif_imagetype($this -> file) > 0) {

            $size = getimagesize($this -> file);
            return $size[0];
        }

        return null;
    }


    /**
     * Returns the height of the file (i.e. image)
     * @return null|int If the height of the file could not be determined, this will return null. Otherwise it will returns the height of the file (image) as an integer
     */
    public function getHeight(): ?int {

        if(@exif_imagetype($this -> file) > 0) {

            $size = getimagesize($this -> file);
            return $size[1];
        }

        return null;
    }


    /**
     * Returns the camera information about the file
     * @return array Information like the orientation, creation date, etc.
     */
    public function getCameraInfo(): array {

        $data = [];

        if($headers = @exif_read_data($this -> file)) {

            $data['camera'] = [

                'Make' 				=> $headers['Make'] ?? null,
                'Model' 			=> $headers['Model'] ?? null,
                'Orientation' 		=> $headers['Orientation'] ?? null,
                'XResolution' 		=> $headers['XResolution'] ?? null,
                'YResolution' 		=> $headers['YResolution'] ?? null,
                'ResolutionUnit' 	=> $headers['ResolutionUnit'] ?? null,
                'Software' 			=> $headers['Software'] ?? null,
                'ExposureTime' 		=> $headers['ExposureTime'] ?? null,
                'FNumber' 			=> $headers['FNumber'] ?? null,
                'ISOSpeedRatings' 	=> $headers['ISOSpeedRatings'] ?? null,
                'ShutterSpeedValue' => $headers['ShutterSpeedValue'] ?? null,
                'ApertureValue' 	=> $headers['ApertureValue'] ?? null,
                'BrightnessValue' 	=> $headers['BrightnessValue'] ?? null,
                'ExposureBiasValue' => $headers['ExposureBiasValue'] ?? null,
                'MaxApertureValue' 	=> $headers['MaxApertureValue'] ?? null,
                'MeteringMode' 		=> $headers['MeteringMode'] ?? null,
                'Flash' 			=> $headers['Flash'] ?? null
            ];

            $data['created'] = $headers['DateTime'] ?? null;
            $data['mime'] 	 = $headers['MimeType'] ?? null;
        }

        return $data;
    }


    /**
     * Moves the current file to given directory
     * @param string $directory The directory the file should be moved to
     * @return bool Returns true on success, false otherwise
     * @throws RuntimeException
     */
    public function move(string $directory): bool {

        if(false === is_dir($directory)) {
            throw new RuntimeException(sprintf('Directory "%s" passed to %s() is not an existing directory', $directory, __METHOD__));
        }

        if(false === is_writable($directory)) {
            throw new RuntimeException(sprintf('Directory "%s" passed to %s() is not writable', $directory, __METHOD__));
        }

        //Add directory separator to directory
        $directory = rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        if(false !== $this -> exists()) {

            if(@rename($this -> file, $directory . $this -> getBaseName())) {

                $this -> file = $directory . $this -> getBaseName();
                return true;
            }
        }

        return false;
    }


    /**
     * Deletes current file
     * @return bool Returns true on success, false otherwise
     */
    public function delete(): bool {

        if(false !== $this -> exists()) {
            return @unlink($this -> file);
        }

        return false;
    }


    /**
     * Creates the current file if not exists
     * @return bool Returns true on success, false otherwise
     */
    public function create(): bool {

        if(false === $this -> exists()) {
            return false !== @fopen($this -> file, 'w+');
        }

        return true;
    }


    /**
     * Makes a copy of the file to the destination directory
     * if the file already exists, it will be overwritten
     * @param string $directory A path to an existing directory
     * @param string $name [optional] A new name for the copied file
     * @return bool Returns true on success, false otherwise
     * @throws RuntimeException
     */
    public function copy(string $directory, ?string $name = null): bool {

        if(false === is_dir($directory)) {
            throw new RuntimeException(sprintf('Directory "%s" passed to %s() is not an existing directory', $directory, __METHOD__));
        }

        if(false === is_writable($directory)) {
            throw new RuntimeException(sprintf('Directory "%s" passed to %s() is not writable', $directory, __METHOD__));
        }

        if(false !== $this -> exists()) {

            //Add directory separator to directory
            $directory = rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

            return copy($this -> file, $directory . ($name ?? $this -> getBaseName()));
        }

        return false;
    }


    /**
     * Attempts to rename old name to new name. If new name exists, it will be overwritten.
     * @param string $name The new name for the file
     * @return bool Returns true on success, false otherwise
     */
    public function rename(string $name): bool {

        if(false !== $this -> exists()) {

            $file = $this -> getBasePath() . $name;

            if(rename($this -> file, $file)) {

                $this -> file = $file;
                return true;
            }
        }

        return false;
    }


    /**
     * Returns the content of current file if exists
     * @return null|string The content of the file as a string
     * @throws RuntimeException
     */
    public function getContent(): ?string {

        if(false !== $this -> exists()) {

            if(false === $this -> isReadable()) {
                throw new RuntimeException(sprintf('File "%s" is not readable', $this -> file));
            }

            return file_get_contents($this -> file);
        }

        return null;
    }


    /**
     * Appends data to current file
     * @param string $data The data that needs to be appended to the existing file content
     * @return bool Returns true on success, false otherwise
     * @throws RuntimeException
     */
    public function append(string $data): bool {

        if(false !== $this -> exists()) {

            if(false === $this -> isWritable()) {
                throw new RuntimeException(sprintf('File "%s" is not writable', $this -> file));
            }

            $handle = fopen($this -> file, 'a');

            $written = fwrite($handle, $data);
            fclose($handle);

            return false !== $written;
        }

        return false;
    }


    /**
     * Prepends data to current file
     * @param string $data The data that needs to be prepended to the existing file content
     * @return self
     * @throws RuntimeException
     */
    public function prepend(string $data): self {

        if(false !== $this -> exists()) {

            if(false === $this -> isWritable()) {
                throw new RuntimeException(sprintf('File "%s" is not writable', $this -> file));
            }

            $handle    = fopen($this -> file, 'r+');
            $len 	   = strlen($data);
            $final_len = filesize($this -> file) + $len;
            $cache_old = fread($handle, $len);

            rewind($handle);

            $i = 1;

            while(ftell($handle) < $final_len) {

                fwrite($handle, $data);

                $data 		= $cache_old;
                $cache_old 	= fread($handle, $len);

                fseek($handle, $i * $len);

                $i++;
            }
        }

        return $this;
    }


    /**
     * Checks whether the current file exists
     * @return bool Returns true if exists, false otherwise
     */
    public function exists(): bool {
        return true === is_file($this -> file);
    }


    /**
     * Empty the current file content
     * @return bool Returns true on success, false otherwise
     * @throws RuntimeException
     */
    public function flush(): bool {

        $flushed = false;

        if(false !== $this -> exists()) {

            if(false === $this -> isWritable()) {
                throw new RuntimeException(sprintf('File "%s" passed to %s() is not writable', $this -> file, __METHOD__));
            }

            $fh = fopen($this -> file, 'w');

            if(flock($fh, LOCK_EX)) {

                ftruncate($fh, 0);
                $flushed = (bool) fflush($fh);
                flock($fh, LOCK_UN);
            }

            fclose($fh);
        }

        return $flushed;
    }


    /**
     * Change the owner of the current file
     * @param int $userId The id of the user
     * @return bool Returns true if current file has successfully changed owner, otherwise false
     */
    public function chown(int $userId): bool {

        if(false !== $this -> exists()) {
            return chown($this -> file, (int) $userId);
        }

        return false;
    }
}