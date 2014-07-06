<?php
namespace Test\Formular;
use bigwhoop\Formular\Value;

class ValueTest extends \PHPUnit_Framework_TestCase
{
    public function testEmptyValue()
    {
        $value = new Value('id');
        $this->assertSame(null, $value->val());
        $this->assertSame('first_name', $value->val('first_name'));
        $this->assertSame(['a' => 'b'], $value->val(['a' => 'b']));
        
        $obj = new \stdClass();
        $this->assertSame($obj, $value->val($obj));
    }
    
    public function testValue()
    {
        $value = new Value('id', 'last_name');
        $this->assertSame('last_name', $value->val());
        $this->assertSame('last_name', $value->val('first_name'));
    }
    
    public function testAttributeOfEmptyValue()
    {
        $value = new Value('id');
        $this->assertSame('', $value->attr());
        $this->assertSame('id="first_name"', $value->attr('first_name'));
    }
    
    public function testNamedAttributeOfEmptyValue()
    {
        $value = new Value('id');
        $this->assertSame('', $value->attr(null, 'name'));
        $this->assertSame('name="first_name"', $value->attr('first_name', 'name'));
    }
    
    public function testAttribute()
    {
        $value = new Value('id', 'last_name');
        $this->assertSame('id="last_name"', $value->attr());
        $this->assertSame('id="last_name"', $value->attr('first_name'));
    }
    
    public function testNamedAttribute()
    {
        $value = new Value('id', 'last_name');
        $this->assertSame('name="last_name"', $value->attr(null, 'name'));
        $this->assertSame('name="last_name"', $value->attr('first_name', 'name'));
    }
    
    public function testPropertyOfEmptyValue()
    {
        $value = new Value('disabled');
        $this->assertSame('', $value->prop());
        $this->assertSame('', $value->prop(false));
        $this->assertSame('disabled', $value->prop(true));
    }
    
    public function testNamedPropertyOfEmptyValue()
    {
        $value = new Value('disabled');
        $this->assertSame('', $value->prop(null, 'checked'));
        $this->assertSame('', $value->prop(false, 'checked'));
        $this->assertSame('checked', $value->prop(true, 'checked'));
    }
    
    public function testProperty()
    {
        $value = new Value('disabled', true);
        $this->assertSame('disabled', $value->prop());
        $this->assertSame('disabled', $value->prop(false));
    }
    
    public function testNamedProperty()
    {
        $value = new Value('disabled', true);
        $this->assertSame('checked', $value->prop(null, 'checked'));
        $this->assertSame('checked', $value->prop(false, 'checked'));
    }
}