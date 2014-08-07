<?php
/**
 * This file is part of Formular.
 *
 * (c) Philippe Gerber
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace bigwhoop\Formular;
use bigwhoop\Formular\Element\ElementCollection;
use bigwhoop\Formular\Element\ElementInterface;
use bigwhoop\Formular\Element\Element;
use bigwhoop\Formular\Element\ElementQueue;
use bigwhoop\Formular\Filtering\CallbackFilter;
use bigwhoop\Formular\Filtering\FilterInterface;
use bigwhoop\Formular\TemplateFactory\TemplateFactoryInterface;
use bigwhoop\Formular\Validation\CallbackValidator;
use bigwhoop\Formular\Validation\ValidatorInterface;

/**
 * @author Philippe Gerber <philippe@bigwhoop.ch>
 */
class Form
{
    /** @var array */
    static protected $defaultOptions = [];
    
    /** @var array */
    protected $options = [];
    
    /** @var ElementCollection */
    private $elements = null;
    
    /** @var ElementQueue|null */
    private $renderQueue = null;
    
    /** @var TemplateFactoryInterface|null */
    private $templateFactory = null;

    /** @var FilterInterface[] */
    private $filters = [];
    
    /** @var ValidatorInterface[] */
    private $validators = [];
    
    /** @var array */
    private $errorMessages = [];
    

    /**
     * @param array $options
     */
    static public function setDefaultOptions(array $options)
    {
        static::$defaultOptions = $options;
    }


    /**
     * Constructor
     */
    public function __construct(array $options = [])
    {
        $this->options = $options + static::$defaultOptions;
        $this->elements = new ElementCollection();
        $this->init();
    }

    
    /**
     * Can be implemented by subclasses.
     */
    public function init()
    {
        // ...
    }


    /**
     * @param TemplateFactoryInterface $locator
     * @return $this
     */
    public function setTemplateFactory(TemplateFactoryInterface $locator)
    {
        $this->templateFactory = $locator;
        return $this;
    }


    /**
     * @return TemplateFactoryInterface|null
     */
    public function getTemplateFactory()
    {
        return $this->templateFactory;
    }


    /**
     * @return $this
     */
    public function clearElements()
    {
        $this->elements->reset();
        $this->resetRenderQueue();
        return $this;
    }
    

    /**
     * @param ElementInterface|string $template
     * @param array $attributes
     * @return $this
     */
    public function addElement($template, array $attributes = [])
    {
        if (!$template instanceof ElementInterface) {
            $template = new Element($template, $attributes);
        }
        $this->elements->addElement($template);
        return $this;
    }


    /**
     * @param string $id
     * @param mixed $default
     * @return mixed
     * @throws \OutOfBoundsException        If the element does not exist.
     */
    public function getElementValueByID($id, $default = null)
    {
        try {
            return $this->elements->getElementByID($id)->getValue($default);
        } catch (\OutOfBoundsException $e) {
            throw $e;
        }
    }


    /**
     * @param array $filters
     * @return $this
     */
    public function setFilters(array $filters)
    {
        $this->filters = [];
        foreach ($filters as $scope => $filter) {
            $this->addFilter($scope, $filter);
        }
        return $this;
    }


    /**
     * @param $scope
     * @param callable|FilterInterface $filter
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function addFilter($scope, $filter)
    {
        if (!is_string($scope)) {
            throw new \InvalidArgumentException("Scope must be string.");
        }
        if (!is_callable($filter) && !$filter instanceof FilterInterface) {
            throw new \InvalidArgumentException("Validator must be a callable or an instance of FilterInterface.");
        }
        if (!$filter instanceof FilterInterface) {
            $filter = new CallbackFilter($filter);
        }
        $this->filters[$scope] = $filter;
        return $this;
    }


    /**
     * @param array $validators
     * @return $this
     */
    public function setValidators(array $validators)
    {
        $this->validators = [];
        foreach ($validators as $scope => $validator) {
            $this->setValidator($scope, $validator);
        }
        return $this;
    }


    /**
     * @param $scope
     * @param callable|ValidatorInterface $validator
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setValidator($scope, $validator)
    {
        if (!is_string($scope)) {
            throw new \InvalidArgumentException("Scope must be string.");
        }
        if (!is_callable($validator) && !$validator instanceof ValidatorInterface) {
            throw new \InvalidArgumentException("Validator must be a callable or an instance of ValidatorInterface.");
        }
        if (!$validator instanceof ValidatorInterface) {
            $validator = new CallbackValidator($validator, 'Value %VALUE% is not valid.');
        }
        $this->validators[$scope] = $validator;
        return $this;
    }
    

    /**
     * @param mixed|callable $value
     * @return callable
     */
    public function bindValue($value)
    {
        // Bind callable
        if (is_callable($value)) {
            return $value;
        }
        
        // Bind object property
        if (is_array($value) && count($value) == 2 && is_object($value[0]) && is_string($value[1]) && property_exists($value[0], $value[1])) {
            return function() use ($value) {
                return $value[0]->$value[1];
            };
        }
        
        // Bind everything else
        return function() use ($value) {
            return $value;
        };
    }


    /**
     * @param mixed $var
     * @return callable
     */
    public function bindVariable(&$var)
    {
        return function() use (&$var) {
            return $var;
        };
    }
    
    
    /**
     * Just call render() again to continue rendering of the form. All the elements
     * are in a queue and we can just continue to dequeue the next element.
     * 
     * @return callable
     */
    public function bindContinue()
    {
        return $this->bindValue(function() {
            return $this->render();
        });
    }
    
    
    /**
     * @return callable
     */
    public function bindErrorMessages()
    {
        return $this->bindValue(function() {
            return $this->getErrorMessages();
        });
    }


    /**
     * @return $this
     */
    public function resetRenderQueue()
    {
        $this->renderQueue = null;
        return $this;
    }


    /**
     * @return string
     */
    public function render()
    {
        if ($this->renderQueue === null) {
            $this->renderQueue = ElementQueue::createFromCollection($this->elements);
        }
        
        $out = '';
        while (count($this->renderQueue) > 0) {
            $element = $this->renderQueue->dequeue();
            $out .= $this->renderElement($element);
        }
        return $out;
    }


    /**
     * @param ElementInterface $element
     * @return string
     * @throws \LogicException
     */
    private function renderElement(ElementInterface $element)
    {
        $factory = $this->getTemplateFactory();
        if (!$factory) {
            throw new \LogicException("A template factory must be set to render elements.");
        }
        $template = $factory->createTemplate($element->getTemplateName());
        return $template->render($element->getAttributes());
    }


    /**
     * Validates all the elements against the defined validators.
     * 
     * During validation the given values will be set as the new values of the elements.
     * 
     * @param array $values
     * @return bool
     */
    public function isValid(array $values)
    {
        $elements = $this->elements->getElements();
        
        if (empty($elements) || empty($this->validators)) {
            return true;
        }
        
        $errors = [];
        foreach ($elements as $element) {
            $elementId = $element->getID();
            if (empty($elementId)) {
                continue;
            }
            
            $value = array_key_exists($elementId, $values) ? $values[$elementId] : null;
            $element->setValue($value);
            
            foreach ($this->validators as $scope => $validator) {
                $elementIds = array_map('trim', explode(',', $scope));
                
                if ($scope === '*' || in_array($elementId, $elementIds)) {
                    if (!$validator->isValid($value)) {
                        $errors[] = $validator->getErrorMessage();
                    }
                }
            }
        }
        
        $this->errorMessages = $errors;
        
        return count($errors) === 0;
    }


    /**
     * @return array
     */
    public function getErrorMessages()
    {
        return $this->errorMessages;
    }
}
