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
class FileTemplate extends AbstractTemplate
{
    /** @var string */
    private $path = '';
    
    
    /**
     * @param string $path
     * @throws \InvalidArgumentException
     */
    public function __construct($path)
    {
        if (!is_readable($path)) {
            throw new \InvalidArgumentException("Template '$path' must be readable.");
        }
        
        $this->path = $path;
    }


    /**
     * {@inheritdoc}
     */
    protected function renderTemplate()
    {
        ob_start();
        require $this->path;
        return ob_get_clean();
    }
}
