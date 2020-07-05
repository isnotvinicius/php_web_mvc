<?php

namespace Alura\Cursos\Controller;

use Alura\Cursos\Entity\Curso;
use Alura\Cursos\Helper\ControllerHtmlTrait;
use Alura\Cursos\Infra\EntityManagerCreator;

class FormularioEdicao  implements IControladorRequisicao
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
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if(is_null($id) || $id === false){
            header('Location: /listar-cursos');
            return;
        }

        $curso = $this->repositorioDeCursos->find($id);

        echo $this->renderizaHtml('cursos/novo-curso.php', [
            'curso' => $curso,
            'titulo' => "Alterar Curso " . $curso->getDescricao(),
        ]);
    }
}