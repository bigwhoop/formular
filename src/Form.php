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
use bigwhoop\Formular\Filter\CallbackFilter;
use bigwhoop\Formular\Filter\FilterInterface;
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
    
    /** @var Element[] */
    private $elements = [];
    
    /** @var \SplQueue|null */
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
        $this->elements = [];
        $this->resetRenderQueue();
        return $this;
    }
    

    /**
     * @param Element|string $template
     * @param array $attributes
     * @return $this
     */
    public function addElement($template, array $attributes = [])
    {
        if (!$template instanceof Element) {
            $template = new Element($template, $attributes);
        }
        $this->elements[] = $template;
        return $this;
    }


    /**
     * @param string $id
     * @return Element
     * @throws \OutOfBoundsException
     */
    public function getElementByID($id)
    {
        foreach ($this->elements as $element) {
            if ($element->getAttribute('id') === $id) {
                return $element;
            }
        }
        throw new \OutOfBoundsException("No element with an attribute 'id' = '$id' exists.");
    }


    /**
     * @param string $id
     * @param mixed $default
     * @return mixed
     */
    public function getElementValueByID($id, $default = null)
    {
        return $this->getElementByID($id)->getAttribute('value', $default);
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
            $validator = new CallbackValidator($validator);
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
            $this->renderQueue = new \SplQueue();
            foreach ($this->elements as $element) {
                $this->renderQueue->enqueue($element);
            }
        }
        
        $out = '';
        while (count($this->renderQueue) > 0) {
            $element = $this->renderQueue->dequeue();
            $out .= $this->renderElement($element);
        }
        return $out;
    }


    /**
     * @param Element $element
     * @return string
     * @throws \LogicException
     */
    public function renderElement(Element $element)
    {
        $factory = $this->getTemplateFactory();
        if (!$factory) {
            throw new \LogicException("A template factory must be set to render an element.");
        }
        $template = $factory->createTemplate($element->getTemplate());
        return $template->render($element->getAttributes());
    }


    /**
     * @param array $values
     * @return bool
     */
    public function isValid(array $values)
    {
        $errors = [];
        foreach ($this->elements as $element) {
            $elementId = $element->getAttribute('id');
            if (empty($elementId)) {
                continue;
            }
            
            $value = array_key_exists($elementId, $values) ? $values[$elementId] : null;
            $element->setAttribute('value', $value);
            
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
