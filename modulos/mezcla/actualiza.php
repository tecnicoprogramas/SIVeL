<?php
// vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker fileencoding=utf-8:
/**
* Actualiza
 *
 * PHP version 5
 *
 * @category  SIVeL
 * @package   SIVeL
 * @author    Vladimir Támara <vtamara@pasosdeJesus.org>
 * @copyright 2009 Dominio público. Sin garantías.
 * @license   https://www.pasosdejesus.org/dominio_publico_colombia.html Dominio Público. Sin garantías.
 * @link      http://sivel.sf.net
 */

/** Actualiza base de datos después de actualizar fuentes */
require_once "aut.php";
require_once $_SESSION['dirsitio'] . '/conf.php';
require_once "confv.php";
require_once "misc.php";
require_once "misc_actualiza.php";


$aut_usuario = "";
$db = autentica_usuario($dsn, $accno, $aut_usuario, 21);


$act = objeto_tabla('actualizacionbase');

$idac = 'mez-em';
if (!aplicado($idac)) {
    inserta_etiqueta_si_falta(
        $db, 'MEZCLA_CASOS', 'Caso tras mezclar dos'
    );
    aplicaact($act, $idac, 'Etiqueta para mezcla');
}


?>
