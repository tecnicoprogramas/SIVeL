<?php
// vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker fileencoding=utf-8:
/**
 * Variables de configuración.
 * Basado en script de configuración http://structio.sourceforge.net/seguidor
 *
 * PHP version 5
 *
 * @category  SIVeL
 * @package   SIVeL
 * @author    Vladimir Támara <vtamara@pasosdeJesus.org>
 * @copyright 2004 Dominio público. Sin garantías.
 * @license   https://www.pasosdejesus.org/dominio_publico_colombia.html Dominio Público. Sin garantías.
 * @link      http://sivel.sf.net
 * Acceso: SÓLO DEFINICIONES
 */

/** Servidor/socket del Motor de bases de datos */
$dbservidor = "unix(/tmp)"; # Si prefiere TCP/IP (no recomendado) use tcp(localhost)

/** Nombre de base de datos */
$dbnombre = "sivel";

/** Usuario del MBD */
$dbusuario = "sivel";

/** Clave del usuario ante el MBD */
$dbclave = "xyz";

/** Opciones especiales para acceder base de datos desde consola */
$socketopt = "-h /var/www/tmp";

/** Directorio en el que correo servidor web */
$dirchroot = "/var/www";

/** Directorio de fuentes en servidor web */
$dirserv = "/htdocs/sivel";

/** Directorio del sitio relativo a $dirserv */
$dirsitio = "sitios/sivel";

// RELATOS

/** Organización responsable, aparecerá al exportar relatos
 * @global string $GLOBALS['organizacion_responsable']
 */
$GLOBALS['organizacion_responsable'] = 'D';

/** Derechos de reproducción por defecto, aparecerán al exportar relatos
 * @global string $GLOBALS['derechos']
 */
$GLOBALS['derechos'] = 'Dominio Público';

/**
 * Directorio con relatos
 * @global string $GLOBALS['DIR_RELATOS']
 */
$GLOBALS['DIR_RELATOS'] = '/sincodh-publico/relatos/';

/**
 * Prefijo para nombres de archivo de relatos
 * @global string $GLOBALS['PREF_RELATOS']
 */
$GLOBALS['PREF_RELATOS'] = 'org';

/**
 * Estilo: nombres y apellidos de victimas.
 * MAYUSCULAS seria JOSE PEREZ
 * a_minusculas seria Jose Perez
 *
 * @global string $GLOBALS['estilo_nombres']
 */
$GLOBALS['estilo_nombres'] = 'MAYUSCULAS';


// VOLCADOS  - COPIAS DE RESPALDO LOCALES

/** Contenedor cifrado de volcados */
$imagenrlocal = "/var/resbase.img";

/** Directorio local donde quedara volcado diarío del último mes
 * Se espera que se almacene en el contenedor cifrado.
 */
$rlocal = "/var/www/resbase";

/**
 * Se copian fuentes de PHP en directorio de respaldos?
 */
$copiaphp = false;

// COPIAS DE RESPALDO REMOTAS

/** Destinos a los cuales copiar volcado diario de la última semana.
 * e.g "usuario1@maquina1: usuario2@maquina2:" */
$rremotos = "";

/** Llave ssh. Generela con ssh-keygen sin clave, el dueño debe ser quien
 * ejecuta el script respaldo.sh */
$llave = $dirchroot . $dirserv . $dirsitio . "/id_rsa";

// PARTICULARIDADES

/** Incluir iglesias cristianas en ficha
 * @global string $GLOBALS['iglesias_cristianas']
 */
$GLOBALS['iglesias_cristianas'] = true;

/**
 * Color del fondo de la ficha de captura en notacion HTML
 * @global string $GLOBALS['ficha_color_fondo']
 */
$GLOBALS['ficha_color_fondo'] = '#EEE';


// PUBLICACIÓN EN PÁGINA WEB

/**  Usuario */
$usuarioact = "sivel";

/** Comptuador al cual copiar */
$maquinaweb = "otramaquina";

/** Directorio */
$dirweb = "/tmp";

/** Opciones para scp de actweb, e.g -i ... */
$opscpweb = "";


// Mejor no empleamos sobrecarga porque no funciona en
// diversas versiones de PHP
if (!defined('DB_DATAOBJECT_NO_OVERLOAD')) {
    define('DB_DATAOBJECT_NO_OVERLOAD',1);
}

/** DSN de la base de datos.  */
$dsn = "pgsql://$dbusuario:$dbclave@$dbservidor/$dbnombre";

require_once "PEAR.php";

require_once 'DB/DataObject.php';
require_once 'DB/DataObject/FormBuilder.php';

$options = &PEAR::getStaticProperty('DB_DataObject', 'options');
$options = array(
    'database' => $dsn,
    'schema_location' => $dirsitio . '/DataObjects',
    'class_location' => 'DataObjects/',
    'require_prefix' => 'DataObjects/',
    'class_prefix' => 'DataObjects_',
    'extends_location' => 'DataObjects_',
    'debug' => '0',
);

$_DB_DATAOBJECT_FORMBUILDER['CONFIG'] = array (
    'select_display_field' => 'nombre',
    'hidePrimaryKey' => false,
    'submitText' => 'Enviar',
    'linkDisplayLevel' => 2,
    'dbDateFormat' => 1,
    'dateElementFormat' => "d-m-Y",
    'useCallTimePassByReference' => 0
);

// MODULOS

/** Módulos empleados (relativos a directorio con fuentes) */
$modulos = "modulos/anexos modulos/etiquetas modulos/belicas modulos/segjudicial modulos/estrotulos modulos/mapag";

/** Directorio donde se almacenan anexos */
$GLOBALS['dir_anexos'] = '/resbase/anexos';

// Opciones de Reporte Tabla
$GLOBALS['reporte_tabla_fila_totales'] = false;

// Opciones del menú


$GLOBALS['modulo'][100] = 'modulos/estrotulos/estrotulos.php';
$GLOBALS['modulo'][101] = 'modulos/estrotulos/estcolectivas.php';
$GLOBALS['modulo'][200] = 'modulos/belicas/estadisticas_comb.php';
$GLOBALS['modulo'][300] = 'modulos/mapag/index.php';

require_once "modulos/estrotulos/reporteRotulos.php";

$GLOBALS['consultaweb_ordenarpor'][0] = "rotulos_cwebordenar";
$GLOBALS['reporterevista_reginicial'][0] = "rotulos_inicial";
$GLOBALS['reporterevista_regfinal'][0] = "rotulos_final";
$GLOBALS['misc_ordencons'][0] = "rotulos_orden_cons";


/* Rutas en particular donde haya subdirectorios DataObjects */
$rutas_include = ini_get('include_path').
    ":.:$dirserv:$dirserv/$dirsitio:$dirsitio:";
$lm = explode(" ", $modulos);
foreach ($lm as $m) {
    $rutas_include .= "$m:$m/DataObjects/:";
}

/* La siguiente requiere AllowOverride All en configuración de Apache */
ini_set('include_path', $rutas_include);

/** Cadena en caso de no existir usuario o clave */
$accno = "Acceso no autorizado\n";

/** Palabra clave para algunos cifrados.
 * @global string $GLOBALS['PALABRA_SITIO']
 */
$GLOBALS['PALABRA_SITIO'] = 'sigamos el ejemplo de Jesús';

/** Deshabilita operaciones con usuarios
 * @global string $GLOBALS['deshabilita_manejo_usuarios']
 */
$GLOBALS['deshabilita_manejo_usuarios'] = false;

/** Mensaje por presentar si se encuentran fallas ortográficas al validar
 * @global string $GLOBALS['MENS_ORTOGRAFIA']
 */
$GLOBALS['MENS_ORTOGRAFIA'] = 'Las palabras que estén bien por favor agreguelas al diccionario (%l).';

/** Deshabilita operaciones con usuarios
 * @global string $GLOBALS['deshabilita_manejo_usuarios']
 */
$GLOBALS['deshabilita_manejo_usuarios'] = false;

/** Mensaje por presentar en la página principal para indicar donde reportar fallas.
 * @global string $GLOBALS['REPORTA_FALLAS']
 */
$GLOBALS['REPORTA_FALLAS'] = "<a href=\"http://sivel.sf.net/\">Documentación</a><br><a href=\"http://190.25.231.236/Divipola/Divipola.asp\" target=\"2\">DIVIPOLA</a><br>Por favor reporte fallas o requerimientos en el sistema de seguimiento disponible <a href='http://sourceforge.net/tracker/?group_id=104373&atid=637817'>en línea</a>";

/** Ancho en porcentaje de tablas en reporte general.
 * Puede cambiarse en caso de que tenga problemas al imprimir (por ejemplo
 * si las fuentes de su computador no son iguales a las de la impresora).
 * @global string $GLOBALS['ancho-tabla']
 */
$GLOBALS['ancho-tabla'] = "78%";

/** Determina si se indentan o no víctimas en reporte general y revista
 * @global string $GLOBALS['reporte_indenta_victimas']
 */
$GLOBALS['reporte_indenta_victimas'] = true;

/** Fecha máxima de caso por usar en consulta web.
 * año-mes-año
 * @global string $GLOBALS['consulta_web_fecha_max']
 */
$GLOBALS['consulta_web_fecha_max'] = '2024-11-30';

/** Fecha mínima de caso por consultar en web
 * @global string $GLOBALS['consulta_web_fecha_min']
 */
$GLOBALS['consulta_web_fecha_min'] = '2001-1-1';

/** Máximo de registros por retornar en una consulta web (0 es ilimitado)
 * @global string $GLOBALS['consulta_web_max']
 */
$GLOBALS['consulta_web_max']=400;

/** Año mínimo que puede elegirse en fechas de la Ficha
 * @global string $GLOBALS['anio_min']
 */
$GLOBALS['anio_min']=1990;

/** Dirección de correo a la cual enviar mensajes cifrados.
 * @global string $GLOBALS['receptor_correo']
 */
$GLOBALS['receptor_correo'] = 'sivel@localhost';

/** Dirección de la cual provendrán mensajes cifrados.
 * @global string $GLOBALS['emisor_correo']
 */
$GLOBALS['emisor_correo'] = 'bancodat@nocheyniebla.org';

/** Cabezote en consulta_web.
 * Dejar '' si no hay
 * @global string $GLOBALS['cabezote_consulta_web']
 */
$GLOBALS['cabezote_consulta_web'] = '';

/** Pie en consulta_web.
 * Dejar '' si no hay
 * @global string $GLOBALS['pie_consulta_web']
 */
$GLOBALS['pie_consulta_web'] = '';

/** Pie en consulta_web publica.
 * Dejar '&nbsp;' si no hay
 * @global string $GLOBALS['pie_consulta_web_publica']
 */
$GLOBALS['pie_consulta_web_publica'] = '<div align="right"><a href="http://sivel.sourceforge.net/1.1/consultaweb.html">Documentación</a></div>';

/** Cabezote para enviar correos desde consulta_web.
 * Dejar '' si no hay
 * @global string $GLOBALS['cabezote_consulta_web_correo']
 */
$GLOBALS['cabezote_consulta_web_correo'] = '';

/** Pie para enviar correos desde consulta_web.
 * Dejar '' si no hay
 * @global string $GLOBALS['pie_consulta_web_correo']
 */
$GLOBALS['pie_consulta_web_correo'] = '<hr/><a href="consulta_web.php">Consulta web</a>';

/** Archivo HTML que se pone como cabezote (antes del menú) del menú principal
 * Dejar '' si no hay
 * @global string $GLOBALS['cabezote_principal']
 */
$GLOBALS['cabezote_principal'] = '';

/** Archivo HTML que se pone en el centro del menú principal
 * Dejar '' si no hay
 * @global string $GLOBALS['centro_principal']
 */
$GLOBALS['centro_principal'] = 'centro_principal.html';

/** Imagen de fondo
 * @global string $GLOBALS['fondo']
 */
$GLOBALS['fondo']= $dirsitio . '/fondo.jpg';


/** Indica si en la pestaña Actos deben presentarse actos colectivos
 * @global bool $GLOBALS['actoscolectivos']
*/
$GLOBALS['actoscolectivos'] = true;


/** Pestañas de la Ficha  de captura
    'id', 'Clase', 'orden en eliminación (no rep)' */
$GLOBALS['ficha_tabuladores'] = array(
    0 => array('basicos', 'PagBasicos', 15),
    1 => array('ubicacion', 'PagUbicacion', 5),
    2 => array('frecuentes', 'modulos/anexos/PagFrecuenteAnexo', 8),
    3 => array('otras', 'modulos/anexos/PagOtraAnexo', 10),
    4 => array('tipoViolencia', 'PagTipoViolencia', 6),
    5 => array('pResponsables', 'PagPResponsables', 7),
    6 => array('victimaIndividual', 'PagVictimaIndividual', 2),
    7 => array('victimaColectiva', 'PagVictimaColectiva',3),
    8 => array('victimaCombatiente', 'modulos/belicas/PagVictimaCombatiente', 4)
,
    9 => array('acto', 'PagActo', 1),
    10=> array('segjudicial', 'modulos/segjudicial/PagSegJudicial', 11),
    11 => array('memo', 'PagMemo', 9),
    12 => array('anexos', 'modulos/anexos/PagAnexo', 12),
    13 => array('etiquetas', 'modulos/etiquetas/PagEtiquetas', 13),
    14 => array('evaluacion', 'PagEvaluacion', 14)
);


/** Tablas básicas */
$GLOBALS['menu_tablas_basicas'] = array(
    array('title' => 'Información geográfica', 'url'=> null, 'sub' => array(
        array('title'=>'Departamento', 'url'=>'departamento','sub'=>null),
        array('title'=>'Municipio', 'url'=>'municipio', 'sub'=>null),
        array('title'=>'Tipo Clase', 'url'=>'tipo_clase', 'sub'=>null),

        array('title'=>'Clase', 'url'=>'clase', 'sub'=>null),
        array('title'=>'Región', 'url'=>'region', 'sub'=>null),
        array('title'=>'Frontera', 'url'=>'frontera', 'sub'=>null),
        array('title'=>'Tipo de Sitio', 'url'=>'tipo_sitio', 'sub'=>null),
        ),
    ),
    array('title' => 'Información implicado', 'url'=> null, 'sub' => array(
        array('title'=>'Etnia', 'url'=>'etnia', 'sub'=>null),
        array('title'=>'Filiación', 'url'=>'filiacion', 'sub'=>null),
        array('title'=>'Iglesia', 'url'=>'iglesia', 'sub'=>null),
        array('title'=>'Organización social', 'url'=>'organizacion', 'sub'=>null),
        array('title'=>'Profesión', 'url'=>'profesion', 'sub'=>null),
        array('title'=>'Rango edad', 'url'=>'rango_edad', 'sub'=>null),
        array('title'=>'Resultado agresión', 'url'=>'resultado_agresion', 'sub'=>null),
        array('title'=>'Sector Social', 'url'=>'sector_social', 'sub'=>null),
        array('title'=>'Tipo de Relación', 'url'=>'tipo_relacion', 'sub'=>null),
        array('title'=>'Vínculo con el estado', 'url'=>'vinculo_estado', 'sub'=>null),
        ),
    ),
    array('title' => 'Información caso', 'url'=> null, 'sub' => array(
        array('title'=>'Tipo de Violencia', 'url'=>'tipo_violencia', 'sub'=>null),
        array('title'=>'Supracategoria', 'url'=>'supracategoria', 'sub'=>null),
        array('title'=>'Categoria', 'url'=>'categoria', 'sub'=>null),
        array('title'=>'Contexto', 'url'=>'contexto', 'sub'=>null),
        array('title'=>'Presuntos responsables', 'url'=>'presuntos_responsables', 'sub'=>null),
        array('title'=>'Antecedentes', 'url'=>'antecedente', 'sub'=>null),
        array('title'=>'Intervalo', 'url'=>'intervalo', 'sub'=>null),
        ),
    ),
    array('title' => 'Información fuentes', 'url'=> null, 'sub' => array(
        array('title'=>'Fuentes frecuentes', 'url'=>'prensa', 'sub'=>null),
        ),
    ),
    array('title' => 'Reportes', 'url'=> null, 'sub' => array(
        array('title'=>'Columnas de Reporte Consolidado',
            'url'=>'parametros_reporte_consolidado', 'sub'=>null),
        ),
    ),
);


/** Etiquetas que aparecen en la interfaz */
$GLOBALS['etiqueta'] = array(
// Caso
    'titulo' => 'Titulo',
    'fecha' => 'Fecha',
    'hora' => 'Hora',
    'duracion' => 'Duración',
    'tipo_ubicacion' => 'Tipo de ubicación',
    'id_intervalo' => 'Intervalo',

// Ubicación
    'region' => 'Región',
    'frontera' => 'Frontera',

    'departamento' => 'Departamento',
    'municipio' => 'Municipio',
    'clase' => 'Clase',

    'ubicacion' => 'Ubicación',
    'lugar' => 'Lugar',
    'sitio' => 'Sitio',

// Fuente frecuente
    'id_prensa' => 'Fuente',
    'fecha_fuente' => 'Fecha',
    'ubicacion_fuente' => 'Ubicación',
    'clasificacion_fuente' => 'Clasificación',
    'ubicacion_fisica' => 'Ubicación Física',

// Otras fuentes
    'nombre' => 'Nombre',
    'ubicacion_fisica' => 'Ubicación Física',
    'tipo_fuente' => 'Tipo de Fuente',
    'anotacion' => 'Anotacion',

// Tipo de violencia
    'clasificacion' => 'Contexto',
    'contexto' => 'Contexto',
    'antecedente' => 'Antecedente',
    'bienes' => 'Bienes Afectados',

// Presuntos responsables
    'p_responsable' => 'Presunto Responsable',
    'p_responsables' => 'Presuntos Responsables',
    'tipo' => 'Bando',
    'bloque' => 'Bloque',
    'frente' => 'Frente',
    'brigada' => 'Brigada',
    'batallon' => 'Batallón',
    'division' => 'División',
    'otro' => 'Otro',

// Víctima Individual
    'victimas_individuales'=> 'Víctimas Individuales',
//    'nombre'=> 'Nombre',
    'edad'=> 'Edad',
    'hijos'=> 'Hijos',
    'sexo'=> 'Sexo',
    'profesion'=> 'Profesión',
    'rango_edad'=> 'Rango de Edad',
    'filiacion'=> 'Filiación política',
    'sector_social'=> 'Sector Social',
    'organizacion'=> 'Organización Social',
    'vinculo_estado'=> 'Vínculo con el Estado',
    'organizacion_armada'=> 'Organización Armada Víctima',
    'anotaciones_victima' => 'Anotaciones',

    'p_responsable'=> 'Presunto Responsable',
    'antecedentes'=> 'Antecedentes',
    'tipo_violencia'=> 'Tipo Violencia',

// Víctima Colectiva
    'victimas_colectivas'=> 'Víctimas Colectivas',
//   'nombre' => 'Nombre',
//   'organizacion_armada'=> 'Organización Armada Víctima',
    'personas_aprox' => 'Num. Aprox. Personas',
    'anotacion' => 'Anotaciones',

//    'tipo_violencia' =>
//    'antecedentes' =>
//    'p_responsable'=> 'Presunto Responsable',
//    'rango_edad'=> 'Rango de Edad',
//    'sector_social'=> 'Sector Social',
//    'vinculo_estado'=> 'Vínculo con el Estado',
//    'filiacion'=> 'Filiación política',
//    'profesion'=> 'Profesión',
//    'organizacion'=> 'Organización Social',


// Víctima Combatiente
    'victimas_combatientes'=> 'Víctimas Combatientes',
//       'nombre'=>'Nombre',
    'alias'=>'Alias',
//       'edad'=>'Edad',
//       'sexo'=>'Sexo',
//       'rango_edad' => 'Rango de Edad',
//       'sector_social'=> 'Sector Social',
//       'vinculo_estado'=> 'Vínculo Estado',
//       'filiacion'=> 'Filiación Política',
//       'profesion'=> 'Profesion',
//       'organizacion'=> 'Organización Social',
//       'organizacion_armada'=> 'Organización Armada',
    'resultado_agresion'=> 'Resultado Agresión',

//Actos
    'Actos' => 'Actos',
//Memo
    'memo' => 'Memo',

//Evaluación
    'gr_confiabilidad' => 'Gr. Confiabilidad Fuente',
    'gr_esclarecimiento' => 'Gr.Esclarecimiento',
    'gr_impunidad' => 'Gr. Impunidad',
    'gr_informacion' => 'Gr. Informacion',

// Otros
    'analista' => 'Analista',
    'meses' => 'Meses',
    'victimas' => 'Víctimas',

// Pestañas

    'PagBasicos' => 'Datos básicos',
    'PagUbicacion' => 'Ubicación',
    'PagFuentesFrecuentes' => 'Fuentes Frecuentes',
    'PagOtrasFuentes' => 'Otras Fuentes',
    'PagTipoViolencia' => 'Contexto',
    'PagPResponsables' => 'Presuntos Responsables',
    'PagVictimaColectiva' => 'Víctima Colectiva',
    'PagVictimaIndividual' => 'Víctimas Individuales',
    'PagVictimaCombatiente' => 'Víctima Combatiente',
    'PagMemoPagEvaluacion' => 'Evaluación',

);

// Opciones del menú
//

// $GLOBALS['modulo'][1] = 'sitios/misitio/miopcion.php';

/** Campos que pueden elegirse en consultas */
$GLOBALS['cw_ncampos'] = array('caso_id' => 'Código',
    'caso_memo' => 'Descripción',
    'caso_fecha' => 'Fecha',
    'm_ubicacion' => 'Ubicación',
    'm_victimas' => 'Víctimas',
    'm_presponsables' => 'Pr. Resp.',
    'm_tipificacion' => 'Tipificación',
    #'m_observaciones' => 'Observaciones',
    #'m_anexos' => 'Anexos',
);

