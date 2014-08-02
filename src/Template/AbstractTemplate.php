<?php
/**
 * This file is part of Formular.
 *
 * (c) Philippe Gerber
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace bigwhoop\Formular\Template;

use bigwhoop\Formular\TemplateFactory\TemplateFactoryInterface;
use Zend\Escaper\Escaper;

/**
 * @author Philippe Gerber <philippe@bigwhoop.ch>
 */
abstract class AbstractTemplate
{
    /** @var array */
    protected $attributes = [];
    
    /** @var TemplateFactoryInterface|null */
    private $templateFactory = null;
    
    /** @var Escaper|null */
    private $escaper = null;


    /**
     * @param array $attributes
     * @return $this
     */
    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;
        return $this;
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
     * @return Escaper
     */
    public function getEscaper()
    {
        if (!$this->escaper) {
            $this->escaper = new Escaper();
        }
        return $this->escaper;
    }


    /**
     * @param Escaper $escaper
     * @return $this
     */
    public function setEscaper(Escaper $escaper)
    {
        $this->escaper = $escaper;
        return $this;
    }


    /**
     * @param array $attributes
     * @return string
     */
    public function render(array $attributes = [])
    {
        $this->setAttributes($attributes);
        return $this->renderTemplate();
    }


    /**
     * @return string
     */
    abstract protected function renderTemplate();


    /**
     * Renders the all the given keys as HTML element attributes.
     * 
     * @param array|string $keys
     * @return string
     */
    protected function attr($keys = [])
    {
        if (!is_array($keys)) {
            $keys = [$keys];
        }
        
        $out = '';
        foreach ($keys as $key) {
            $value = $this->getValue($key)->attr();
            if (!empty($value)) {
                $out .= ' ' . $value;
            }
        }
        return ltrim($out, ' ');
    }


    /**
     * Renders the all the given keys as HTML element properties.
     * 
     * @param array|string $keys
     * @return string
     */
    protected function prop($keys = [])
    {
        if (!is_array($keys)) {
            $keys = [$keys];
        }
        
        $out = '';
        foreach ($keys as $key) {
            $value = $this->getValue($key)->prop();
            if (!empty($value)) {
                $out .= ' ' . $value;
            }
        }
        return ltrim($out, ' ');
    }


    /**
     * @param string $templateName
     * @return string
     * @throws \RuntimeException
     */
    protected function partial($templateName)
    {
        if (!$this->templateFactory) {
            throw new \RuntimeException("Template factory must be set to render partials.");
        }
        $template = $this->templateFactory->createTemplate($templateName);
        return $template->render($this->attributes);
    }


    /**
     * @param string $key
     * @return Value
     */
    private function getValue($key)
    {
        return $this->createValue($key, $this->dereferenceValue($key));
    }


    /**
     * @param string $key
     * @return mixed
     */
    private function dereferenceValue($key)
    {
        $value = '';
        if (array_key_exists($key, $this->attributes)) {
            $value = $this->attributes[$key];
            if (is_callable($value)) {
                $value = $value();
            }
        }
        return $value;
    }
    

    /**
     * @param string $key
     * @param mixed $value
     * @return Value
     */
    private function createValue($key, $value)
    {
        $val = new Value($key, $value);
        $val->setEscaper($this->getEscaper());
        return $val;
    }


    /**
     * @param string $key
     * @return Value
     */
    public function __get($key)
    {
        return $this->getValue($key);
    }
}
