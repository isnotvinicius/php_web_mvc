<?php

namespace Alura\Cursos\Controller;

use Alura\Cursos\Entity\Curso;
use Alura\Cursos\Helper\ControllerHtmlTrait;
use Alura\Cursos\Infra\EntityManagerCreator;

class ListarCursos implements IControladorRequisicao
{
    use ControllerHtmlTrait;

    private $repositorioDeCursos;

    public function __construct()
    {
        $entityManager = (new EntityManagerCreator())->getEntityManager();
        $this->repositorioDeCursos = $entityManager->getRepository(Curso::class);
    }

    public function processaRequisicao(): void
    {
        echo $this->renderizaHtml('cursos/listar-cursos.php', [
            'cursos' => $this->repositorioDeCursos->findAll(),
            'titulo' => 'Lista de Cursos',
        ]);
    }
}