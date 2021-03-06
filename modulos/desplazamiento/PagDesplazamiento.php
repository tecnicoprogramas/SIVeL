<?php
// vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker fileencoding=utf-8:
/**
 *Página del multi-formulario para capturar caso (captura_caso.php).
 *
 * PHP version 5
 *
 * @category  SIVeL
 * @package   SIVeL
 * @author    Vladimir Támara <vtamara@pasosdeJesus.org>
 * @copyright 2013 Dominio público. Sin garantías.
 * @license   https://www.pasosdejesus.org/dominio_publico_colombia.html Dominio Público. Sin garantías.
 * @link      http://sivel.sf.net
 * Acceso: SÓLO DEFINICIONES
 */

require_once 'PagBaseMultiple.php';
require_once 'ResConsulta.php';
require_once 'PagUbicacion.php';
require_once 'HTML/QuickForm/Action.php';

require_once 'DataObjects/Acreditacion.php';
require_once 'DataObjects/Clasifdesp.php';
require_once 'DataObjects/Declaroante.php';
require_once 'DataObjects/Desplazamiento.php';
require_once 'DataObjects/Inclusion.php';
require_once 'DataObjects/Modalidadtierra.php';
require_once 'DataObjects/Tipodesp.php';


/**
 * Página Desplazamiento
 * Ver documentación de funciones en clase base.
 *
 * @category SIVeL
 * @package  SIVeL
 * @author   Vladimir Támara <vtamara@pasosdeJesus.org>
 * @license  Dominio público.
 * @link     http://sivel.sf.net/tec
*/
class PagDesplazamiento extends PagBaseMultiple
{

    var $bdesplazamiento;

    var $pref = "des";

    var $nuevaCopia = false;

    var $clase_modelo = 'desplazamiento';

    var $titulo = 'Desplazamiento';

    /**
     * Pone en null variables asociadas a tablas de la pestaña.
     *
     * @return null
     */
    function nullVar()
    {
        $this->bdesplazamiento= null;
    }

    /**
     * Retorna una identificación del registro actual.
     *
     * @return string Identifación
     */
    function copiaId()
    {
        return array(
            $this->bdesplazamiento->_do->id_caso,
            $this->bdesplazamiento->_do->fechaexpulsion
        );
    }


    /**
     * Elimina de base de datos el registro actual.
     *
     * @param array &$valores Valores enviados por formulario.
     *
     * @return null
     */
    function elimina(&$valores)
    {
        $this->iniVar();
        if (isset($this->bdesplazamiento->_do->fechaexpulsion)) {
            $this->eliminaDesplazamiento($this->bdesplazamiento->_do);
            $_SESSION[$this->pref.'_total']--;
        }
    }


     /**
     * Inicializa variables y datos de la pestaña.
     * Ver documentación completa en clase base.
     *
     * @param array $aper Arreglo de parametros. Vacio aqui.
     *
     * @return handle Conexión a base de datos
     */
    function iniVar($aper = null)
    {
        $ddesplazamiento =& objeto_tabla('desplazamiento');

        $db =& $ddesplazamiento->getDatabaseConnection();

        $idcaso =& $_SESSION['basicos_id'];
        if (!isset($idcaso) || $idcaso == null) {
            die("Bug: idcaso no debería ser null");
        }

        $idf = array();
        $indid = -1;
        $tot = PagDesplazamiento::extrae_desplazamientos($idcaso, $db, $idf);

        $_SESSION[$this->pref.'_total'] = $tot;
        $ddesplazamiento->id_caso= $idcaso;
        if ($_SESSION[$this->pref.'_pag'] < 0
            || $_SESSION[$this->pref.'_pag'] >= $tot
        ) {
            $ddesplazamiento->fechaexpulsion = null;
        } else {
            $ddesplazamiento->fechaexpulsion 
                = $idf[$_SESSION[$this->pref.'_pag']];
            $ddesplazamiento->id_caso = $idcaso;
            $ddesplazamiento->find();
            $ddesplazamiento->fetch();
        }

        $this->bdesplazamiento =& DB_DataObject_FormBuilder::create(
            $ddesplazamiento,
            array('requiredRuleMessage' => $GLOBALS['mreglareq'],
                'ruleViolationMessage' => $GLOBALS['mreglavio']
            )
        );

        return $db;
    }


    /**
     * Constructora.
     * Ver documentación completa en clase base.
     *
     * @param string $nomForma Nombre
     *
     * @return void
     */
    function PagDesplazamiento($nomForma)
    {
        parent::PagBaseMultiple($nomForma);
        $this->titulo  = _('Desplazamiento');
        $this->tcorto  = _('Desplazamiento');
        if (isset($GLOBALS['etiqueta']['Desplazamiento'])) {
            $this->titulo = $GLOBALS['etiqueta']['Desplazamiento'];
            $this->tcorto = $GLOBALS['etiqueta']['Desplazamiento'];
        }
        $this->addAction(
            'departamentodecl', new CamDepartamento('departamentodecl')
        );
        $this->addAction(
            'municipiodecl', 
            new CamMunicipio('departamentodecl', 'municipiodecl')
        );
        $this->addAction('siguiente', new Siguiente());
        $this->addAction('anterior', new Anterior());
    }


    /**
     * Agrega elementos al formulario.
     * Ver documentación completa en clase base.
     *
     * @param handle &$db    Conexión a base de datos
     * @param string $idcaso Id del caso
     *
     * @return void
     *
     * @see PagBaseSimple
     */
    function formularioAgrega(&$db, $idcaso)
    {
        $vv = isset($this->bdesplazamiento->_do->fechaexpulsion)
            ? $this->bdesplazamiento->_do->fechaexpulsion : '';
        $this->addElement('');

        list($dep, $mun, $cla) = PagUbicacion::creaCampos(
            $this, 'departamentodecl', 'municipiodecl', 'clasedecl'
        );
        $gr[] =& $dep;
        $gr[] =& $mun;

        $_SESSION['pagDesplazamiento_id'] = $vv;
        $this->addGroup(
            $gr, 'sitiodeclaracion', _('Declaro en'), '&nbsp;', false
        );
        PagUbicacion::modCampos(
            $db, $this, 'departamentodecl', 'municipiodecl', null,
            $this->bdesplazamiento->_do->departamentodecl, 
            $this->bdesplazamiento->_do->municipiodecl, 
            null
        );
        $this->bdesplazamiento->createSubmit = 0;
        $this->bdesplazamiento->useForm($this);
        $this->bdesplazamiento->getForm($this);

    }

    /**
     * Llena valores del formulario.
     * Ver documentación completa en clase base.
     *
     * @param handle  &$db    Conexión a base de datos
     * @param integer $idcaso Id del caso
     *
     * @return void
     * @see PagBaseSimple
     */
    function formularioValores(&$db, $idcaso)
    {
        $vv = isset($this->bdesplazamiento->_do->fechaexpulsion) ?
            $this->bdesplazamiento->_do->fechaexpulsion : '';

        valores_pordefecto_form($this->bdesplazamiento->_do, $this);
        if ($vv == '') {
            $dcaso = objeto_tabla('caso');
            $dcaso->id = $_SESSION['basicos_id'];
            $dcaso->find();
            $dcaso->fetch(1);
            $pf = explode('-', $dcaso->fecha);
            $f =& $this->getElement('fechaexpulsion');
            $f->setValue(array('d' => $pf[2], 'M' => $pf[1], 'Y' => $pf[0]));
            $f =& $this->getElement('fechallegada');
            $f->setValue(
                array('d' => $pf[2],
                'M' => $pf[1],
                'Y' => $pf[0])
            );
        } else {
            $e =& $this->getElement('sitiodeclaracion');
            $dep =& $e->_elements[0];
            $mun =& $e->_elements[1];
            PagUbicacion::valoresUbicacion(
                $this, $this->bdesplazamiento->_do->departamentodecl,
                $this->bdesplazamiento->_do->municipiodecl, null,
                $dep, $mun, null, 'departamentodecl', 'municipiodecl'
            );
        }
    }

    /**
     * Elimina un registro 
     *
     * @param object $ddesplazamiento DataObject
     * 
     * @return  void
     */
    function eliminaDesplazamiento($ddesplazamiento)
    {
        assert($ddesplazamiento != null);
        assert($ddesplazamiento->fechaexpulsion != null);
        $db =& $ddesplazamiento->getDatabaseConnection();
        $q = "DELETE FROM desplazamiento WHERE fechaexpulsion=' "
            . "{$ddesplazamiento->fechaexpulsion}' "
            . " AND id_caso={$_SESSION['basicos_id']}";
        $result = hace_consulta($db, $q);
    }

    /**
     * eliminaDep($db, $idcaso) elimina victimas de la base $db presentados 
     * en este formulario, que dependen del caso $idcaso 
     *
     * @param object &$db    Conexión a base de datos
     * @param int    $idcaso Id del Caso.
     *
     * @return void
     */
    static function eliminaDep(&$db, $idcaso)
    {
        assert($db != null);
        assert(isset($idcaso));
        $ddesplazamiento =& objeto_tabla('desplazamiento');
        sin_error_pear($ddesplazamiento);
        $ddesplazamiento->id_caso = $idcaso;
        $ddesplazamiento->find();
        while ($ddesplazamiento->fetch()) {
            PagDesplazamiento::eliminaProceso($ddesplazamiento);
            $ddesplazamiento->delete();
        }
    }

    /**
     * Procesa
     *
     * @param array &$valores del formulario
     *
     * @return bool V sii pudo procesar
     */
    function procesa(&$valores)
    {
        $fechaex = arr_a_fecha($valores['fechaexpulsion'], true); 
        $fechall = arr_a_fecha($valores['fechallegada'], true); 

        $es_vacio = (!isset($valores['expulsion'])
                || $valores['expulsion'] === ''
            );

        if ($es_vacio) {
            return true;
        }

        if (!$this->validate() ) {
            return false;
        }
        if ($fechall < $fechaex) {
            error_valida(
                _('Fecha de llegada no puede ser anterior a la de expulsión'),
                $valores
            );
            return false;
        }

        if (in_array(31, $_SESSION['opciones'])
            && !in_array(21, $_SESSION['opciones'])
        ) {
            return true;
        }

        $db = $this->iniVar();
        $dcaso = objeto_tabla('caso');
        if (PEAR::isError($dcaso)) {
            die($dcaso->getMessage());
        }
        $idcaso = $_SESSION['basicos_id'];
        $q = "SELECT COUNT(*) FROM desplazamiento WHERE id_caso='$idcaso' "
            . " AND fechaexpulsion='$fechaex';";
        $this->bdesplazamiento->useMutators = true;
        $nr = $db->getOne($q);
        if ($this->bdesplazamiento->_do->fechaexpulsion == null 
            || $this->bdesplazamiento->_do->fechaexpulsion == ''
        ) {
            if ($nr > 0) {
                error_valida(
                    _('Ya había desplazamiento con esa fecha de expulsión'),
                    $valores
                );
                return false;
            }
        }

        if ($nr == 0) {
            $this->bdesplazamiento->forceQueryType(
                DB_DATAOBJECT_FORMBUILDER_QUERY_FORCEINSERT
            );
        } else {
            $this->bdesplazamiento->forceQueryType(
                DB_DATAOBJECT_FORMBUILDER_QUERY_FORCEUPDATE
            );
        }
 
        $ret = $this->process(
            array(&$this->bdesplazamiento, 'processForm'), false
        );
        if (PEAR::isError($ret)) {
            die($ret->getMessage());
        }

        caso_funcionario($idcaso);
        return  $ret;
    }

    /**
     * Prepara consulta SQL para buscar datos de este formulario.
     * Ver documentación completa en clase base.
     *
     * @param string &$w       Consulta que se construye
     * @param string &$t       Tablas
     * @param object &$db      Conexión a base de datos
     * @param object $idcaso   Identificación del caso
     * @param string &$subcons Subconsulta
     *
     * @return void
     * @see PagBaseSimple
     */
    function datosBusqueda(&$w, &$t, &$db, $idcaso, &$subcons)
    {

    }

    /** Extrae desplazamientos de un caso y retorna su información en 
     *  vectores
     *
     *  @param integer $idcaso Id. del Caso
     *  @param object  &$db    Conexión a BD
     *  @param array   &$idf   Para retornar fechas
     *
     *  @return integer Cantidad de desplazamientos retornados
     **/
    function extrae_desplazamientos($idcaso, &$db, &$idf)
    {
        $q = "SELECT fechaexpulsion FROM desplazamiento WHERE " 
            . "desplazamiento.id_caso='" . (int)$idcaso 
            . "' ORDER BY fechaexpulsion";
        $result = hace_consulta($db, $q);
        $row = array();
        $tot = 0;
        while ($result->fetchInto($row)) {
            $idf[] = $row[0];
            $tot++;
        }
        return $tot;
    }

    /**
     * Llamada para inicializar variables globales
     *
     * @return void
     */
    static function act_globales()
    {
        html_menu_agrega_submenu(
            $GLOBALS['menu_tablas_basicas'],
            null, 'Desplazamiento',
            '', null
        );
        html_menu_agrega_submenu(
            $GLOBALS['menu_tablas_basicas'],
            'Desplazamiento', 'Clasificación',
            'clasifdesp', null
        );
        html_menu_agrega_submenu(
            $GLOBALS['menu_tablas_basicas'],
            'Desplazamiento', 'Tipos',
            'tipodesp', null
        );
        html_menu_agrega_submenu(
            $GLOBALS['menu_tablas_basicas'],
            'Desplazamiento', 'Entidades para declarar',
            'declaroante', null
        );
        html_menu_agrega_submenu(
            $GLOBALS['menu_tablas_basicas'],
            'Desplazamiento', 'Inclusión',
            'inclusion', null
        );
        html_menu_agrega_submenu(
            $GLOBALS['menu_tablas_basicas'],
            'Desplazamiento', 'Modalidad Tenencia de Tierra',
            'modalidadtierra', null
        );
    }

    /**
     * Compara datos relacionados con esta pestaña de los casos
     * con identificación id1 e id2.
     *
     * @param object  &$db Conexión a base de datos
     * @param array   &$r  Para llenar resultados de comparación, cada
     *   entrada es de la forma
     *      id_unica => ('etiqueta', 'valor1', 'valor2', pref)
     *   donde valor1 es valor en primer caso, valor2 es valor en segundo
     *   caso y pref es 1 o 2 para indicar cual de los valores será por defecto
     * @param integer $id1 Código de primer caso
     * @param integer $id2 Código de segundo caso
     * @param array   $cls Especificación de las tablas por revisar.
     *
     * @return void Añade a $r datos de comparación
     * @see PagBaseSimple
     */
    static function compara(&$db, &$r, $id1, $id2, $cls)
    {
        PagBaseMultiple::compara(
            $db, $r, $id1, $id2,
            array('Desplazamiento' => array('desplazamiento', 'fechaexpulsion'))
        );
    }

    /**
     * Mezcla valores de los casos $id1 e $id2 en el caso $idn de
     * acuerdo a las preferencias especificadas en $sol.
     *
     * @param object  &$db Conexión a base de datos
     * @param array   $sol Arreglo con solicitudes de cambios de la forma
     *   id_unica => (pref)
     *   donde pref es 1 si el valor relacionado con id_unica debe
     *   tomarse del caso $id1 o 2 si debe tomarse de $id2.  Las
     *   identificaciones id_unica son las empleadas por la función
     *   compara.
     * @param integer $id1 Código de primer caso
     * @param integer $id2 Código de segundo caso
     * @param integer $idn Código del caso en el que aplicará los cambios
     * @param array   $cls Especificación de tablas por mezclar
     *
     * @return Mezcla valores de los casos $id1 e $id2 en el caso $idn de
     * acuerdo a las preferencias especificadas en $sol.
     * @see PagBaseSimple
     */
    static function mezcla(&$db, $sol, $id1, $id2, $idn, $cls)
    {
        //echo "OJO PagDesplazamiento::mezcla(db, sol, $id1, $id2, $idn, $cls)";
        $e1 = isset($sol['desplazamiento']['fechaexpulsion'])
            && $sol['desplazamiento']['fechaexpulsion'] == 1;
        if (($e1 && $idn != $id1) || (!$e1 && $idn != $id2)) {
            PagDesplazamiento::eliminaDep($db, $idn);
            PagBaseMultiple::mezcla(
                $db, $sol, $id1, $id2, $idn,
                array('Desplazamiento' => array(
                    'desplazamiento', 'id_fechaexpulsion'
                ))
            );
            echo "Falta completar copia de Desplazamiento<br>";
        }
    }

}

?>
