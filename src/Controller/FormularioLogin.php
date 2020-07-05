<?php


namespace Alura\Cursos\Controller;


use Alura\Cursos\Helper\ControllerHtmlTrait;

class FormularioLogin implements IControladorRequisicao
{
    use ControllerHtmlTrait;

    public function processaRequisicao(): void
    {
        echo $this->renderizaHtml('login/login.php', [
            'titulo' => 'Login',
        ]);
    }
}