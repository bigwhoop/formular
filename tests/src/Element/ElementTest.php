<?php
namespace Test\Formular\Element;
use bigwhoop\Formular\Element\Element;

class ElementTest extends \PHPUnit_Framework_TestCase
{
    /** @var array */
    protected $elementAttr = [
        'a' => null,
        'b' => 12,
        'c' => ['a', 'b', 'c'],
        'd' => 'hello',
        'e' => 0,
        'f' => '',
    ];


    /**
     * @return Element
     */
    public function testInitialization()
    {
        $e = new Element('my-template', $this->elementAttr);
        $this->assertSame('my-template', $e->getTemplateName());
        $this->assertSame($this->elementAttr, $e->getAttributes());
        return $e;
    }
    

    /**
     * @param Element $e
     * @depends testInitialization
     */
    public function testGetExistingAttribute(Element $e)
    {
        foreach ($this->elementAttr as $key => $value) {
            $this->assertSame($value, $e->getAttribute($key));
        }
    }
    

    /**
     * @param Element $e
     * @depends testInitialization
     */
    public function testGetExistingAttributeWithDefaultValue(Element $e)
    {
        foreach ($this->elementAttr as $key => $value) {
            $this->assertSame($value, $e->getAttribute($key, 'foobar'));
        }
    }
    

    /**
     * @param Element $e
     * @depends testInitialization
     */
    public function testGetNonExistingAttribute(Element $e)
    {
        $this->assertSame(null, $e->getAttribute('foo'));
    }
    

    /**
     * @param Element $e
     * @depends testInitialization
     */
    public function testGetNonExistingAttributeWithDefaultValue(Element $e)
    {
        $this->assertSame('bar', $e->getAttribute('foo', 'bar'));
    }
    

    public function testGetExistingValue()
    {
        $e = new Element('my-template', [
            'value' => 'my-value',
        ]);
        $this->assertSame('my-value', $e->getValue());
    }
    

    public function testGetNonExistingValue()
    {
        $e = new Element('my-template');
        $this->assertSame(null, $e->getValue());
        return $e;
    }


    /**
     * @param Element $e
     * @depends testGetNonExistingValue
     */
    public function testSetValue(Element $e)
    {
        $e->setValue('my-value');
        $this->assertSame('my-value', $e->getValue());
    }
    

    public function testGetExistingID()
    {
        $e = new Element('my-template', [
            'id' => 'my-id',
        ]);
        $this->assertSame('my-id', $e->getID());
    }
    

    public function testGetNonExistingID()
    {
        $e = new Element('my-template');
        $this->assertSame(null, $e->getID());
    }
}