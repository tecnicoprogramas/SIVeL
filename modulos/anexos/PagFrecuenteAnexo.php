<?php
// vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker fileencoding=utf-8:
/**
 * Página del multi-formulario para capturar caso (captura_caso.php).
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

/**
 * Pestaña Fuentes Frecuentes con Anexo del multi-formulario capturar caso
 */

require_once 'PagFuentesFrecuentes.php';
require_once 'ResConsulta.php';


/**
 * Página fuentes frecuentes con anexo.
 * Ver documentación de funciones en clase base.
 *
 * @category SIVeL
 * @package  SIVeL
 * @author   Vladimir Támara <vtamara@pasosdeJesus.org>
 * @license  https://www.pasosdejesus.org/dominio_publico_colombia.html Dominio Público.
 * @link     http://sivel.sf.net/tec
 * @see      PagFuentesFrecuentes
 */
class PagFrecuenteAnexo extends PagFuentesFrecuentes
{


    var $titulo = 'Fuentes Frecuentes';

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
        if ($this->bcaso_ffrecuente->_do->id_ffrecuente != null
            && $this->bcaso_ffrecuente->_do->fecha != null
        ) {
            $idcaso = $this->bcaso_ffrecuente->_do->id_caso;
            $vf = "'{$this->bcaso_ffrecuente->_do->fecha}'";
            $vp = "'{$this->bcaso_ffrecuente->_do->id_ffrecuente}'";
            $q =  "UPDATE anexo SET fechaffrecuente=NULL, id_ffrecuente=NULL" .
                " WHERE id_caso='$idcaso' AND fechaffrecuente=$vf " .
                " AND id_ffrecuente=$vp";
            $db = $this->bcaso_ffrecuente->_do->getDatabaseConnection();
            hace_consulta($db, $q, false);
        }

        parent::elimina($valores);
    }


    /**
     * Constructora.
     * Ver documentación completa en clase base.
     *
     * @param string $nomForma Nombre
     *
     * @return void
     */
    function PagFrecuenteAnexo($nomForma)
    {
        parent::PagFuentesFrecuentes($nomForma);
        $this->titulo  = _('Fuentes Frecuentes');
        if (isset($GLOBALS['etiqueta']['Fuentes Frecuentes'])) {
            $this->titulo = $GLOBALS['etiqueta']['Fuentes Frecuentes'];
            $this->tcorto = $GLOBALS['etiqueta']['Fuentes Frecuentes'];
        }
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

        parent::formularioAgrega($db, $idcaso);

        if (!isset($_SESSION['forma_modo'])
            || $_SESSION['forma_modo'] != 'busqueda'
        ) {
            if ($this->bcaso_ffrecuente->_do->id_ffrecuente != null) {
                $cor = "OR (id_ffrecuente=" .
                    "'{$this->bcaso_ffrecuente->_do->id_ffrecuente}' " .
                    "AND fechaffrecuente='{$this->bcaso_ffrecuente->_do->fecha}')";
            } else {
                $cor = "";
            }
            $condb = "AND id_fotra IS NULL " .
            "AND (id_ffrecuente IS NULL $cor)  " ;
            $an = $this->addElement(
                'select', 'id_anexo', _('Anexo'),
                array()
            );
            $q = "SELECT  id, archivo FROM anexo " .
                "WHERE id_caso='" . (int)$_SESSION['basicos_id'] . "' " .
                $condb .
                "ORDER BY archivo ";
            //echo $q;
            $a = $db->getAssoc($q);
            sin_error_pear($a);
            $options = array('' => '') +
                htmlentities_array($a);
            $an->loadArray($options);
        }

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
        parent::formularioValores($db, $idcaso);

        $puesto = false;
        $sel = $this->getElement('id_anexo');
        if ($this->bcaso_ffrecuente->_do->id_ffrecuente != null) {
            $danexo = objeto_tabla('anexo');
            $danexo->id_caso = $_SESSION['basicos_id'];
            $danexo->id_ffrecuente = $this->bcaso_ffrecuente->_do->id_ffrecuente;
            $danexo->id_fechaffrecuente = $this->bcaso_ffrecuente->_do->fecha;
            $danexo->find();
            if ($danexo->fetch()) {
                $sel->setValue($danexo->id);
                $puesto = true;
            }
        }
        if ((!isset($_SESSION['forma_modo'])
            || $_SESSION['forma_modo'] != 'busqueda') 
            && !$puesto
        ) {
            $sel->setValue('');
        }
    }



    /**
     * Procesa valores del formulario enviados por el usuario.
     * Ver documentación completa en clase base.
     *
     * @param handle &$valores Valores ingresados por usuario
     *
     * @return bool Verdadero si y solo si puede completarlo con éxito
     * @see PagBaseSimple
     */
    function procesa(&$valores)
    {
        $idcaso = $_SESSION['basicos_id'];

        $db = $this->iniVar();

        $r = parent::procesa($valores);
        if (in_array(31, $_SESSION['opciones'])
            && !in_array(21, $_SESSION['opciones'])
        ) {
            return true;
        }


        if (!es_objeto_nulo($this->bcaso_ffrecuente->_do->id_ffrecuente)
            && !es_objeto_nulo($this->bcaso_ffrecuente->_do->fecha)
        ) {
            $vf = "'{$this->bcaso_ffrecuente->_do->fecha}'";
            $vp = "'{$this->bcaso_ffrecuente->_do->id_ffrecuente}'";
            if (isset($valores['id_anexo'])
                && $valores['id_anexo'] != ''
            ) {
                $ida = var_escapa($valores['id_anexo'], $db);
                $q =  "UPDATE anexo SET fechaffrecuente=$vf, id_ffrecuente=$vp " .
                    "WHERE id_caso='$idcaso' AND id='$ida'";
            } else {
                $q =  "UPDATE anexo SET fechaffrecuente=NULL, id_ffrecuente=NULL" .
                    " WHERE id_caso='$idcaso' AND fechaffrecuente=$vf " .
                    " AND id_ffrecuente=$vp";
            }
            //echo $q;
            hace_consulta($db, $q, false);
        }

        caso_funcionario($_SESSION['basicos_id']);
        return $r;
    }



}

?>
