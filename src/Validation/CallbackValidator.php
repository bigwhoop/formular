<?php
/**
 * This file is part of Formular.
 *
 * (c) Philippe Gerber
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace bigwhoop\Formular\Validation;

/**
 * @author Philippe Gerber <philippe@bigwhoop.ch>
 */
class CallbackValidator implements ValidatorInterface
{
    /** @var callable */
    private $callable;

    /** @var string */
    private $errorMessageTemplate = '';
    
    /** @var string */
    private $errorMessage = '';

    
    /**
     * @param callable $callable
     * @param string $errorMessageTemplate
     */
    public function __construct(callable $callable, $errorMessageTemplate)
    {
        $this->callable = $callable;
        $this->errorMessageTemplate = $errorMessageTemplate;
    }
    
    
    /**
     * {@inheritdoc}
     */
    public function isValid($value)
    {
        if (call_user_func($this->callable, $value)) {
            $this->errorMessage = '';
            return true;
        }
        $this->errorMessage = str_replace('%VALUE%', print_r($value, true), $this->errorMessageTemplate);
        return false;
    }
    
    
    /**
     * {@inheritdoc}
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }
}
