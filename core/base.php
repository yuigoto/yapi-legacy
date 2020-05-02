<?php
/**
 * YAPI : Base
 * ======================================================================
 * Definição de constantes e funções globais do sistema.
 * ----------------------------------------------------------------------
 * @package     br.com.yuiti.yapi
 * @author      Fabio Y. Goto <lab@yuiti.com.br>
 * @version     0.0.1
 * @copyright   ©2017 Fabio Y. Goto
 * @license     MIT License
 */

// CRIANDO DIRETÓRIOS INICIAIS
if ( !is_dir( $yapi_app ) ) mkdir( $yapi_app );
if ( !is_dir( $yapi_blueprints ) ) mkdir( $yapi_blueprints );
if ( !is_dir( $yapi_data ) ) mkdir( $yapi_data );
if ( !is_dir( $yapi_upload ) ) mkdir( $yapi_upload );



// CONSTANTES
// ----------------------------------------------------------------------

// DIRECTORY_SEPARATOR shorthand
if ( !defined( "YAPI_DR" ) ) {
    /**
     * Shorthand para `DIRECTORY_SEPARATOR`.
     *
     * @var string
     */
    define( "YAPI_DR", DIRECTORY_SEPARATOR );
}

// YAPI core path
if ( !defined( "YAPI_CORE" ) ) {
    /**
     * Caminho para a pasta do núcleo do projeto.
     *
     * @var string
     */
    define( "YAPI_CORE", realpath( $yapi_system ).YAPI_DR );
}

// YAPI third-party libraries folder
if ( !defined( "YAPI_LIBS" ) ) {
    /**
     * Caminho para a pasta de bibliotecas de terceiros utilizadas no projeto.
     *
     * Deve conter apenas arquivos PHP.
     *
     * @var string
     */
    define( "YAPI_LIBS", YAPI_CORE."libs".YAPI_DR );
    if ( !is_dir( YAPI_LIBS ) ) mkdir( YAPI_LIBS );
}

// YAPI root path
if ( !defined( "YAPI_ROOT" ) ) {
    /**
     * Caminho para o root do projeto.
     *
     * @var string
     */
    define( "YAPI_ROOT", dirname( YAPI_CORE ).YAPI_DR );
}

// YAPI main application path
if ( !defined( "YAPI_MAIN" ) ) {
    /**
     * Caminho para a pasta de aplicação principal, aonde devem ser salvas as
     * classes da API.
     *
     * @var string
     */
    define( "YAPI_MAIN", realpath( $yapi_app ).YAPI_DR );
}

// YAPI database forge blueprint path
if ( !defined( "YAPI_BLUEPRINTS" ) ) {
    /**
     * Caminho para a pasta de blueprints gerados pelo DBForgeTool.
     *
     * @var string
     */
    define( "YAPI_BLUEPRINTS", realpath( $yapi_blueprints ).YAPI_DR );
}

// YAPI data storage path
if ( !defined( "YAPI_DATA" ) ) {
    /**
     * Caminho para a pasta de armazenamento de dados do sistema.
     *
     * @var string
     */
    define( "YAPI_DATA", realpath( $yapi_data ).YAPI_DR );
}

// YAPI file upload path
if ( !defined( "YAPI_UPLOAD" ) ) {
    /**
     * Caminho para a pasta de uploads do sistema.
     *
     * @var string
     */
    define( "YAPI_UPLOAD", realpath( $yapi_upload ).YAPI_DR );
}

// Base URL
if ( !defined( "YAPI_URL" ) ) {
    /**
     * URL de base do sistema.
     *
     * @var string
     */
    define( "YAPI_URL", $yapi_url );
}



// FUNÇÕES GLOBAIS
// ----------------------------------------------------------------------

if ( !function_exists( "yapiAcceptedHeaders" ) ) {
    /**
     * Define headers PHP, de acordo com o definido no arquivo de configurações
     * na pasta root (`yapi.json`).
     */
    function yapiAcceptedHeaders()
    {
        // Lê arquivo de configurações
        $config = json_decode( file_get_contents( YAPI_CORE."yapi.json" ) );
        
        // Define headers
        foreach ( $config->headers as $name => $vals ) {
            header( $name .": ".$vals );
        }
    }
}

if ( !function_exists( "yapiIsJSON" ) ) {
    /**
     * Verifica se uma string pode ser, ou não, convertida em JSON válido.
     *
     * @param string $data
     *      String com dados para decoding em JSON
     * @return bool
     *      True, caso seja possível transformar $data em JSON, false se não for
     */
    function yapiIsJSON( $data )
    {
        json_decode( $data );
        return ( json__last_error() == JSON_ERROR_NONE );
    }
}

if ( !function_exists( "yapiRouter" ) ) {
    /**
     * Verifica se o conteúdo da query string `yapi` está vazio ou não, realiza o
     * explode e retorna um array com os membros do método a ser utilizado.
     *
     * @return array
     *      Array com dados do caminho e do método a ser utilizado, ou array vazio
     *      se nada for encontrado/declarado
     */
    function yapiRouter()
    {
        // Se não existir a query `yapi`, retorna vazio
        if ( !isset( $_GET['yapi'] ) ) return array();
        
        // Trimming
        $yapi = trim( $_GET['yapi'], "/\\" );
        
        // Se vazio, retorna array vazio
        if ( !$yapi || trim( $yapi ) === "" ) return array();
        
        // Substitui as barras e dá explode
        $yapi = explode( "/", str_replace( "\\", "/", $yapi ) );
        
        return $yapi;
    }
}

if ( !function_exists( "yapiRequestFilter" ) ) {
    /**
     * @param array $array
     *      Array associativo com dados para filtragem
     * @param string|array $filter
     *      String, ou array, contendo a chave associativa para filtrar no array
     *      de input
     * @return array
     *      Array com dados filtrados, ou array vazio, se não houver nada que
     *      coincida com o mesmo
     */
    function yapiRequestFilter( $array, $filter )
    {
        // Array temporário
        $temp = array();
        
        // Se não for string, nem array, retorna vazio
        if ( !is_string( $filter ) && !is_array( $filter ) ) return $temp;
        
        if ( is_string( $filter ) && trim( $filter ) !== "" ) {
            $filter = trim( $filter );
            // Se string, e existir
            if ( isset( $array[$filter] ) ) $temp[$filter] = $array[$filter];
        } elseif ( is_array( $filter ) && count( $filter ) > 0 ) {
            foreach ( $filter as $item ) {
                $item = trim( $item );
                if ( isset( $array[$item] ) ) $temp[$item] = $array[$item];
            }
        }
        
        return $temp;
    }
}

if ( !function_exists( "yapiHeader" ) ) {
    /**
     * Retorna todos os request headers, com possibilidade de filtrá-los.
     *
     * @param array|string $filter
     *      Opcional, deve ser uma string contendo o nome, ou um array com nomes,
     *      das chaves associativas/headers para filtragem, campos não inclusos
     *      nesta lista, ou com o nome desejado, seráo ignorados, default: null
     * @return array
     *      Array associativo com os headers
     */
    function yapiHeader( $filter = null )
    {
        /**
         * Array com todos os request headers.
         *
         * @var array
         */
        $header = getallheaders();
        
        // Verificando filtros
        if (
            ( is_string( $filter ) && trim( $filter ) != "" )
            || ( is_array( $filter ) && count( $filter ) > 0 )
        ) {
            $header = yapiRequestFilter( $header, $filter );
        }
        
        return $header;
    }
}

if ( !function_exists( "yapiRequestData" ) ) {
    /**
     * Solicita dados de formulário por um dos seguintes possíveis métodos:
     * - Globais `$_GET` ou `$_POST`;
     * - Input stream `php://input`;
     *
     * Para dados GET e POST, primeiro são verificadas as globais e, então, o input
     * stream.
     *
     * Para PUT e DELETE, apenas o input stream é aceito.
     *
     * IMPORTANTE:
     * `php://input` NÃO funciona com dados fornecidos como `multipart/form-data`.
     *
     * @param array|string $filter
     *      Opcional, deve ser uma string contendo o nome, ou um array com nomes,
     *      das chaves associativas para filtragem dos campos, nomes não inclusos
     *      nesta lista seráo ignorados, default: null
     * @return array
     *      Array associativo contendo dados de um dos tipos de request aceitos
     */
    function yapiRequestData( $filter = null )
    {
        /**
         * Request method, pode ser POST, GET, PUT OU DELETE.
         *
         * @var string
         */
        $method = $_SERVER["REQUEST_METHOD"];
        
        /**
         * Array associativo com dados de request.
         *
         * Inicialmente populado com conteúdo dos arrays $_GET ou $_POST, pode ser
         * preenchido com dados de stream de input, caso os arrays estejam vazios e
         * haja conteúdo no stream.
         *
         * @var array
         */
        $request = ( $method == "GET" ) ? $_GET : $_POST;
        
        // Se request for vazio, verifica input stream
        if ( $method == "PUT" || $method == "DELETE" || empty( $request ) ) {
            // Extrai stream e converte em JSON
            $request = file_get_contents( "php://input" );
            
            // Se for possível transformá-lo em JSON
            if ( yapiIsJSON( $request ) ) {
                $request = json_decode( $request, true );
            } else {
                // Caso contrário, esvazie
                $request = array();
            }
        }
        
        // Verificando filtros
        if (
            ( is_string( $filter ) && trim( $filter ) != "" )
            || ( is_array( $filter ) && count( $filter ) > 0 )
        ) {
            $request = yapiRequestFilter( $request, $filter );
        }
        
        return $request;
    }
}

if ( !function_exists( "yapiFileData" ) ) {
    /**
     * Solicita arquivos enviados através de formulários `multipart/form-data`.
     *
     * Retorna o conteúdo de `$_FILES`, mas com possibilidade de uma filtragem
     * básica dos itens a serem retornados.
     *
     * Retorna algo, apenas, se o método de request for `POST`.
     *
     * @param array|string $filter
     *      Opcional, deve ser uma string contendo o nome, ou um array com nomes,
     *      das chaves associativas para filtragem dos campos, nomes não inclusos
     *      nesta lista seráo ignorados, default: null
     * @return array
     *      Array associativo com os campos com dados de arquivos do request, ou
     *      array vazio se não houver nada
     */
    function yapiFileData( $filter = null )
    {
        /**
         * Request method, esperado que seja 'POST'.
         *
         * @var string
         */
        $method = $_SERVER["REQUEST_METHOD"];
        
        // Se post, define arquivos
        if ( $method == "POST" ) {
            // Define request
            $request = $_FILES;
            
            // Verificando filtros
            if (
                ( is_string( $filter ) && trim( $filter ) != "" )
                || ( is_array( $filter ) && count( $filter ) > 0 )
            ) {
                $request = yapiRequestFilter( $request, $filter );
            }
            
            return $request;
        }
        
        // Retorna array vazio, se o método for inválido
        return array();
    }
}



// INCLUI BIBLIOTECAS
// ----------------------------------------------------------------------

// Bibliotecas primárias e third-party
$yapi_read = glob( YAPI_LIBS."*.php" );
if ( count( $yapi_read ) ) foreach ( $yapi_read as $libs ) include $libs;

// Inclui YAPI
include YAPI_CORE."yapi.php";



// DEFINE HEADERS
// ----------------------------------------------------------------------

// Define headers em configurações
yapiAcceptedHeaders();



// EXECUTA API
// ----------------------------------------------------------------------

