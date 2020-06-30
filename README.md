# PHP Web com MVC

## URL

- Quando inicializamos um servidor web por padrão ele procura o arquivo index da aplicação para rodar. O mesmo se aplica caso coloquemos uma url qualquer SEM extensão.

- Para termos urls mais amigáveis para o usuário criaremos o arquivo index.php e nele utilizaremos a váriavel $_SERVER para pegar as urls e personalizar elas.

- Na váriavel $_SERVER no indice PATH_INFO faremos um if ou um switch com a url desejada e fazemos um require para o arquivo desejado.

```
<?php

switch($_SERVER['PATH_INFO']){
    case '/listar-cursos':
        require 'listar-cursos.php';
        break;
    case '/novo-curso':
        require 'formulario-novo-curso.php';
        break;
    default:
        echo "Error 404 - Page not found";
}
```

- Isto faz com que quando digitado um parametro na url a página seja direcionada para o arquivo correto ou para uma página de erro caso a url digitada não esteja correta.


## Controller

- A Controladora é quem determina que resposta será enviada de volta ao usuário quando ele faz uma requisição via navegador.

- Nela criamos as classes ListarCursos e FormularioInsercao, colocando o método processaRequisicao que retorna através do index a página requisitada.

- Fizemos uma interface para implementar o mesmo método em todas as classes e garantir a segurança da aplicação.

```
class ListarCursos implements IControladorRequisicao
{
    private $repositorioDeCursos;

    public function __construct()
    {
        $entityManager = (new EntityManagerCreator())->getEntityManager();
        $this->repositorioDeCursos = $entityManager->getRepository(Curso::class);
    }

    public function processaRequisicao(): void
    {
        $cursos = $this->repositorioDeCursos->findAll();
        require __DIR__ . '/../../view/cursos/listar-cursos.php';
    }
}

interface IControladorRequisicao
{
    public function processaRequisicao(): void;
}
```

- No arquivo index ao invés de fazermos um require para o arquivo em questão fizemos o require apenas do autoload e na hora de fazer a requisição nós criamos um objeto da classe desejada e chamamos o método processaRequisicao que é o responsável por devolver o html para o usuário.

```
require_once __DIR__ . '/../vendor/autoload.php';

switch($_SERVER['PATH_INFO']){
    case '/listar-cursos':
        $controlador = new ListarCursos();
        $controlador->processaRequisicao();
        break;
    case '/novo-curso':
        $controlador = new FormularioInsercao();
        $controlador->processaRequisicao();
        break;
    default:
        echo "Error 404 - Page not found";
}
```


## View

- A view é onde ficam as páginas da nossa aplicação, é através dela que a controle irá fazer a requisição das páginas para o usuário.

- No método que processa a requisição na controle precisamos colocar um require do arquivo html da view e a requisição é feita corretamente. Se uma váriavel criada no método de requisição precisa ser utilizada no html o require dá conta disto, basta utilizar os mesmos nomes de váriaveis no método e no html.