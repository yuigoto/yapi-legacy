<?php

/**
 * Caminho para a pasta de classes principal da API, é nesta pasta que serão
 * armazenadas as classes e métodos de retorno.
 *
 * Pode ser caminho relativo.
 *
 * @var string
 */
$application = "../api";

/**
 * Caminho do local aonde foi instalada a pasta "core" do projeto, pode ser um
 * caminho relativo.
 *
 * @var string
 */
$system = "../core/";

/**
 * Caminho para a pasta pública de uploads.
 *
 * Selecione um caminho acessível via navegador, mas lembre-se de protegê-lo!
 *
 * @var string
 */
$upload = "./upload/";

require_once "libs/_0.php";
require_once "core/base.php";
require_once "core/yapi.php";
require_once "core/yapi_forge.php";
header('Access-Control-Allow-Origin: *');
header( "Content-type: application/json" );
new YAPI( yapiRouter() );


