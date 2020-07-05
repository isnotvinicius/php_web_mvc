<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Alura\Cursos\Controller\IControladorRequisicao;

$caminho = $_SERVER['PATH_INFO'];
$rota = require __DIR__ . '/../config/routes.php';

if(!array_key_exists($caminho, $rota)){
    http_response_code(404);
}

session_start();

$RotaLogin = stripos($caminho, 'login');
if(!isset($_SESSION['logado']) && $RotaLogin === false){
    header('Location: /login');
    exit();
}
$classeControladora = $rota[$caminho];
/** @var IControladorRequisicao $controlador */
$controlador = new $classeControladora();
$controlador->processaRequisicao();


