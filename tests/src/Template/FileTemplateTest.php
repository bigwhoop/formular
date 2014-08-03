<?php
namespace Test\Formular\Template;
use bigwhoop\Formular\Template\FileTemplate;
use bigwhoop\Formular\TemplateFactory\FileBasedFactory;

class FileTemplateTest extends \PHPUnit_Framework_TestCase
{
    public function testTemplate()
    {
        $tpl = new FileTemplate(__DIR__ . '/../../data/templates/error.phtml');
        
        $this->assertSame('<div class="alert&#x20;alert-danger"></div>', $tpl->render());
        $this->assertSame('<div class="alert&#x20;alert-danger">This is an error!</div>', $tpl->render([
            'error' => 'This is an error!',
        ]));
    }
    
    
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Template '/non/existing/path' must be readable.
     */
    public function testInvalidPath()
    {
        $tpl = new FileTemplate('/non/existing/path');
    }
}