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
class ElementQueue extends \SplQueue
{
    /**
     * @param ElementCollection $collection
     * @return ElementQueue
     */
    static public function createFromCollection(ElementCollection $collection)
    {
        $queue = new self();
        foreach ($collection->getElements() as $element) {
            $queue->enqueue($element);
        }
        return $queue;
    }
}
