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

/**
 * @author Philippe Gerber <philippe@bigwhoop.ch>
 */
class Form
{
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
        foreach ($this->renderQueue as $element) {
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
        if ($element->isRendered()) {
            return '';
        }
        
        $templatePath = $this->getTemplatePath($element->getTemplate());
        $template = new Template($this, $templatePath, $element->getAttributes());
        
        $element->setIsRendered(true);
        $html = $template->render();
        
        return $html;
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
