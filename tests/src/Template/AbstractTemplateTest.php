<?php
namespace Test\Formular\Template;
use bigwhoop\Formular\Filtering\ZendEscaperAdapter;
use bigwhoop\Formular\Template\CallbackTemplate;

class AbstractTemplateTest extends \PHPUnit_Framework_TestCase
{
    public function testGetEscaper()
    {
        $tpl = new CallbackTemplate(function() { return ''; });
        $this->assertInstanceOf('bigwhoop\Formular\Filtering\EscaperInterface', $tpl->getEscaper());
    }
    
    public function testSetEscaper()
    {
        $tpl = new CallbackTemplate(function() { return ''; });
        
        $escaper = new ZendEscaperAdapter();
        $tpl->setEscaper($escaper);
        $this->assertSame($escaper, $tpl->getEscaper());
    }
    
    public function testAttributes()
    {
        $tpl = new CallbackTemplate(function(CallbackTemplate $tpl) { 
            return $tpl->attr(['id', 'name', 'value']); 
        });
        
        $this->assertSame('id="a" name="b" value="c"', $tpl->render([
            'id'    => 'a',
            'name'  => 'b',
            'value' => 'c',
        ]));
    }
    
    public function testNonArrayAttributes()
    {
        $tpl = new CallbackTemplate(function(CallbackTemplate $tpl) { 
            return $tpl->attr('foo'); 
        });
        
        $this->assertSame('foo="bar"', $tpl->render([
            'foo' => 'bar'
        ]));
    }
    
    public function testProperties()
    {
        $tpl = new CallbackTemplate(function(CallbackTemplate $tpl) { 
            return $tpl->prop(['a', 'b', 'c', 'd', 'e', 'f']); 
        });
        
        $this->assertSame('a c e', $tpl->render([
            'a' => true,
            'b' => false,
            'c' => 'yes',
            'd' => '',
            'e' => 1,
            'f' => 0,
        ]));
    }
    
    public function testNonArrayProperties()
    {
        $tpl = new CallbackTemplate(function(CallbackTemplate $tpl) { 
            return $tpl->prop('foo'); 
        });
        
        $this->assertSame('foo', $tpl->render(['foo' => true]));
        $this->assertSame('', $tpl->render(['foo' => false]));
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Template factory must be set to render partials.
     */
    public function testPartialWithoutTemplatesFactory()
    {
        $tpl = new CallbackTemplate(function(CallbackTemplate $tpl) { 
            return $tpl->partial('my-template'); 
        });
        $tpl->render();
    }
}