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

use Zend\Escaper\Escaper;

/**
 * @author Philippe Gerber <philippe@bigwhoop.ch>
 */
class Value
{
    /** @var string */
    private $key = '';
    
    /** @var mixed */
    private $value = null;
    
    /** @var Escaper|null */
    private $escaper = null;


    /**
     * @param string $key
     * @param mixed $value
     */
    public function __construct($key, $value = null)
    {
        $this->key   = $key;
        $this->value = $value;
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
     * @param mixed $default
     * @return mixed
     */
    public function val($default = null)
    {
        if (empty($this->value)) {
            return $default;
        }
        return $this->value;
    }


    /**
     * Returns val() as an escaped string that can be used inside HTML tags.
     * 
     * @param mixed $default
     * @param bool $escape
     * @return string
     */
    public function str($default = null, $escape = true)
    {
        $val = $this->val($default);
        if (is_scalar($val)) {
            $val = (string)$val;
        } else {
            $val = print_r($this->value, true);
        }
        return $escape && $this->escaper ? $this->escaper->escapeHtml($val) : $val;
    }


    /**
     * Returns val() as an escaped string that can be used inside HTML element attributes.
     * 
     * @param mixed $default
     * @param bool $escape
     * @return string
     */
    public function attrVal($default = null, $escape = true)
    {
        $val = $this->str($default, false);
        return $escape && $this->escaper ? $this->escaper->escapeHtmlAttr($val) : $val;
    }


    /**
     * Returns val() as an escaped string that can be used inside HTML element style attributes.
     * 
     * @param mixed $default
     * @param bool $escape
     * @return string
     */
    public function attrValCSS($default = null, $escape = true)
    {
        $val = $this->str($default, false);
        return $escape && $this->escaper ? $this->escaper->escapeCss($val) : $val;
    }


    /**
     * Returns val() as an escaped string that can be used inside HTML element JS event attributes.
     * 
     * @param mixed $default
     * @param bool $escape
     * @return string
     */
    public function attrValJS($default = null, $escape = true)
    {
        $val = $this->str($default, false);
        return $escape && $this->escaper ? $this->escaper->escapeJs($val) : $val;
    }
    

    /**
     * @param mixed $default
     * @param string|null $key
     * @return string
     */
    public function attr($default = null, $key = null)
    {
        $value = $this->attrVal($default);
        if (empty($value)) {
            return '';
        }
        return sprintf('%s="%s"', $key === null ? $this->key : $key, $value);
    }
    

    /**
     * @param mixed $default
     * @param string|null $key
     * @return string
     */
    public function cssAttr($default = null, $key = null)
    {
        $value = $this->attrValCSS($default);
        if (empty($value)) {
            return '';
        }
        return sprintf('%s="%s"', $key === null ? $this->key : $key, $value);
    }


    /**
     * @param mixed $default
     * @param string|null $key
     * @return string
     */
    public function prop($default = null, $key = null)
    {
        $value = $this->val($default);
        if (empty($value)) {
            return '';
        }
        return $key === null ? $this->key : $key;
    }

    
    /**
     * @return mixed
     */
    public function __toString()
    {
        return $this->str();
    }


    /**
     * @param mixed $default
     * @return mixed
     */
    public function __invoke($default = null)
    {
        return $this->val($default);
    }
}
