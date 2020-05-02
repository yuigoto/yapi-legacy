<?php
/**
 * YAPI : Main
 * ======================================================================
 * Classe principal da YAPI, classes da API estendem desta.
 *
 * Ela, basicamente, inicializa o banco de dados, boa parte do trabalho braçal
 * deve ser realizado nos models da API.
 * ----------------------------------------------------------------------
 * @package     br.com.yuiti.yapi
 * @author      Fabio Y. Goto <lab@yuiti.com.br>
 * @version     0.0.1
 * @copyright   ©2017 Fabio Y. Goto
 * @license     MIT
 */
class YAPI
{
    /**
     * Handle PDO, para comunicação com banco de dados.
     *
     * @var PDO
     */
    protected $db;
    
    /**
     * HTTP request method utilizado.
     *
     * @var string
     */
    protected $method;
    
    /**
     * YAPI constructor.
     *
     * @param array $args
     *      Array com argumentos a serem passados para os modelos
     */
    public function __construct( $args = null ) {
        // Define request type
        $this->method = $_SERVER['REQUEST_METHOD'];
        
        // Initialize PDO
        $this->initDatabase();
        
        if ( get_class( $this ) === "YAPI" ) {
        
        } else {
        }
        
        if ( is_array( $args ) && count ( $args ) < 1 || !is_array( $args ) ) {
            if ( get_class( $this ) === "YAPI" ) {
                // Bad request
                http_response_code( 400 );
    
                // New empty endpoint
                $endpoint             = $this->endpointInit();
                $endpoint->result     = false;
                $endpoint->resultType = "NOT_EXECUTED";
    
                // Echoes empty endpoint
                echo json_encode( $endpoint );
            }
        } else {
            if ( get_class( $this ) === "YAPI" ) {
                $_idx   = 0;
                $path   = YAPI_MAIN;
                $file   = $path . $args[ $_idx ] . ".php";
                $exists = false;
    
                if ( ! file_exists( $file ) ) {
                    while ( ! file_exists( $file ) ) {
                        $path = $path . $args[ $_idx ] . YAPI_DR;
            
                        if ( is_dir( $path ) ) {
                            $_idx += 1;
                
                            if ( isset( $args[ $_idx ] ) ) {
                                $file = $path . $args[ $_idx ] . ".php";
                    
                                if ( ! file_exists( $file ) ) {
                                    $exists = false;
                                    break;
                                } else {
                                    $exists = true;
                                }
                            } else {
                                $exists = false;
                                break;
                            }
                        } else {
                            $exists = false;
                            break;
                        }
                    }
        
                    if ( $exists === false ) {
                        // Bad request
                        http_response_code( 404 );
            
                        // New empty endpoint
                        $endpoint             = $this->endpointInit();
                        $endpoint->result     = "Class not found.";
                        $endpoint->resultType = "NOT_FOUND";
            
                        // Echoes empty endpoint
                        echo json_encode( $endpoint );
                    } else {
                        include "{$file}";
                        $info = pathinfo( $file );
                        $this->makeInstanceOf( $info['filename'], $args, $_idx );
                    }
                } else {
                    include "{$file}";
                    $info = pathinfo( $file );
                    $this->makeInstanceOf( $info['filename'], $args, $_idx );
                }
            }
        }
    }
    
    /**
     * Instancia uma nova classe, executa o método desejado e passa os argumentos.
     *
     * Caso não haja métodos, apenas instancia uma nova classe, passando os itens
     * restantes como argumentos.
     *
     * @param $name
     *      Nome da classe a ser instanciada, normalmente é o primeiro argumento
     *      do array passado
     * @param array $args
     *      Array com os argumentos passados pela query string
     * @param int $idx
     *      Índex do primeiro item dos argumentos, normalmente é 0
     */
    protected function makeInstanceOf( $name, $args, $idx )
    {
        // Define nome da classe e incrementa index
        $name = ucwords( $name );
        $idx += 1;
        
        if ( !class_exists( $name ) ) {
            $return = $this->endpointInit();
            die;
        }
        
        // Array de argumentos
        $new_args = array();
        
        // Instância inicial
        $instance = new $name();
        
        // Verifica se método com o nome do primeiro argumento existe
        if ( isset( $args[$idx] ) && method_exists( $instance, $args[$idx] ) ) {
            // Define método, incrementa index
            $method = $args[$idx];
            $idx   += 1;
            
            // Populando array de argumentos
            if ( count( $args ) > $idx ) {
                for ( $i = $idx; $i < count( $args ); $i ++ ) {
                    $new_args[] = $args[$i];
                }
            }
            
            // Chama o método na instância e passa o array como argumentos
            call_user_func_array( array( $instance, $method ), $new_args );
        } else {
            // Populando array de argumentos
            if ( count( $args ) > $idx ) {
                for ( $i = $idx; $i < count( $args ); $i ++ ) {
                    $new_args[] = $args[$i];
                }
            }
            
            // Executa método index na instância
            if ( method_exists( $instance, "index" ) ) {
                $instance->index( $new_args );
            } else {
                // Bad request
                http_response_code( 404 );
    
                // Endpoint vazio
                $endpoint = $this->endpointInit();
                $endpoint->result = "Method not found on class '{$name}'.";
                $endpoint->resultType = "NOT_FOUND";
    
                // Exibe endpoint vazio
                echo json_encode( $endpoint );
            }
        }
    }
    
    /**
     * Inicializa um objeto de endpoint, para exibição em tela.
     *
     * O objeto é "vazio", é preciso preenchê-lo com dados dos modelos.
     *
     * @return stdClass
     */
    protected function endpointInit()
    {
        $return = new stdClass();
        
        // Detalhes
        $return->info = new stdClass();
        $return->info->name = "";
        $return->info->description = "";
        
        // Membros/colunas/campos
        $return->members = array();
        
        // Resultado do request
        $return->result = null;
        
        // Tipo do resultado
        $return->resultType = null;
        
        // Mensagem de erro, se necessário
        $return->message = null;
        
        // Exceção
        $return->exception = null;
        
        // Datas de início e finalização
        $return->started_at = time();
        $return->finished_in = null;
        return $return;
    }
    
    /**
     * Cria uma nova tabela no banco de dados, de acordo com o blueprint desejado.
     *
     * @param string $table_name
     *      Nome da tabela (e do blueprint) a ser criado
     */
    protected function buildTable( $table_name )
    {
        // Local do blueprint
        $file = YAPI_BLUEPRINTS."{$table_name}.dbforge";
        // Se arquivo existir, monta o comando
        if ( file_exists( $file ) ) {
            // Checa o driver de banco de dados
            $type = _0::dbTest( $this->db );
            
            // Monta o comando
            $cmds = YAPI_Forge::blueprint_build( $file, $type );
            
            // Cria tabela
            if ( !_0::dbTablesTest( $table_name, $this->db ) ) {
                _0::dbTablesInit( $table_name, $cmds, $this->db );
            }
        }
    }
    
    /**
     * Inicializa o objeto PDO para o banco de dados.
     */
    private function initDatabase()
    {
        // Lê configuração e inicia
        $read = json_decode( file_get_contents( YAPI_CORE."yapi.json" ) );
        $this->db = _0::dbInit(
            $read->database->type,
            $read->database->path,
            $read->database->name,
            $read->database->user,
            $read->database->password
        );
    }
}
