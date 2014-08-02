<?php
/**
 * This file is part of Formular.
 *
 * (c) Philippe Gerber
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace bigwhoop\Formular\Template\Factory;

use bigwhoop\Formular\Template\Template;

/**
 * @author Philippe Gerber <philippe@bigwhoop.ch>
 */
interface TemplateFactoryInterface
{
    /**
     * @param string|null $namespace
     * @return $this
     */
    public function setDefaultNamespace($namespace);
    
    /**
     * @param string $name
     * @param array $attributes
     * @return Template
     */
    public function createTemplate($name, array $attributes = []);
}
