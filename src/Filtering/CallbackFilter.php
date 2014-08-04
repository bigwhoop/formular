<?php
/**
 * This file is part of Formular.
 *
 * (c) Philippe Gerber
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace bigwhoop\Formular\Filtering;

/**
 * @author Philippe Gerber <philippe@bigwhoop.ch>
 */
class CallbackFilter implements FilterInterface
{
    /** @var callable */
    private $callable;


    /**
     * @param callable $callable
     */
    public function __construct(callable $callable)
    {
        $this->callable = $callable;
    }
    
    
    /**
     * {@inheritdoc}
     */
    public function filter($value)
    {
        return call_user_func($this->callable, $value);
    }
}
