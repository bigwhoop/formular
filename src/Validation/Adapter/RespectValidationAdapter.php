<?php
/**
 * This file is part of Formular.
 *
 * (c) Philippe Gerber
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace bigwhoop\Formular\Validation\Adapter;
use bigwhoop\Formular\Validation\ValidatorInterface;
use Respect\Validation\Validator as RespectValidator;
use Respect\Validation\Exceptions\ValidationException;;

/**
 * @author Philippe Gerber <philippe@bigwhoop.ch>
 */
class RespectValidationAdapter implements ValidatorInterface
{
    /** @var string */
    private $label;
    
    /** @var RespectValidator */
    private $validator;
    
    /** @var string */
    private $errorMessage = '';


    /**
     * @param string $label
     * @param RespectValidator $validator
     */
    public function __construct($label, RespectValidator $validator)
    {
        $this->label = $label;
        $this->validator = $validator;
    }
    
    
    /**
     * {@inheritdoc}
     */
    public function isValid($value)
    {
        try {
            $this->validator->check($value);
            $this->errorMessage = '';
            return true;
        } catch (ValidationException $e) {
            $this->errorMessage = (empty($this->label) ? '' : "{$this->label}: ") . $e->getMessage();
            return false;
        }
    }
    
    
    /**
     * {@inheritdoc}
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }
}
