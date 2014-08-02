<?php
namespace Test\Formular\Template;
use bigwhoop\Formular\Template\CallbackTemplate;

class CallbackTemplateTest extends \PHPUnit_Framework_TestCase
{
    public function testTemplate()
    {
        $tpl = new CallbackTemplate(function(CallbackTemplate $tpl) {
            return sprintf(
                '<div class="%s" %s>%s</div>',
                $tpl->class->attrVal('my-div'),
                $tpl->style->attr(),
                $tpl->text->str('Hello')
            );
        });
        $this->assertSame('<div class="my-div" >Hello</div>', $tpl->render());
        $this->assertSame('<div class="my-div" style="font-weight&#x3A;&#x20;bold&#x3B;">Hi</div>', $tpl->render([
            'style' => 'font-weight: bold;',
            'text'  => 'Hi',
        ]));
    }
}