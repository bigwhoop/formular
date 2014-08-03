<?php
namespace Test\Formular\Validation;

use bigwhoop\Formular\Filter\CallbackFilter;

class CallbackFilterTest extends \PHPUnit_Framework_TestCase
{
    public function testFilter()
    {
        $filter = new CallbackFilter(function($value) {
            return preg_replace('|[^\d]|', '', $value);
        });
        
        $this->assertSame('4645', $filter->filter('4sdfgsdfg6_sa dfasdfs ... 4.SDFGd.fg5 FGHDfgh;asd.-'));
    }
}
