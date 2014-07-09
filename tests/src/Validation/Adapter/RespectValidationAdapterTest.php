<?php
namespace Test\Formular\Validation\Adapter;
use bigwhoop\Formular\Validation\Adapter\RespectValidationAdapter;
use Respect\Validation\Validator;

class RespectValidationAdapterTest extends \PHPUnit_Framework_TestCase
{
    public function testAdapter()
    {
        $validator = new RespectValidationAdapter('Some label', Validator::create()->email()->length(40));
        
        $this->assertFalse($validator->isValid('x'));
        $this->assertSame('Some label: "x" must be valid email', $validator->getErrorMessage());
        
        $this->assertFalse($validator->isValid('x@x.com'));
        $this->assertSame('Some label: "x@x.com" must have a length greater than 40', $validator->getErrorMessage());
        
        $this->assertTrue($validator->isValid('xxxxxxxxxxxxxxxxxxxxxx@xxxxxxxxxxxxxxxxxxxxx.com'));
        $this->assertSame('', $validator->getErrorMessage());
    }
}
