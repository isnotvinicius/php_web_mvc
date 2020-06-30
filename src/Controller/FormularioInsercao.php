<?php

namespace Alura\Cursos\Controller;

class FormularioInsercao implements IControladorRequisicao
{
    public function processaRequisicao(): void
    {
        $titulo = 'Novo Curso';
        require __DIR__ . '/../../view/cursos/novo-curso.php';
    }
}