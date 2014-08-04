<?php
/**
 * This file is part of Formular.
 *
 * (c) Philippe Gerber
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace bigwhoop\Formular\Template;

/**
 * @author Philippe Gerber <philippe@bigwhoop.ch>
 */
class CallbackTemplate extends AbstractTemplate
{
    /** @var callable */
    private $callback;


    /**
     * @param callable $callback
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }


    /**
     * {@inheritdoc}
     */
    protected function renderTemplate()
    {
        return call_user_func($this->callback, $this);
    }
}
