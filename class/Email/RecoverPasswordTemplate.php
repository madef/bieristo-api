<?php

namespace Bieristo\Email;

class RecoverPasswordTemplate extends TemplateAbstract
{
    protected function getTemplateName()
    {
        return 'recover-password';
    }
}

