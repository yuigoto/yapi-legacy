<?php
class Healthcheck extends YAPI
{
    /**
     * Healthcheck constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    public function index( $args )
    {
        // Define http response code
        http_response_code( 200 );
    
        // Inicializando endpoint
        $endpoint               = $this->endpointInit();
        $endpoint->name         = "Healthcheck";
        $endpoint->description  = "Simple healthcheck for the API";
        $endpoint->result       = true;
        $endpoint->resultType   = "SUCCESS";
        $endpoint->message      = "Healthcheck funcionando perfeitamente";
        $endpoint->finished_in  = time();
    
        // Exibindo
        echo json_encode( $endpoint );
    }
}
