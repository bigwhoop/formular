<?php
/**
 * This file is part of Formular.
 *
 * (c) Philippe Gerber
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace bigwhoop\Formular\TemplateFactory;

use bigwhoop\Formular\Template\AbstractTemplate;

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
     * @return AbstractTemplate
     */
    public function createTemplate($name);
}
