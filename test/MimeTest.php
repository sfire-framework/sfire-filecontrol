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
use sFire\FileControl\MimeType;

/**
 * Class MimeTest
 */
final class MimeTest extends TestCase {


	/**
	 * Testing retrieving mime type
	 * @return void
	 */
    public function testGet(): void {

        $this -> assertNull(MimeType :: getInstance() -> get('key'));
        MimeType :: getInstance() -> add('key', 'value');
        $this -> assertEquals(MimeType :: getInstance() -> get('key'), 'value');
    }


    /**
     * Testing retrieving all mime types
     * @return void
     */
    public function testAll(): void {

        MimeType :: getInstance() -> add('key', 'value');
        $this -> assertTrue(count(MimeType::all()) > 0);
    }


    /**
     * Testing setting new mime type
     * @return void
     */
    public function testSet(): void {

        MimeType :: getInstance() -> set('key', 'value');
        $this -> assertEquals('value', MimeType :: getInstance() -> get('key') );
    }


    /**
     * Testing pulling mime type
     * @return void
     */
    public function testPull(): void {

        MimeType :: getInstance() -> add('key', 'value');
        $this -> assertEquals(MimeType :: getInstance() -> pull('key'), 'value');
        $this -> assertNull(MimeType :: getInstance() -> get('key'));
    }


    /**
     * Testing removing mime type
     * @return void
     */
    public function testRemove(): void {

        $this -> assertEquals('text/plain', MimeType :: getInstance() -> get('txt') );
        MimeType::remove('txt');
        $this -> assertNull(MimeType :: getInstance() -> get('txt'));
    }


    /**
     * Testing existing of mime types
     * @return void
     */
    public function testExists(): void {

        MimeType :: getInstance() -> add('key', 'value');
        $this -> assertTrue(MimeType::has('key'));
    }


    /**
     * Testing flushing all mime types
     * @return void
     */
    public function testFlush(): void {

        MimeType :: getInstance() -> flush();
        $this -> assertCount(0, MimeType::all());
    }
}