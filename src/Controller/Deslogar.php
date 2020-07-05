<?php

namespace Alura\Cursos\Controller;

class Deslogar implements IControladorRequisicao
{

    public function processaRequisicao(): void
    {
        session_destroy();
        header('Location: /login');
    }
}