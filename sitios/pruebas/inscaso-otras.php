<?php
// vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker fileencoding=utf-8:

/**
* Inserción de otras fuentes de un caso
 *
 * PHP version 5
 *
 * @category  SIVeL
 * @package   SIVeL-pruebas
 * @author    Vladimir Támara <vtamara@pasosdeJesus.org>
 * @copyright 2007 Dominio público. Sin garantías.
 * @license   https://www.pasosdejesus.org/dominio_publico_colombia.html Dominio Público. Sin garantías.
 * @link      http://sivel.sf.net
*/

/**
 * Inserción de otras fuentes de un caso
 */
if (PHP_SAPI !== 'cli') {
    die("Acceso: INTERPRETE DE COMANDOS");
}
require_once "ambiente.php";

/*** OTRAS FUENTES ***/

$post = array();
$post['nombre'] = 'n';
$post['id'] = $post['id_fotra'] = '';
$post['anotacion'] = 'a';
$post['fecha']['d'] = '15';
$post['fecha']['M'] = '10';
$post['fecha']['Y'] = '2007';
$post['ubicacionfisica'] = 'ubicación';
$post['tfuente'] = '0';
$post['_qf_otras_siguienteMultiple'] = 'Fuente siguiente';
$post['_qf_default'] = 'otras:siguiente';
pasaPestanaFicha($db, array("fotra"), $post, 1);

exit(0);
?>
