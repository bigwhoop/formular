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
interface FilterInterface
{
    /**
     * @param mixed $value
     * @return mixed
     */
    public function filter($value);
}
