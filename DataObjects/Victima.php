<?php
// vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker fileencoding=utf-8:
/**
 * Objeto asociado a una tabla de la base de datos.
 * Parcialmente generado por DB_DataObject.
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
 * Definicion para la tabla victima.
 */
require_once 'DB_DataObject_SIVeL.php';
require_once "Profesion.php";
require_once "Etnia.php";
require_once "Iglesia.php";
require_once "Rangoedad.php";
require_once "DataObjects/Filiacion.php";
require_once "Sectorsocial.php";
require_once "Organizacion.php";
require_once "Vinculoestado.php";
require_once "Presponsable.php";

/**
 * Definicion para la tabla victima.
 * Ver documentación de DataObjects_Caso.
 *
 * @category SIVeL
 * @package  SIVeL
 * @author   Vladimir Támara <vtamara@pasosdeJesus.org>
 * @license  https://www.pasosdejesus.org/dominio_publico_colombia.html Dominio Público.
 * @link     http://sivel.sf.net/tec
 * @see      DataObjects_Caso
 */
class DataObjects_Victima extends DB_DataObject_SIVeL
{

    var $__table = 'victima';                         // table name
    var $id_persona;                      // int4(4)  not_null primary_key
    var $id_caso;                         // int4(4)
    var $hijos;                            // varchar(-1)
    var $id_profesion;                    // int4(4)
    var $id_rangoedad;                   // int4(4)
    var $id_filiacion;                    // int4(4)
    var $id_sectorsocial;                // int4(4)
    var $id_organizacion;                 // int4(4)
    var $id_vinculoestado;               // int4(4)
    var $organizacionarmada;          // int4(4)
    var $id_etnia;
    var $id_iglesia;
    var $anotaciones;                       // varchar(-1)
    var $orientacionsexual;

    var $fb_preDefOrder = array('hijos',
        'id_profesion', 'id_rangoedad', 'id_filiacion',
        'id_sectorsocial', 'id_organizacion', 'id_vinculoestado',
        'organizacionarmada', 'id_etnia', 'id_iglesia',
        'orientacionsexual','anotaciones'
    );
    var $fb_fieldsToRender = array('hijos',
        'id_profesion', 'id_rangoedad', 'id_filiacion',
        'id_sectorsocial', 'id_organizacion', 'id_vinculoestado',
        'organizacionarmada', 'id_etnia',
        'orientacionsexual', 'anotaciones'
    );
    var $fb_enumFields = array('orientacionsexual');
    var $fb_addFormHeader = false;
    var $fb_selectAddEmpty = array();
    var $fb_fieldsRequired = array('id_profesion', 'id_rangoedad',
        'id_filiacion', 'id_sectorsocial', 'id_organizacion',
        'id_vinculoestado', 'organizacionarmada', 'id_etnia',
        'orientacionsexual'
    );
    var $fb_excludeFromAutoRules = array('nombre', 'id_profesion',
        'id_rangoedad', 'id_filiacion', 'id_sectorsocial',
        'id_organizacion', 'id_vinculoestado', 'organizacionarmada',
        'id_etnia', 'id_iglesia', 'orientacionsexual'
    );
    /**
     * Constructora
     * return @void
     */
    public function __construct()
    {
        parent::__construct();

        $this->fb_fieldLabels= array(
            'hijos'=> _('Hijos'),
            'id_profesion'=> _('Profesión'),
            'id_rangoedad'=> _('Rango de Edad en Hecho'),
            'id_filiacion'=> _('Filiación Política'),
            'id_sectorsocial'=> _('Sector Social'),
            'id_organizacion'=> _('Organización'),
            'id_vinculoestado'=> _('Vínculo con el Estado'),
            'organizacionarmada'=> _('Organización Armada Víctima'),
            'id_etnia' => _('Etnia'),
            'id_iglesia' => _('Iglesia'),
            'anotaciones'=> _('Anotaciones'),
            'orientacionsexual' => _('Orientación Sexual'),
        );
        $this->es_enumOptions = array(
            'orientacionsexual' => array(
                'L' => _('Lesbiana'),
                'G' => _('Gay'),
                'B'=> _('Bisexual'),
                'T'=> _('Transexual'),
                'I'=> _('Intersexual'),
                'H'=> _('Heterosexual'),
            )
        );
    }

    var $fb_hidePrimaryKey = true;
    var $fb_linkDisplayFields = array('id_persona');

    /**
    * Modifica cantidad de hijos antes de incluirlo en base de datos.
    *
    * @param string $value Valor recibido de formulario
    *
    * @return Valor para base de datos
    */
    function sethijos($value)
    {
        $this->hijos= ($value == '') ? 'null' : $value;
    }


    /**
     * Campos que pueden ser SIN INFORMACION y el código correspondiente
     *
     * @return array Arreglo de campos que pueden ser sin información
     */
    static function camposSinInfo()
    {
        return array(
            'id_rangoedad'=> DataObjects_Rangoedad::idSinInfo(),
            'id_profesion'=> DataObjects_Profesion::idSinInfo(),
            'id_filiacion' => DataObjects_Filiacion::idSinInfo(),
            'id_sectorsocial' => DataObjects_Sectorsocial::idSinInfo(),
            'id_organizacion' => DataObjects_Organizacion::idSinInfo(),
            'id_vinculoestado' => DataObjects_Vinculoestado::idSinInfo(),
            'organizacionarmada' =>
                DataObjects_Presponsable::idSinInfo(),
            'id_etnia' =>
                DataObjects_Etnia::idSinInfo(),
            'id_iglesia' =>
                DataObjects_Iglesia::idSinInfo(),
            'orientacionsexual' => 'H'
        );
    }

    /**
     * Prepara consulta agregando objeto enlazado a este por
     * campo field.
     *
     * @param object &$opts  objeto DB para completar consulta
     * @param string &$field campo por el cual enlazar
     *
     * @return void
     */
    function prepareLinkedDataObject(&$opts, &$field)
    {
        switch ($field) {
        case 'id_sectorsocial':
        case 'id_vinculoestado':
        case 'id_organizacion':
        case 'id_profesion':
        case 'id_filiacion':
        case 'id_etnia':
        case 'id_iglesia':
            $opts->whereAdd('fechadeshabilitacion IS NULL');
            break;

        }
    }


    /**
     * Prepara antes de generar formulario.
     *
     * @param object &$formbuilder Generador DataObject_FormBuilder
     *
     * @return void
     */
    function preGenerateForm(&$formbuilder)
    {
        $formbuilder->enumOptionsCallback = array($this,
            "enumCallback"
        );
        $csin = $this->camposSinInfo();
        foreach ($csin as $c => $v) {
            if (!isset($this->$c)) {
                $this->$c = $csin[$c];
            }
        }
        $this->fb_preDefElements = array('id_persona' =>
            HTML_QuickForm::createElement('hidden', 'id_persona')
        );
        $this->fb_preDefElements = array('id_caso' =>
            HTML_QuickForm::createElement('hidden', 'id_caso')
        );
    }

    /**
     * Ajusta formulario generado.
     *
     * @param object &$form        Formulario HTML_QuickForm
     * @param object &$formbuilder Generador DataObject_FormBuilder
     *
     * @return void
     */
    function postGenerateForm(&$form, &$formbuilder)
    {
        parent::postGenerateForm($form, $formbuilder);
        $sel =& $form->getElement('hijos');
        if (isset($this->hijos) && $this->hijos == 'null') {
            $sel->setValue('');
        }
        $sel =& $form->getElement('anotaciones');
        if (isset($sel) && !PEAR::isError($sel)) {
            $sel->setSize(75);
        }

    }


    /**
     * Convierte registro a relato (arreglo de elementos) que agrega a $ar
     * dad son datos adicionales que pueden requerirse para la conversión.
     *
     * @param array &$ar Arreglo con elementos
     * @param array $dad Datos adicionales para conversión
     *
     * @return array $ar modificado
     */
    function aRelato(&$ar, $dad = array())
    {
        parent::aRelato($ar, $dad);
        $ar['id_persona'] = $this->id_persona;
        $dpersona = $this->getLink('id_persona');
        $dpersona->fb_fieldsToRender = array_merge(
            $dpersona->fb_fieldsToRender,
            array('id_departamento', 'id_municipio', 'id_clase')
        );
        $dpersona->aRelato($ar, $dad);
        $ar['docid'] = trim(
            $dpersona->tipodocumento . " " .
            $dpersona->numerodocumento
        );
        if ($dpersona->anionac != '') {
            $m = $dpersona->mesnac != '' ? $dpersona->mesnac : '06';
            $d = $dpersona->dianac != '' ? $dpersona->dianac : '15';
            $ar['fecha_nacimiento'] = $dpersona->anionac . "-" .
                $m . "-" . $d ;
        }

        return $ar;
    }
}

?>
