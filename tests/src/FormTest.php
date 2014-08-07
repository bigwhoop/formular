<?php
namespace Test\Formular;
use bigwhoop\Formular\Element\Element;
use bigwhoop\Formular\Filtering\CallbackFilter;
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
    
    
    public function testDefaultOptions()
    {
        Form::setDefaultOptions([]);
        $form = new Form();
        $this->assertSame([], $this->readAttribute($form, 'options'));
        
        Form::setDefaultOptions(['a' => 'b', 'c' => 12]);
        $form = new Form();
        $this->assertSame(['a' => 'b', 'c' => 12], $this->readAttribute($form, 'options'));
        
        $form = new Form(['c' => 'd']);
        $this->assertSame(['c' => 'd', 'a' => 'b'], $this->readAttribute($form, 'options'));
        
        Form::setDefaultOptions([]);
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
            'value5' => $form->bindValue('Earth'),
        ]);
        $this->assertSame('John-Doe-21-Bern-Earth', $form->render());
        
        $form->resetRenderQueue();
        
        $user->firstName = 'Jack';
        $user->lastName = 'Jones';
        $age = 34;
        $location = 'Thun';
        $this->assertSame('Jack-Jones-34-Thun-Earth', $form->render());
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
    
    
    public function testValidation()
    {
        $form = $this->createForm();
        $form->addElement('template', ['id' => 'foo']);
        $form->setValidator('foo', new CallbackValidator(function($value) {
            return $value === 'bar';
        }, "'%VALUE%' is not 'bar'"));
        
        $this->assertFalse($form->isValid(['foo' => 'baz']));
        $this->assertSame(["'baz' is not 'bar'"], $form->getErrorMessages());
        $this->assertTrue($form->isValid(['foo' => 'bar']));
    }
    
    
    public function testMatchAllValidation()
    {
        $form = $this->createForm();
        $form->addElement('template', ['id' => 'foo']);
        $form->setValidators([
            '*' => function($value) {
                return $value === 'bar';
            },
        ]);
        
        $this->assertFalse($form->isValid(['foo' => 'baz']));
        $this->assertSame(["Value baz is not valid."], $form->getErrorMessages());
        $this->assertTrue($form->isValid(['foo' => 'bar']));
    }


    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Scope must be string.
     */
    public function testSetValidatorWithInvalidScope()
    {
        $form = $this->createForm();
        $form->setValidator(12, function() {});
    }


    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Validator must be a callable or an instance of ValidatorInterface.
     */
    public function testSetValidatorWithInvalidValidator()
    {
        $form = $this->createForm();
        $form->setValidator('*', 'foo');
    }
    
    
    public function testFiltering()
    {
        $form = $this->createForm();
        $form->setFilters([
            'foo' => function($value) {
                return str_replace('foo', 'bar', $value);
            },
        ]);
        $form->setFilter('*', new CallbackFilter(function($value) {
            return $value . '_';
        }));
        
        $filterMethod = $this->getAccessibleMethod(get_class($form), 'filterElement');
        
        $e = new Element('template', ['id' => 'aaa', 'value' => 'foobar']);
        $filterMethod->invoke($form, $e);
        $this->assertSame('foobar_', $e->getValue());
        
        $e = new Element('template', ['id' => 'foo', 'value' => 'foobar']);
        $filterMethod->invoke($form, $e);
        $this->assertSame('barbar_', $e->getValue());
    }


    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Scope must be string.
     */
    public function testSetFilterWithInvalidScope()
    {
        $form = $this->createForm();
        $form->setFilter(12, new CallbackFilter(function() {}));
    }


    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Filter must be a callable or an instance of FilterInterface.
     */
    public function testSetFilterWithInvalidFilter()
    {
        $form = $this->createForm();
        $form->setFilter('*', 'foo');
    }


    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage A template factory must be set to render elements.
     */
    public function testRenderWithoutTemplatesFactory()
    {
        $form = new Form();
        $form->addElement('template');
        $form->render();
    }
    

    /**
     * @param string $class
     * @param string $method
     * @return \ReflectionMethod
     */
    private function getAccessibleMethod($class, $method)
    {
        $method = new \ReflectionMethod($class, $method);
        $method->setAccessible(true);
        return $method;
    }
}