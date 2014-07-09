<?php
namespace Test\Formular\Validation;

use bigwhoop\Formular\Validation\CallbackValidator;

class CallbackValidatorTest extends \PHPUnit_Framework_TestCase
{
    public function testValidator()
    {
        $validator = new CallbackValidator(function($value) {
            return $value === 'TEST';
        }, "Got '%VALUE%', expected 'TEST'.");
        
        $this->assertFalse($validator->isValid('x'));
        $this->assertSame("Got 'x', expected 'TEST'.", $validator->getErrorMessage());
        
        $this->assertTrue($validator->isValid('TEST'));
        $this->assertSame('', $validator->getErrorMessage());
    }
}
