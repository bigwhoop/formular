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
interface ValidatorInterface
{
    /**
     * @param mixed $value
     * @return bool
     */
    public function isValid($value);
    
    /**
     * @return string
     */
    public function getErrorMessage();
}
