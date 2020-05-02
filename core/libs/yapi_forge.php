<?php
/**
 * YAPI : Forge Blueprint Reader
 * ======================================================================
 * Parser para blueprints do DBForge Builder, permitindo o seu uso fora do
 * CodeIgniter, em API própria.
 *
 * É um parser simples, portanto muita coisa ainda deve ser feita manualmente,
 * como checagem de campos, sanitização, etc.
 *
 * Mas dá uma mãozinha na hora de criar a API.
 * ----------------------------------------------------------------------
 * @package     br.com.yuiti.yapi
 * @author      Fabio Y. Goto <lab@yuiti.com.br>
 * @version     0.0.1
 * @copyright   ©2017 Fabio Y. Goto
 * @license     MIT
 */
class YAPI_Forge
{
    /**
     * Realiza a leitura de um blueprint '*.dbforge' e retorna o comando para
     * criação/drop da tabela.
     *
     * @param string $file
     *      Caminho completo para a pasta com o blueprint da tabela
     * @param bool $is_sqlite
     *      Opcional, se o comando for para uma tabela em SQLite, precisa de
     *      pequenas mudanças, defina para true para ativá-la, default: false
     * @param bool $drop
     *      Opcional, caso seja necessário derrubar a tabela, se ela já existir,
     *      defina como true, default: false
     * @return string
     *      String com o comando SQL para criação da tabela
     */
    public static function blueprint_build(
        $file,
        $is_sqlite = false,
        $drop = false
    ) {
        // Lê blueprint e decoda o JSON
        $read = file_get_contents($file);
        $json = json_decode($read);
        $tabs = "    ";
        
        // Arrays temporários para dados
        $table_data = array();
        $column_data = array();
        
        // Abre comando
        if ( $drop ) {
            $table_data[] = "DROP TABLE `{$json->name}`;";
            $table_data[] = "CREATE TABLE `{$json->name}` (";
        } else {
            $table_data[] = "CREATE TABLE IF NOT EXISTS `{$json->name}` (";
        }
        
        // Monta os comandos de colunas
        foreach ( $json->columns as $column ) {
            $item = array();
            
            // Nome
            $item[] = "`{$column->name}`";
            
            // Tipo
            if ( $column->type === "INT" && $is_sqlite ) {
                $item[] = "INTEGER";
            } else {
                if ( isset( $column->constraint ) && $column->constraint !== "" ) {
                    $item[] = "{$column->type}({$column->constraint})";
                } else {
                    $item[] = $column->type;
                }
            }
            
            // Unsigned
            if ( isset( $column->unsigned ) && $column->unsigned ) {
                $item[] = "UNSIGNED";
            }
            
            // Nulo
            if ( isset( $column->null ) && $column->null === false ) {
                $item[] = "NOT NULL";
            }
            
            // Auto incrementação
            if ( isset( $column->auto_increment ) && !$is_sqlite ) {
                $item[] = "AUTO_INCREMENT";
            }
            
            // Chave primária
            if ( $column->meta->is_primary ) {
                $item[] = "PRIMARY KEY";
            }
            
            // Se é uma chave única
            if ( isset( $column->unique ) && $column->unique === true ) {
                $item[] = "UNIQUE";
            }
            
            // Valor padrão
            if ( isset( $column->default ) && $column->default !== "" ) {
                $item[] = "DEFAULT '{$column->default}'";
            }
            
            // Dá um implode e adiciona ao comando
            $column_data[] = $tabs.implode( " ", $item );
        }
        $table_data[] = implode( ",\r\n", $column_data );
        
        // Fecha a tabela
        $table_data[] = ( $is_sqlite ) ? ");" : ") ENGINE=MyISAM DEFAULT CHARSET=utf8;";
        return implode( "\r\n", $table_data );
    }
    
    /**
     * Realiza a leitura de um blueprint e retorna as informações do mesmo, pode
     * ser utilizado, por exemplo, para exibir dados de uma API.
     *
     * @param string $file
     *      Caminho completo para a pasta com o blueprint da tabela
     * @return string
     *      String JSON contendo as informações do blueprint
     */
    public static function blueprint_info( $file )
    {
        // Lê blueprint e decoda o JSON
        $read = file_get_contents($file);
        $json = json_decode($read);
        
        // Objeto para retorno
        $return = new stdClass();
        
        // Define propriedades
        $return->name = $json->name;
        $return->description = $json->description;
        $return->members = self::blueprint_members( $file );
        return json_encode($return);
    }
    
    /**
     * Realiza a leitura de um blueprint e retorna informações sobre os membros
     * inputáveis do mesmo.
     *
     * IMPORTANTE:
     * Exibe apenas campos que o usuário deve digitar e fornecer. Campos que
     * são obrigatórios, mas definidos pelo sistema/engine, não são exibidos e
     * devem ser processados internamente.
     *
     * @param string $file
     *      Caminho completo para a pasta com o blueprint da tabela
     * @return array
     *      Array contendo objetos com as informações dos membros
     */
    public static function blueprint_members( $file )
    {
        // Lê blueprint e decoda o JSON
        $read = file_get_contents($file);
        $json = json_decode($read);
        
        // Array de retorno
        $return = array();
        
        // Parsing de colunas
        foreach ( $json->columns as $column ) {
            // Se chave primária, auto incrementa ou não for input, pula
            if (
                $column->meta->is_primary
                && isset( $column->auto_increment )
                && $column->auto_increment
                || $column->meta->user_input === false
            ) {
                continue;
            }
            
            // Objeto temporário
            $temp = new stdClass();
            
            // Define propriedades do campo/coluna
            $temp->name = $column->name;
            $temp->value = ( isset( $column->default ) )
                ? $column->default : null;
            $temp->default = ( isset( $column->default ) )
                ? $column->default : null;
            $temp->message = null;
            $temp->label = $column->meta->label;
            $temp->description = $column->meta->description;
            $temp->placeholder = $column->meta->placeholder;
            $temp->is_primary = $column->meta->is_primary;
            $temp->required = $column->meta->required;
            switch ( $column->type ) {
                case "INT":
                    $temp->type = "integer";
                    break;
                case "VARCHAR":
                    $temp->type = "string";
                    break;
                case "TEXT":
                    $temp->type = "string";
                    break;
                case "REAL":
                case "FLOAT":
                case "DECIMAL":
                    $temp->type = "float";
                    break;
                case "BLOB":
                    $temp->type = "blob";
                    break;
                case "BOOLEAN":
                    $temp->type = "boolean";
                    break;
                default:
                    $temp->type = "string";
                    break;
            }
            $return[] = $temp;
        }
        return $return;
    }
    
    /**
     * Realiza a leitura de um blueprint e retorna informações sobre todas as
     * colunas da tabela, se são obrigatórias, nulas, possuem valor default,
     * etc.
     *
     * Similar à `blueprint_members()`, mas apenas para uso interno do sistema.
     *
     * @param string $file
     *      Caminho completo para a pasta com o blueprint da tabela
     * @return array
     *      Array contendo objetos com as informações das colunas
     */
    public static function blueprint_fields( $file )
    {
        // Lê blueprint e decoda o JSON
        $read = file_get_contents($file);
        $json = json_decode($read);
        
        // Array de retorno
        $return = array();
        
        // Parsing de colunas
        foreach ( $json->columns as $column ) {
            // Objeto temporário
            $temp = new stdClass();
            
            // Define propriedades do campo/coluna
            $temp->name = $column->name;
            $temp->default = ( isset( $column->default ) )
                ? $column->default : null;
            $temp->message = null;
            $temp->label = $column->meta->label;
            $temp->description = $column->meta->description;
            $temp->placeholder = $column->meta->placeholder;
            $temp->is_primary = $column->meta->is_primary;
            $temp->required = $column->meta->required;
            switch ( $column->type ) {
                case "INT":
                    $temp->type = "integer";
                    break;
                case "VARCHAR":
                    $temp->type = "string";
                    break;
                case "TEXT":
                    $temp->type = "string";
                    break;
                case "REAL":
                case "FLOAT":
                case "DECIMAL":
                    $temp->type = "float";
                    break;
                case "BLOB":
                    $temp->type = "blob";
                    break;
                case "BOOLEAN":
                    $temp->type = "boolean";
                    break;
                default:
                    $temp->type = "string";
                    break;
            }
            $return[] = $temp;
        }
        return $return;
    }
}
