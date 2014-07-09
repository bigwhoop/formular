<?php
namespace Test\Formular\Validation\Adapter;
use bigwhoop\Formular\Validation\Adapter\ZendFrameworkAdapter;
use Zend\Validator;

class ZendFrameworkAdapterTest extends \PHPUnit_Framework_TestCase
{
    public function testAdapter()
    {
        $validator = new ZendFrameworkAdapter(
            'Some label',
            new Validator\EmailAddress(),
            new Validator\StringLength(['min' => 40])
        );
        
        $this->assertFalse($validator->isValid('x'));
        $this->assertSame('Some label: The input is not a valid email address. Use the basic format local-part@hostname', $validator->getErrorMessage());
        
        $this->assertFalse($validator->isValid('x@x.com'));
        $this->assertSame('Some label: The input is less than 40 characters long', $validator->getErrorMessage());
        
        $this->assertTrue($validator->isValid('xxxxxxxxxxxxxxxxxxxxxx@xxxxxxxxxxxxxxxxxxxxx.com'));
        $this->assertSame('', $validator->getErrorMessage());
    }
}
