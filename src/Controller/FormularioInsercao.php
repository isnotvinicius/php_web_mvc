<?php

namespace Alura\Cursos\Controller;

class FormularioInsercao extends ControllerComHtml implements IControladorRequisicao
{
    public function processaRequisicao(): void
    {
        echo $this->renderizaHtml('cursos/novo-curso.php', [
            'titulo' => 'Novo curso',
        ]);
    }
}