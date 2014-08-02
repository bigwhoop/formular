<?php
namespace Test\Formular\Template;
use bigwhoop\Formular\Template\Template;

class TemplateTest extends \PHPUnit_Framework_TestCase
{
    public function testTemplate()
    {
        $tpl = new Template(__DIR__ . '/../../data/templates/error.phtml');
        $this->assertSame('<div class="alert&#x20;alert-danger"></div>', $tpl->render());
        
        $tpl = new Template(__DIR__ . '/../../data/templates/error.phtml', [
            'error' => 'This is an error!',
        ]);
        $this->assertSame('<div class="alert&#x20;alert-danger">This is an error!</div>', $tpl->render());
    }
}