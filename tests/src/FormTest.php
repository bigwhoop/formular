<?php
namespace Test\Formular;
use bigwhoop\Formular\Validator\CallbackValidator;
use Test\Models\User;
use bigwhoop\Formular\Form;

class FormTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return Form
     */
    private function createForm()
    {
        $form = new Form();
        $form->addTemplatesPath(__DIR__ . '/../data/templates');
        return $form;
    }
    
    
    public function testValueBinding()
    {
        $user = new User('John', 'Doe');
        $age = 21;
        
        $form = $this->createForm();
        $form->addElement('values-printer', [
            'value1' => $form->bind([$user, 'getFirstName']),
            'value2' => $form->bind([$user, 'lastName']),
            'value3' => $form->bind(function() use (&$age) { return $age; }),
            'value4' => '?',
        ]);
        $this->assertSame('John-Doe-21-?', $form->render());
        
        $user->firstName = 'Jack';
        $user->lastName = 'Jones';
        $age = 34;
        $form->resetRenderQueue();
        $this->assertSame('Jack-Jones-34-?', $form->render());
    }
    
    
    public function testContinueBinding()
    {
        $form = $this->createForm();
        $form->addElement('nested', ['next' => $form->bindContinue(), 'level' => 1]);
        $form->addElement('nested', ['next' => $form->bindContinue(), 'level' => 2]);
        $form->addElement('nested', ['next' => $form->bindContinue(), 'level' => 3, 'value' => 'here']);
        
        $this->assertSame('<div class="level-1"><div class="level-2"><div class="level-3">here</div></div></div>', $form->render());
    }
    
    
    public function testErrorMessagesBindingWithoutValidators()
    {
        $form = $this->createForm();
        $form->addElement('array-printer', [
            'array' => $form->bindErrorMessages(),
        ]);
        $this->assertTrue($form->isValid([]));
        $this->assertEquals('', $form->render());
    }
    
    
    public function testErrorMessagesBindingWithValidators()
    {
        $form = $this->createForm();
        $form->addElement('array-printer', [
            'id'    => 'test',
            'array' => $form->bindErrorMessages(),
        ]);
        $form->setValidator('test', new CallbackValidator(function() {
            return false;
        }, "Value: '%VALUE%'."));
        $form->setValidator('*', new CallbackValidator(function() {
            return false;
        }, "_%VALUE%_"));
        $this->assertFalse($form->isValid(['test' => 'foobar']));
        $this->assertEquals("Value: 'foobar'., _foobar_", $form->render());
    }
}