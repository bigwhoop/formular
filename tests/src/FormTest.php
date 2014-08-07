<?php
namespace Test\Formular;
use bigwhoop\Formular\Element\Element;
use bigwhoop\Formular\TemplateFactory\FileBasedFactory;
use bigwhoop\Formular\Validation\CallbackValidator;
use MyProject\Proxies\__CG__\stdClass;
use Test\Models\User;
use bigwhoop\Formular\Form;

class FormTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return Form
     */
    private function createForm()
    {
        $templates = new FileBasedFactory();
        $templates->addTemplatesPath(__DIR__ . '/../data/templates');
        
        $form = new Form();
        $form->setTemplateFactory($templates);
        
        return $form;
    }
    

    public function testGetSpecificElementValue()
    {
        $obj = new \stdClass();
        $obj->a = 'b';
        
        $form = $this->createForm();
        $form->addElement(new Element('foo', ['id' => 'str', 'value' => 'string']));
        $form->addElement(new Element('foo', ['id' => 'int', 'value' => 873]));
        $form->addElement(new Element('foo', ['id' => 'float', 'value' => 12.1443]));
        $form->addElement(new Element('foo', ['id' => 'obj', 'value' => $obj]));
        $form->addElement(new Element('foo', ['id' => 'arr', 'value' => ['a', 'b', 'c']]));
        $this->assertSame('string', $form->getElementValueByID('str'));
        $this->assertSame(873, $form->getElementValueByID('int'));
        $this->assertSame(12.1443, $form->getElementValueByID('float'));
        $this->assertSame($obj, $form->getElementValueByID('obj'));
        $this->assertSame(['a', 'b', 'c'], $form->getElementValueByID('arr'));
    }
    

    /**
     * @expectedException \OutOfBoundsException
     * @expectedExceptionMessage No element with ID 'bar' exists.
     */
    public function testGetSpecificNonExistingElementValue()
    {
        $form = $this->createForm();
        $form->addElement(new Element('foo', ['id' => 'bla']));
        $form->getElementValueByID('bar');
    }
    

    public function testGetSpecificElementDefaultValue()
    {
        $form = $this->createForm();
        $form->addElement(new Element('foo', ['id' => 'bla']));
        $this->assertSame(null, $form->getElementValueByID('bla'));
        $this->assertSame('my default value', $form->getElementValueByID('bla', 'my default value'));
    }
    
    
    public function testAddElements()
    {
        $form = $this->createForm();
        $this->assertCount(0, $this->readAttribute($form, 'elements')->getElements());
        $form->addElement('dirname');
        $this->assertCount(1, $this->readAttribute($form, 'elements')->getElements());
        $form->addElement('dirname');
        $this->assertCount(2, $this->readAttribute($form, 'elements')->getElements());
        return $form;
    }
    

    /**
     * @param Form $form
     * @depends testAddElements
     */
    public function testClearElements(Form $form)
    {
        $form->clearElements();
        $this->assertCount(0, $this->readAttribute($form, 'elements')->getElements());
    }
    

    /**
     * @expectedException \OverflowException
     * @expectedExceptionMessage Can't import template 'dirname' from
     */
    public function testNamespaceCollision()
    {
        $templates = new FileBasedFactory();
        $templates->addTemplatesPath(__DIR__ . '/../data/templates')
                  ->addTemplatesPath(__DIR__ . '/../data/templates2');
        
        $form = new Form();
        $form->setTemplateFactory($templates)
             ->addElement('dirname')
             ->render();
    }
    
    
    public function testNamespace()
    {
        $templates = new FileBasedFactory();
        $templates->addTemplatesPath(__DIR__ . '/../data/templates')
                  ->addTemplatesPath(__DIR__ . '/../data/templates2', 't2');
        
        $form = new Form();
        $form->setTemplateFactory($templates)
             ->addElement('dirname')
             ->addElement('dirname@t2');
        
        $this->assertSame('templatestemplates2', $form->render());
    }
    
    
    public function testDefaultNamespace()
    {
        $templates = new FileBasedFactory();
        $templates->addTemplatesPath(__DIR__ . '/../data/templates')
                  ->addTemplatesPath(__DIR__ . '/../data/templates2', 't2')
                  ->setDefaultNamespace('t2');
        
        $form = new Form();
        $form->setTemplateFactory($templates)
             ->addElement('dirname')
             ->addElement('dirname');
        
        $this->assertSame('templates2templates2', $form->render());
    }


    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage A template by the name 'dirname' does not exist.
     */
    public function testUnresolvableMultipleNamespaces()
    {
        $templates = new FileBasedFactory();
        $templates->addTemplatesPath(__DIR__ . '/../data/templates', 't1')
                  ->addTemplatesPath(__DIR__ . '/../data/templates2', 't2');
        
        $form = new Form();
        $form->setTemplateFactory($templates)
             ->addElement('dirname')
             ->render();
    }

    
    public function testMultipleNamespaces()
    {
        $templates = new FileBasedFactory();
        $templates->addTemplatesPath(__DIR__ . '/../data/templates', 't1')
                  ->addTemplatesPath(__DIR__ . '/../data/templates2', 't2');
        
        $form = new Form();
        $form->setTemplateFactory($templates)
             ->addElement('dirname@t2')
             ->addElement('dirname@t1');
        
        $this->assertSame('templates2templates', $form->render());
    }
    
    
    public function testValueBinding()
    {
        $form = $this->createForm();
        
        $user = new User('John', 'Doe');
        $age = 21;
        $location = 'Bern';
        
        $form->addElement('values-printer', [
            'value1' => $form->bindValue([$user, 'getFirstName']),
            'value2' => $form->bindValue([$user, 'lastName']),
            'value3' => $form->bindValue(function() use (&$age) { return $age; }),
            'value4' => $form->bindVariable($location),
        ]);
        $this->assertSame('John-Doe-21-Bern', $form->render());
        
        $form->resetRenderQueue();
        
        $user->firstName = 'Jack';
        $user->lastName = 'Jones';
        $age = 34;
        $location = 'Thun';
        $this->assertSame('Jack-Jones-34-Thun', $form->render());
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
    
    
    public function testPartial()
    {
        $form = $this->createForm();
        $form->addElement('partial-array-printer', [
            'array' => ['foo', 3, 'bar'],
        ]);
        $this->assertEquals('foo, 3, bar', $form->render());
    }
}