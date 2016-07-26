<?php

/*
 * This file is part of FacturaSctipts
 * Copyright (C) 2016  Carlos Garcia Gomez  neorazorx@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

require_model('presupuesto_cliente.php');
require_model('albaran_cliente.php');
require_model('factura_cliente.php');
require_model('pedido_cliente.php');

/**
 * Description of megacopiador
 *
 * @author Carlos García Gómez
 */
class megacopiador extends fs_controller
{
   public $documento;
   public $tipo;

   public function __construct()
   {
      parent::__construct(__CLASS__, 'Megacopiador', 'ventas', FALSE, FALSE);
   }

   protected function private_core()
   {
      $this->share_extensions();

      $this->documento = FALSE;
      $this->tipo = FALSE;
      
      if( isset($_REQUEST['id']) )
      {
         /**
          * Si nos llega un ID, comprobamos el tipo de documento que es y hacemos
          * una cosa distinta para cada tipo.
          */
         
         if( isset($_REQUEST['presu']) )
         {
            $presu = new presupuesto_cliente();
            $this->documento = $presu->get($_REQUEST['id']);
            $this->tipo = 'presu';

            if($this->documento)
            {
               if( isset($_REQUEST['copiar']) )
               {
                  /**
                   * Si nos llega la variable copiar es que han pulsado el botón
                   * de copiar, así que copiamos el presupuesto.
                   */
                  
                  $presu = clone $this->documento;
                  $presu->idpresupuesto = NULL;
                  $presu->idpedido = NULL;
                  $presu->fecha = $this->today();
                  $presu->hora = $this->hour();
                  $presu->status = 0;
                  if( $presu->save() )
                  {
                     /// también copiamos las líneas del presupuesto
                     foreach($this->documento->get_lineas() as $linea)
                     {
                        $newl = clone $linea;
                        $newl->idlinea = NULL;
                        $newl->idpresupuesto = $presu->idpresupuesto;
                        $newl->save();
                     }

                     $this->new_message('<a href="' . $presu->url() . '">Documento</a> de ' . FS_PRESUPUESTO . ' copiado correctamente.');
                  }
                  else
                  {
                     $this->new_error_msg('Error al copiar el documento.');
                  }
               }
            }
         }
         else if( isset($_REQUEST['albaran']) )
         {
            $albaran = new albaran_cliente();
            $this->documento = $albaran->get($_REQUEST['id']);
            $this->tipo = 'albaran';

            if($this->documento)
            {
               if( isset($_REQUEST['copiar']) )
               {
                  /**
                   * Si nos llega la variable copiar es que han pulsado el botón
                   * de copiar, así que copiamos el albarán.
                   */
                  
                  $albaran = clone $this->documento;
                  $albaran->idpresupuesto = NULL;
                  $albaran->idpedido = NULL;
                  $albaran->idalbaran = NULL;
                  $albaran->fecha = $this->today();
                  $albaran->hora = $this->hour();
                  $albaran->observaciones = $this->observaciones;
                  if( $albaran->save() )
                  {
                     /// también copiamos las líneas del albarán
                     foreach($this->documento->get_lineas() as $linea)
                     {
                        $newl = clone $linea;
                        $newl->idlinea = NULL;
                        $newl->idalbaran = $albaran->idalbaran;
                        $newl->save();
                     }

                     $this->new_message('<a href="' . $albaran->url() . '">Documento</a> de ' . FS_ALBARAN . ' copiado correctamente.');
                  }
                  else
                  {
                     $this->new_error_msg('Error al copiar el documento.');
                  }
               }
            }
         }
         else if( isset($_REQUEST['factura']) )
         {
            $factura = new factura_cliente();
            $this->documento = $factura->get($_REQUEST['id']);
            $this->tipo = 'factura';

            if($this->documento)
            {
               if( isset($_REQUEST['copiar']) )
               {
                  /**
                   * Si nos llega la variable copiar es que han pulsado el botón
                   * de copiar, así que copiamos la factura.
                   */
                  
                  $factura = clone $this->documento;
                  $factura->idpresupuesto = NULL;
                  $factura->idpedido = NULL;
                  $factura->idalbaran = NULL;
                  $factura->idfactura = NULL;
                  $factura->fecha = $this->today();
                  $factura->hora = $this->hour();
                  $factura->observaciones = $this->observaciones;
                  if( $factura->save() )
                  {
                     /// también copiamos las líneas de la factura
                     foreach($this->documento->get_lineas() as $linea)
                     {
                        $newl = clone $linea;
                        $newl->idlinea = NULL;
                        $newl->idfactura = $factura->idfactura;
                        $newl->save();
                     }

                     $this->new_message('<a href="' . $factura->url() . '">Documento</a> de factura copiado correctamente.');
                  }
                  else
                  {
                     $this->new_error_msg('Error al copiar el documento.');
                  }
               }
            }
         }
         else if( isset($_REQUEST['pedido']) )
         {
            $pedido = new pedido_cliente();
            $this->documento = $pedido->get($_REQUEST['id']);
            $this->tipo = 'pedido';

            if($this->documento)
            {
               if( isset($_REQUEST['copiar']) )
               {
                  /**
                   * Si nos llega la variable copiar es que han pulsado el botón
                   * de copiar, así que copiamos el pedido.
                   */
                  
                  $pedido = clone $this->documento;
                  $pedido->idpresupuesto = NULL;
                  $pedido->idpedido = NULL;
                  $pedido->fecha = $this->today();
                  $pedido->hora = $this->hour();
                  if( $pedido->save() )
                  {
                     /// también copiamos las líneas del pedido
                     foreach($this->documento->get_lineas() as $linea)
                     {
                        $newl = clone $linea;
                        $newl->idlinea = NULL;
                        $newl->idpedido = $pedido->idpedido;
                        $newl->save();
                     }

                     $this->new_message('<a href="' . $pedido->url() . '">Documento</a> de ' . FS_PEDIDO . ' copiado correctamente.');
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

   public function url()
   {
      if($this->documento)
      {
         return 'index.php?page=' . __CLASS__ . '&id=' . $_REQUEST['id'] . '&' . $this->tipo . '=TRUE';
      }
      else
      {
         return 'index.php?page=' . __CLASS__;
      }
   }
   
   /**
    * Añadimos las extensiones (los botones).
    */
   private function share_extensions()
   {
      $fsext = new fs_extension();
      $fsext->name = 'copiar_presu';
      $fsext->from = __CLASS__;
      $fsext->to = 'ventas_presupuesto';
      $fsext->type = 'button';
      $fsext->text = '<span class="glyphicon glyphicon-scissors" aria-hidden="true"></span><span>&nbsp; Copiar</span>';
      $fsext->params = '&presu=TRUE';
      $fsext->save();
      
      unset($fsext);
      
      $fsext = new fs_extension();
      $fsext->name = 'copiar_pedido';
      $fsext->from = __CLASS__;
      $fsext->to = 'ventas_pedido';
      $fsext->type = 'button';
      $fsext->text = '<span class="glyphicon glyphicon-scissors" aria-hidden="true"></span><span>&nbsp; Copiar</span>';
      $fsext->params = '&pedido=TRUE';
      $fsext->save();
      
      unset($fsext);
      
      $fsext = new fs_extension();
      $fsext->name = 'copiar_albaran';
      $fsext->from = __CLASS__;
      $fsext->to = 'ventas_albaran';
      $fsext->type = 'button';
      $fsext->text = '<span class="glyphicon glyphicon-scissors" aria-hidden="true"></span><span>&nbsp; Copiar</span>';
      $fsext->params = '&albaran=TRUE';
      $fsext->save();
      
      unset($fsext);
      
      $fsext = new fs_extension();
      $fsext->name = 'copiar_factura';
      $fsext->from = __CLASS__;
      $fsext->to = 'ventas_factura';
      $fsext->type = 'button';
      $fsext->text = '<span class="glyphicon glyphicon-scissors" aria-hidden="true"></span><span>&nbsp; Copiar</span>';
      $fsext->params = '&factura=TRUE';
      $fsext->save();
   }
}
