<?php
/**
 * YAPI : Yuiti's API
 * ======================================================================
 * YAPI is a simple
 * ----------------------------------------------------------------------
 * @package     br.com.yuiti.yapi
 * @author      Fabio Y. Goto <lab@yuiti.com.br>
 * @version     0.0.1
 * @copyright   ©2017 Fabio Y. Goto
 * @license     MIT License
 */

// BASE VARIABLES
// ----------------------------------------------------------------------

/**
 * Caminho para a pasta de classes principal da API, é nesta pasta que serão
 * armazenadas as classes e métodos de retorno.
 *
 * @var string
 */
$yapi_app = "./api/";

/**
 * Caminho para a pasta de blueprints criados pelo DBForge.
 *
 * Salvo pelos blueprints do profiler, os blueprints da API devem ser guardados
 * nesta pasta.
 *
 * @var string
 */
$yapi_blueprints = "./blueprints/";

/**
 * Caminho aonde serão salvos dados estáticos, além do banco de dados SQLite e
 * qualquer outra informação gerada pelo sistema.
 *
 * Deve ser diferente da pasta de uploads e deve estar bem protegida!
 *
 * @var string
 */
$yapi_data = "./data/";

/**
 * Caminho do local aonde foi instalada a pasta "core" do projeto, pode ser um
 * caminho relativo.
 *
 * @var string
 */
$yapi_system = "./core/";

/**
 * Caminho para a pasta pública de uploads.
 *
 * Selecione um caminho acessível via navegador, mas lembre-se de protegê-lo!
 *
 * @var string
 */
$yapi_upload = "./upload/";

/**
 * URL de base para requests, com protocolo, mas sem trailing slash.
 *
 * @var string
 */
$yapi_url = "http://yapi.sx/";



// INCLUI BASEFILE E BOOTSTRAPPER
// ----------------------------------------------------------------------

// Inclui base file e inicia
include $yapi_system."base.php";



// INICIALIZA YAPI
// ----------------------------------------------------------------------

