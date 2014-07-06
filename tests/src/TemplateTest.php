<?php
namespace Test\Formular;
use bigwhoop\Formular\Form;
use bigwhoop\Formular\Template;

class TemplateTest extends \PHPUnit_Framework_TestCase
{
    public function testTemplate()
    {
        $tpl = new Template(new Form(), __DIR__ . '/../data/templates/error.phtml', [
            'error' => 'This is an error!',
        ]);
        $this->assertSame('<div class="alert alert-danger">This is an error!</div>', $tpl->render());
    }
}