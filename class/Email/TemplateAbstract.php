<?php

namespace Bieristo\Email;

abstract class TemplateAbstract
{
    abstract protected function getTemplateName();
    protected $vars = [];
    protected $lang = 'en';

    protected function getTemplate()
    {
        // Use https://htmlemail.io/inline/ to inline html
        return file_get_contents(dirname(__FILE__).'/templates/'.$this->lang.'/'.$this->getTemplateName().'.inlined.html');
    }

    public function setLanguage($lang)
    {
        $this->lang = $lang;

        return $this;
    }

    public function setVar($name, $value)
    {
        $this->vars[$name] = $value;

        return $this;
    }

    protected function getVars()
    {
        return $this->vars;
    }

    public function render()
    {
        $template = $this->getTemplate();
        foreach ($this->getVars() as $var => $value) {
            $template = str_replace('%'.$var.'%', $value, $template);
        }

        return $template;
    }
}
