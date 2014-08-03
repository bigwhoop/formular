<?php
namespace Test\Formular\TemplateFactory;

use bigwhoop\Formular\TemplateFactory\FileBasedFactory;

class FileBasedFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Templates path '/non/existing/path' does not exist or is not readable.
     */
    public function testInvalidPath()
    {
        $factory = new FileBasedFactory();
        $factory->addTemplatesPath('/non/existing/path');
        $factory->createTemplate('template');
    }
}