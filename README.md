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


## Formulários

- Num formulário precisamos informar o action dele que é para onde ele irá enviar os dados contidos nele.

- Uma página HTML possuí dois verbos HTTP: GET e POST. 
    * O verbo GET serve para acessar uma URL ou atualizar uma página;
    * O verbo POST serve para enviar os dados junto com a requisição.

- Um formulário que cadastra algo no banco de dados por deve ter o method dele como POST.

- Existem outros métodos HTTP mas um formulario HTML trabalha apenas com POST e GET.


## Persistindo dados no banco

- Para persistirmos os dados de um formulário no banco de dados nós precisamos pegar os dados da view. Existem algumas formas de fazer isso com o PHP e neste exemplo estaremos utilizando a váriavel $_POST passando um indice, este que por sua vez é o nome do campo que queremos pegar o valor na view.

- Criamos um objeto da entidade que será persistida no banco.

- Adicionamos os valores resgatados da view com o método set passando as váriaveis que recebem o $_POST.

- Fazemos o persist e o flush utilizando o entityManager que foi inicializado no construtor.

```
    public function processaRequisicao(): void
    {
        $curso = new Curso();
        $curso->setDescricao($_POST['descricao']);
        $this->entityManager->persist($curso);
        $this->entityManager->flush();
    }
```

- Não podemos esquecer de chamar o método processaRequisicao() no arquivo index.


## Filtrar Dados

- É importante filtrarmos os dados para evitar problemas na hora da inserção. 

- O PHP nos fornece a função filter_input que pode nos ajudar nisso. Nela passamos como parâmetro o tipo de dado, no nosso caso INPUT_POST pois o dado está vindo de um post, e depois passamos a váriavel que queremos filtrar. 

- Com estes dois parâmetros estamos apenas pegando o dado, precisamos passar um terceiro parâmetro que é como este dado será filtrado. Existem várias formas de se filtrar o dado, no nosso caso utilizaremos o filtro FILTER_SANITIZE_STRING, ele filtra a string retirando caracteres que podem ser considerados maliciosos.

```
$descricao = filter_input(INPUT_POST, 'descricao', FILTER_SANITIZE_STRING);
```

- Para conhecer os outros tipos de filtro basta checar a documentação do PHP.
<a>https://www.php.net/manual/pt_BR/filter.constants.php</a>


## Cabeçalhos HTTP

- Note que após salvarmos um curso novo na nossa lista somos redirecionados a uma página vazia e se atualizarmos ela podemos reenviar os dados do formulário para a controle fazendo que o valor seja duplicado no banco.

- Para evitarmos isto podemos redirecionar o usuário para uma página específica utilizando um cabeçalho HTTP.

- Como o PHP funciona bem na WEB ele fornece algumas facilidades para nós como a função header(); que tem como parâmetro uma string onde iremos informar o cabeçalho HTTP e colocar para onde o usuário deve ser redirecionado após o dado ser persistido no banco.

```
header('Location: /listar-cursos');
```

- Podemos passar alguns outros parâmetros no cabeçalho também, tais como se iremos sobrepôr um cabeçalho já existente e o código de resposta HTTP. Mas apenas o location funcionaria para redirecionar o usuário para a página correta.

- Lembrando que o Location redireciona o usuário automaticamente impedindo que uma mensagem de erro ou sucesso seja exibida, para que uma mensagem seja exibida devemos utilizar ferramentas do lado do cliente como o JavaScript.


## Utilizando Rotas

- Para deixarmos o arquivo index mais legível e tirar nosso switch case ou if faremos o uso de rotas.

- Criamos um arquivo chamado routes.php e nele retornamos um array de rotas colocando como chave a URL e como valor a classe correspondente a requisição..

```
return [
    '/listar-curso' => ListarCursos::class,
    '/novo-curso' => FormularioInsercao::class,
    '/salvar-curso' => Persistencia::class,
];
```

- No nosso arquivo index precisamos ter duas váriaveis, uma para o caminho e uma para a rota:
    * O caminho recebe o $_SERVER['PATH_INFO'].
    * A rota recebe um require para o arquivo routes.php.

- Depois disso verificamos se este caminho NÃO está incluso no array de rotas e exibimos uma mensagem de página não encontrada com a função http_response_code caso isto seja verdade.

```
if(!array_key_exists($caminho, $rota)){
    http_response_code(404);
}
```

- Caso o caminho recebido exista no nosso array de rotas adicionamos ele a uma váriavel, instânciamos o objeto e chamamos o método processaRequisicao()

```
$classeControladora = $rota[$caminho];
$controlador = new $classeControladora();
$controlador->processaRequisicao();
```

- Note que estamos instânciando o objeto com uma váriavel e não com o nome da classe e isto é perfeitamente possível em PHP, basta que esta váriavel tenha como valor o nome da classe.

- Note também que estamos chamando apenas um método para todas as requisições e isto é possível pois estamos utilizando uma interface nas controladoras com o método processaRequisicao().


## Excluindo Dados

- Para excluirmos um dado precisamos pegar o id dele e utilizar este id para remover o dado do banco.

- Para pegarmos o id do form utilizamos o input_filter especificando o INPUT_GET, o id e validamos se o id é inteiro ou não com o FILTER_VALIDATE_INT.

- Fazemos um if para verificar se o id é válido ou não e caso não seja colocamos um header para redirecionar o usuário para a lista de cursos e um return para parar a função e evitar que o resto do código seja executado.

- Pegamos a referência do curso utilizando o entity manager e passando o id recebido como parâmetro.

- Chamamos o remove do entity manager passando o curso selecionado.

- Fazemos o flush no banco para enviar as alterações.

```
    public function processaRequisicao(): void
    {
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if(is_null($id) || $id === false){
            header('Location: /listar-cursos');
            return;
        }

        $curso = $this->entityManager->getReference(Curso::class, $id);
        $this->entityManager->remove($curso);
        $this->entityManager->flush();
        header('Location: /listar-cursos');
    }
```

- Para recebermos o id na url precisamos adicionar um getId() no botão de excluir.

```
<a href="/excluir-curso?id=<?= $curso->getId(); ?> class=""> Excluir </a>
```

- Algumas vezes pode ser gerado um erro de AbstractProxyFactory pois o Doctrine cria uma classe nova e a utiliza para fazer as manipulações. Caso se depare com este erro execute este comando na pasta do projeto e suba o servidor de novo.

```
php vendor/bin/doctrine orm:generate-proxies
```

## Alterando Dados

- Adicionamos o botão no formulário pegando o id do curso em questão.

```
<a href="/alterar-curso?id=<?= $curso->getId(); ?>" class=""> Alterar </a>
```

- Adicionamos a url no arquivo de rotas.

- Na controle iremos buscar o id da mesma forma que fizemos na hora de excluir.

- Criamos um repositório de cursos no método construtor.

```
    private $repositorioDeCursos;

    public function __construct()
    {
        $entityManager = (new EntityManagerCreator())->getEntityManager();
        $this->repositorioDeCursos = $entityManager->getRepository(Curso::class);
    }
```

- Fazemos um find passando o id recebido do form.

- Como estamos utilizando o mesmo formulário de criação de um curso passamos também o titulo do curso.

- Fazemos um require do formulário na view.

```
public function processaRequisicao(): void
{
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    if(is_null($id) || $id === false){
        header('Location: /listar-cursos');
        return;
    }

    $curso = $this->repositorioDeCursos->find($id);
    $titulo = "Alterar Curso " . $curso->getDescricao();
    require __DIR__ . '/../../view/cursos/novo-curso.php';
}   
```

- No input do formulário adicionamos o value colocando o nome do curso a ser alterado.

```
<input type="text" name="descricao" id="descricao" 
value="<?= isset($curso) ? $curso->getDescricao() : ''; ?>">
```

- Como estamos utilizando o mesmo form de criação de curso adicionamos o isset para que se o id for nulo o input não terá valor, caso receba um id o nome do curso será mostrado no input. Isto evita que o form de criação de curso dê erro na hora de exibir o input.
