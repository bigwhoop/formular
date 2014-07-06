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
class Value
{
    /** @var string */
    private $key = '';
    
    /** @var mixed */
    private $value = null;


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
     * @param mixed $default
     * @param string|null $key
     * @return string
     */
    public function attr($default = null, $key = null)
    {
        $value = $this->val($default);
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
        return (string)$this->value;
    }
}
