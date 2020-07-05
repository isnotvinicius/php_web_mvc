<?php

namespace Alura\Cursos\Controller;

use Alura\Cursos\Helper\ControllerHtmlTrait;

class FormularioInsercao implements IControladorRequisicao
{
    use ControllerHtmlTrait;

    public function processaRequisicao(): void
    {
        echo $this->renderizaHtml('cursos/novo-curso.php', [
            'titulo' => 'Novo curso',
        ]);
    }
}