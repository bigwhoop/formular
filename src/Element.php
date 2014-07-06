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
class Element
{
    /** @var string */
    private $template = '';
    
    /** @var array */
    private $attributes = [];
    
    /** @var bool */
    private $isRendered = false;


    /**
     * @param string $template
     * @param array $attributes
     */
    public function __construct($template, array $attributes = [])
    {
        $this->setTemplate($template);
        $this->setAttributes($attributes);
    }


    /**
     * @param string $template
     * @return $this
     */
    public function setTemplate($template)
    {
        $this->template = $template;
        return $this;
    }


    /**
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }


    /**
     * @param array $attributes
     * @return $this
     */
    public function setAttributes(array $attributes)
    {
        $this->attributes = [];
        $this->addAttributes($attributes);
        return $this;
    }


    /**
     * @param array $attributes
     * @return $this
     */
    public function addAttributes(array $attributes)
    {
        foreach ($attributes as $key => $value) {
            $this->addAttribute($key, $value);
        }
        return $this;
    }
    

    /**
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function addAttribute($key, $value)
    {
        $keys = array_map('trim', explode(',', $key));
        foreach ($keys as $key) {
            $this->attributes[$key] = $value;
        }
        return $this;
    }
    

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }


    /**
     * @param string $key
     * @param mixed $defaultValue
     * @return string
     */
    public function getAttribute($key, $defaultValue = null)
    {
        if (array_key_exists($key, $this->attributes)) {
            return $this->attributes[$key];
        }
        return $defaultValue;
    }


    /**
     * @return bool
     */
    public function isRendered()
    {
        return $this->isRendered;
    }


    /**
     * @param bool $b
     * @return $this
     */
    public function setIsRendered($b)
    {
        $this->isRendered = $b;
        return $this;
    }
}
