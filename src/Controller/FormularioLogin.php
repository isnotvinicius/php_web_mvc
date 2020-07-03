<?php


namespace Alura\Cursos\Controller;


class FormularioLogin extends ControllerComHtml implements IControladorRequisicao
{

    public function processaRequisicao(): void
    {
        echo $this->renderizaHtml('login/login.php', [
            'titulo' => 'Login',
        ]);
    }
}