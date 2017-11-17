<?php

/*
 * This file is part of megacopiador
 * Copyright (C) 2016-2017  Carlos Garcia Gomez  neorazorx@gmail.com
 * Copyright (C) 2016-2017  Luis Miguel Pérez Romero luismipr@gmail.com
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

require_model('albaran_cliente.php');
require_model('almacen.php');
require_model('articulo.php');
require_model('cliente.php');
require_model('factura_cliente.php');
require_model('pedido_cliente.php');
require_model('presupuesto_cliente.php');
require_model('serie.php');
require_model('servicio_cliente.php');

/**
 * Description of megacopiador
 *
 * @author Carlos García Gómez
 */
class megacopiador extends fs_controller
{
   public $almacen;
   public $cliente;
   public $documento;
   public $ejercicio;
   public $serie;
   public $tipo;
   public $tipo2;
   public $url_recarga;
   
   public function __construct()
   {
      parent::__construct(__CLASS__, 'Megacopiador', 'ventas', FALSE, FALSE);
   }

   protected function private_core()
   {
      $this->share_extensions();
      
      $this->almacen = new almacen();
      $this->cliente = new cliente();
      $this->documento = FALSE;
      $this->ejercicio = new ejercicio();
      $this->serie = new serie();
      $this->tipo = FALSE;
      $this->tipo2 = FALSE;
      $this->url_recarga = FALSE;
      
      if( isset($_REQUEST['buscar_cliente']) )
      {
         $this->buscar_cliente();
      }
      else if( isset($_REQUEST['id']) )
      {
         /// seleccionamos el cliente
         if( isset($_REQUEST['codcliente']) )
         {
            $cli0 = new cliente();
            $this->cliente = $cli0->get($_REQUEST['codcliente']);
         }
         
         /// seleccionamos serie
         if( isset($_REQUEST['codserie']) )
         {
            $ser0 = new serie();
            $this->serie = $ser0->get($_REQUEST['codserie']);
            if(!$this->serie)
            {
               $this->serie = $ser0->is_default();
            }
         }
         
         /// seleccionams almacen
         if( isset($_REQUEST['codalmacen']) )
         {
            $alm0 = new almacen();
            $this->almacen = $alm0->get($_REQUEST['codalmacen']);
            if(!$this->almacen)
            {
               $this->almacen = $alm0->is_default();
            }
         }
         
         $this->copiar_documento();
      }
      else if( isset($_REQUEST['articulo']) )
      {
         $this->copiar_articulo();
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
      $fsext->text = '<span class="glyphicon glyphicon-scissors" aria-hidden="true"></span>'
              . '<span class="hidden-xs">&nbsp; Copiar</span>';
      $fsext->params = '&presu=TRUE';
      $fsext->save();

      unset($fsext);

      $fsext = new fs_extension();
      $fsext->name = 'copiar_pedido';
      $fsext->from = __CLASS__;
      $fsext->to = 'ventas_pedido';
      $fsext->type = 'button';
      $fsext->text = '<span class="glyphicon glyphicon-scissors" aria-hidden="true"></span>'
              . '<span class="hidden-xs">&nbsp; Copiar</span>';
      $fsext->params = '&pedido=TRUE';
      $fsext->save();

      unset($fsext);

      $fsext = new fs_extension();
      $fsext->name = 'copiar_albaran';
      $fsext->from = __CLASS__;
      $fsext->to = 'ventas_albaran';
      $fsext->type = 'button';
      $fsext->text = '<span class="glyphicon glyphicon-scissors" aria-hidden="true"></span>'
              . '<spanclass="hidden-xs" >&nbsp; Copiar</span>';
      $fsext->params = '&albaran=TRUE';
      $fsext->save();

      unset($fsext);

      $fsext = new fs_extension();
      $fsext->name = 'copiar_factura';
      $fsext->from = __CLASS__;
      $fsext->to = 'ventas_factura';
      $fsext->type = 'button';
      $fsext->text = '<span class="glyphicon glyphicon-scissors" aria-hidden="true"></span>'
              . '<span class="hidden-xs">&nbsp; Copiar</span>';
      $fsext->params = '&factura=TRUE';
      $fsext->save();

      unset($fsext);

      $fsext = new fs_extension();
      $fsext->name = 'copiar_articulo';
      $fsext->from = __CLASS__;
      $fsext->to = 'ventas_articulo';
      $fsext->type = 'button';
      $fsext->text = '<span class="glyphicon glyphicon-scissors" aria-hidden="true"></span>'
              . '<span class="hidden-xs">&nbsp; Copiar</span>';
      $fsext->params = '&articulo=TRUE';
      $fsext->save();
      
      unset($fsext);

      $fsext = new fs_extension();
      $fsext->name = 'copiar_servicio';
      $fsext->from = __CLASS__;
      $fsext->to = 'ventas_servicio';
      $fsext->type = 'button';
      $fsext->text = '<span class="glyphicon glyphicon-scissors" aria-hidden="true"></span>'
              . '<span class="hidden-xs">&nbsp; Copiar</span>';
      $fsext->params = '&servicio=TRUE';
      $fsext->save();
   }
   
   private function buscar_cliente()
   {
      /// desactivamos la plantilla HTML
      $this->template = FALSE;

      $json = array();
      foreach($this->cliente->search($_REQUEST['buscar_cliente']) as $cli)
      {
         $json[] = array('value' => $cli->razonsocial, 'data' => $cli->codcliente);
      }
      
      header('Content-Type: application/json');
      echo json_encode(array('query' => $_REQUEST['buscar_cliente'], 'suggestions' => $json));
   }
   
   private function copiar_articulo()
   {
      $this->template = 'megacopiador_articulo';
      $art0 = new articulo();
      
      $artori = $art0->get($_REQUEST['ref']);
      if($artori)
      {
         $articulo = clone $artori;
         $articulo->referencia = $articulo->get_new_referencia();
         $articulo->stockfis = 0;
         
         if( $articulo->save() )
         {
            $this->url_recarga = $articulo->url();
         }
         else
         {
            $this->new_error_msg('Error al copiar el articulo.');
         }
      }
   }
   
   private function copiar_documento()
   {
      if( isset($_REQUEST['presu']) )
      {
         $this->copiar_presupuesto();
      }
      else if( isset($_REQUEST['albaran']) )
      {
         $this->copiar_albaran();
      }
      else if( isset($_REQUEST['factura']) )
      {
         $this->copiar_factura();
      }
      else if( isset($_REQUEST['pedido']) )
      {
         $this->copiar_pedido();
      }
      else if( isset($_REQUEST['servicio']) )
      {
         $this->copiar_servicio();
      }
   }
   
   private function copiar_factura()
   {
      $factura = new factura_cliente();
      $this->documento = $factura->get($_REQUEST['id']);
      $this->tipo = $this->tipo2 = 'factura';
      
      if($this->documento)
      {
         if( isset($_REQUEST['copiar']) )
         {
            /**
             * Si nos llega la variable copiar es que han pulsado el botón
             * de copiar, así que copiamos la factura.
             */
            $factura = clone $this->documento;
            $factura->idalbaran = NULL;
            $factura->idfactura = NULL;
            $factura->idfacturarect = NULL;
            $factura->idasiento = NULL;
            $factura->idasientop = NULL;
            $factura->codigorect = NULL;
            $factura->fecha = $_REQUEST['fecha'];
            $factura->vencimiento = date('d-m-Y', strtotime($_REQUEST['fecha'].' +1 month'));
            $factura->femail = NULL;
            $factura->numdocs = NULL;
            $factura->numero2 = $_REQUEST['numero2'];
            $factura->observaciones = $_REQUEST['observaciones'];
            $factura->codserie = $this->serie->codserie;
            $factura->codalmacen = $this->almacen->codalmacen;
            $factura->codagente = $this->user->codagente;
            
            /// necesitamos el ejercico correcto
            $ejercicio = $this->ejercicio->get_by_fecha($factura->fecha);
            if($ejercicio)
            {
               $factura->codejercicio = $ejercicio->codejercicio;
            }
            
            /// cliente:
            if($this->cliente)
            {
               $factura->codcliente = $this->cliente->codcliente;
               $factura->cifnif = $this->cliente->cifnif;
               $factura->nombrecliente = $this->cliente->razonsocial;
               
               foreach($this->cliente->get_direcciones() as $d)
               {
                  if($d->domfacturacion)
                  {
                     $factura->apartado = $d->apartado;
                     $factura->ciudad = $d->ciudad;
                     $factura->coddir = $d->id;
                     $factura->codpais = $d->codpais;
                     $factura->codpostal = $d->codpostal;
                     $factura->direccion = $d->direccion;
                     $factura->provincia = $d->provincia;
                     break;
                  }
               }
               
               if( $factura->save() )
               {
                  /// también copiamos las líneas de la factura
                  foreach($this->documento->get_lineas() as $linea)
                  {
                     $newl = clone $linea;
                     $newl->idlinea = NULL;
                     $newl->idfactura = $factura->idfactura;
                     $newl->idalbaran = NULL;
                     $newl->idlineaalbaran = NULL;
                     $newl->save();
                  }
                  
                  $this->new_message('<a href="' . $factura->url() . '">Documento</a> de factura copiado correctamente.');
               }
               else
               {
                  $this->new_error_msg('Error al copiar el documento.');
               }
            }
            else
            {
               $this->new_error_msg('No se ha encontrado el cliente');
            }
         }
      }
   }
   
   private function copiar_albaran() 
   {
      $albaran = new albaran_cliente();
      $this->documento = $albaran->get($_REQUEST['id']);
      $this->tipo = 'albaran';
      $this->tipo2 = FS_ALBARAN;
      
      if($this->documento)
      {
         if( isset($_REQUEST['copiar']) )
         {
            /**
             * Si nos llega la variable copiar es que han pulsado el botón
             * de copiar, así que copiamos el albarán.
             */
            $albaran = clone $this->documento;
            $albaran->idpedido = NULL;
            $albaran->idalbaran = NULL;
            $albaran->idfactura = NULL;
            $albaran->ptefactura = TRUE;
            $albaran->fecha = $_REQUEST['fecha'];
            $albaran->femail = NULL;
            $albaran->numdocs = NULL;
            $albaran->numero2 = $_REQUEST['numero2'];
            $albaran->codserie = $this->serie->codserie;
            $albaran->codalmacen = $this->almacen->codalmacen;
            $albaran->codagente = $this->user->codagente;
            $albaran->observaciones = $_REQUEST['observaciones'];
            
            /// necesitamos el ejercico correcto
            $ejercicio = $this->ejercicio->get_by_fecha($albaran->fecha);
            if($ejercicio)
            {
               $albaran->codejercicio = $ejercicio->codejercicio;
            }
            
            /// cliente:
            if($this->cliente)
            {
               $albaran->codcliente = $this->cliente->codcliente;
               $albaran->cifnif = $this->cliente->cifnif;
               $albaran->nombrecliente = $this->cliente->razonsocial;
               
               foreach($this->cliente->get_direcciones() as $d)
               {
                  if($d->domfacturacion)
                  {
                     $albaran->apartado = $d->apartado;
                     $albaran->ciudad = $d->ciudad;
                     $albaran->coddir = $d->id;
                     $albaran->codpais = $d->codpais;
                     $albaran->codpostal = $d->codpostal;
                     $albaran->direccion = $d->direccion;
                     $albaran->provincia = $d->provincia;
                     break;
                  }
               }
               
               if( $albaran->save() )
               {
                  /// también copiamos las líneas del albarán
                  foreach($this->documento->get_lineas() as $linea)
                  {
                     $newl = clone $linea;
                     $newl->idlinea = NULL;
                     $newl->idalbaran = $albaran->idalbaran;
                     $newl->idpedido = NULL;
                     $newl->idlineapedido = NULL;
                     $newl->save();
                  }
                  
                  $this->new_message('<a href="' . $albaran->url() . '">Documento</a> de ' . FS_ALBARAN . ' copiado correctamente.');
               }
               else
               {
                  $this->new_error_msg('Error al copiar el documento.');
               }
            }
            else
            {
               $this->new_error_msg('No se ha encontrado el cliente.');
            }
         }
      }
   }
   
   private function copiar_pedido()
   {
      $pedido = new pedido_cliente();
      $this->documento = $pedido->get($_REQUEST['id']);
      $this->tipo = 'pedido';
      $this->tipo2 = FS_PEDIDO;
      
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
            $pedido->idalbaran = NULL;
            $pedido->idoriginal = NULL;
            $pedido->fecha = $_REQUEST['fecha'];
            $pedido->fechasalida = NULL;
            $pedido->femail = NULL;
            $pedido->numdocs = NULL;
            $pedido->numero2 = $_REQUEST['numero2'];
            $pedido->codserie = $this->serie->codserie;
            $pedido->codalmacen = $this->almacen->codalmacen;
            $pedido->codagente = $this->user->codagente;
            $pedido->observaciones = $_REQUEST['observaciones'];
            
            /// necesitamos el ejercico correcto
            $ejercicio = $this->ejercicio->get_by_fecha($pedido->fecha);
            if($ejercicio)
            {
               $pedido->codejercicio = $ejercicio->codejercicio;
            }
            
            /// cliente:
            if($this->cliente)
            {
               $pedido->codcliente = $this->cliente->codcliente;
               $pedido->cifnif = $this->cliente->cifnif;
               $pedido->nombrecliente = $this->cliente->razonsocial;
               
               foreach($this->cliente->get_direcciones() as $d)
               {
                  if($d->domfacturacion)
                  {
                     $pedido->apartado = $d->apartado;
                     $pedido->ciudad = $d->ciudad;
                     $pedido->coddir = $d->id;
                     $pedido->codpais = $d->codpais;
                     $pedido->codpostal = $d->codpostal;
                     $pedido->direccion = $d->direccion;
                     $pedido->provincia = $d->provincia;
                     break;
                  }
               }
               
               if( $pedido->save() )
               {
                  /// también copiamos las líneas del pedido
                  foreach($this->documento->get_lineas() as $linea)
                  {
                     $newl = clone $linea;
                     $newl->idlinea = NULL;
                     $newl->idpedido = $pedido->idpedido;
                     $newl->idpresupuesto = NULL;
                     $newl->idlineapresupuesto = NULL;
                     $newl->save();
                  }
                  
                  $this->new_message('<a href="' . $pedido->url() . '">Documento</a> de ' . FS_PEDIDO . ' copiado correctamente.');
               }
               else
               {
                  $this->new_error_msg('Error al copiar el documento.');
               }
            }
            else
            {
               $this->new_error_msg('No se ha encontrado el cliente');
            }
         }
      }
   }
   
   private function copiar_presupuesto()
   {
      $presu = new presupuesto_cliente();
      $this->documento = $presu->get($_REQUEST['id']);
      $this->tipo = 'presu';
      $this->tipo2 = FS_PRESUPUESTO;
      
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
            $presu->idoriginal = NULL;
            $presu->numdocs = 0;
            $presu->numero2 = $_REQUEST['numero2'];
            $presu->fecha = $_REQUEST['fecha'];
            $presu->femail = NULL;
            
            //fecha de fin de oferta
            $fsvar = new fs_var();
            $dias = $fsvar->simple_get('presu_validez');
            if ($dias) {
               $presu->finoferta = date('d-m-Y', strtotime($_REQUEST['fecha'] . ' +' . intval($dias) . ' days'));
            } else
               $presu->finoferta = date('d-m-Y', strtotime($_REQUEST['fecha'] . ' +30 days'));
            
            $presu->status = 0;
            $presu->codserie = $this->serie->codserie;
            $presu->codalmacen = $this->almacen->codalmacen;
            $presu->codagente = $this->user->codagente;
            $presu->observaciones = $_REQUEST['observaciones'];

            /// necesitamos el ejercico correcto
            $ejercicio = $this->ejercicio->get_by_fecha($presu->fecha);
            if($ejercicio)
            {
               $presu->codejercicio = $ejercicio->codejercicio;
            }
            
            /// cliente:
            if($this->cliente)
            {
               $presu->codcliente = $this->cliente->codcliente;
               $presu->cifnif = $this->cliente->cifnif;
               $presu->nombrecliente = $this->cliente->razonsocial;
               
               foreach($this->cliente->get_direcciones() as $d)
               {
                  if($d->domfacturacion)
                  {
                     $presu->apartado = $d->apartado;
                     $presu->ciudad = $d->ciudad;
                     $presu->coddir = $d->id;
                     $presu->codpais = $d->codpais;
                     $presu->codpostal = $d->codpostal;
                     $presu->direccion = $d->direccion;
                     $presu->provincia = $d->provincia;
                     break;
                  }
               }
               
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
                  
                  $this->new_message('<a href="'.$presu->url().'">Documento</a> de '.FS_PRESUPUESTO.' copiado correctamente.');
               }
               else
               {
                  $this->new_error_msg('Error al copiar el documento.');
               }
            }
            else
            {
               $this->new_error_msg('No se ha encontrado el cliente.');
            }
         }
      }
   }
   
   private function copiar_servicio()
   {
      $servicio = new servicio_cliente();
      $this->documento = $servicio->get($_REQUEST['id']);
      $this->tipo = $this->tipo2 = 'servicio';

      if($this->documento)
      {
         if(isset($_REQUEST['copiar']))
         {
            /// cargamos la configuración de servicios
            $fsvar = new fs_var();
            $opciones_servicios = $fsvar->array_get(
                    array(
                'servicios_diasfin' => 10,
                    ), FALSE
            );


            /**
             * Si nos llega la variable copiar es que han pulsado el botón
             * de copiar, así que copiamos el servicio.
             */
            $servicio = clone $this->documento;
            $servicio->idservicio = NULL;
            $servicio->idalbaran = NULL;
            $servicio->idestado = 1;
            $servicio->fecha = $_REQUEST['fecha'];
            $servicio->fechainicio = date('d-m-Y H:i:s', strtotime($_REQUEST['fecha']));
            $servicio->fechafin = date('d-m-Y H:i:s', strtotime($servicio->fechainicio. ' + '.$opciones_servicios['servicios_diasfin'].' days'));
            $servicio->femail = NULL;
            $servicio->numero2 = $_REQUEST['numero2'];
            $servicio->codserie = $this->serie->codserie;
            $servicio->codalmacen = $this->almacen->codalmacen;
            $servicio->codagente = $this->user->codagente;
            $servicio->observaciones = $_REQUEST['observaciones'];
            $servicio->numdocs = 0;
            
            /// necesitamos el ejercico correcto
            $ejercicio = $this->ejercicio->get_by_fecha($servicio->fecha);
            if($ejercicio)
            {
               $servicio->codejercicio = $ejercicio->codejercicio;
            }
            
            /// cliente:
            if($this->cliente)
            {
               $servicio->codcliente = $this->cliente->codcliente;
               $servicio->cifnif = $this->cliente->cifnif;
               $servicio->nombrecliente = $this->cliente->razonsocial;
               
               foreach($this->cliente->get_direcciones() as $d)
               {
                  if($d->domfacturacion)
                  {
                     $servicio->apartado = $d->apartado;
                     $servicio->ciudad = $d->ciudad;
                     $servicio->coddir = $d->id;
                     $servicio->codpais = $d->codpais;
                     $servicio->codpostal = $d->codpostal;
                     $servicio->direccion = $d->direccion;
                     $servicio->provincia = $d->provincia;
                     break;
                  }
               }
               
               if( $servicio->save() )
               {
                  /// también copiamos las líneas del servicio
                  foreach($this->documento->get_lineas() as $linea)
                  {
                     $newl = clone $linea;
                     $newl->idlinea = NULL;
                     $newl->idservicio = $servicio->idservicio;
                     $newl->save();
                  }
                  
                  $this->new_message( 'El . ' . FS_SERVICIO . '<a href="' . $servicio->url() . '"> ' . $servicio->codigo . '</a> se ha generado correctamente.');
               }
               else
               {
                  $this->new_error_msg('Error al copiar el documento.');
               }
            }
            else
            {
               $this->new_error_msg('No se ha encontrado el cliente');
            }
         }
      }
   }
}
