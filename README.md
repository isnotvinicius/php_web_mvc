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

- Note que ao alterarmos um dado ele na verdade é inserido como um novo dado e o dado antigo permanece na nossa lista. Para que isso não aconteça vamos pegar o ID na nossa classe de persistência e vamos verificar se ele veio ou não nos dados do form. Caso o ID não venha junto da descrição sabemos que precisamos inserir o dado, caso o ID venha junto da descrição sabemos que precisamos atualizar este dado com a nova descrição e para isso utilizaremos o merge do entity manager.

```
    public function processaRequisicao(): void
    {
        $descricao = filter_input(INPUT_POST, 'descricao', FILTER_SANITIZE_STRING);
        $curso = new Curso();
        $curso->setDescricao($descricao);
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if(!is_null($id) && $id !== false){
            $curso->setId($id);
            $this->entityManager->merge($curso);
        }else{
            $this->entityManager->persist($curso);         
        }

        $this->entityManager->flush();
        header('Location: /listar-cursos', true, 302);
    }
```

- O método merge gerência a entidade passada como se ela estivesse no banco, faz as alterações necessárias e só depois envia o dado de fato para o banco de dados.

- No action do nosso form não podemos esquecer de verificar se o id já existe ou não com o isset, exatamente como fizemos no input de descrição.

```
<form action="/salvar-curso<?= isset($curso) ? '?id=' . $curso->getId() : ''; ?>" method="post">
```

- Com isto feito será possível alterar um dado sem inserir um novo na lista.


## Chamada do HTML

- É interessante isolarmos a chamada dos arquivos da view numa classe para que facilite a chamada evitando de passar o caminho completo para a view e também evitando de passarmos váriavies desnecessárias para a view.

- Criamos uma classe abstrata na pasta de Controller e nela adicionamos uma função que renderiza o HTML recebendo o caminho para o arquivo e os dados que serão enviados.

- Faremos um require para a pasta da view concatenando com o caminho passado.

- Faremos um extract dos dados para que as chaves do array associativo se transformem em váriaveis.

```
abstract class ControllerComHtml
{
    public function renderizaHtml(string $caminhoTemplate, array $dados): string
    {
        extract($dados);
        ob_start();
        require __DIR__ . '/../../view/' . $caminhoTemplate;
        $html = ob_get_clean();
        return $html;
    }
}
```

- Os métodos ob permitem que retornemos o conteúdo do html sem exibi-lo na tela, isto ajuda caso não seja necessário exibir o conteúdo na hora.

- As classes que chamam os arquivos da view precisam extender esta classe abstrata.

- Faremos um echo da chamada do método passando como parâmetros o caminho do arquivo e os dados que serão exibidos.

```
    public function processaRequisicao(): void
    {
        echo $this->renderizaHtml('cursos/listar-cursos.php', [
            'cursos' => $this->repositorioDeCursos->findAll(),
            'titulo' => 'Lista de Cursos',
        ]);
    }
```


## Senhas no banco de dados

- Ao inserirmos um usuário no banco é mais do que viável criptografarmos sua senha para que ela não seja visível a qualquer pessoa. Uma das tecnologias que permite isto é a Hash MD5 que transforma a senha do usuário em uma hash e a insere no banco, mas este é um método tão comum que já existem algumas tabelas de mapeamento de dados que possuem uma vasta quantidade de hashs e seus valores o que torna o método não tão seguro quanto deveria ser.

- O PHP nos fornece uma API de criação de hashs chamada password_hash. Nela nós passamos a senha e o tipo de criptografia que será usada e a hash é criada para nós.
<a>https://www.php.net/manual/pt_BR/function.password-hash.php</a>

- Neste exemplo estaremos inserindo o usuário no banco através da linha de comando, mas o correto é ter uma tela de cadastro.

- No terminal interativo passamos a senha desejada no método password_hash junto do tipo de criptografia.

```
echo password_hash('123456', PASSWORD_ARGON2I);
```

- Será exibido uma string na tela e usaremos ela no campo senha da tabela.

```
php vendor/bin/doctrine dbal:run-sql 'INSERT INTO usuarios (email, senha) VALUES ("vinicius@alura.com.br", "senha gerada");'
```

- Com isto o usuário será inserido no banco. Lembrando que o correto é ter este processo numa tela de cadastro.


## Realizando o Login

- Para verificarmos se o usuário está cadastrado no sistema precisamos comparar a senha digitada no form com a senha cadastrada. Para isso nós iremos transformar a senha digitada em hash e comparar com a hash cadastrada no banco de dados.

- Pegamos o email e a senha do usuário através do filter_input.

- Verificamos se o email existe na tabela, ou seja, está cadastrado no sistema, caso esteja faremos a comparação da senha.

```
$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);

    if(is_null($email) || $email === false){
        echo "Email inválido";
        return;
    }
    $senha = filter_input(INPUT_POST, 'senha', FILTER_SANITIZE_STRING);
```
- A verificação pode ser feita na própria controller ou então dentro da entidade. Neste exemplo ela sendo feita dentro da entidade através do método senhaEstaCorreta. Este método recebe a senha digitada no form e executa o método password_verify que recebe como parâmetro a senha digitada e a senha em hash para a comparação.

```
$usuario = $this->repositorioDeUsuarios->findOneBy(['email' => $email]);

    if(is_null($usuario) || !$usuario->senhaEstaCorreta($senha)){
        echo "Email ou senha inválidos";
        return;
    }

public function senhaEstaCorreta(string $senhaPura): bool
{
    return password_verify($senhaPura, $this->senha);
}
```

- Depois de verificado o usuário e senha redirecionamos ele para a lista de cursos.


## Sessão

- Como sabemos o HTTP não armazena estados nas requisições, então como saber se um usuário está logado ou não no sistema? Exatamente, com sessões.

- Através de um número que o navegador pode salvar em um cookie, cada usuário da aplicação pode ser identificado, e no servidor, as informações de cada um podem ser armazenadas para que sejam buscadas novamente na próxima requisição.

- Iniciamos uma sessão no PHP com o ```session_start()``` e através disso temos acesso ao array associativo $_SESSION onde podemos defifinir qualquer coisa com qualquer valor. Como por exemplo ``` $_SESSION['nome'] = 'Vinicius';```.

- Por termos apenas um único ponto de entrada, arquivo index, podemos iniciar a sessão lá fazendo com que sempre que chamarmos uma controle também criamos uma sessão. O session_start() precisa ser executado SEMPRE antes de uma saída.

- Depois de iniciarmos a sessão podemos adicionar um if para verificar se o indice definido na controle existe, caso não exista encerramos a aplicação.


- Verificamos também se o caminho possui a palavra login, caso possua isto quer dizer que o usuário já está na página de login e não precisamos redirecionar ele, o que evita um lopping infinito.

```
session_start();

$RotaLogin = stripos($caminho, 'login');

if(!isset($_SESSION['logado']) && $RotaLogin === false){
    header('Location: /login');
    exit();
}
```

- Para encerrar a sessão do usuário e fazer ele deslogar do sistema faremos com que o botão de deslogar leve para a controle de deslogar e lá utilizaremos o ```session_destroy();``` e redirecionaremos ele para a página de login.

- A sessão nos possibilita fazer muitas coisas, como exibir mensagens, realizar login de usuários entre outras coisas.


## PSRs e boas práticas

- PSR são padrões de desenvolvimento universais utilizados na linguagem PHP. Elas englobam várias regras para se escrever o código e são difíceis de explicar, este projeto utiliza algumas delas. As PSRs são de extrema importância nos códigos por isso vale a pena checar a documentação oficial delas e entende-las. <a>https://www.php-fig.org/</a>


## WebServices

- Os web services são funções de softwares que apresentam uma estrutura arquitetural que permitem a comunicação entre aplicações, mesmo que suas linguagens sejam diferentes. Desse jeito nós podemos devolver os dados de outra forma que não seja HTML para o desenvolvimento da aplicação em outras plataformas por exemplo.

- Para fornecer os dados em JSON nós iremos:
    * Criar a classe controladora tendo um repositório do dado desejado no construtor.
    * Fazemos o ```findAll();``` para o repositório.
    * Chamamos o método ```json_enconde();``` passando os cursos como parâmetro.
    * Como os dados da entidade estão privados precisamos implementar uma interface chamada JsonSerialize na nossa entidade e retornar os dados como um array associativo para que os dados de fato apareçam na tela.

```
class Curso implements \JsonSerializable
{
    public function jsonSerialize()
    {
        return [
        'id' => $this->id,
        'descricao' => $this->descricao,
        ];
    }
}
```

- Para fornecer os dados em XML nós não temos um método que faz isso como em JSON, mas podemos fazer a mão com PHP puro.
    
    * Primeiro precisamos fazer um ```findAll();``` utilizando o repositório de cursos.
    * Depois iremos instanciar um objeto da classe XMLElement passando como parâmetro a tag que desejamos que apareça na tela.
    * Fazemos um foreach dos cursos e dentro dele chamamos o método ```addChild()``` do nosso objeto passando o nome que colocamos na tag e os dados que iremos passar.
    * Finalizamos retornando a respostas como XML.

```
public function handle(ServerRequestInterface $request): ResponseInterface
{
    $cursos = $this->repositorioDeCursos->findAll();
    $cursosEmXml = new \SimpleXMLElement('<cursos/>');

    foreach($cursos as $curso){
        $cursoEmXml = $cursosEmXml->addChild('curso');
        $cursoEmXml->addChild('id', $curso->getId());
        $cursoEmXml->addChild('descricao', $curso->getDescricao());
    }
    return new Response(200, ['Content-Type' => 'application/xml'], $cursoEmXml->asXML());
}

```

- Lembrando que não implementamos autenticação então qualquer usuário pode requisitar estes serviços. Para fazermos uma autenticação em WebServices fazemos o uso da API Key, que faz com que a cada requisição seja pedida a chave única do usuário para autenticação.