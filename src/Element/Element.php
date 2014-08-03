<?php
/**
 * This file is part of Formular.
 *
 * (c) Philippe Gerber
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace bigwhoop\Formular\Element;

/**
 * @author Philippe Gerber <philippe@bigwhoop.ch>
 */
class Element implements ElementInterface
{
    /** @var string */
    private $templateName = '';
    
    /** @var array */
    private $attributes = [];


    /**
     * @param string $templateName
     * @param array $attributes
     */
    public function __construct($templateName, array $attributes = [])
    {
        $this->setTemplateName($templateName);
        $this->setAttributes($attributes);
    }


    /**
     * @param string $templateName
     * @return $this
     */
    public function setTemplateName($templateName)
    {
        $this->templateName = $templateName;
        return $this;
    }


    /**
     * {@inheritdoc}
     */
    public function getTemplateName()
    {
        return $this->templateName;
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
            $this->setAttribute($key, $value);
        }
        return $this;
    }
    

    /**
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function setAttribute($key, $value)
    {
        $keys = array_map('trim', explode(',', $key));
        foreach ($keys as $key) {
            $this->attributes[$key] = $value;
        }
        return $this;
    }
    

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function getID()
    {
        return $this->getAttribute('id');
    }


    /**
     * {@inheritdoc}
     */
    public function setValue($value)
    {
        $this->setAttribute('value', $value);
        return $this;
    }


    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        return $this->getAttribute('value');
    }
}
