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
    const ORIENTATION_BASIC = 'basic';
    const ORIENTATION_HORIZONTAL = 'horizontal';
    const ORIENTATION_INLINE = 'inline';
    
    const NS = 'bs3';
    
    /** @var array */
    static protected $defaultOptions = [
        'orientation'    => self::ORIENTATION_HORIZONTAL,  // string. See self::ORIENTATION_*
        'default_ns'     => self::NS,                      // string|null. The default namespace for the form elements.
        'form_element'   => [],                            // array. Passed on to the form element.
        'errors_element' => [],                            // array. Passed on to the errors element.
    ];
    
    
    public function init()
    {
        $this->addTemplatesPath(__DIR__ . '/../../templates/bootstrap3/' . $this->options['orientation'], self::NS);
        $this->setDefaultNamespace($this->options['default_ns']);
        
        $this->addElement('form@' . self::NS, $this->options['form_element'] + [
            'elements' => $this->bindContinue(),
        ]);
        $this->addElement('errors@' . self::NS, $this->options['errors_element'] + [
            'errors' => $this->bindErrorMessages(),
        ]);
    }
}
