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

use Zend\Escaper\Escaper;

/**
 * @author Philippe Gerber <philippe@bigwhoop.ch>
 */
class ZendEscaperAdapter implements EscaperInterface
{
    /** @var Escaper */
    private $escaper;
    
    
    /**
     * @param Escaper|null $escaper
     */
    public function __construct(Escaper $escaper = null)
    {
        if (!$escaper) {
            $escaper = new Escaper();
        }
        $this->escaper = $escaper;
    }


    /**
     * {@inheritdoc}
     */
    public function escapeHTML($value)
    {
        return $this->escaper->escapeHtml($value);
    }


    /**
     * {@inheritdoc}
     */
    public function escapeHTMLAttr($value)
    {
        return $this->escaper->escapeHtmlAttr($value);
    }
}
