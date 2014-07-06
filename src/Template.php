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
class Template
{
    /** @var Form */
    private $form;
    
    /** @var string */
    private $path = '';
    
    /** @var array */
    private $attributes = [];


    /**
     * @param Form $form
     * @param string $path
     * @param array $attributes
     * @throws \InvalidArgumentException
     */
    public function __construct(Form $form, $path, array $attributes)
    {
        if (!is_readable($path)) {
            throw new \InvalidArgumentException("Template '$path' must be readable.");
        }
        
        $this->form = $form;
        $this->path = $path;
        $this->attributes = $attributes;
    }


    /**
     * @return string
     */
    public function render()
    {
        ob_start();
        require $this->path;
        return ob_get_clean();
    }


    /**
     * @return string
     */
    protected function elements()
    {
        return $this->form->render();
    }


    /**
     * @param string $elementId
     * @return string
     * @throws \RuntimeException
     */
    public function element($elementId)
    {
        try {
            $element = $this->form->getElementByID($elementId);
            return $this->form->renderElement($element);
        } catch (\OutOfBoundsException $e) {
            throw new \RuntimeException("Failed to render partial '$elementId'. No such element exists on the form.");
        }
    }


    /**
     * @param array $keys
     * @return string
     */
    protected function attributes(array $keys = [])
    {
        if (empty($keys)) {
            $keys = array_keys($this->attributes);
        }
        
        $out = '';
        foreach ($keys as $key) {
            $value = $this->getValue($key)->attr();
            if (!empty($value)) {
                $out .= ' ' . $value;
            }
        }
        return ltrim($out, " ");
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
    protected  function createValue($key, $value)
    {
        return new Value($key, $value);
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
