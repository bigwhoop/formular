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
use bigwhoop\Formular\Validator\CallbackValidator;
use bigwhoop\Formular\Validator\ValidatorInterface;

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
    
    /** @var array */
    private $templatesPaths = [];
    
    /** @var array */
    private $templatesMap = [];
    
    /** @var null|string */
    private $defaultNamespace = null;

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
     * @param string|null $namespace
     * @return $this
     */
    public function setDefaultNamespace($namespace)
    {
        $this->defaultNamespace = $namespace;
        return $this;
    }


    /**
     * @return $this
     */
    public function clearElements()
    {
        $this->elements = [];
        $this->renderQueue = null;
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
     * @param string $path
     * @param string|null $namespace
     * @return $this
     */
    public function addTemplatesPath($path, $namespace = null)
    {
        $this->templatesPaths[$path] = $namespace;
        return $this;
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
            $this->addValidator($scope, $validator);
        }
        return $this;
    }


    /**
     * @param $scope
     * @param callable|ValidatorInterface $validator
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function addValidator($scope, $validator)
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
    public function bind($value)
    {
        if (is_callable($value)) {
            $callable = $value;
        } elseif (is_array($value) && count($value) == 2 && is_object($value[0]) && is_string($value[1]) && property_exists($value[0], $value[1])) {
            $callable = function() use ($value) {
                return $value[0]->$value[1];
            };
        } else {
            $callable = function() use ($value) {
                return $value;
            };
        }
        return $callable;
    }
    
    
    /**
     * @return callable
     */
    public function bindContinue()
    {
        return $this->bind(function() {
            return $this->render();
        });
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
     */
    public function renderElement(Element $element)
    {
        $templatePath = $this->getTemplatePath($element->getTemplate());
        $template = new Template($templatePath, $element->getAttributes());
        return $template->render();
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
            
            foreach ($this->validators as $scope => $validator) {
                $elementIds = array_map('trim', explode(',', $scope));
                
                if ($scope === '*' || in_array($elementId, $elementIds)) {
                    $element->setAttribute('value', $value);
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
    

    /**
     * Builds a map of template names to paths.
     * 
     * @return array
     * @throws \OverflowException|\RuntimeException
     */
    private function buildTemplatesMap()
    {
        if (!empty($this->templatesMap)) {
            return $this->templatesMap;
        }
        
        $this->templatesMap = [];
        foreach ($this->templatesPaths as $templatesPath => $templatesNamespace) {
            if (!is_readable($templatesPath)) {
                throw new \RuntimeException("Templates path '$templatesPath' does not exist or is not readable.");
            }
            
            foreach (glob($templatesPath . '/*.phtml') as $templatePath) {
                $template = pathinfo($templatePath, PATHINFO_FILENAME);
                if ($templatesNamespace !== null) {
                    $template .= '@' . $templatesNamespace;
                }
                if (array_key_exists($template, $this->templatesMap)) {
                    throw new \OverflowException("Can't import template '$template' from '$templatePath' as a template with the same name already exists at '{$this->templatesMap[$template]}'. You may want to use namespaces.");
                }
                $this->templatesMap[$template] = $templatePath;
            }
        }
        return $this->templatesMap;
    }
    

    /**
     * Returns the path to a given template.
     * 
     * The template name can contain a namespace like "template@ns" in case there are templates with the same name.
     * 
     * @param string $template
     * @return string
     * @throws \RuntimeException
     */
    private function getTemplatePath($template)
    {
        if (strpos($template, '@') === false && !empty($this->defaultNamespace)) {
            $template .= '@' . $this->defaultNamespace;
        }
        
        $map = $this->buildTemplatesMap();
        
        if (!array_key_exists($template, $map)) {
            throw new \RuntimeException("A template by the name '$template' does not exist. The following templates are available: " . join(', ', array_keys($map)));
        }
        return $map[$template];
    }
}
