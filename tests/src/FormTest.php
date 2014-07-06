<?php
namespace Test\Formular;
use Test\Models\User;
use bigwhoop\Formular\Form;

class FormTest extends \PHPUnit_Framework_TestCase
{
    public function testForm()
    {
        $user = new User('John', 'Doe');
        
        $form = new Form();
        $form->addTemplatesPath(__DIR__ . '/../../templates/bootstrap3', 'bs3');
        $form->addTemplatesPath(__DIR__ . '/../../templates', 'local');
        $form->setDefaultNamespace('bs3');
        
        
    }
}