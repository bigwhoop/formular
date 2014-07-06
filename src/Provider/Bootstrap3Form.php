<?php
/**
 * This file is part of Formular.
 *
 * (c) Philippe Gerber
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace bigwhoop\Formular\Provider;
use bigwhoop\Formular\Form;

/**
 * @author Philippe Gerber <philippe@bigwhoop.ch>
 */
class Bootstrap3Form extends Form
{
    const NS = 'bs3';
    
    /** @var array */
    static protected $defaultOptions = [
        'orientation' => 'horizontal',      // string. Either 'basic', 'horizontal' or 'inline'.
        'default_ns'  => self::NS,          // string|null. The default namespace for the form elements.
    ];
    
    
    public function init()
    {
        $this->addTemplatesPath(__DIR__ . '/../../templates/bootstrap3', self::NS);
        $this->setDefaultNamespace($this->options['default_ns']);
        
        switch ($this->options['orientation'])
        {
            case 'inline'     : $formClass = 'form-inline';     break;
            case 'horizontal' : $formClass = 'form-horizontal'; break;
            default           : $formClass = '';                break;
        }
        
        $this->addElement('form@' . self::NS, [
            'class'    => $formClass,
            'elements' => $this->bindContinue(),
        ]);
        $this->addElement('errors@' . self::NS, [
            'errors' => $this->bind(function() {
                return $this->getErrorMessages();
            }),
        ]);
    }
}
