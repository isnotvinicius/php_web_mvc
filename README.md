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
