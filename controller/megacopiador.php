<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_model('presupuesto_cliente.php');

/**
 * Description of megacopiador
 *
 * @author neora
 */
class megacopiador extends fs_controller
{
    public $documento;
    public $tipo;
    
    public function __construct() {
        parent::__construct(__CLASS__, 'megacopiador', 'ventas', FALSE, FALSE);
    }
    
    protected function private_core() {
        /// añadimos el botón copiar al presupuesto
        $fsext = new fs_extension();
        $fsext->name = 'copiar_presu';
        $fsext->from = __CLASS__;
        $fsext->to = 'ventas_presupuesto';
        $fsext->type = 'button';
        $fsext->text = 'Copiar';
        $fsext->params = '&presu=TRUE';
        $fsext->save();
        
        $this->documento = FALSE;
        $this->tipo = FALSE;
        if( isset($_REQUEST['id']) )
        {
            if( isset($_REQUEST['presu']) )
            {
                $presu = new presupuesto_cliente();
                $this->documento = $presu->get($_REQUEST['id']);
                $this->tipo = 'presu';
                
                if($this->documento)
                {
                    if( isset($_REQUEST['copiar']) )
                    {
                        $presu = clone $this->documento;
                        $presu->idpresupuesto = NULL;
                        $presu->idpedido = NULL;
                        $presu->fecha = $this->today();
                        $presu->hora = $this->hour();
                        if( $presu->save() )
                        {
                            foreach($this->documento->get_lineas() as $linea)
                            {
                                $newl = clone $linea;
                                $newl->idlinea = NULL;
                                $newl->idpresupuesto = $presu->idpresupuesto;
                                $newl->save();
                            }
                            
                            $this->new_message('Documento copiado correctamente.');
                        }
                        else
                        {
                            $this->new_error_msg('Error al copiar el documento.');
                        }
                    }
                }
            }
        }
        
    }
    
    public function url() {
        if($this->documento)
        {
            return 'index.php?page='.__CLASS__.'&id='.$_REQUEST['id'].'&'.$this->tipo.'=TRUE';
        }
        else
        {
            return 'index.php?page='.__CLASS__;
        }
    }
}
