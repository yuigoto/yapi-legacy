<?php

class YAPI
{
    /**
     * Instância herdável do PDO, para conexão com o banco de dados.
     *
     * @var PDO
     */
    protected $db;
    
    /**
     * Handle interno para o HTTP request method.
     *
     * @var string
     */
    protected $method;
    
    /**
     * Construtor do YAPI.
     */
    public function __construct( $args = null )
    {
        // Inicializa banco de dados
        $this->initDatabase();
        
        // Se for a classe "PAI", realiza autoloading
        if ( get_class( $this ) === "YAPI" ) {
        }
    }
    
    private function execute( $args = null )
    {
        // Inicia um endpoint, para caso de erro
        $endpoint = $this->endpointInit();
        
        // Verifica argumentos
        if ( !is_array( $args ) || count( $args ) < 1 ) {
            // Bad request
            http_response_code( 400 );
            
            // Define mensagens e resultado
            $endpoint->result = false;
            $endpoint->resultType = "NOT_EXECUTED";
            $endpoint->message = "Método e classe inexistentes.";
            
            // Exibe endpoint
            echo json_encode( $endpoint );
        } else {
            // Define argumentos de busca
            $_idx   = 0;
            $path   = YAPI_MAIN;
            $file   = $path.$args[ $_idx ].".php";
            $exists = false;
            
            if ( !file_exists( $file ) ) {
                while ( !file_exists( $file ) ) {
                    // Add current index to path
                    $path.= $args[ $_idx ].YAPI_DR;
                    
                    // Checks if path exists
                    if ( is_dir( $path ) ) {
                        $_idx += 1;
                        
                        if ( isset( $args[ $_idx ] ) ) {
                            $file = $path.$args[ $_idx ].".php";
                            
                            if ( file_exists( $file ) ) {
                                $exists = true;
                                break;
                            }
                        }
                    } else {
                        break;
                    }
                }
            }
            
            if ( !file_exists( $file ) ) {
                while ( !file_exists( $file ) ) {
                    // Add new path item
                    $path.= $args[ $_idx ].YAPI_DR;
                    
                    if ( is_dir( $path ) ) {
                        $_idx += 1;
                        
                        if ( isset( $args[ $_idx ] ) ) {
                            $file = $path.$args[ $_idx ].".php";
                            
                            if ( !file_exists( $file ) ) {
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
            } else {
                // Inclui e instancia
                include "{$file}";
                $this->makeInstanceOf( $args );
            }
        }
    }
    
    /**
     * Instancia uma classe e executa o método indicado nos argumentos.
     *
     * Caso não exista o método, apenas executa a classe e passa os argumentos
     * à mesma.
     *
     * @param array $args
     *      Array com argumentos de caminho, classe, métodos e parâmetros
     * @param int $index
     *      Index do item, em $args, que indica o nome da classe, é a partir
     *      deste item que serão contados o nome do método e argumentos a serem
     *      passados
     */
    private function makeInstanceOf( $args, $index )
    {
    }
    
    /**
     * Inicializa um objeto de endpoint vazio, pronto para preenchimento e,
     * posteriormente, renderização em JSON.
     *
     * @return stdClass
     *      Objeto endpoint com argumentos, parâmetros e valores da API
     */
    protected function endpointInit()
    {
        // Inicializa objeto
        $return = new stdClass();
        
        // Define objeto interno para meta informações
        $return->info = new stdClass();
        
        // Nome e descrição
        $return->info->name = "";
        $return->info->description = "";
        
        // Membros/colunas/campos
        $return->members = array();
        
        // Resultado
        $return->result = null;
        
        // Tipo de resultado/status
        $return->resultType = null;
        
        // Mensagens de erro/sucesso
        $return->message = null;
        
        // Exceção e stack tracing
        $return->exception = null;
        
        // Data de início/finalização
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
