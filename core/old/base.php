<?php
/**
 * YAPI : Base
 * ======================================================================
 * Definição de constantes utilizadas no sistema.
 * ----------------------------------------------------------------------
 * @author      Fabio Y. Goto <lab@yuiti.com.br>
 * @since       0.0.1
 */

// DIRECTORY_SEPARATOR shorthand
if ( !defined( "YAPI_DR" ) ) {
    /**
     * Shorthand para `DIRECTORY_SEPARATOR`.
     *
     * @var string
     */
    define( "YAPI_DR", DIRECTORY_SEPARATOR );
}

// YAPI core
if ( !defined( "YAPI_CORE" ) ) {
    /**
     * Caminho para a pasta de núcleo do sistema.
     *
     * @var string
     */
    define( "YAPI_CORE", dirname( __FILE__ ).YAPI_DR );
}

// YAPI root
if ( !defined( "YAPI_ROOT" ) ) {
    /**
     * Caminho para a raiz do sistema.
     *
     * @var string
     */
    define( "YAPI_ROOT", dirname( YAPI_CORE ).YAPI_DR );
}

// YAPI api root
if ( !defined( "YAPI_MAIN" ) ) {
    /**
     * Caminho para a pasta com classes da API.
     *
     * @var string
     */
    define( "YAPI_MAIN", YAPI_ROOT."api".YAPI_DR );
    if ( !is_dir( YAPI_MAIN ) ) mkdir( YAPI_MAIN );
}

// YAPI blueprints root
if ( !defined( "YAPI_BLUEPRINTS" ) ) {
    /**
     * Caminho para pastas com blueprints do DBForgeTool.
     *
     * @var string
     */
    define( "YAPI_BLUEPRINTS", YAPI_ROOT."blueprints".YAPI_DR );
}

// YAPI libraries root
if ( !defined( "YAPI_LIBS" ) ) {
    /**
     * Caminho para pastas com bibliotecas utilizadas pelo sistema.
     *
     * @var string
     */
    define( "YAPI_LIBS", YAPI_ROOT."libs".YAPI_DR );
}

/**
 * Verifica se uma string é, ou não, JSON válido.
 *
 * @param string $data
 *      String para verificar se é, ou não, JSON
 * @return bool
 *      True se for JSON, false se não for
 */
function yapiIsJSON( $data )
{
    json_decode( $data );
    return ( json_last_error() == JSON_ERROR_NONE );
}

/**
 * Retorna o conteúdo da query string 'yapi'.
 */
function yapiRouter()
{
    if ( !isset( $_GET['yapi'] ) ) return array();
    
    $yapi = trim( $_GET['yapi'], "/\\" );
    
    // Retorna array vazio, se não houver nada
    if ( !$yapi || trim( $yapi ) === "" ) return array();
    
    // Substitui barra
    $yapi = str_replace( "\\", "/", $yapi );
    $yapi = explode( "/", $yapi );
    
    // Retorna
    return $yapi;
}

/**
 * Retorna todos os request headers, sendo possível filtrá-los.
 *
 * @param array|string $filter
 *      Opcional, deve ser uma string com nome, ou array com nomes, dos campos
 *      a serem filtrados no request, campos não inclusos na lista serão
 *      ignorados pela função, default: null
 * @return array
 *      Array associativo com os dados
 */
function yapiHeader( $filter = null )
{
    /**
     * Array com todos os request headers.
     *
     * @var array
     */
    $header = getallheaders();
    
    // Filtrando
    $temp = array();
    if ( is_string( $filter ) && trim( $filter ) != "" ) {
        $temp[$filter] = trim( $header[$filter] );
        $header = $temp;
    } else if ( is_array( $filter ) && count( $filter ) > 0 ) {
        foreach ( $filter as $item ) {
            if ( isset( $header[$item] ) && trim( $header[$item] ) != "" ) {
                $temp[$item] = trim( $header[$item] );
            }
        }
        $header = $temp;
    }
    
    return $header;
}

/**
 * Solicita os dados de formulário por um dos seguintes possíveis meios:
 * - Globais POST ou GET;
 * - Input stream do php://input;
 *
 * Importante:
 * 'php://input' NÃO funciona se os dados fornecidos forem 'multipart/form-data'.
 *
 * @param array|string $filter
 *      Opcional, deve ser uma string com nome, ou array com nomes, dos campos
 *      a serem filtrados no request, campos não inclusos na lista serão
 *      ignorados pela função, default: null
 * @return array
 *      Array associativo com os dados
 */
function yapiRequestData( $filter = null )
{
    /**
     * Request method, esperado que seja 'POST' ou 'GET'.
     *
     * @var string
     */
    $method = $_SERVER["REQUEST_METHOD"];
    
    /**
     * Array com dados GET ou POST, inicialment.e
     * @var array
     */
    $request = ( $method == "GET" ) ? $_GET : $_POST;
    
    // Se request for vazio, verifica se input stream contém JSON
    if ( $method == "PUT" || $method == "DELETE" || empty( $request ) ) {
        // Extrai input stream
        $request = file_get_contents( "php://input" );
        
        // Se o input stream for um array passível de decode
        if ( yapiIsJSON( $request ) ) $request = json_decode( $request, true );
    }
    
    // Filtrando
    $temp = array();
    if ( is_string( $filter ) && trim( $filter ) != "" ) {
        $temp[$filter] = trim( $request[$filter] );
        $request = $temp;
    } else if ( is_array( $filter ) && count( $filter ) > 0 ) {
        foreach ( $filter as $item ) {
            if ( isset( $request[$item] ) && trim( $request[$item] ) != "" ) {
                $temp[$item] = trim( $request[$item] );
            }
        }
        $request = $temp;
    }
    
    return $request;
}

/**
 * Solicita arquivos enviados através de formulário 'multipart/form-data'.
 *
 * Retorna, basicamente, o array $_FILES, sendo possível filtrar qual item de
 * $_FILES será retornado.
 *
 * Arquivos são aceitos, apenas, se o método for POST.
 *
 * @param array|string $filter
 *      Opcional, deve ser uma string com nome, ou array com nomes, dos campos
 *      a serem filtrados no request, campos não inclusos na lista serão
 *      ignorados pela função, default: null
 * @return array|false
 *      Array associativo com os dados de arquivos, boolean false se não for
 *      possível enviar os arquivos
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
        $request = $_FILES;
        
        // Filtrando
        $temp = array();
        if ( is_string( $filter ) && trim( $filter ) != "" ) {
            $temp[$filter] = $request[$filter];
            $request = $temp;
        } else if ( is_array( $filter ) && count( $filter ) > 0 ) {
            foreach ( $filter as $item ) {
                if ( isset( $request[$item] ) && !empty( $request[$item] ) ) {
                    $temp[$item] = $request[$item];
                }
            }
            $request = $temp;
        }
        
        return $request;
    }
    
    return false;
}
