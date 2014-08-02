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
use Zend\Validator\ValidatorInterface as Validator;
use Zend\Validator\ValidatorChain;
use Zend\Validator\NotEmpty;

/**
 * @author Philippe Gerber <philippe@bigwhoop.ch>
 */
class ZendFrameworkAdapter implements ValidatorInterface
{
    /** @var string */
    private $label;
    
    /** @var Validator */
    private $validator;
    
    /** @var string */
    private $errorMessage = '';
    
    /** @var bool */
    private $allowEmpty = true;


    /**
     * @param string $label
     * @param Validator $validator   1st ...
     * @param Validator $validator   2nd ...
     * @param Validator $validator   3rd ...
     */
    public function __construct($label, Validator $validator)
    {
        $this->label = $label;
        
        $validatorChain = new ValidatorChain();
        foreach (array_slice(func_get_args(), 1) as $validator) {
            if ($validator instanceof NotEmpty) {
                $this->allowEmpty = false;
            }
            $validatorChain->attach($validator, true);
        }
        $this->validator = $validatorChain;
    }
    
    
    /**
     * {@inheritdoc}
     */
    public function isValid($value)
    {
        if (($this->allowEmpty && empty($value)) || $this->validator->isValid($value)) {
            $this->errorMessage = '';
            return true;
        }
        $this->errorMessage = (empty($this->label) ? '' : "{$this->label}: ") . join(' / ', $this->validator->getMessages());
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
