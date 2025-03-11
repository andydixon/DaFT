<?php

use PHPUnit\Framework\TestCase;
use DaFT\Helpers;

class HelpersTest extends TestCase
{
    public function testCleanUriRemovesQueryString()
    {
        $_SERVER['REQUEST_URI'] = '/test/path?param=value';
        $result = Helpers::cleanUri();
        $this->assertEquals('/test/path', $result);
    }

    public function testCleanUriReturnsRootWhenEmpty()
    {
        unset($_SERVER['REQUEST_URI']);
        $result = Helpers::cleanUri();
        $this->assertEquals('/', $result);
    }

    public function testSanitiseStringRemovesSpecialCharacters()
    {
        $input = "Hello <b>World</b>!";
        $expected = "Hello World!";

        $result = Helpers::sanitiseString($input);

        $this->assertEquals($expected, $result);
    }

    public function testSanitiseStringHandlesEmptyString()
    {
        $input = "";
        $expected = "";

        $result = Helpers::sanitiseString($input);

        $this->assertEquals($expected, $result);
    }

    public function testArrayFlattenFlattensNestedArray()
    {
        $input = [
            'a' => 1,
            'b' => [
                'c' => 2,
                'd' => [
                    'e' => 3
                ]
            ]
        ];
        $expected = ['a' => 1, 'c' => 2, 'e' => 3];

        $result = Helpers::arrayFlatten($input);

        $this->assertEquals($expected, $result);
    }    
}
