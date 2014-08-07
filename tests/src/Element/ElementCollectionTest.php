<?php
namespace Test\Formular\Element;
use bigwhoop\Formular\Element\Element;
use bigwhoop\Formular\Element\ElementCollection;

class ElementCollectionTest extends \PHPUnit_Framework_TestCase
{
    public function testAddElements()
    {
        $e1 = new Element('one');
        $e2 = new Element('two');
        
        $elements = new ElementCollection();
        
        $elements->addElement($e1);
        $this->assertCount(1, $elements->getElements());
        $this->assertSame([$e1], $elements->getElements());
        
        $elements->addElement($e2);
        $this->assertCount(2, $elements->getElements());
        $this->assertSame([$e1, $e2], $elements->getElements());
        
        return $elements;
    }

    /**
     * @param ElementCollection $elements
     * @depends testAddElements
     */
    public function testReset(ElementCollection $elements)
    {
        $elements->reset();
        $this->assertCount(0, $elements->getElements());
        $this->assertSame([], $elements->getElements());
    }

    public function testGetSpecificElement()
    {
        $element  = new Element('foo', ['id' => 'bla']);
        $elements = new ElementCollection();
        $elements->addElement($element);
        $this->assertSame($element, $elements->getElementByID('bla'));
    }

    /**
     * @expectedException \OutOfBoundsException
     * @expectedExceptionMessage No element with ID 'bla-foo' exists.
     */
    public function testGetSpecificNonExistingElement()
    {
        $elements = new ElementCollection();
        $elements->addElement(new Element('foo', ['id' => 'bla']));
        $elements->getElementByID('bla-foo');
    }
}