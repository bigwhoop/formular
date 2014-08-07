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
class ElementCollection
{
    /** @var ElementInterface[] */
    private $elements = [];


    /**
     * @return $this
     */
    public function reset()
    {
        $this->elements = [];
        return $this;
    }


    /**
     * @param ElementInterface $element
     * @return $this
     */
    public function addElement(ElementInterface $element)
    {
        $this->elements[] = $element;
        return $this;
    }


    /**
     * @return ElementInterface[]
     */
    public function getElements()
    {
        return $this->elements;
    }

    
    /**
     * @param string $id
     * @return ElementInterface
     * @throws \OutOfBoundsException
     */
    public function getElementByID($id)
    {
        foreach ($this->elements as $element) {
            if ($element->getID() === $id) {
                return $element;
            }
        }
        throw new \OutOfBoundsException("No element with ID '$id' exists.");
    }
}
