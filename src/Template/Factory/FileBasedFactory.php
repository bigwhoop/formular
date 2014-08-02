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
class FileBasedFactory implements TemplateFactoryInterface
{
    /** @var array */
    private $templatesPaths = [];
    
    /** @var array */
    private $templatesMap = [];
    
    /** @var null|string */
    private $defaultNamespace = null;

    
    /**
     * {@inheritdoc}
     */
    public function setDefaultNamespace($namespace)
    {
        $this->defaultNamespace = $namespace;
        return $this;
    }


    /**
     * @param string $path
     * @param string|null $namespace
     * @return $this
     */
    public function addTemplatesPath($path, $namespace = null)
    {
        $this->templatesPaths[$path] = $namespace;
        return $this;
    }


    /**
     * {@inheritdoc}
     */
    public function createTemplate($name, array $attributes = [])
    {
        $templatePath = $this->getTemplatePath($name);
        $template = new Template($templatePath, $attributes);
        $template->setTemplateFactory($this);
        return $template;
    }
    

    
    /**
     * Returns the path to a given template.
     * 
     * The template name can contain a namespace like "template@ns" in case there are templates with the same name.
     * 
     * @param string $template
     * @return string
     * @throws \RuntimeException
     */
    private function getTemplatePath($template)
    {
        if (strpos($template, '@') === false && !empty($this->defaultNamespace)) {
            $template .= '@' . $this->defaultNamespace;
        }
        
        $map = $this->buildTemplatesMap();
        
        if (!array_key_exists($template, $map)) {
            throw new \RuntimeException("A template by the name '$template' does not exist. The following templates are available: " . join(', ', array_keys($map)));
        }
        return $map[$template];
    }
    

    /**
     * Builds a map of template names to paths.
     * 
     * @return array
     * @throws \OverflowException|\RuntimeException
     */
    private function buildTemplatesMap()
    {
        if (!empty($this->templatesMap)) {
            return $this->templatesMap;
        }
        
        $this->templatesMap = [];
        foreach ($this->templatesPaths as $templatesPath => $templatesNamespace) {
            if (!is_readable($templatesPath)) {
                throw new \RuntimeException("Templates path '$templatesPath' does not exist or is not readable.");
            }
            
            foreach (glob($templatesPath . '/*.phtml') as $templatePath) {
                $template = pathinfo($templatePath, PATHINFO_FILENAME);
                if ($templatesNamespace !== null) {
                    $template .= '@' . $templatesNamespace;
                }
                if (array_key_exists($template, $this->templatesMap)) {
                    throw new \OverflowException("Can't import template '$template' from '$templatePath' as a template with the same name already exists at '{$this->templatesMap[$template]}'. You may want to use namespaces.");
                }
                $this->templatesMap[$template] = $templatePath;
            }
        }
        return $this->templatesMap;
    }
}
