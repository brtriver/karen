<?php
namespace Karen;

trait Templatable
{
    protected $template;
    public function setTemplate($template)
    {
        $this->template = $template;
    }

    public function renderWithT($tpl, $args)
        {
            $output = $this->template->render($tpl, $args);
            return $this->render($output);
        }
}
