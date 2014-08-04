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
interface ElementInterface
{
    /**
     * @return string
     */
    public function getTemplateName();


    /**
     * @return array
     */
    public function getAttributes();


    /**
     * @return string|null
     */
    public function getID();


    /**
     * @param mixed $defaultValue
     * @return mixed
     */
    public function getValue($defaultValue = null);


    /**
     * @param mixed $value
     * @return $this
     */
    public function setValue($value);
}
