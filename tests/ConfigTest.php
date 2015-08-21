<?php

class ConfigTest extends PHPUnit_Framework_TestCase
{
    public function testConfigCorrectPath()
    {
        \JsonConfig\Config::setup('tests/files/config.normal.json');
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Файл конфига не найден
     */
    public function testConfigErrorPath()
    {
        \JsonConfig\Config::setup('tests/files/config.not_found.json');
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Не удается распарсить JSON
     */
    public function testConfigErrorFormat()
    {
        \JsonConfig\Config::setup('tests/files/config.format_error.xml');
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Не удается распарсить JSON
     */
    public function testConfigNotValidFormat()
    {
        \JsonConfig\Config::setup('tests/files/config.syntax_error.json');
    }

    public function testGetCorrectValues()
    {
        \JsonConfig\Config::setup('tests/files/config.normal.json');

        $this->assertEquals('Suppa Project', \JsonConfig\Config::get('name'));
        $this->assertEquals('Suppa Project', \JsonConfig\Config::get('NaMe'));

        $this->assertEquals(1, \JsonConfig\Config::get('version'));

        $this->assertEquals(2.5, \JsonConfig\Config::get('rating'));

        $this->assertEquals(['code.god@gmail.com'], \JsonConfig\Config::get('authors'));

        $this->assertEquals(['user1@gmail.com', 'user2@gmail.com'], \JsonConfig\Config::get('emails.subscription'));

        $this->assertEquals('Johny', \JsonConfig\Config::get('emails.user.name'));

        $this->assertEquals(true, \JsonConfig\Config::get('emails.should_notify'));
    }

    public function testGetIncorrectValues()
    {
        $this->assertFalse(\JsonConfig\Config::get('not_exists_property'));

        $this->assertFalse(\JsonConfig\Config::get('emails:subscription'));

        $this->assertEquals(['name' => 'Johny'], \JsonConfig\Config::get('emails.user'));
    }

    public function testGetValueType()
    {
        \JsonConfig\Config::setup('tests/files/config.normal.json');

        $this->assertInternalType('string', \JsonConfig\Config::get('name'));

        $this->assertInternalType('int', \JsonConfig\Config::get('version'));

        $this->assertInternalType('float', \JsonConfig\Config::get('rating'));

        $this->assertInternalType('bool', \JsonConfig\Config::get('emails.should_notify'));

        $this->assertInternalType('array', \JsonConfig\Config::get('authors'));
        $this->assertInternalType('array', \JsonConfig\Config::get('emails.subscription'));
        $this->assertInternalType('array', \JsonConfig\Config::get('emails'));
    }
}
