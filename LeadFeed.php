<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * SugarCRM Community Edition is a customer relationship management program developed by
 * SugarCRM, Inc. Copyright (C) 2004-2013 SugarCRM Inc.
 * 
 *     MODULO    : Leads          SERGIO ARBOLEDA     AMAZON
 *     NOMBRE    : Lead_Feed.php
 *     OBJETO    
 *                 Actualiza el campo de Asesores asignados por programa  (   assigned_user_id   )  
 *     RUTA      : /modules/Leads/LeadFeed.php
 *     FECHA     : Nov-1-2016
 *     FECHA     : Feb-16-2017  --> Revision
 *     FECHA     : Mar-1-2017   --> sacar del nombre del programa el tipo de programa
 *     FECHA     : Junio-21-2017 --> Reemlzar Numeros de nombre y apellido por posible hackeo 
 *
 * SuiteCRM is an extension to SugarCRM Community Edition developed by Salesagility Ltd.
 * Copyright (C) 2011 - 2014 Salesagility Ltd.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY SUGARCRM, SUGARCRM DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Affero General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 *
 * You can contact SugarCRM, Inc. headquarters at 10050 North Wolfe Road,
 * SW2-130, Cupertino, CA 95014, USA. or at email address contact@sugarcrm.com.
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * SugarCRM" logo and "Supercharged by SuiteCRM" logo. If the display of the logos is not
 * reasonably feasible for  technical reasons, the Appropriate Legal Notices must
 * display the words  "Powered by SugarCRM" and "Supercharged by SuiteCRM".
 ********************************************************************************/

require_once('modules/SugarFeed/feedLogicBase.php');


class LeadFeed extends FeedLogicBase {
    var $module = 'Leads';
    function pushFeed($bean, $event, $arguments){
        global $locale;
// Fer-Nov-1-2016 --  Nombres y Apellidos en Mayusculas  
		$bean->first_name = str_replace (array("0","1","2","3","4","5","6","7","8","9"), '', $bean->first_name );
		$bean->first_name = strtoupper($bean->first_name);
		$bean->last_name = str_replace (array("0","1","2","3","4","5","6","7","8","9"), '', $bean->last_name );	
		$bean->last_name = strtoupper($bean->last_name);
		
// --> Pasa el campo de programa de interes al campo assistant  pra el control de registros duplicados
		$bean->assistant = $bean->d_programa_interes_c;	
		$programa = $bean->d_programa_interes_c;
		$origen = $bean->lead_source;
		$estado = $bean->status;	
		$sw = 0;
// Hasta aqui Fer-Nov-1-2016
        $text = '';
// --> Nov-1-2016 -- REGISTRO NO GESTIONADO
        if(empty($bean->fetched_row)){
            $full_name = $locale->getLocaleFormattedName($bean->first_name, $bean->last_name, '');
            $text =  '{SugarFeed.CREATED_LEAD} [' . $bean->module_dir . ':' . $bean->id . ':' . $full_name . ']';
// Desde aqui Fer-Mar-1-2017
// --> Se extrae el tipo de programa desde el nombre del programa ( solo funciona para listas dinamicas )  solo para nuevos
			$long = strlen($programa);
			$count = stripos($programa,"_");
			$tipoprog = substr($programa,0,$count);
			$bean->d_tipo_programa_c = $tipoprog;	
// Hasta aqui Fer-Mar-1-2017
// --> Paso 1 -- Primero busca el programa para el origen especifico ( si lo tiene ) y para registro NO GESTIONADO 
// --> Si lo encuentra lo asigna , si no lo encuentra va al paso 2 origen TODOS 
			$qq = "SELECT id, user_id_c, d_programa_interes, d_numero_prospectos, d_origen, d_tipo_registro  
					FROM as_asignacion
					WHERE  deleted = 0 AND d_estado_asignacion = 'Activo' AND d_origen = '".$origen."' AND d_tipo_registro = 'NO GESTIONADO' 
					AND d_programa_interes = '".$programa."' 
					ORDER BY d_numero_prospectos ASC 
					LIMIT 1	";
			$re = $GLOBALS['db']->query($qq);
			while($row = $GLOBALS['db']->fetchByAssoc($re) )
				{
					$id = $row['id'];
					$nump = $row['d_numero_prospectos'] + 1;
					$qq = "UPDATE as_asignacion 
						   SET d_numero_prospectos = '".$nump."' WHERE as_asignacion.id = '".$id."'";
					$re = $GLOBALS['db']->query($qq);
// Asigna el agente seleccionado al prospecto
					$bean->assigned_user_id = $row['user_id_c'];	
					$sw = 1;
				}    //   END WHILE	
				
   //  La asignacion no existe en el origen especificado y entonces busca con TODOS 
			if ( $sw == 0 )	 
			{
// --> Paso 2 		
// --> Busca si el programa para TODOS los origenes y para registro NO GESTIONADO es de asignacion automatica en tabla Asignacion
				$qq = "SELECT id, user_id_c, d_programa_interes, d_numero_prospectos, d_origen, d_tipo_registro  
						FROM as_asignacion
						WHERE  deleted = 0 AND d_estado_asignacion = 'Activo' AND d_origen = 'TODOS' AND d_tipo_registro = 'NO GESTIONADO' 
						AND d_programa_interes = '".$programa."'
						ORDER BY d_numero_prospectos ASC 
						LIMIT 1	";
				$re = $GLOBALS['db']->query($qq);	
				while($row = $GLOBALS['db']->fetchByAssoc($re) )
					{
						$id = $row['id'];
						$nump = $row['d_numero_prospectos'] + 1;
						$qq = "UPDATE as_asignacion 
							SET d_numero_prospectos = '".$nump."' WHERE as_asignacion.id = '".$id."'";
						$re = $GLOBALS['db']->query($qq);
// Asigna el agente seleccionado al prospecto
						$bean->assigned_user_id = $row['user_id_c'];					
					}    //   END WHILE				
			}    //   END IF $sw 
	
        }else{

            if(!empty($bean->fetched_row['status'] ) && $bean->fetched_row['status'] != $bean->status && $bean->status == 'Converted'){
                // Repeated here so we don't format the name on "uninteresting" events
				
			
				
                $full_name = $locale->getLocaleFormattedName($bean->first_name, $bean->last_name, '');

                $text =  '{SugarFeed.CONVERTED_LEAD} [' . $bean->module_dir . ':' . $bean->id . ':' . $full_name . ']';
            }
        }
		
        if($estado == 'Validado'){		
// -- REGISTRO EXISTENTE
// $$$$

// --> Paso 1 -- Primero busca el programa para el origen especifico ( si lo tiene ) y para registro LEAD CALIFICADO
// --> Si lo encuentra lo asigna , si no lo encuentra va al paso 2 origen TODOS 
			$qq = "SELECT id, user_id_c, d_programa_interes, d_numero_prospectos, d_origen, d_tipo_registro  
					FROM as_asignacion
					WHERE  deleted = 0 AND d_estado_asignacion = 'Activo' AND d_origen = '".$origen."' AND d_tipo_registro = 'LEAD CALIFICADO' 
					AND d_programa_interes = '".$programa."' 
					ORDER BY d_numero_prospectos ASC 
					LIMIT 1	";
			$re = $GLOBALS['db']->query($qq);
			while($row = $GLOBALS['db']->fetchByAssoc($re) )
				{
					$id = $row['id'];
					$nump = $row['d_numero_prospectos'] + 1;
					$qq = "UPDATE as_asignacion 
						   SET d_numero_prospectos = '".$nump."' WHERE as_asignacion.id = '".$id."'";
					$re = $GLOBALS['db']->query($qq);
// Asigna el agente seleccionado al prospecto
					$bean->assigned_user_id = $row['user_id_c'];	
					$sw = 1;
				}    //   END WHILE	

  //  La asignacion no existe en el origen especificado y entonces busca con TODOS 
				if ( $sw == 0 )	
				{	

// $$$$$

// -- desde aqui Feb-15-2017	 				
// --> Busca si el programa para TODOS los origenes y para registro NO GESTIONADO es de asignacion automatica en tabla Asignacion
			$qq = "SELECT id, user_id_c, d_programa_interes, d_numero_prospectos, d_origen, d_tipo_registro  
					FROM as_asignacion
					WHERE  deleted = 0 AND d_estado_asignacion = 'Activo' AND d_origen = 'TODOS' AND d_tipo_registro = 'LEAD CALIFICADO' 
					AND d_programa_interes = '".$programa."'
					ORDER BY d_numero_prospectos ASC 
					LIMIT 1	";
			$re = $GLOBALS['db']->query($qq);	
			while($row = $GLOBALS['db']->fetchByAssoc($re) )
				{
					$id = $row['id'];
					$nump = $row['d_numero_prospectos'] + 1;
					$qq = "UPDATE as_asignacion 
						   SET d_numero_prospectos = '".$nump."' WHERE as_asignacion.id = '".$id."'";
					$re = $GLOBALS['db']->query($qq);
// Asigna el agente seleccionado al prospecto
//					$qq = "UPDATE leads
//						   SET assigned_user_id = '".$row['user_id_c']."' WHERE leads.id = '".$id."'";

					$bean->assigned_user_id = $row['user_id_c'];					
				}    //   END WHILE						
				}  //  END IF SW 
				
		}
// -- Hasta aqui Feb-15-2017	
		
		
		
        if(!empty($text)){ 
        	SugarFeed::pushFeed2($text, $bean);
			
			
        }
		
    }
}
?>
