<?php
// vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker fileencoding=utf-8:
/**
* Actualiza base de datos después de actualizar fuentes
 *
 * PHP version 5
 *
 * @category  SIVeL
 * @package   SIVeL
 * @author    Vladimir Támara <vtamara@pasosdeJesus.org>
 * @copyright 2006 Dominio público. Sin garantías.
 * @license   https://www.pasosdejesus.org/dominio_publico_colombia.html Dominio Público. Sin garantías.
 * @link      http://sivel.sf.net
 */

/** Actualiza base de datos después de actualizar fuentes */
require_once "aut.php";
require_once $_SESSION['dirsitio'] . '/conf.php';

$aut_usuario = "";
$db = autentica_usuario($dsn, $aut_usuario, 21);

require_once $_SESSION['dirsitio'] . '/conf_int.php';
require_once "confv.php";
require_once "misc.php";
require_once "DataObjects/Categoria.php";
require_once "misc_actualiza.php";

if (!in_array(63, $_SESSION['opciones'])) {
    hace_consulta(
        $db, "INSERT INTO opcion VALUES ('63', 'Actualizar', '60', " .
        "'actualiza')", false, false
    );
    hace_consulta(
        $db, "INSERT INTO opcion_rol (id_opcion, id_rol) " .
        "VALUES (63, 1)", false, false
    );
    include "terminar.php";
    die("");
}
$db = autentica_usuario($dsn, $aut_usuario, 63);

/**
 * Regenera esquemas para base de datos base.ini y base.links.in
 * sacando informacion de los archivos estructura.ini y estructura.links.ini
 * de SIVeL basico y de los modulos.
 *
 * @return void
 */
function regenera_esquemas()
{
    global $dirserv, $dirsitio, $dbnombre, $dirchroot, $modulos;
    $nini = "$dirserv/$dirsitio/DataObjects/$dbnombre.ini";
    $nlinksini = "$dirserv/$dirsitio/DataObjects/$dbnombre.links.ini";
    $nestini = "$dirserv/$dirsitio/DataObjects/estructura-dataobject.ini";
    if (is_writable($nini) && is_writable($nlinksini)) {
        lee_escritura($dirserv, $dbnombre, "$dirserv/$dirsitio", "w");
        foreach (explode(" ", $modulos) as $i) {
            lee_escritura("$dirserv/$i", $dbnombre, "$dirserv/$dirsitio", "a");
        }
        if (file_exists($nestini)) {
            lee_escritura(
                "$dirserv/$dirsitio", $dbnombre, "$dirserv/$dirsitio", "a"
            );
        }
        echo_esc(
            "Regenerados $dirserv/$dirsitio/DataObjects/$dbnombre.ini y "
            . "$dirserv/$dirsitio/DataObjects/$dbnombre.ini "
        );
        echo "<br><font color='#FF9999'>Se sugiere quitar "
            . "permiso de escritura desde el servidor web a estos archivos.</font> "
            . "Desde la línea de comandos intente el siguiente comando y vuelva "
            . " a cargar esta página: <br><tt>";
        echo_esc(
            "  sudo chmod a-w $dirchroot/$dirserv/$dirsitio/DataObjects/$dbnombre.*"
        );
        echo "</tt>";
    } else {
        echo_esc(
            "No se regenerará esquema, rengerelo manualmente o de permiso "
            . " de escritura a "
            . "$dirchroot/$dirserv/$dirsitio/DataObjects/$dbnombre.ini y "
            . "$dirchroot/$dirserv/$dirsitio/DataObjects/$dbnombre.links.ini "
            . "Puede ser desde una línea de comandos con:"
        );
        echo "<tt>";
        echo_esc(
            "  sudo chown www:www "
            . "$dirchroot/$dirserv/$dirsitio/DataObjects/$dbnombre.*"
        );
        echo_esc(
            "  sudo chmod u+w "
            . "$dirchroot/$dirserv/$dirsitio/DataObjects/$dbnombre.*"
        );
        echo "</tt>";
    }
}

encabezado_envia(_('Actualizando'));
echo '<table width="100%"><td style="white-space: nowrap; '
    . 'background-color: #CCCCCC;" align="left" valign="top" colspan="2">'
    . '<b><div align=center>' . _('Actualizando') . '</div></b></td></table>';
$r = $db->getOne('SELECT COUNT(*) FROM actualizacionbase');
if (PEAR::isError($r)) {
    $r = $db->query(
        'ALTER TABLE actualizacion_base RENAME TO actualizacionbase'
    );
    regenera_esquemas();
}


echo "Preactualizando sitio<br>";
$ap = $_SESSION['dirsitio'] . '/preactualiza.php';
if (file_exists($ap)) {
    echo "Preactualizando personalización<br>";
    include_once "$ap";
}

$act = objeto_tabla('Actualizacionbase');
if (PEAR::isError($act)) {
    die("No pudo act");
}

$idact = '0.92post';
if (!aplicado($idact)) {
    hace_consulta(
        $db, "ALTER TABLE categoria ADD COLUMN contada_en INTEGER " .
        " REFERENCES categoria"
    );
    $cat = objeto_tabla('categoria');
    // EJECUCIÓN EXTRAJUDICIAL
    $cat = DataObjects_Categoria::staticGet('10'); $cat->contada_en='701';
    $cat->update();
    // AMENAZA
    $cat = DataObjects_Categoria::staticGet('15'); $cat->contada_en='73';
    $cat->update();
    // COLECTIVO AMENAZADO
    $cat = DataObjects_Categoria::staticGet('18'); $cat->contada_en='706';
    $cat->update();
    // TORTURA
    $cat = DataObjects_Categoria::staticGet('12'); $cat->contada_en='72';
    $cat->update();
    // HERIDO
    $cat = DataObjects_Categoria::staticGet('13'); $cat->contada_en='702';
    $cat->update();
    // VIOLENCIA SEXUAL
    $cat = DataObjects_Categoria::staticGet('19'); $cat->contada_en='77';
    $cat->update();
    // COLECTIVO DESPLAZADO
    $cat = DataObjects_Categoria::staticGet('102'); $cat->contada_en='903';
    $cat->update();

    aplicaact($act, $idac, 'Categorias repetidas marcadas');
}

$idact = '0.94';
if (!aplicado($idact)) {
    hace_consulta(
        $db, "UPDATE opcion " .
        " SET descripcion='Conteos V. Individuales' " .
        " WHERE id_opcion='51';"
    );
    hace_consulta(
        $db, "UPDATE opcion " .
        " SET descripcion='Conteos V. Combatientes' " .
        " WHERE id_opcion='52';"
    );
    hace_consulta(
        $db, "UPDATE opcion " .
        " SET descripcion='Menú Conteos' " .
        " WHERE id_opcion='50';"
    );
    aplicaact($act, $idac, 'Conteos');
}

$idact = '1.0';
if (!aplicado($idact)) {
    hace_consulta(
        $db, "ALTER TABLE victima " .
        " ADD COLUMN anotaciones VARCHAR(1000)"
    );
    aplicaact($act, $idac, 'Anotaciones en víctima');
}

$idact = '1.0';
if (!aplicado($idact)) {

    // Consistencia en combatiente

    $nr = $db->getOne(
        "SELECT COUNT(id) FROM combatiente " .
        "WHERE id_resultado_agresion IS NULL"
    );
    if (PEAR::isError($nr)) {
        die($nr->getMessage() . " - " . $nr->getUserInfo());
    }
    if ($nr > 0) {
        echo "<font color='red'>Antes de aplicar la actualización " .
        "debe definir 'Resultados " .
        "de Agresión' no especificados contra " . (int)$nr .
        "combatientes de los siguientes casos: <br>";
        $r = hace_consulta(
            $db, "SELECT DISTINCT id_caso " .
            "FROM combatiente WHERE id_resultado_agresion IS NULL"
        );
        $row = array();
        $sep = "";
        while ($r->fetchInto($row)) {
            echo htmlentities($sep . $row[0], ENT_COMPAT, 'UTF-8');
            $sep = ", ";
        }
        echo "</font>";
        die("");
    }

    hace_consulta(
        $db, "ALTER TABLE combatiente ALTER " .
        "COLUMN id_resultado_agresion SET NOT NULL"
    );

    // Consistencia en demográficas, no sabe y otros = SIN INFORMACIÓN
    hace_consulta(
        $db, "UPDATE victima SET id_rango_edad='6' " .
        "WHERE id_rango_edad IS NULL"
    );
    hace_consulta($db, "ALTER TABLE victima ALTER id_rango_edad SET NOT NULL");
    hace_consulta(
        $db, "UPDATE victima SET id_profesion='22' " .
        "WHERE id_profesion IS NULL"
    );
    hace_consulta($db, "ALTER TABLE victima ALTER id_profesion SET NOT NULL");
    hace_consulta(
        $db, "UPDATE victima SET id_filiacion='10' " .
        "WHERE id_filiacion IS NULL"
    );
    hace_consulta($db, "ALTER TABLE victima ALTER id_filiacion SET NOT NULL");
    hace_consulta(
        $db, "UPDATE victima SET id_sector_social='15' " .
        "WHERE id_sector_social IS NULL"
    );
    hace_consulta($db, "ALTER TABLE victima ALTER id_sector_social SET NOT NULL");
    hace_consulta(
        $db, "UPDATE victima SET id_organizacion='16' " .
        "WHERE id_organizacion IS NULL"
    );
    hace_consulta($db, "ALTER TABLE victima ALTER id_organizacion SET NOT NULL");
    hace_consulta(
        $db, "UPDATE victima SET id_vinculo_estado='38' " .
        "WHERE id_vinculo_estado IS NULL"
    );
    hace_consulta($db, "ALTER TABLE victima ALTER id_vinculo_estado SET NOT NULL");
    hace_consulta(
        $db, "UPDATE victima SET id_organizacion_armada='35' " .
        "WHERE id_organizacion_armada IS NULL"
    );
    hace_consulta(
        $db, "ALTER TABLE victima ALTER id_organizacion_armada " .
        "SET NOT NULL"
    );


    // Nuevas categorias en marco NyN

    hace_consulta(
        $db, "UPDATE parametros_reporte_consolidado " .
        "SET rotulo = 'RECLUTAMIENTO DE MENORES' WHERE no_columna='20'"
    );
    hace_consulta(
        $db, "UPDATE parametros_reporte_consolidado " .
        "SET rotulo = 'TOMA DE REHENES' WHERE no_columna='21'"
    );
    hace_consulta(
        $db, "INSERT INTO parametros_reporte_consolidado " .
        "(no_columna, rotulo, tipo_violencia, clasificacion) VALUES " .
        "(25, 'COLECTIVO CONFINADO', 'DIH', 'INTEGRIDAD')", false
    );

    $preins = "INSERT INTO categoria (id, fecha_creacion, " .
        " fecha_deshabilitacion, id_supracategoria, id_tipo_violencia, " .
        " col_rep_consolidado, nombre) VALUES ";
    hace_consulta(
        $db, $preins .
        "(196, '2008-10-20', NULL, 1, 'A', NULL, 'V.S. - ABUSO SEXUAL')",
        false
    );
    hace_consulta(
        $db, $preins .
        "(291, '2008-10-20', NULL, 2, 'A', 8, 'V.S. - VIOLACIÓN')",
        false
    );
    hace_consulta(
        $db, $preins .
        "(292, '2008-10-20', NULL, 2, 'A', 8, 'V.S. - EMBARAZO FORZADO')",
        false
    );
    hace_consulta(
        $db, $preins .
        "(293, '2008-10-20', NULL, 2, 'A', 8, 'V.S. - PROSTITUCIÓN FORZADA')",
        false
    );
    hace_consulta(
        $db, $preins .  "(294, '2008-10-20', NULL, 2, " .
        "'A', 8, 'V.S. - ESTERILIZACIÓN FORZADA')", false
    );
    hace_consulta(
        $db, $preins .  "(295, '2008-10-20', NULL, 2, " .
        "'A', 8, 'V.S. - ESCLAVITUD SEXUAL')", false
    );
    hace_consulta(
        $db, $preins .
        "(296, '2008-10-20', NULL, 2, 'A', NULL, 'V.S. - ABUSO SEXUAL')", false
    );
    hace_consulta(
        $db, $preins .
        "(391, '2008-10-20', NULL, 3, 'A', 8, 'V.S. - VIOLACIÓN')", false
    );
    hace_consulta(
        $db, $preins .
        "(392, '2008-10-20', NULL, 3, 'A', 8, 'V.S. - EMBARAZO FORZADO')", false
    );
    hace_consulta(
        $db, $preins .
        "(393, '2008-10-20', NULL, 3, 'A', 8, 'V.S. - PROSTITUCIÓN FORZADA')",
        false
    );
    hace_consulta(
        $db, $preins .
        "(394, '2008-10-20', NULL, 3, 'A', 8, 'V.S. - ESTERILIZACIÓN FORZADA')",
        false
    );
    hace_consulta(
        $db, $preins .
        "(395, '2008-10-20', NULL, 3, 'A', 8, 'V.S. - ESCLAVITUD SEXUAL')",
        false
    );
    hace_consulta(
        $db, $preins .
        "(771, '2008-10-20', NULL, 1, 'D', 12, 'VIOLACIÓN')", false
    );
    hace_consulta(
        $db, $preins .
        "(772, '2008-10-20', NULL, 1, 'D', 12, 'EMBARAZO FORZADO')", false
    );
    hace_consulta(
        $db, $preins .
        "(773, '2008-10-20', NULL, 1, 'D', 12, 'PROSTITUCIÓN FORZADA')", false
    );
    hace_consulta(
        $db, $preins .
        "(774, '2008-10-20', NULL, 1, 'D', 12, 'ESTERILIZACIÓN FORZADA')", false
    );
    hace_consulta(
        $db, $preins .
        "(775, '2008-10-20', NULL, 1, 'D', 12, 'ESCLAVITUD SEXUAL')", false
    );
    hace_consulta(
        $db, $preins .
        "(776, '2008-10-20', NULL, 1, 'D', 12, 'ABUSO SEXUAL')", false
    );
    hace_consulta(
        $db, $preins .
        "(191, '2008-10-20', NULL, 1, 'A', 8, 'V.S. - VIOLACIÓN')", false
    );
    hace_consulta(
        $db, $preins .
        "(192, '2008-10-20', NULL, 1, 'A', 8, 'V.S. - EMBARAZO FORZADO');",
        false
    );
    hace_consulta(
        $db, $preins .
        "(193, '2008-10-20', NULL, 1, 'A', 8, 'V.S. - PROSTITUCIÓN FORZADA')",
        false
    );
    hace_consulta(
        $db, $preins .
        "(194, '2008-10-20', NULL, 1, 'A', 8, 'V.S. - ESTERILIZACIÓN FORZADA')",
        false
    );
    hace_consulta(
        $db, $preins .
        "(195, '2008-10-20', NULL, 1, 'A', 8, 'V.S. - ESCLAVITUD SEXUAL')",
        false
    );
    hace_consulta(
        $db, $preins .
        "(906, '2008-10-21', NULL, 1, 'D', NULL, 'CONFINAMIENTO')",
        false
    );
    hace_consulta(
        $db, $preins .
        "(104, '2008-10-17', NULL, 1, 'A', 25, 'COLECTIVO CONFINADO')",
        false
    );


    aplicaact($act, $idac, 'Consistencia demográficos, nuevas categorías');
}


$idact = '1.0f';
if (!aplicado($idact)) {

    hace_consulta(
        $db, "INSERT INTO prensa " .
        " VALUES (0,'SIN INFORMACIÓN','Indirecta');", false
    );

    aplicaact(
        $act, $idac, 'SIN INFORMACIÓN en Fuente Frecuente '
        . 'para permitir consulta externa'
    );
}

$idact = '1.0g';
if (!aplicado($idact)) {

    hace_consulta($db, "ALTER TABLE usuario ADD COLUMN npass VARCHAR(64);");
    hace_consulta($db, "UPDATE usuario SET npass=password;");
    hace_consulta($db, "ALTER TABLE usuario DROP COLUMN password;");
    hace_consulta($db, "ALTER TABLE usuario RENAME COLUMN npass TO password;");

    hace_consulta($db, "ALTER TABLE funcionario ADD UNIQUE(nombre);", false);

    aplicaact($act, $idac, 'Condensado de clave es sha1 en lugar de md5');
}


$idac = '1.1a1-sld';
if (!aplicado($idac)) {
    $r = hace_consulta(
        $db, "SELECT id_caso, id_departamento " .
        " FROM departamento_caso " .
        " WHERE id_caso NOT IN (SELECT id_caso FROM ubicacion_caso,ubicacion " .
        " WHERE ubicacion_caso.id_ubicacion=ubicacion.id " .
        " AND ubicacion.id_departamento=departamento_caso.id_departamento);",
        true
    );
    $ndep = 0;
    $row = array();
    $herr = false;
    $lc = $sep = '';
    while ($r->fetchInto($row)) {
        $nubi = (int)$db->getOne('SELECT nextval(\'ubicacion_seq\')');
        $ri = hace_consulta(
            $db, "INSERT INTO ubicacion (id, lugar, sitio, " .
            " id_clase, id_municipio, id_departamento) " .
            " VALUES ('$nubi', '', '', " .
            " NULL, NULL, ${row[1]})", false
        );
        $herr = $herr || PEAR::isError($ri);
        $ri = hace_consulta(
            $db, "INSERT INTO ubicacion_caso " .
            "(id_caso, id_ubicacion) " .
            " VALUES (${row[0]}, $nubi) ", false
        );
        $herr = $herr || PEAR::isError($ri);
        $ri = hace_consulta(
            $db, "DELETE FROM departamento_caso " .
            " WHERE id_caso = '${row[0]}' " .
            " AND id_departamento = '${row[1]}' ", false
        );
        $herr = $herr || PEAR::isError($ri);
        $lc .= $sep . $row[0];
        $sep = ",";
        $ndep++;
    }
    if (!$herr) {
        echo_esc("Como ubicaciones insertados $ndep deptos de casos $lc.");
        hace_consulta($db, "DROP VIEW cons2", false, false);
        hace_consulta($db, "DROP VIEW vestcomb", false, false);
        hace_consulta($db, "DROP VIEW comb", false, false);
        hace_consulta($db, "DROP TABLE departamento_caso");
        aplicaact($act, $idac, 'Sin departamento_caso');
    }
}

$idac = '1.1a1-slm';
if (!aplicado($idac)) {
    $r = hace_consulta(
        $db, "SELECT id_caso, id_departamento, id_municipio " .
        " FROM municipio_caso " .
        " WHERE id_caso NOT IN (SELECT id_caso FROM ubicacion_caso,ubicacion " .
        " WHERE ubicacion_caso.id_ubicacion=ubicacion.id " .
        " AND ubicacion.id_departamento=municipio_caso.id_departamento  " .
        " AND ubicacion.id_municipio=municipio_caso.id_municipio);",
        true
    );
    $nu = 0;
    $row = array();
    $herr = false;
    $lc = $sep = '';
    while ($r->fetchInto($row)) {
        $nubi = (int)$db->getOne('SELECT nextval(\'ubicacion_seq\')');
        $ri = hace_consulta(
            $db, "INSERT INTO ubicacion (id, lugar, sitio, " .
            " id_clase, id_municipio, id_departamento) " .
            " VALUES ('$nubi', '', '', NULL, ${row[2]}, ${row[1]})", false
        );
        $herr = $herr || PEAR::isError($ri);
        $ri = hace_consulta(
            $db, "INSERT INTO ubicacion_caso " .
            "(id_caso, id_ubicacion) " .
            " VALUES (${row[0]}, $nubi) ", false
        );
        $herr = $herr || PEAR::isError($ri);
        $ri = hace_consulta(
            $db, "DELETE FROM municipio_caso " .
            " WHERE id_caso = '${row[0]}' AND id_departamento = '${row[1]}' " .
            " AND id_municipio = '${row[2]}'", false
        );
        $herr = $herr || PEAR::isError($ri);
        $lc .= $sep . $row[0];
        $sep = ",";
        $nu++;
    }
    if (!$herr) {
        echo_esc(
            "Como ubicaciones insertados $nu municipios de casos $lc."
        );
        hace_consulta($db, "DROP VIEW cons2", false, false);
        hace_consulta($db, "DROP TABLE municipio_caso");
        aplicaact($act, $idac, 'Sin municipio_caso');
    }
}

$idac = '1.1a1-slc';
if (!aplicado($idac)) {
    $r = hace_consulta(
        $db, "SELECT id_caso, id_departamento, id_municipio, " .
        " id_clase FROM clase_caso " .
        " WHERE id_caso NOT IN (SELECT id_caso FROM ubicacion_caso,ubicacion " .
        " WHERE ubicacion_caso.id_ubicacion=ubicacion.id " .
        " AND ubicacion.id_departamento=clase_caso.id_departamento  " .
        " AND ubicacion.id_municipio=clase_caso.id_municipio " .
        " AND ubicacion.id_clase=clase_caso.id_clase);",
        true
    );
    $nu = 0;
    $row = array();
    $herr = false;
    $lc = $sep = '';
    while ($r->fetchInto($row)) {
        $nubi = (int)$db->getOne('SELECT nextval(\'ubicacion_seq\')');
        $ri = hace_consulta(
            $db, "INSERT INTO ubicacion (id, lugar, sitio, " .
            " id_clase, id_municipio, id_departamento) " .
            " VALUES ('$nubi', '', '', " .
            " ${row[3]}, ${row[2]}, ${row[1]})", false
        );
        $herr = $herr || PEAR::isError($ri);
        $ri = hace_consulta(
            $db, "INSERT INTO ubicacion_caso " .
            "(id_caso, id_ubicacion) " .
            " VALUES (${row[0]}, $nubi) ", false
        );
        $herr = $herr || PEAR::isError($ri);
        $lc .= $sep . $row[0];
        $sep = ",";
        $nu++;
    }
    if (!$herr) {
        echo_esc("Como ubicaciones insertados $nu centros de casos $lc.");
        hace_consulta($db, "DROP VIEW cons2", false, false);
        hace_consulta($db, "DROP TABLE clase_caso");
        aplicaact($act, $idac, 'Sin clase_caso');
    }
}


$idac = '1.1a1-ubi';
if (!aplicado($idac)) {

    hace_consulta($db, "CREATE SEQUENCE tipo_sitio_seq", false);
    hace_consulta(
        $db, "CREATE TABLE tipo_sitio (" .
        " id INTEGER PRIMARY KEY DEFAULT(nextval('tipo_sitio_seq')), " .
        " nombre VARCHAR(50) NOT NULL " .
        ")", false
    );
    hace_consulta(
        $db, "INSERT INTO tipo_sitio VALUES (1, 'SIN INFORMACIÓN')",
        false
    );
    hace_consulta($db, "INSERT INTO tipo_sitio VALUES (2, 'URBANO')", false);
    hace_consulta($db, "INSERT INTO tipo_sitio VALUES (3, 'RURAL')", false);
    hace_consulta(
        $db, "INSERT INTO tipo_sitio VALUES (4, 'URBANO Y RURAL')",
        false
    );

    hace_consulta(
        $db, "ALTER TABLE ubicacion ADD " .
        "COLUMN id_tipo_sitio INTEGER REFERENCES tipo_sitio NOT NULL DEFAULT 1",
        false
    );

    foreach (array('S' => 1, 'U' => 2, 'R' => 3, 'A' => 4) as $a => $n) {
        hace_consulta(
            $db, "UPDATE ubicacion SET id_tipo_sitio='$n' " .
            "FROM caso, ubicacion_caso WHERE " .
            "ubicacion.id=ubicacion_caso.id_ubicacion AND " .
            "ubicacion_caso.id_caso=caso.id AND " .
            "caso.tipo_ubicacion='$a';", false
        );
    }

    hace_consulta(
        $db, "ALTER TABLE caso DROP " .
        "COLUMN tipo_ubicacion", false
    );
    aplicaact($act, $idac, 'Tipo de ubicación en ubicación');
}



$idac = '1.1a1-uc';
if (!aplicado($idac) && aplicado('1.1a1-slc') && aplicado('1.1a1-slm')
    && aplicado('1.1a1-sld')
) {

    // Replica en ubicacion cuando hay una misma ubicacion en varios casos
    hace_consulta($db, "DROP TABLE ubicacion2", false, false);
    $q = "CREATE TABLE ubicacion2 (" .
        "id INTEGER PRIMARY KEY DEFAULT (nextval('ubicacion_seq')), " .
        "lugar VARCHAR(260), " .
        "sitio VARCHAR(260), " .
        "id_clase INTEGER, " .
        "id_municipio INTEGER, " .
        "id_departamento INTEGER REFERENCES departamento, " .
        "id_tipo_sitio INTEGER REFERENCES tipo_sitio NOT NULL, " .
        "id_caso INTEGER NOT NULL REFERENCES caso, " .
        "latitud FLOAT, " .
        "longitud FLOAT, " .
        "FOREIGN KEY (id_municipio, id_departamento) REFERENCES " .
        "        municipio (id, id_departamento), " .
        "FOREIGN KEY (id_clase, id_municipio, id_departamento) REFERENCES " .
        "        clase (id, id_municipio, id_departamento) " .
        ")";
    //echo $q;
    hace_consulta($db, $q);
    hace_consulta(
        $db, "INSERT INTO ubicacion2 (id_caso, lugar, sitio, " .
        "id_clase, id_municipio, id_departamento, id_tipo_sitio) " .
        "SELECT ubicacion_caso.id_caso, lugar, sitio, " .
        "id_clase, id_municipio, id_departamento, id_tipo_sitio  " .
        "FROM ubicacion, ubicacion_caso " .
        "WHERE ubicacion.id=ubicacion_caso.id_ubicacion "
    );
    $n = (int)$db->getOne(
        "SELECT COUNT(*) FROM ubicacion WHERE " .
        "id_caso IS NULL"
    );
    if ($n > 0) {
        echo_esc("  Eliminando $n ubicaciones no asociadas a caso alguno");
        echo "<br>";
    }
    hace_consulta($db, "DROP TABLE ubicacion_caso CASCADE");
    hace_consulta($db, "DROP TABLE ubicacion CASCADE");
    hace_consulta($db, "ALTER TABLE ubicacion2 RENAME TO ubicacion");

    aplicaact($act, $idac, 'Sin ubicacion_caso y con latitud, longitud');
}

$idac = '1.1a1-per';
if (!aplicado($idac)) {
    $r = hace_consulta($db, "DROP VIEW cons;", false, false);
    $r = hace_consulta($db, "DROP VIEW t1;", false, false);
    $r = hace_consulta($db, "DROP VIEW t2;", false, false);
    $r = hace_consulta($db, "CREATE SEQUENCE persona_seq;", false);
    $r = hace_consulta(
        $db, "CREATE TABLE persona (
        id INTEGER PRIMARY KEY DEFAULT(nextval('persona_seq')),
        nombres VARCHAR(100) NOT NULL,
        apellidos VARCHAR(100) NOT NULL,
        anionac         INTEGER,
        mesnac          INTEGER CHECK (mesnac IS NULL OR
            (mesnac>='1' AND mesnac <='12')),
        dianac          INTEGER CHECK (dianac IS NULL
            OR (dianac>='1' AND (((mesnac='1' OR mesnac='3' OR mesnac='5'
            OR mesnac = '7' OR mesnac='8' OR mesnac='10' OR mesnac='12')
            AND dianac<='31')) OR ((mesnac='4' OR mesnac='6' OR mesnac='9'
            OR mesnac = '11') AND dianac<='30')
            OR (mesnac = '2' AND dianac<='29'))),
        sexo CHAR(1) NOT NULL CHECK (sexo = 'S' OR sexo='F' OR sexo='M'),
        id_departamento INTEGER REFERENCES departamento ON DELETE CASCADE,
        id_municipio    INTEGER,
        id_clase        INTEGER,
        tipo_documento VARCHAR(2),
        numero_documento VARCHAR(50)
        );", false
    );
    $r = hace_consulta(
        $db, "CREATE TABLE tipo_relacion (
            id      CHAR(2) PRIMARY KEY,
            nombre VARCHAR(50) NOT NULL,
            dirigido BOOLEAN NOT NULL,
            observaciones VARCHAR(200)
        );", false
    );
    $r = hace_consulta(
        $db, "CREATE TABLE relacion_personas (
        id_persona1 INTEGER NOT NULL REFERENCES persona,
        id_persona2 INTEGER NOT NULL REFERENCES persona,
        id_tipo CHAR(2) NOT NULL REFERENCES tipo_relacion,
        observaciones VARCHAR(200),
        PRIMARY KEY(id_persona1, id_persona2, id_tipo)
        );", false
    );

    $r = hace_consulta(
        $db, "INSERT INTO persona "
        . " (id, nombres, apellidos, anionac, sexo) "
        . " (SELECT victima.id, nombre, '', "
        . " extract(year from caso.fecha) - edad, sexo "
        . " FROM caso, victima WHERE caso.id=victima.id_caso);", false
    );

    $r = hace_consulta(
        $db, "ALTER TABLE victima ADD COLUMN id_persona INTEGER "
        . " REFERENCES persona", false
    );
    $r = hace_consulta($db, "UPDATE victima SET id_persona=id", false);
    foreach (array(
        'categoria_personal'  => 'id_tipo_violencia, id_supracategoria, '
            . 'id_categoria, id_persona, id_caso',
        'p_responsable_agrede_persona' => 'id_p_responsable, id_persona, '
            . 'id_caso',
        'antecedente_victima' => 'id_antecedente, id_persona, id_caso'
    ) as
        $tabla => $llave
    ) {
        $r = hace_consulta(
            $db, "ALTER TABLE $tabla ADD COLUMN id_persona "
            . " INTEGER REFERENCES persona", false
        );
        $r = hace_consulta(
            $db, "ALTER TABLE $tabla ADD COLUMN id_caso "
            . " INTEGER REFERENCES caso", false
        );
        $r = hace_consulta(
            $db, "UPDATE $tabla SET id_persona=victima.id, "
            . "id_caso=victima.id_caso "
            . " FROM victima WHERE id_victima=victima.id", false
        );
        $r = hace_consulta(
            $db, "ALTER TABLE $tabla DROP COLUMN id_victima",
            false
        );
        $r = hace_consulta(
            $db, "ALTER TABLE $tabla ADD PRIMARY KEY ($llave)",
            false
        );
        $r = hace_consulta(
            $db, "ALTER TABLE $tabla ALTER COLUMN id_persona "
            . " SET NOT NULL", false
        );
        $r = hace_consulta(
            $db, "ALTER TABLE $tabla ALTER COLUMN id_caso SET NOT NULL", false
        );
    }
    $r = hace_consulta($db, "ALTER TABLE victima DROP COLUMN id", false);
    $r = hace_consulta(
        $db, "ALTER TABLE victima ADD "
        . " PRIMARY KEY (id_persona, id_caso)", false
    );
    $r = hace_consulta($db, "ALTER TABLE victima DROP COLUMN nombre", false);
    $r = hace_consulta($db, "ALTER TABLE victima DROP COLUMN sexo", false);
    $r = hace_consulta($db, "ALTER TABLE victima DROP COLUMN edad", false);
    foreach (array('categoria_personal', 'p_responsable_agrede_persona',
        'antecedente_victima') as $tabla
    ) {
        $r = hace_consulta(
            $db, "ALTER TABLE $tabla ADD "
            . " FOREIGN KEY (id_persona, id_caso) "
            . " REFERENCES victima(id_persona, id_caso)", false
        );
    }
    $r = hace_consulta($db, "DROP SEQUENCE victima_seq", false);
    aplicaact($act, $idac, 'Persona');
}

$idac = '1.1a1-col';
if (!aplicado($idac)) {
    $r = hace_consulta($db, "DROP VIEW cons;", false, false);
    $grave = false;
    $np = $db->getOne(
        "SELECT COUNT(*) FROM victima_colectiva "
        . " WHERE id NOT IN "
        . " (SELECT id_v_colectiva FROM victima_colectiva_caso)"
    );
    if (PEAR::isError($np)) {
        die($np->getMessage() . " - " . $np->getUserInfo());
    }
    if ((int)$np > 0) {
        echo_esc(
            "Hay $np registros en victima_colectiva que no están en "
            . " victima_colectiva_caso. Resolver primero"
        );
        $grave = true;
    }
    foreach (array('categoria_comunidad', 'p_responsable_agrede_comunidad',
    ) as $tabla
    ) {
        $np = $db->getOne(
            "SELECT COUNT(*) FROM $tabla "
            . " WHERE  (id_v_colectiva, id_caso) NOT IN "
            . " (SELECT id_v_colectiva, id_caso FROM victima_colectiva_caso)"
        );
        if (PEAR::isError($np)) {
            die($np->getMessage() . " - " . $np->getUserInfo());
        }
        if ((int)$np > 0) {
            echo_esc(
                "Hay $np registros en $tabla que no están en "
                . " victima_colectiva_caso"
            );
            $grave = true;
        }
    }
    if ($grave) {
        die("Solucionar primero");
    }

    $r = hace_consulta($db, "CREATE SEQUENCE grupoper_seq;", false);
    $r = hace_consulta(
        $db, "CREATE TABLE grupoper (
        id INTEGER PRIMARY KEY DEFAULT(nextval('grupoper_seq')),
        nombre VARCHAR(150) NOT NULL,
        anotaciones VARCHAR(1000)
    );", false
    );
    $r = hace_consulta(
        $db, "INSERT INTO grupoper (id, nombre, anotaciones)
        (SELECT victima_colectiva.id, nombre, anotacion
        FROM victima_colectiva);"
    );

    $r = hace_consulta(
        $db, "ALTER TABLE victima_colectiva
        ADD COLUMN id_grupoper INTEGER REFERENCES grupoper"
    );
    $r = hace_consulta(
        $db, "ALTER TABLE victima_colectiva
        ADD COLUMN id_caso INTEGER REFERENCES caso"
    );
    $r = hace_consulta(
        $db, "UPDATE victima_colectiva
        SET id_grupoper = victima_colectiva_caso.id_v_colectiva,
        id_caso = victima_colectiva_caso.id_caso
        FROM victima_colectiva_caso
        WHERE victima_colectiva.id = victima_colectiva_caso.id_v_colectiva"
    );
    $r = hace_consulta($db, "UPDATE victima_colectiva SET id_grupoper=id");

    foreach (array('p_responsable_agrede_comunidad' => 'id_p_responsable, '
        . 'id_grupoper, id_caso',
        'categoria_comunidad'  => 'id_tipo_violencia, id_supracategoria, '
        . 'id_categoria, id_grupoper, id_caso',) as $tabla => $llave
    ) {
            $r = hace_consulta(
                $db, "ALTER TABLE $tabla
                ADD COLUMN id_grupoper INTEGER REFERENCES grupoper"
            );
            $r = hace_consulta(
                $db, "UPDATE $tabla
                SET id_grupoper = id_v_colectiva"
            );
            $r = hace_consulta(
                $db, "ALTER TABLE $tabla
                DROP COLUMN id_v_colectiva"
            );
            $r = hace_consulta(
                $db, "ALTER TABLE $tabla
                ADD PRIMARY KEY ($llave)"
            );
    }
    foreach (array(
        'vinculo_estado_comunidad'  => 'id_vinculo_estado, id_grupoper, id_caso',
        'profesion_comunidad'  => 'id_profesion, id_grupoper, id_caso',
        'antecedente_comunidad'  => 'id_antecedente, id_grupoper, id_caso',
        'filiacion_comunidad'  => 'id_filiacion, id_grupoper, id_caso',
        'organizacion_comunidad'  => 'id_organizacion, id_grupoper, id_caso',
        'rango_edad_comunidad'  => 'id_rango, id_grupoper, id_caso',
        'sector_social_comunidad'  => 'id_sector, id_grupoper, id_caso',
        ) as $tabla => $llave
    ) {
            $r = hace_consulta(
                $db, "ALTER TABLE $tabla " .
                " ADD COLUMN id_grupoper INTEGER REFERENCES grupoper"
            );
            $r = hace_consulta(
                $db, "ALTER TABLE $tabla " .
                " ADD COLUMN id_caso INTEGER REFERENCES caso"
            );
            $r = hace_consulta(
                $db, "UPDATE $tabla " .
                " SET id_grupoper=victima_colectiva_caso.id_v_colectiva, " .
                " id_caso=victima_colectiva_caso.id_caso " .
                " FROM victima_colectiva_caso " .
                " WHERE $tabla.id_v_colectiva=" .
                " victima_colectiva_caso.id_v_colectiva"
            );
            $r = hace_consulta(
                $db, "ALTER TABLE $tabla " .
                " DROP COLUMN id_v_colectiva"
            );
            $r = hace_consulta(
                $db, "DELETE FROM $tabla " .
                " WHERE id_caso IS NULL"
            );
            $r = hace_consulta(
                $db, "ALTER TABLE $tabla " .
                " ADD PRIMARY KEY ($llave)"
            );
    }
    $r = hace_consulta($db, "DROP TABLE victima_colectiva_caso");
    $r = hace_consulta(
        $db, "DELETE FROM victima_colectiva " .
        " WHERE id_caso IS NULL"
    );
    $r = hace_consulta(
        $db, "DELETE FROM victima_colectiva " .
        " WHERE id_grupoper IS NULL"
    );
    $r = hace_consulta($db, "ALTER TABLE victima_colectiva DROP COLUMN id");
    $r = hace_consulta(
        $db, "ALTER TABLE victima_colectiva " .
        " ADD PRIMARY KEY (id_grupoper, id_caso)"
    );
    $r = hace_consulta(
        $db, "ALTER TABLE victima_colectiva " .
        " DROP COLUMN nombre"
    );
    $r = hace_consulta(
        $db, "ALTER TABLE victima_colectiva " .
        " DROP COLUMN anotacion"
    );
    foreach (array('categoria_comunidad', 'p_responsable_agrede_comunidad',
        'antecedente_comunidad', 'vinculo_estado_comunidad',
        'profesion_comunidad', 'antecedente_comunidad', 'filiacion_comunidad',
        'organizacion_comunidad', 'rango_edad_comunidad',
        'sector_social_comunidad',
    ) as $tabla
    ) {
        $q = "ALTER TABLE $tabla ADD FOREIGN KEY (id_grupoper, id_caso) " .
            " REFERENCES victima_colectiva(id_grupoper, id_caso)";
        $r = hace_consulta($db, $q);
    }
    aplicaact($act, $idac, 'Víctimas colectivas');
}

$idac = '1.1a1-act';
if (!aplicado($idac)) {
    $r = hace_consulta(
        $db, "CREATE TABLE acto (
            id_p_responsable INTEGER REFERENCES presuntos_responsables,
            id_categoria INTEGER REFERENCES categoria,
            id_persona INTEGER REFERENCES persona,
            id_caso INTEGER REFERENCES caso,
            FOREIGN KEY (id_persona, id_caso) REFERENCES victima,
            PRIMARY KEY(id_p_responsable, id_categoria, id_persona, id_caso)
        );", false
    );
    $r = hace_consulta(
        $db, "CREATE TABLE actocolectivo (
        id_p_responsable INTEGER REFERENCES presuntos_responsables,
        id_categoria INTEGER REFERENCES categoria,
        id_grupoper INTEGER REFERENCES grupoper,
        id_caso INTEGER REFERENCES caso,
        FOREIGN KEY (id_grupoper, id_caso)
        REFERENCES victima_colectiva(id_grupoper, id_caso),
            PRIMARY KEY(id_p_responsable, id_categoria, id_grupoper, id_caso)
        );", false
    );


    $r = hace_consulta(
        $db, "INSERT INTO acto
        (id_p_responsable, id_categoria, id_persona, id_caso)
        (SELECT DISTINCT id_p_responsable, id_categoria,
        categoria_personal.id_persona, categoria_personal.id_caso
        FROM categoria_personal, p_responsable_agrede_persona
        WHERE p_responsable_agrede_persona.id_caso =
        categoria_personal.id_caso
        AND p_responsable_agrede_persona.id_persona =
        categoria_personal.id_persona)"
    );

    $r = hace_consulta(
        $db, "INSERT INTO actocolectivo
        (id_p_responsable, id_categoria, id_grupoper, id_caso)
        (SELECT DISTINCT id_p_responsable, id_categoria,
        categoria_comunidad.id_grupoper,
        categoria_comunidad.id_caso
        FROM categoria_comunidad, p_responsable_agrede_comunidad
        WHERE categoria_comunidad.id_caso =
        p_responsable_agrede_comunidad.id_caso
        AND categoria_comunidad.id_grupoper =
        p_responsable_agrede_comunidad.id_grupoper)"
    );

    $r = hace_consulta($db, "DROP VIEW cons2", false, false);
    $r = hace_consulta($db, "DROP VIEW cons", false, false);
    $r = hace_consulta($db, "DROP TABLE categoria_personal");
    $r = hace_consulta($db, "DROP TABLE categoria_comunidad");
    $r = hace_consulta($db, "DROP TABLE p_responsable_agrede_persona");
    $r = hace_consulta($db, "DROP TABLE p_responsable_agrede_comunidad");

    aplicaact($act, $idac, 'Actos');
}

$idac = '1.1a1-com';
if (!aplicado($idac)) {

    $r = hace_consulta(
        $db, "DELETE FROM opcion " .
        " WHERE descripcion = 'Conteos V. Combatientes'"
    );
    aplicaact($act, $idac, 'Bélicas es módulo');
}

$idac = '1.1a1-jp';
if (!aplicado($idac)) {

    $r = hace_consulta(
        $db, "ALTER TABLE presuntos_responsables " .
        " DROP COLUMN polo", false
    );
    $r = hace_consulta(
        $db, "ALTER TABLE presuntos_responsables " .
        " ADD COLUMN id_papa INTEGER REFERENCES presuntos_responsables", false
    );
    $r = hace_consulta(
        $db, "UPDATE presuntos_responsables " .
        " SET id_papa='1' WHERE id in ('2', '12', '14', '38', '39')"
    );
    $r = hace_consulta(
        $db, "UPDATE presuntos_responsables " .
        " SET id_papa='2' WHERE id in ('3', '4', '5', '6', '7', '11')"
    );
    $r = hace_consulta(
        $db, "UPDATE presuntos_responsables " .
        " SET id_papa='3' WHERE id in ('8')"
    );
    $r = hace_consulta(
        $db, "UPDATE presuntos_responsables " .
        " SET id_papa='7' WHERE id in ('9', '10')"
    );
    $r = hace_consulta(
        $db, "UPDATE presuntos_responsables " .
        " SET id_papa='14' WHERE id in ('15', '16', '17', '18', '19', " .
        " '20', '21', '22', '23')"
    );
    $r = hace_consulta(
        $db, "UPDATE presuntos_responsables " .
        " SET id_papa='25' WHERE id in ('26', '27', '28', '29', " .
        " '30', '31', '32')"
    );
    $r = hace_consulta(
        $db, "UPDATE presuntos_responsables " .
        " SET id_papa='38' WHERE id in ('13')"
    );

    aplicaact($act, $idac, 'Jerarquía presuntos responsables');
}

$idac = '1.1a1-tc';
if (!aplicado($idac)) {

    hace_consulta(
        $db, "ALTER TABLE categoria " .
        " ADD COLUMN tipocat CHAR DEFAULT 'I' " .
        " CHECK (tipocat='I' OR tipocat='C' OR tipocat='O')",
        false
    );
    hace_consulta(
        $db, "UPDATE categoria SET tipocat='C' " .
        " WHERE id IN ('501', '902', '102', '18', '28', '38', " .
        " '49', '59', '706', '401', '903', '104')"
    );
    hace_consulta(
        $db, "UPDATE categoria SET tipocat='O' " .
        " WHERE id IN ('61', '66', '67', '69', '80', '81', " .
        " '82', '83', '84', '85', '86', '89', '94', '95', '707', " .
        " '708', '709', '801' )"
    );

    hace_consulta(
        $db, "ALTER TABLE categoria_p_responsable_caso " .
        " DROP CONSTRAINT categoria_p_responsable_caso_id_caso_fkey1", false
    );
    hace_consulta($db, "DROP TABLE categoria_caso CASCADE");

    aplicaact($act, $idac, 'Tipo de categoria (Individual, Colectiva u Otra)');
}



$idac = '1.1a1-org';
if (!aplicado($idac)) {
    foreach (array('organizacion', 'filiacion', 'profesion',
        'presuntos_responsables', 'antecedente', 'categoria', 'departamento',
        'municipio', 'clase', 'tipo_clase', 'frontera', 'fuente_directa',
        'prensa', 'region', 'tipo_sitio', 'sector_social',
        'vinculo_estado'
    ) as $t
    ) {
        cambia_tipocol($db, "$t", 'nombre', 'VARCHAR(500)');
    }
    foreach (array('vinculo_estado', 'sector_social',
        'organizacion', 'filiacion', 'profesion'
    ) as $t
    ) {
        hace_consulta(
            $db, "ALTER TABLE $t " .
            "ADD COLUMN fechacreacion DATE NOT NULL DEFAULT '2001-01-01'",
            false
        );
        hace_consulta(
            $db, "ALTER TABLE $t " .
            "ADD COLUMN fechadeshabilitacion DATE " .
            "CHECK (fechadeshabilitacion IS NULL OR " .
            "fechadeshabilitacion >= fechacreacion)", false
        );
    }

    aplicaact($act, $idac, 'Personalizabilidad de más básicas');
}




$idac = '1.1a2-ccg';  // Gracias Carlos Garavis
if (!aplicado($idac)) {
    $m = $db->getOne("SELECT max(id) FROM presuntos_responsables");
    if ($m > 100) {
        echo "No aplica porque presuntos_responsables tiene codigos "
            . " mayores que 100 <br>";
    } else {
        hace_consulta(
            $db, "INSERT INTO presuntos_responsables
            (id, nombre, fecha_creacion, fecha_deshabilitacion, id_papa)
            (SELECT id+100, nombre, fecha_creacion, fecha_deshabilitacion,
             NULL FROM presuntos_responsables
             WHERE id>='39' AND id<'100')", false
        );
        hace_consulta(
            $db, "INSERT INTO presuntos_responsables_caso " .
            " (id_caso, id_p_responsable, tipo, bloque, frente, brigada, " .
            " batallon, division, otro, id) " .
            " (SELECT id_caso, id_p_responsable+100, tipo, bloque, frente, " .
            " brigada, batallon, division, otro, id " .
            " FROM presuntos_responsables_caso " .
            " WHERE id_p_responsable>='39' " .
            " AND id_p_responsable<'100')", false
        );
        foreach (array('categoria_p_responsable_caso', 'acto',
            'actocolectivo'
        ) as $t
        ) {
            hace_consulta(
                $db, "UPDATE $t " .
                "SET id_p_responsable=id_p_responsable+100 " .
                " WHERE id_p_responsable>='39' " .
                " AND id_p_responsable<'100'", false
            );
        }
        foreach (array('victima',  'victima_colectiva', 'combatiente') as $t) {
            hace_consulta(
                $db, "UPDATE $t SET " .
                "id_organizacion_armada=id_organizacion_armada+100 " .
                " WHERE id_organizacion_armada>='39' " .
                " AND id_organizacion_armada<'100'", false
            );
        }
        hace_consulta(
            $db, "DELETE FROM presuntos_responsables_caso " .
            " WHERE id_p_responsable>='39' AND id_p_responsable<'100'", false
        );
        hace_consulta(
            $db, "DELETE FROM presuntos_responsables " .
            " WHERE id>='39' AND id<'100'", false
        );
    }

    hace_consulta(
        $db, "UPDATE presuntos_responsables SET id_papa='36' " .
        " WHERE id in ('24', '37', '33')", false
    );
    hace_consulta(
        $db, "INSERT INTO presuntos_responsables " .
        " (id, nombre, fecha_creacion, fecha_deshabilitacion, id_papa) " .
        " VALUES (39, 'POLO ESTATAL', '2001-01-30', NULL, NULL)", false
    );
    hace_consulta(
        $db, "UPDATE presuntos_responsables SET id_papa='39' " .
        " WHERE id in ('12', '1', '38', '2', '14')", false
    );
    hace_consulta(
        $db, "INSERT INTO presuntos_responsables " .
        " (id, nombre, fecha_creacion, fecha_deshabilitacion, id_papa) " .
        " VALUES (40, 'POLO INSURGENTE', '2001-01-30', NULL, NULL)", false
    );
    hace_consulta(
        $db, "UPDATE presuntos_responsables SET id_papa='40' " .
        " WHERE id in ('25')", false
    );

    hace_consulta(
        $db, "UPDATE categoria SET tipocat='I' " .
        " WHERE id IN ('10', '11', '12', '13', '14', '15', '16', '19',
        '20', '21', '22', '23', '24', '25', '26', '29', '30', '33',
        '35', '36', '37', '39', '101', '191', '192', '193', '194', '195',
        '196', '291' ,'292', '293', '294', '295', '296', '301', '302', '391',
        '392', '393', '394', '395', '40', '41', '43', '45', '46', '47',
        '48', '50', '53', '55', '56', '57', '58', '72', '73', '74',
        '75', '77', '78', '79', '87', '88', '97', '98', '701', '702',
        '703', '704', '771', '772', '773', '774', '775', '776')"
    );
    hace_consulta(
        $db, "UPDATE categoria SET tipocat='C' " .
        " WHERE id IN ('18', '28', '38', '102', '104', '49', '59', " .
        " '401', '501', '706', '902', '903', '904', '906')"
    );
    hace_consulta(
        $db, "UPDATE categoria SET tipocat='O' " .
        " WHERE id IN ('62', '63', '64', '65', '66', '67', '68', '69', " .
        " '910', '80', '84', '85', '86', '89', '90', '91', '92', '93', " .
        " '95', '707', '708', '709', '801')"
    );
    hace_consulta(
        $db, "DELETE FROM categoria_p_responsable_caso " .
        " WHERE id_categoria IN (SELECT id FROM categoria  " .
        " WHERE tipocat<>'O');"
    );


    aplicaact($act, $idac, 'Jerarquía refinada');
}

$idac = '1.1a2-imp';
if (!aplicado($idac)) {

    hace_consulta(
        $db, "UPDATE opcion SET id_opcion='69' " .
        " WHERE descripcion='Salir';", false
    );
    hace_consulta(
        $db, "INSERT INTO opcion " .
        " (id_opcion, descripcion, id_mama, nomid) " .
        " VALUES ('61', 'Importar Relatos', '60', 'importaRelato')", false
    );

    aplicaact($act, $idac, 'Importa relatos');
}


$idac = '1.1b1-vs';
if (!aplicado($idac)) {

    hace_consulta(
        $db, "UPDATE categoria SET contada_en='771' " .
        " WHERE id='191';", false
    );
    hace_consulta(
        $db, "UPDATE categoria SET contada_en='772' " .
        " WHERE id='192';", false
    );
    hace_consulta(
        $db, "UPDATE categoria SET contada_en='773' " .
        " WHERE id='193';", false
    );
    hace_consulta(
        $db, "UPDATE categoria SET contada_en='774' " .
        " WHERE id='194';", false
    );
    hace_consulta(
        $db, "UPDATE categoria SET contada_en='775' " .
        " WHERE id='195';", false
    );
    hace_consulta(
        $db, "UPDATE categoria SET contada_en='776' " .
        " WHERE id='196';", false
    );
    hace_consulta(
        $db, "UPDATE categoria SET contada_en='906' " .
        " WHERE id='104';", false
    );

    aplicaact($act, $idac, 'Categorias recientes replicadas');
}

$idac = '1.1b1-cr';
if (!aplicado($idac)) {

    hace_consulta(
        $db, "UPDATE opcion set descripcion='Completar actos', " .
        " id_mama='60', nomid='completaActos' WHERE id_opcion='62'", false
    );
    hace_consulta(
        $db, "INSERT INTO opcion (id_opcion, descripcion, " .
        " id_mama, nomid) " .
        " VALUES ('62', 'Completar actos', '60', 'completaActos')", false
    );

    aplicaact($act, $idac, 'Completar actos');
}

$idac = '1.1b1-ref';
if (!aplicado($idac)) {

    hace_consulta(
        $db, "UPDATE categoria SET nombre='RAPTO' " .
        " WHERE id='48' AND nombre='DESAPARCIÓN';", false
    );
    hace_consulta(
        $db, "UPDATE categoria SET nombre='ATENTADO' " .
        " WHERE id='16';", false
    );

    aplicaact($act, $idac, 'Categorias refinadas');
}

$idac = '1.1b1-ren';
if (!aplicado($idac)) {
    hace_consulta(
        $db, "ALTER TABLE contexto " .
        " RENAME COLUMN fecha_creacion TO fechacreacion", false
    );
    hace_consulta(
        $db, "ALTER TABLE contexto " .
        " RENAME COLUMN fecha_deshabilitacion TO fechadeshabilitacion", false
    );

    aplicaact($act, $idac, 'Renombra');
}



$idac = '1.1b1-pes';
if (!aplicado($idac)) {
    hace_consulta(
        $db, "ALTER TABLE parametros_reporte_consolidado " .
        "ADD COLUMN peso INTEGER DEFAULT '0'", false
    );

    aplicaact($act, $idac, 'Pesos en rótulos de reporte consolidado');
}

$idac = '1.1b1-ctx';
if (!aplicado($idac)) {

    $m = $db->getOne("SELECT nombre FROM contexto WHERE id='29'");
    if ($m != '' && trim(a_mayusculas($m)) != 'FALSO POSITIVO') {
        $mi = $db->getOne("SELECT max(id) FROM contexto");
        if ($mi > 100) {
            echo "No aplica porque contexto tiene codigos mayores que 100 <br>";
        } else {
            hace_consulta(
                $db, "INSERT INTO contexto " .
                " (id, nombre, fechacreacion, fechadeshabilitacion) " .
                " (SELECT id+71, nombre, fechacreacion, fechadeshabilitacion " .
                " FROM contexto WHERE id>='29')", false
            );
            hace_consulta(
                $db, "INSERT INTO caso_contexto " .
                " (id_caso, id_contexto) (SELECT id_caso, id_contexto+71 " .
                " FROM caso_contexto WHERE id_contexto>='29')", false
            );
            hace_consulta(
                $db, "DELETE FROM caso_contexto " .
                " WHERE id_contexto>='29' AND id_contexto<'100'", false
            );
            hace_consulta(
                $db, "DELETE FROM contexto " .
                " WHERE id>='29' AND id<'100'", false
            );
        }
    }
    if (trim(a_mayusculas($m)) != 'FALSO POSITIVO') {
        hace_consulta(
            $db, "INSERT INTO contexto " .
            " (id, nombre, fechacreacion, fechadeshabilitacion) " .
            " VALUES ('29', 'FALSO POSITIVO', '2007-07-06', NULL)", false
        );
    }

    hace_consulta(
        $db, "UPDATE opcion SET descripcion='Consulta Detallada' " .
        " WHERE descripcion='Consulta Externa'", false
    );

    cambia_tipocol(
        $db, 'presuntos_responsables_caso', 'otro',
        'VARCHAR(500)'
    );
    hace_consulta(
        $db, "INSERT INTO opcion " .
        " VALUES ('63', 'Actualizar', '60', 'actualiza')", false
    );

    aplicaact($act, $idac, 'Contexto y detalles pequeños requeridos por RB');
}

$idac = '1.1b1-tr';
if (!aplicado($idac)) {
    $r = hace_consulta(
        $db, "INSERT INTO tipo_relacion " .
        " (id, nombre, dirigido, observaciones) " .
        " VALUES ('AB', 'Abuela', true, NULL)", false
    );
    $r = hace_consulta(
        $db, "INSERT INTO tipo_relacion " .
        " (id, nombre, dirigido, observaciones) " .
        " VALUES ('AO','Abuelo', true, NULL)", false
    );
    $r = hace_consulta(
        $db, "INSERT INTO tipo_relacion " .
        " (id, nombre, dirigido, observaciones) " .
        " VALUES ('CO','Conyuge y/o Companero Permanente', false, NULL)",
        false
    );
    $r = hace_consulta(
        $db, "INSERT INTO tipo_relacion " .
        " (id, nombre, dirigido, observaciones) " .
        " VALUES ('HA','Hija', true, NULL)", false
    );
    $r = hace_consulta(
        $db, "INSERT INTO tipo_relacion " .
        " (id, nombre, dirigido, observaciones) " .
        " VALUES ('HE','Hermano', false, NULL)", false
    );
    $r = hace_consulta(
        $db, "INSERT INTO tipo_relacion " .
        " (id, nombre, dirigido, observaciones) " .
        " VALUES ('HI','Hijo', true, NULL)", false
    );
    $r = hace_consulta(
        $db, "INSERT INTO tipo_relacion " .
        " (id, nombre, dirigido, observaciones) " .
        " VALUES ('HR','Hermana', false, NULL)", false
    );
    $r = hace_consulta(
        $db, "INSERT INTO tipo_relacion " .
        " (id, nombre, dirigido, observaciones) " .
        " VALUES ('MA','Madrina', true, NULL)", false
    );
    $r = hace_consulta(
        $db, "INSERT INTO tipo_relacion
        (id, nombre, dirigido, observaciones)
        VALUES ('ME','Madre', true, NULL)", false
    );
    $r = hace_consulta(
        $db, "INSERT INTO tipo_relacion
        (id, nombre, dirigido, observaciones)
        VALUES ('PA','Padre', true, NULL)", false
    );
    $r = hace_consulta(
        $db, "INSERT INTO tipo_relacion (id, nombre, dirigido, observaciones)
        VALUES ('PO','Padrino', true, NULL)", false
    );
    $r = hace_consulta(
        $db, "INSERT INTO tipo_relacion (id, nombre, dirigido, observaciones)
        VALUES ('TA','Tia', true, NULL)", false
    );
    $r = hace_consulta(
        $db, "INSERT INTO tipo_relacion (id, nombre, dirigido, observaciones)
        VALUES ('TO','Tio', true, NULL)", false
    );
    $r = hace_consulta(
        $db, "INSERT INTO tipo_relacion (id, nombre, dirigido, observaciones)
        VALUES ('SI','SIN INFORMACIÓN', true, NULL)", false
    );

    aplicaact($act, $idac, 'Tipos de relaciones familiares');
}


$idac = '1.1b1-esp';
if (!aplicado($idac)) {
    $r = hace_consulta(
        $db, "UPDATE  tipo_violencia SET nombre='VIOLENCIA POLÍTICO SOCIAL'
        WHERE id = 'B'", false
    );
    $r = hace_consulta(
        $db, "UPDATE  categoria SET
        nombre = 'CONFINAMIENTO COMO REPRESALIA O CASTIGO COLECTIVO'
        WHERE id = '104'", false
    );
    $r = hace_consulta(
        $db, "UPDATE  categoria SET
        nombre = 'CONFINAMIENTO COMO REPRESALIA O CASTIGO COLECTIVO'
        WHERE id = '906'", false
    );
    $r = hace_consulta(
        $db, "UPDATE  presuntos_responsables SET
        nombre = 'GRUPOS DE INTOLERANCIA' WHERE id='33'", false
    );

    $r = hace_consulta(
        $db, "INSERT INTO categoria
        (id, fecha_creacion, fecha_deshabilitacion, id_supracategoria,
        id_tipo_violencia, col_rep_consolidado, nombre, tipocat)
        VALUES (420, '2010-04-17', NULL, 1,
        'B', NULL, 'VIOLENCIA SEXUAL', 'I')", false
    );
    $r = hace_consulta(
        $db, "INSERT INTO categoria (id, fecha_creacion, fecha_deshabilitacion,
        id_supracategoria, id_tipo_violencia, col_rep_consolidado, nombre,
        tipocat) VALUES (421, '2010-04-17', NULL, 1, 'B', NULL, 'VIOLACIÓN',
        'I')", false
    );
    $r = hace_consulta(
        $db, "INSERT INTO categoria (id, fecha_creacion, fecha_deshabilitacion,
        id_supracategoria, id_tipo_violencia, col_rep_consolidado, nombre,
        tipocat) VALUES (422, '2010-04-17', NULL, 1, 'B', NULL,
        'EMBARAZO FORZADO', 'I')", false
    );
    $r = hace_consulta(
        $db, "INSERT INTO categoria (id, fecha_creacion, fecha_deshabilitacion,
        id_supracategoria, id_tipo_violencia, col_rep_consolidado, nombre,
        tipocat) VALUES (423, '2010-04-17', NULL, 1, 'B', NULL,
        'PROSTITUCIÓN FORZADA', 'I')", false
    );
    $r = hace_consulta(
        $db, "INSERT INTO categoria (id, fecha_creacion, fecha_deshabilitacion,
        id_supracategoria, id_tipo_violencia, col_rep_consolidado, nombre,
        tipocat) VALUES (424, '2010-04-17', NULL, 1, 'B', NULL,
        'ESTERILIZACIÓN FORZADA', 'I')", false
    );
    $r = hace_consulta(
        $db, "INSERT INTO categoria (id, fecha_creacion, fecha_deshabilitacion,
        id_supracategoria, id_tipo_violencia, col_rep_consolidado, nombre,
        tipocat) VALUES (425, '2010-04-17', NULL, 1, 'B', NULL,
        'ESCLAVITUD SEXUAL', 'I')", false
    );
    $r = hace_consulta(
        $db, "INSERT INTO categoria (id, fecha_creacion, fecha_deshabilitacion,
        id_supracategoria, id_tipo_violencia, col_rep_consolidado, nombre,
        tipocat) VALUES (426, '2010-04-17', NULL, 1, 'B', NULL, 'ABUSO SEXUAL',
        'I')", false
    );
    $r = hace_consulta(
        $db, "INSERT INTO categoria (id, fecha_creacion, fecha_deshabilitacion,
        id_supracategoria, id_tipo_violencia, col_rep_consolidado, nombre,
        tipocat) VALUES (427, '2010-04-17', NULL, 1, 'B', NULL,
        'ABORTO FORZADO', 'I');", false
    );
    $r = hace_consulta(
        $db, "INSERT INTO categoria (id, fecha_creacion, fecha_deshabilitacion,
        id_supracategoria, id_tipo_violencia, col_rep_consolidado, nombre,
        tipocat) VALUES (297, '2010-04-17', NULL, 2, 'A', NULL,
        'V.S. - ABORTO FORZADO', 'I')", false
    );
    $r = hace_consulta(
        $db, "INSERT INTO categoria (id, fecha_creacion, fecha_deshabilitacion,
        id_supracategoria, id_tipo_violencia, col_rep_consolidado, nombre,
        tipocat)
        VALUES (396, '2008-10-20', NULL, 3, 'A', 8, 'V.S. - ABUSO SEXUAL',
        'I')", false
    );
    $r = hace_consulta(
        $db, "INSERT INTO categoria (id, fecha_creacion, fecha_deshabilitacion,
        id_supracategoria, id_tipo_violencia, col_rep_consolidado, nombre,
        tipocat)
        VALUES (397, '2010-04-17', NULL, 3, 'A', 8, 'V.S. - ABORTO FORZADO',
        'I')", false
    );
    $r = hace_consulta(
        $db, "INSERT INTO categoria (id, fecha_creacion, fecha_deshabilitacion,
        id_supracategoria, id_tipo_violencia, col_rep_consolidado, nombre,
        tipocat)
        VALUES (777, '2010-04-17', NULL, 1, 'D', 12, 'ABORTO FORZADO', 'I')",
        false
    );

    $r = hace_consulta(
        $db, "UPDATE categoria SET contada_en='701' WHERE id='10'", false
    );
    $r = hace_consulta(
        $db, "UPDATE categoria SET contada_en='72' WHERE id='12'", false
    );
    $r = hace_consulta(
        $db, "UPDATE categoria SET contada_en='702' WHERE id='13'", false
    );
    $r = hace_consulta(
        $db, "UPDATE categoria SET contada_en='73' WHERE id='15'", false
    );
    $r = hace_consulta(
        $db, "UPDATE categoria SET contada_en='706' WHERE id='18'", false
    );
    $r = hace_consulta(
        $db, "UPDATE categoria SET contada_en='77' WHERE id='19'", false
    );
    $r = hace_consulta(
        $db, "UPDATE categoria SET contada_en='903' WHERE id='102'", false
    );
    $r = hace_consulta(
        $db, "UPDATE categoria SET contada_en='906' WHERE id='104'", false
    );
    $r = hace_consulta(
        $db, "UPDATE categoria SET contada_en='771' WHERE id='191'", false
    );
    $r = hace_consulta(
        $db, "UPDATE categoria SET contada_en='772' WHERE id='192'", false
    );
    $r = hace_consulta(
        $db, "UPDATE categoria SET contada_en='773' WHERE id='193'", false
    );
    $r = hace_consulta(
        $db, "UPDATE categoria SET contada_en='774' WHERE id='194'", false
    );
    $r = hace_consulta(
        $db, "UPDATE categoria SET contada_en='775' WHERE id='195'", false
    );
    $r = hace_consulta(
        $db, "UPDATE categoria SET contada_en='776' WHERE id='196'", false
    );
    $r = hace_consulta(
        $db, "UPDATE categoria SET contada_en='777' WHERE id='197'", false
    );
    $r = hace_consulta(
        $db, "UPDATE categoria SET contada_en='906' WHERE id='104'", false
    );

    aplicaact($act, $idac, 'Acuerdos Esperanza 2010');
}

$idac = '1.1b2-lu';
if (!aplicado($idac)) {
    cambia_tipocol($db, 'ubicacion', 'sitio', 'VARCHAR(260)', false);
    cambia_tipocol($db, 'ubicacion', 'lugar', 'VARCHAR(260)', false);

    aplicaact($act, $idac, 'Tamaño de ubicación');
}

$idac = '1.1b2-vc';
if (!aplicado($idac)) {
    $r = hace_consulta(
        $db, "UPDATE categoria SET tipocat='C'
        WHERE id = '902'", false
    );

    aplicaact($act, $idac, 'Categoria colectiva');
}

$idac = '1.1b2-val';
if (!aplicado($idac)) {
    hace_consulta(
        $db, "INSERT INTO opcion (id_opcion, descripcion, id_mama, nomid)
        VALUES ('64', 'Validar', '60', 'valida')", false
    );

    aplicaact($act, $idac, 'Validaciones de consistencia');
}

$idac = '1.1b2-br';
if (!aplicado($idac)) {
    hace_consulta(
        $db, "INSERT INTO opcion (id_opcion, descripcion, id_mama, nomid)
        VALUES ('65', 'Buscar Repetidos', '60', 'buscaRepetidos')", false
    );

    aplicaact($act, $idac, 'Buscar repetidos');
}

$idac = '1.1b2-or';
if (!aplicado($idac)) {
    foreach (array(61, 64, 65) as $idop) {
        foreach (array(1, 2, 3) as $idrol) {
            hace_consulta(
                $db, "INSERT INTO opcion_rol (id_opcion, id_rol) " .
                "VALUES ($idop, $idrol)", false
            );
        }
    }
    foreach (array(62, 63) as $idop) {
        hace_consulta(
            $db, "INSERT INTO opcion_rol (id_opcion, id_rol) " .
            "VALUES ($idop, 1)", false
        );
    }

    aplicaact($act, $idac, 'Opciones de rol');
}

$idac = '1.1b3-cat';
if (!aplicado($idac)) {

    $r = hace_consulta(
        $db, "UPDATE categoria SET tipocat='O' WHERE id='902'",
        false
    );
    aplicaact($act, $idac, 'Reversa categoria colectiva. Es por métodos');
}

$idac = '1.1b3-is';
if (!aplicado($idac)) {
    $r = hace_consulta(
        $db, "UPDATE  presuntos_responsables SET
        nombre = 'GRUPOS DE INTOLERANCIA' where id='33'", false
    );
    aplicaact($act, $idac, 'Grupos de intolerancia es 33');
}

$idac = '1.1b3-v';
if (!aplicado($idac)) {
    $r = hace_consulta(
        $db, "INSERT INTO categoria (id, fecha_creacion,
        fecha_deshabilitacion, id_supracategoria, id_tipo_violencia,
        col_rep_consolidado, nombre, tipocat)
        VALUES (291, '2008-10-20', NULL, 2, 'A', 8, 'V.S. - VIOLACIÓN', 'I');",
        false
    );
    $r = hace_consulta(
        $db, "UPDATE categoria SET contada_en='291' WHERE contada_en='221'",
        false
    );
    $r = hace_consulta(
        $db, "UPDATE categoria_p_responsable_caso SET id_categoria='291'
        WHERE id_categoria = '221'", false
    );
    $r = hace_consulta(
        $db, "UPDATE acto SET id_categoria='291' WHERE id_categoria='221'",
        false
    );
    $r = hace_consulta(
        $db, "UPDATE actocolectivo SET id_categoria='291'
        WHERE id_categoria = '221'", false
    );
    $r = hace_consulta(
        $db, "UPDATE categoria_personal SET id_categoria='291'
        WHERE id_categoria = '221'", false
    );
    $r = hace_consulta($db, "DELETE FROM categoria WHERE id='221'", false);

    aplicaact(
        $act, $idac, 'Renumerada categoria 221 a 291 para que coincida '
        . 'en Marco Conceptual'
    );
}


$idac = '1.1b3-ran';
if (!aplicado($idac)) {
    hace_consulta(
        $db, "ALTER TABLE rango_edad " .
        "ADD COLUMN limiteinferior INTEGER DEFAULT '0' NOT NULL", false
    );
    hace_consulta(
        $db, "ALTER TABLE rango_edad " .
        "ADD COLUMN limitesuperior INTEGER DEFAULT '0' NOT NULL", false
    );

    hace_consulta(
        $db, "UPDATE rango_edad SET limiteinferior='0', " .
        "limitesuperior='15' WHERE id='1'", false
    );
    hace_consulta(
        $db, "UPDATE rango_edad SET limiteinferior='16', " .
        "limitesuperior='25' WHERE id='2'", false
    );
    hace_consulta(
        $db, "UPDATE rango_edad SET limiteinferior='26', " .
        "limitesuperior='45' WHERE id='3'", false
    );
    hace_consulta(
        $db, "UPDATE rango_edad SET limiteinferior='46', " .
        "limitesuperior='60' WHERE id='4'", false
    );
    hace_consulta(
        $db, "UPDATE rango_edad SET limiteinferior='61', " .
        "limitesuperior='130' WHERE id='5'", false
    );
    hace_consulta(
        $db, "UPDATE rango_edad SET limiteinferior='-1', " .
        "limitesuperior='-1' WHERE id='6'", false
    );

    aplicaact($act, $idac, 'Limites en rango de edad');
}

$idac = '1.1cp1-r';
if (!aplicado($idac)) {
    foreach (array('antecedente', 'categoria', 'presuntos_responsables',
        'supracategoria', 'tipo_violencia'
    ) as $t
    ) {
        hace_consulta(
            $db, "ALTER TABLE $t RENAME COLUMN fecha_creacion TO fechacreacion",
            false
        );
        hace_consulta(
            $db, "ALTER TABLE $t RENAME COLUMN fecha_deshabilitacion
            TO fechadeshabilitacion", false
        );
    }

    aplicaact($act, $idac, 'Renombra fecha_creacion y fecha_deshabilitacion');
}

$idac = '1.1cp1-mf';
if (!aplicado($idac)) {
    foreach (array('clase', 'intervalo', 'departamento',
        'municipio', 'parametros_reporte_consolidado', 'tipo_clase',
        'tipo_sitio', 'frontera', 'region', 'prensa', 'rango_edad',
        'resultado_agresion', 'tipo_relacion'
    )  as $t
    ) {
        hace_consulta(
            $db, "ALTER TABLE $t " .
            "ADD COLUMN fechacreacion DATE NOT NULL DEFAULT '2001-01-01'",
            false
        );
        hace_consulta(
            $db, "ALTER TABLE $t ADD COLUMN
            fechadeshabilitacion DATE CHECK (fechadeshabilitacion IS NULL OR
            fechadeshabilitacion >= fechacreacion)", false
        );
    }
    aplicaact(
        $act, $idac, 'Añade fechacreacion y fechadeshabilitacion '
        . ' a otras tablas básicas'
    );
}

$idac = '1.1cp2-c';
if (!aplicado($idac)) {

    hace_consulta(
        $db, "INSERT INTO contexto " .
        " (id, nombre, fechacreacion, fechadeshabilitacion) " .
        " VALUES ('30', 'INTOLERANCIA SOCIAL', '2011-04-26', NULL)", false
    );

    hace_consulta($db, "CREATE SEQUENCE etnia_seq;", false);
    hace_consulta(
        $db, "CREATE TABLE etnia (
        id INTEGER PRIMARY KEY DEFAULT(nextval('etnia_seq')),
        nombre VARCHAR(200) NOT NULL,
        descripcion VARCHAR(1000),
        fechacreacion    DATE NOT NULL,
        fechadeshabilitacion    DATE CHECK (fechadeshabilitacion IS NULL
        OR fechadeshabilitacion >= fechacreacion)
        );", false
    );
    hace_consulta(
        $db, "INSERT INTO etnia (id, nombre, descripcion, "
        . " fechacreacion, fechadeshabilitacion) "
        . " VALUES (1, 'SIN INFORMACIÓN', '', '2011-04-26', NULL)", false
    );
    hace_consulta(
        $db, "ALTER TABLE victima " .
        "ADD COLUMN id_etnia INTEGER REFERENCES etnia",
        false
    );
    $na = 'datos-implicado.sql';
    $r = consulta_archivo($db, $na, false, false, false);
    if ($r) {
        aplicaact(
            $act, $idac,
            'Añade etnia y contexto intolerancia social'
        );
    } else {
        echo_esc("No pudo abrir $na");
    }
}

$idac = '1.1cp2-i';
if (!aplicado($idac)) {

    hace_consulta(
        $db, "INSERT INTO contexto " .
        " (id, nombre, fechacreacion, fechadeshabilitacion) " .
        " VALUES ('31', 'SEGURIDAD INFORMÁTICA', '2011-04-28', NULL)", false
    );

    hace_consulta($db, "CREATE SEQUENCE iglesia_seq;", false);
    hace_consulta(
        $db, "CREATE TABLE iglesia (
        id INTEGER PRIMARY KEY DEFAULT(nextval('iglesia_seq')),
        nombre VARCHAR(200) NOT NULL,
        descripcion VARCHAR(1000),
        fechacreacion    DATE NOT NULL,
        fechadeshabilitacion    DATE CHECK (fechadeshabilitacion IS NULL
        OR fechadeshabilitacion >= fechacreacion)
        );", false
    );
    hace_consulta(
        $db, "INSERT INTO iglesia (id, nombre, descripcion, "
        . " fechacreacion, fechadeshabilitacion) "
        . " VALUES (1, 'SIN INFORMACIÓN', '', '2011-04-28', NULL)", false
    );
    hace_consulta(
        $db, "ALTER TABLE victima " .
        "ADD COLUMN id_iglesia INTEGER REFERENCES iglesia",
        false
    );
    $na = 'datos-implicado.sql';
    $r = consulta_archivo($db, $na, false, false, false);
    if ($r) {
        aplicaact(
            $act, $idac,
            'Añade iglesia y contexto Seguridad Informática'
        );
    } else {
        echo_esc("No pudo abrir $na");
    }
}

$idac = '1.1-vs';
if (!aplicado($idac)) {
    $r = hace_consulta(
        $db, "INSERT INTO categoria (id, fechacreacion, fechadeshabilitacion,
        id_supracategoria, id_tipo_violencia, col_rep_consolidado, nombre,
        tipocat) VALUES (197, '2010-04-17', NULL, 1, 'A', '12',
        'V.S. - ABORTO FORZADO', 'I')", false
    );
    $r = hace_consulta(
        $db, "UPDATE categoria SET contada_en='777' WHERE id='197'", false
    );
    $r = hace_consulta(
        $db,
        "INSERT INTO categoria (id, fechacreacion, fechadeshabilitacion,
        id_supracategoria, id_tipo_violencia, col_rep_consolidado,
        nombre, tipocat)
        VALUES (520, '2011-07-07', NULL, 2, 'B', 12, 'VIOLENCIA SEXUAL', 'I');",
        false
    );

    $r = hace_consulta(
        $db,
        "INSERT INTO categoria (id, fechacreacion, fechadeshabilitacion,
        id_supracategoria, id_tipo_violencia, col_rep_consolidado,
        nombre, tipocat)
        VALUES (521, '2011-07-07', NULL, 2, 'B', 12, 'VIOLACIÓN', 'I'); ",
        false
    );
    $r = hace_consulta(
        $db,
        "INSERT INTO categoria (id, fechacreacion, fechadeshabilitacion,
        id_supracategoria, id_tipo_violencia, col_rep_consolidado,
        nombre, tipocat)
        VALUES (522, '2011-07-07', NULL, 2, 'B', 12, 'EMBARAZO FORZADO', 'I');",
        false
    );
    $r = hace_consulta(
        $db,
        "INSERT INTO categoria (id, fechacreacion, fechadeshabilitacion,
        id_supracategoria, id_tipo_violencia, col_rep_consolidado,
        nombre, tipocat)
        VALUES (523, '2011-07-07', NULL, 2, 'B', 12,
        'PROSTITUCIÓN FORZADA', 'I'); ",
        false
    );
    $r = hace_consulta(
        $db,
        "INSERT INTO categoria (id, fechacreacion, fechadeshabilitacion,
        id_supracategoria, id_tipo_violencia, col_rep_consolidado,
        nombre, tipocat)
        VALUES (524, '2011-07-07', NULL, 2, 'B', 12,
        'ESTERILIZACIÓN FORZADA', 'I'); ",
        false
    );
    $r = hace_consulta(
        $db,
        "INSERT INTO categoria (id, fechacreacion, fechadeshabilitacion,
        id_supracategoria, id_tipo_violencia, col_rep_consolidado,
        nombre, tipocat)
        VALUES (525, '2011-07-07', NULL, 2, 'B', 12, 'ESCLAVITUD SEXUAL', 'I');",
        false
    );
    $r = hace_consulta(
        $db,
        "INSERT INTO categoria (id, fechacreacion, fechadeshabilitacion,
        id_supracategoria, id_tipo_violencia, col_rep_consolidado,
        nombre, tipocat)
        VALUES (526, '2011-07-07', NULL, 2, 'B', 12, 'ABUSO SEXUAL', 'I'); ",
        false
    );
    $r = hace_consulta(
        $db,
        "INSERT INTO categoria (id, fechacreacion, fechadeshabilitacion,
        id_supracategoria, id_tipo_violencia, col_rep_consolidado,
        nombre, tipocat)
        VALUES (527, '2011-07-07', NULL, 2, 'B', 12, 'ABORTO FORZADO', 'I'); ",
        false
    );


    aplicaact(
        $act, $idac, 'Violencia sexual mas completa'
    );
}


$idac = '1.1-nd';
if (!aplicado($idac)) {
    hace_consulta(
        $db, "ALTER TABLE persona" .
        " RENAME COLUMN tipo_documento TO tipodocumento", false
    );
    hace_consulta(
        $db, "ALTER TABLE persona" .
        " RENAME COLUMN numero_documento TO numerodocumento", false
    );
    aplicaact(
        $act, $idac, 'Nomenclatura en tabla persona'
    );
}

$idac = '1.1-os';
if (!aplicado($idac)) {
    hace_consulta(
        $db, "ALTER TABLE victima" .
        " ADD COLUMN orientacionsexual CHAR(1) DEFAULT 'H' " .
        " CHECK (orientacionsexual='L' OR orientacionsexual='G' OR " .
        " orientacionsexual='B' OR orientacionsexual='T' OR " .
        " orientacionsexual='I' OR orientacionsexual='H' " .
        " )", false
    );
    hace_consulta(
        $db, "ALTER TABLE victima ALTER " .
        "COLUMN orientacionsexual SET NOT NULL"
    );
    aplicaact(
        $act, $idac, 'Orientación sexual'
    );
}
$idac = '1.2-sm';
if (!aplicado($idac)) {
    hace_consulta($db, 'DROP TABLE opcion_rol', false);
    hace_consulta($db, 'DROP TABLE opcion', false);
    hace_consulta(
        $db, "ALTER TABLE usuario DROP CONSTRAINT usuario_id_rol_fkey", false
    );
    hace_consulta(
        $db, "ALTER TABLE usuario DROP CONSTRAINT usuario_id_rol_check", false
    );
    hace_consulta(
        $db,
        "ALTER TABLE usuario ADD CONSTRAINT usuario_id_rol_check "
        . " CHECK (rol>='1' AND rol<='4')", false
    );
    hace_consulta($db, 'DROP TABLE rol', false);
    hace_consulta($db, 'DROP SEQUENCE rol_seq', false);

    aplicaact(
        $act, $idac, 'Menú pasa de base de datos a interfaz'
    );
}

$idac = '1.2-lu';
if (!aplicado($idac)) {
    hace_consulta(
        $db, "ALTER TABLE usuario ADD COLUMN idioma "
        . " VARCHAR(6) NOT NULL DEFAULT 'es_CO'", false
    );

    aplicaact($act, $idac, 'Idioma preferido por usuario');
}


$idac = '1.2-rt';
if (!aplicado($idac)) {
    hace_consulta(
        $db,
        "ALTER TABLE categoria_p_responsable_caso RENAME TO "
        . "caso_categoria_presponsable", false
    );
    hace_consulta(
        $db,
        "ALTER TABLE presuntos_responsables RENAME TO "
        . "presponsable ", false
    );
    hace_consulta(
        $db,
        "ALTER SEQUENCE presuntos_responsables_seq RENAME TO "
        . "presponsable_seq ", false
    );
    hace_consulta(
        $db,
        "ALTER TABLE presuntos_responsables_caso RENAME TO "
        . "caso_presponsable", false
    );

    aplicaact($act, $idac, 'Renombrando tablas con presunto responsable');
}

$idac = '1.2-rt2';
if (!aplicado($idac)) {
    hace_consulta(
        $db,
        "ALTER TABLE vinculo_estado RENAME TO "
        . "vinculoestado ", false
    );
    hace_consulta(
        $db,
        "ALTER SEQUENCE vinculo_estado_seq RENAME TO "
        . "vinculoestado_seq ", false
    );
    hace_consulta(
        $db,
        "ALTER TABLE vinculo_estado_comunidad RENAME TO "
        . "comunidad_vinculoestado", false
    );

    aplicaact($act, $idac, 'Renombrando tablas vinculoestado');
}

$idac = '1.2-rt3';
if (!aplicado($idac)) {
    hace_consulta(
        $db,
        "ALTER TABLE filiacion_comunidad RENAME TO "
        . "comunidad_filiacion", false
    );
    hace_consulta(
        $db,
        "ALTER TABLE frontera_caso RENAME TO "
        . "caso_frontera", false
    );
    hace_consulta(
        $db,
        "ALTER TABLE funcionario_caso RENAME TO "
        . "caso_funcionario", false
    );
    hace_consulta(
        $db,
        "ALTER TABLE organizacion_comunidad RENAME TO "
        . "comunidad_organizacion", false
    );
    hace_consulta(
        $db,
        "ALTER TABLE profesion_comunidad RENAME TO "
        . "comunidad_profesion", false
    );
    hace_consulta(
        $db,
        "ALTER TABLE region_caso RENAME TO "
        . "caso_region", false
    );
    hace_consulta(
        $db,
        "ALTER TABLE victima_colectiva RENAME TO "
        . "victimacolectiva", false
    );
    hace_consulta(
        $db,
        "ALTER TABLE tipo_violencia RENAME TO "
        . "tviolencia", false
    );
    hace_consulta(
        $db,
        "ALTER TABLE tipo_sitio RENAME TO "
        . "tsitio", false
    );
    hace_consulta(
        $db,
        "ALTER SEQUENCE tipo_sitio_seq RENAME TO "
        . "tsitio_seq ", false
    );
    hace_consulta(
        $db,
        "ALTER TABLE tipo_relacion RENAME TO "
        . "trelacion", false
    );
    hace_consulta(
        $db,
        "ALTER TABLE tipo_clase RENAME TO "
        . "tclase", false
    );
    hace_consulta(
        $db,
        "ALTER TABLE sector_social RENAME TO "
        . "sectorsocial", false
    );
    hace_consulta(
        $db,
        "ALTER SEQUENCE sector_social_seq RENAME TO "
        . "sectorsocial_seq ", false
    );
    hace_consulta(
        $db,
        "ALTER TABLE sector_social_comunidad RENAME TO "
        . "comunidad_sectorsocial", false
    );
    hace_consulta(
        $db,
        "ALTER TABLE resultado_agresion RENAME TO "
        . "resagresion", false
    );
    hace_consulta(
        $db,
        "ALTER SEQUENCE resultado_agresion_seq RENAME TO "
        . "resagresion_seq ", false
    );
    hace_consulta(
        $db,
        "ALTER TABLE relacion_personas RENAME TO "
        . "persona_trelacion", false
    );
    hace_consulta(
        $db,
        "ALTER TABLE rango_edad RENAME TO "
        . "rangoedad", false
    );
    hace_consulta(
        $db,
        "ALTER SEQUENCE rango_edad_seq RENAME TO "
        . "rangoedad_seq ", false
    );
    hace_consulta(
        $db,
        "ALTER TABLE rango_edad_comunidad RENAME TO "
        . "comunidad_rangoedad", false
    );
    hace_consulta(
        $db,
        "ALTER TABLE parametros_reporte_consolidado RENAME TO "
        . "pconsolidado", false
    );
    hace_consulta(
        $db,
        "ALTER SEQUENCE parametros_reporte_consolidado_seq RENAME TO "
        . "pconsolidado_seq ", false
    );
    hace_consulta(
        $db,
        "ALTER TABLE prensa RENAME TO "
        . "ffrecuente", false
    );
    hace_consulta(
        $db,
        "ALTER SEQUENCE prensa_seq RENAME TO "
        . "ffrecuente_seq ", false
    );
    hace_consulta(
        $db,
        "ALTER TABLE escrito_caso RENAME TO "
        . "caso_ffrecuente", false
    );
    hace_consulta(
        $db,
        "ALTER TABLE fuente_directa RENAME TO "
        . "fotra", false
    );
    hace_consulta(
        $db,
        "ALTER SEQUENCE fuente_directa_seq RENAME TO "
        . "fotra_seq ", false
    );
    hace_consulta(
        $db,
        "ALTER TABLE fuente_directa_caso RENAME TO "
        . "caso_fotra", false
    );

    aplicaact($act, $idac, 'Renombrando tablas para seguir estándares SQL');
}

$idac = '1.2-rc1';
if (!aplicado($idac)) {
    foreach (array(
        array("acto", "id_p_responsable", "id_presponsable"),
        array("actocolectivo", "id_p_responsable", "id_presponsable"),
        array("caso", "gr_confiabilidad", "grconfiabilidad"),
        array("caso", "gr_esclarecimiento", "gresclarecimiento"),
        array("caso", "gr_impunidad", "grimpunidad"),
        array("caso", "gr_informacion", "grinformacion"),
        array("caso_categoria_presponsable", "id_p_responsable", "id_presponsable"),
        array("caso_categoria_presponsable", "id_tipo_violencia", "id_tviolencia"),
        array("caso_presponsable", "id_p_responsable", "id_presponsable"),
        array("caso_fotra", "ubicacion_fisica", "ubicacionfisica"),
        array("caso_fotra", "tipo_fuente", "tfuente"),
        array("pconsolidado", "no_columna", "id"),
        array("pconsolidado", "tipo_violencia", "tipoviolencia"),
        array("supracategoria", "id_tipo_violencia", "id_tviolencia"),
        array("categoria", "col_rep_consolidado", "id_pconsolidado"),
        array("categoria", "contada_en", "contadaen"),
        array("categoria", "id_tipo_violencia", "id_tviolencia"),
        array("clase", "id_tipo_clase", "id_tclase"),
        array("comunidad_rangoedad", "id_rango", "id_rangoedad"),
        array("comunidad_sectorsocial", "id_sector", "id_sectorsocial"),
        array("ffrecuente", "tipo_fuente", "tfuente"),
        array("presponsable_caso", "id_p_responsable", "id_presponsable"),
        array("ubicacion", "id_tipo_sitio", "id_tsitio"),
        array("usuario", "id_rol", "rol"),
        array("usuario", "id_usuario", "id"),
        array("usuario", "dias_edicion_caso", "diasedicion"),
        array("victima", "id_rango_edad", "id_rangoedad"),
        array("victima", "id_sector_social", "id_sectorsocial"),
        array("victima", "id_vinculo_estado", "id_vinculoestado"),
        array("victima", "id_organizacion_armada", "organizacionarmada"),
        array("comunidad_vinculoestado", "id_vinculo_estado", "id_vinculoestado"),
        array("presponsable", "id_papa", "papa"),
        array("persona_trelacion", "id_persona1", "persona1"),
        array("persona_trelacion", "id_persona2", "persona2"),
        array("persona_trelacion", "id_tipo", "id_trelacion"),
        array("victimacolectiva", "personas_aprox", "personasaprox"),
        array("victimacolectiva", "id_organizacion_armada", "organizacionarmada"),
        array("caso_ffrecuente", "ubicacion_fisica", "ubicacionfisica"),
        array("caso_ffrecuente", "id_prensa", "id_ffrecuente"),
        array("caso_fotra", "id_fuente_directa", "id_fotra"),
        array("caso_funcionario", "fecha_inicio", "fechainicio"),
    ) as $v) {
        $tabla = $v[0];
        $ant = $v[1];
        $nue = $v[2];
        hace_consulta(
            $db,
            "ALTER TABLE $tabla RENAME COLUMN $ant TO $nue", false
        );
    }

    aplicaact($act, $idac, 'Renombrando campos para seguir estándares SQL');
}

$idac = '1.2-gc';
if (!aplicado($idac)) {
    foreach (array('departamento', 'municipio', 'clase') as $tabla) {
        hace_consulta(
            $db, "ALTER TABLE $tabla " .
            " ADD COLUMN latitud FLOAT", false
        );
        hace_consulta(
            $db, "ALTER TABLE $tabla " .
            " ADD COLUMN longitud FLOAT", false
        );
    }
    aplicaact($act, $idac, 'Latitude y Longitud en departamento, municipio y clase');
}

$idac = '1.1-dp';
if (!aplicado($idac)) {
    hace_consulta(
        $db, "ALTER TABLE tclase ALTER COLUMN id TYPE VARCHAR(10)", false
    );
    hace_consulta(
        $db, "ALTER TABLE clase ALTER COLUMN id_tclase TYPE VARCHAR(10)", false
    );

    aplicaact($act, $idac, 'Actualiza info. geográfica con DIVIPOLA 2012');
}

$idac = '1.1-dp1';
if (!aplicado($idac)) {
    consulta_archivo($db, 'act-nom2012.sql');
    aplicaact($act, $idac, 'Actualiza info. geográfica con DIVIPOLA 2012');
}

$idac = '1.1-dp13';
if (!aplicado($idac)) {
    consulta_archivo($db, 'act-nom2013.sql');
    aplicaact($act, $idac, 'Actualiza info. geográfica con DIVIPOLA 2013');
}

$idac = '1.2-co';
if (!aplicado($idac)) {
    consulta_archivo($db, 'act-coor.sql');
    aplicaact($act, $idac, 'Agrega coordenadas a departamentos y municipios');
}

$idac = '1.2-coc';
if (!aplicado($idac)) {
    $n = (int)$db->getOne(
        "SELECT COUNT(*) FROM ubicacion, municipio 
        WHERE municipio.id_departamento=ubicacion.id_departamento 
        AND municipio.id=ubicacion.id_municipio
        AND ubicacion.latitud IS NULL; "
    );
    echo_esc("  Agregando coordenadas a $n ubicaciones con municipio");
    echo "<br>";
    hace_consulta(
        $db, "UPDATE ubicacion 
        SET latitud = municipio.latitud+random()/1000-0.0005, 
        longitud=municipio.longitud+random()/1000-0.0005 
        FROM municipio 
        WHERE municipio.id_departamento=ubicacion.id_departamento 
        AND municipio.id=ubicacion.id_municipio
        AND ubicacion.latitud IS NULL; "
    );

    aplicaact($act, $idac, 'Agrega coordenadas a casos que no tienen');
}


$idac = '1.2-loc';
if (!aplicado($idac)) {
    hace_consulta(
        $db, "CREATE COLLATION es_co_utf_8 (LOCALE = 'es_CO.UTF-8')", false
    );
    foreach (array(
        'Antecedente', 'Categoria', 'Clase', 'Contexto',
        'Departamento', 'Etnia', 'Ffrecuente', 'Filiacion', 'Frontera', 
        'Fotra', 'Ffrecuente', 'Grupoper',
        'Iglesia', 'Intervalo', 'Municipio', 'Organizacion', 
        'Presponsable', 'Profesion', 'Rangoedad', 'Region', 'Resagresion', 
        'Sectorsocial', 'Supracategoria', 'Tclase', 'Trelacion', 'Tsitio', 
        'Tviolencia', 'Vinculoestado', 
    ) as $t) {
        hace_consulta(
            $db, "ALTER TABLE $t ALTER nombre "
            . " TYPE VARCHAR(500) COLLATE es_co_utf_8", false
        );
    }
    hace_consulta(
        $db, "ALTER TABLE persona ALTER nombres "
        . " TYPE VARCHAR(100) COLLATE es_co_utf_8", false
    );
    hace_consulta(
        $db, "ALTER TABLE persona ALTER apellidos "
        . " TYPE VARCHAR(100) COLLATE es_co_utf_8", false
    );
    hace_consulta(
        $db, "ALTER TABLE usuario ALTER nombre "
        . " TYPE VARCHAR(50) COLLATE es_co_utf_8", false
    );
    hace_consulta(
        $db, "ALTER TABLE ubicacion ALTER lugar "
        . " TYPE VARCHAR(500) COLLATE es_co_utf_8", false
    );
    hace_consulta(
        $db, "ALTER TABLE ubicacion ALTER sitio "
        . " TYPE VARCHAR(500) COLLATE es_co_utf_8", false
    );
    hace_consulta(
        $db, "ALTER TABLE pconsolidado ALTER rotulo "
        . " TYPE VARCHAR(500) COLLATE es_co_utf_8", false
    );

    aplicaact($act, $idac, 'Localización');
}


$idac = '1.2-btc';
if (!aplicado($idac)) {
    hace_consulta(
        $db, "CREATE EXTENSION unaccent", false
    );
    hace_consulta(
        $db, "ALTER TEXT SEARCH DICTIONARY unaccent (RULES='unaccent')", false
    );
    hace_consulta(
        $db, "ALTER FUNCTION unaccent(text) IMMUTABLE", false
    );
    hace_consulta(
        $db, "CREATE INDEX persona_nombres_apellidos ON persona "
        . " USING gin(to_tsvector('spanish', unaccent(persona.nombres) "
        . "|| ' ' || unaccent(persona.apellidos)))", 
        false
    );
    hace_consulta(
        $db, "CREATE INDEX persona_apellidos_nombres ON persona "
        . " USING gin(to_tsvector('spanish', unaccent(persona.apellidos) "
        . " || ' ' || unaccent(persona.nombres)))", 
        false
    );
    hace_consulta(
        $db, "CREATE INDEX persona_nombres_apellidos_doc ON persona "
        . " USING gin(to_tsvector('spanish', unaccent(persona.nombres) "
        . "|| ' ' || unaccent(persona.apellidos) "
        . "|| ' ' || persona.numerodocumento))", 
        false
    );
    hace_consulta(
        $db, "CREATE INDEX persona_apellidos_nombres_doc ON persona "
        . " USING gin(to_tsvector('spanish', unaccent(persona.apellidos) "
        . " || ' ' || unaccent(persona.nombres) "
        . " || ' ' || persona.numerodocumento))", 
        false
    );

    hace_consulta(
        $db, "CREATE INDEX caso_titulo ON caso "
        . " USING gin(to_tsvector('spanish', unaccent(caso.titulo))) ",
        false
    );
    hace_consulta(
        $db, "CREATE INDEX caso_memo ON caso "
        . " USING gin(to_tsvector('spanish', unaccent(caso.memo))) ",
        false
    );

    aplicaact($act, $idac, 'Búsqueda de textos');
}

$idac = '1.2-idn';
if (!aplicado($idac)) {
    hace_consulta(
        $db, "UPDATE persona SET numerodocumento = "
        . " regexp_replace(numerodocumento, '[^0-9]', '', 'g') ", false
    );
    hace_consulta(
        $db, "UPDATE persona SET numerodocumento = NULL "
        . " WHERE numerodocumento = '' ", false
    );
    hace_consulta(
        $db, "ALTER TABLE persona ALTER numerodocumento TYPE BIGINT USING 
        CAST (numerodocumento AS BIGINT)"
    );
    hace_consulta(
        $db, "ALTER TABLE persona DROP CONSTRAINT numerodocumento_key ", false
    );
    hace_consulta(
        $db, "ALTER TABLE persona ADD CONSTRAINT numerodocumento_key "
        . " UNIQUE (tipodocumento, numerodocumento)"
    );
    aplicaact($act, $idac, 'Numero de documento entero');
}

$idac = '1.2-ext';
if (!aplicado($idac)) {
    hace_consulta(
        $db, "INSERT INTO departamento 
        (id, nombre, latitud, longitud, fechacreacion, fechadeshabilitacion) 
        VALUES (10000, 'EXTERIOR', NULL, NULL, '2013-06-13', NULL);", false
    );
    hace_consulta(
        $db, "INSERT INTO municipio
        (id, nombre, id_departamento, latitud, longitud, 
        fechacreacion, fechadeshabilitacion)
        (SELECT id, nombre, 10000, latitud, longitud,
        fechacreacion, fechadeshabilitacion FROM municipio
        WHERE id_departamento='0');", false
    );
    foreach (array('clase', 'ubicacion', 'persona') as $t) {
        hace_consulta(
            $db, "UPDATE $t SET id_departamento = 10000
            WHERE id_departamento = 0", false
        );
    }
    hace_consulta(
        $db, "DELETE FROM municipio WHERE id_departamento = '0'", false
    );
    hace_consulta(
        $db, "DELETE FROM departamento WHERE id = '0'", false
    );
    aplicaact($act, $idac, 'Cambio de código EXTERIOR de 0 a 10000');
}


$idac = '1.2-tb';
if (!aplicado($idac)) {

    hace_consulta(
        $db, "INSERT INTO vinculoestado(id, nombre, fechacreacion) 
        VALUES (40, 'VICEPRESIDENCIA', '2013-07-05')", false
    );
    hace_consulta(
        $db, "INSERT INTO organizacion(id, nombre, fechacreacion) 
        VALUES (17, 'VÍCTIMAS', '2013-07-05')", false
    );
    hace_consulta(
        $db, "INSERT INTO etnia (id, nombre, descripcion, fechacreacion) 
        VALUES (60, 'ROM', '', '2013-07-05')", false
    );

    aplicaact($act, $idac, 'Aumentadas tablas básicas');
}
$idac = '1.2-pr1';
if (!aplicado($idac)) {
    hace_consulta(
        $db, "UPDATE presponsable set papa='39' WHERE id='1'", false
    );
    hace_consulta(
        $db, "UPDATE presponsable set papa='39' WHERE id='14'", false
    );
    hace_consulta(
        $db, "UPDATE presponsable set papa='40' WHERE id='25'", false
    );
    hace_consulta(
        $db, "UPDATE presponsable set papa='36' WHERE id='33'", false
    );

    aplicaact($act, $idac, 'Renombrando tablas');
} 

$idac = '1.2-sx';
if (!aplicado($idac)) {
    hace_consulta(
        $db, "
-- Tomado de http://wiki.postgresql.org/wiki/SoundexESP
-- Oliver Mazariegos http://www.grupovesica.com

CREATE OR REPLACE FUNCTION soundexesp(input text) RETURNS text
IMMUTABLE STRICT COST 500 LANGUAGE plpgsql
AS $$
DECLARE
	soundex text='';	
	-- para determinar la primera letra
	pri_letra text;
	resto text;
	sustituida text ='';
	-- para quitar adyacentes
	anterior text;
	actual text;
	corregido text;
BEGIN
       -- devolver null si recibi un string en blanco o con espacios en blanco
	IF length(trim(input))= 0 then
		RETURN NULL;
	end IF;
 
 
	-- 1: LIMPIEZA:
		-- pasar a mayuscula, eliminar la letra \"H\" inicial, los acentos y la enie
		-- 'holá coñó' => 'OLA CONO'
		input=translate(ltrim(trim(upper(input)),'H'),'ÑÁÉÍÓÚÀÈÌÒÙÜ','NAEIOUAEIOUU');
 
		-- eliminar caracteres no alfabéticos (números, símbolos como &,%,\",*,!,+, etc.
		input=regexp_replace(input, '[^a-zA-Z]', '', 'g');
 
	-- 2: PRIMERA LETRA ES IMPORTANTE, DEBO ASOCIAR LAS SIMILARES
	--  'vaca' se convierte en 'baca'  y 'zapote' se convierte en 'sapote'
	-- un fenomeno importante es GE y GI se vuelven JE y JI; CA se vuelve KA, etc
	pri_letra =substr(input,1,1);
	resto =substr(input,2);
	CASE 
		when pri_letra IN ('V') then
			sustituida='B';
		when pri_letra IN ('Z','X') then
			sustituida='S';
		when pri_letra IN ('G') AND substr(input,2,1) IN ('E','I') then
			sustituida='J';
		when pri_letra IN('C') AND substr(input,2,1) NOT IN ('H','E','I') then
			sustituida='K';
		else
			sustituida=pri_letra;
 
	end case;
	--corregir el parametro con las consonantes sustituidas:
	input=sustituida || resto;		
 
	-- 3: corregir \"letras compuestas\" y volverlas una sola
	input=REPLACE(input,'CH','V');
	input=REPLACE(input,'QU','K');
	input=REPLACE(input,'LL','J');
	input=REPLACE(input,'CE','S');
	input=REPLACE(input,'CI','S');
	input=REPLACE(input,'YA','J');
	input=REPLACE(input,'YE','J');
	input=REPLACE(input,'YI','J');
	input=REPLACE(input,'YO','J');
	input=REPLACE(input,'YU','J');
	input=REPLACE(input,'GE','J');
	input=REPLACE(input,'GI','J');
	input=REPLACE(input,'NY','N');
	-- para debug:    --return input;
 
	-- EMPIEZA EL CALCULO DEL SOUNDEX
	-- 4: OBTENER PRIMERA letra
	pri_letra=substr(input,1,1);
 
	-- 5: retener el resto del string
	resto=substr(input,2);
 
	--6: en el resto del string, quitar vocales y vocales fonéticas
	resto=translate(resto,'@AEIOUHWY','@');
 
	--7: convertir las letras foneticamente equivalentes a numeros  (esto hace que B sea equivalente a V, C con S y Z, etc.)
	resto=translate(resto, 'BPFVCGKSXZDTLMNRQJ', '111122222233455677');
	-- así va quedando la cosa
	soundex=pri_letra || resto;
 
	--8: eliminar números iguales adyacentes (A11233 se vuelve A123)
	anterior=substr(soundex,1,1);
	corregido=anterior;
 
	FOR i IN 2 .. length(soundex) LOOP
		actual = substr(soundex, i, 1);
		IF actual <> anterior THEN
			corregido=corregido || actual;
			anterior=actual;			
		END IF;
	END LOOP;
	-- así va la cosa
	soundex=corregido;
 
	-- 9: siempre retornar un string de 4 posiciones
	soundex=rpad(soundex,4,'0');
	soundex=substr(soundex,1,4);		
 
	-- YA ESTUVO
	RETURN soundex;	
END;	
$$
        ", false
    );
    aplicaact($act, $idac, 'Función Soundex en Español');
} 



if (isset($GLOBALS['menu_tablas_basicas'])) {
    $hayrep = false;
    foreach ($GLOBALS['menu_tablas_basicas'] as $a) {
        if ($a['title'] == 'Reportes'
            || $a['sub'][0]['url'] == 'pconsolidado'
        ) {
            $hayrep = true;
        }
    }
    if (!$hayrep) {
        echo "<font color='red'>En el arreglo <tt>menu_tablas_basicas</tt> " .
            "del archivo <tt>" .
            htmlentities(
                $_SESSION['dirsitio'] . "/conf_int.php", ENT_COMPAT, 'UTF-8'
            ) . "</tt> falta:
<pre>
    array('title' => 'Reportes', 'url'=> null, 'sub' => array(
        array('title'=>'Columnas de Reporte Consolidado',
            'url'=>'parametros_reporte_consolidado', 'sub'=>null),
        ),
    ),
</pre></font>";
    }
} else {
    echo "<font color='red'>No se encontró variable global " .
        "<tt>menu_tablas_basicas</tt></font>";
}


// Creando esquema
regenera_esquemas();

/**
 * Agrega el contendio del archivo $fuente al $destino
 *
 * @param string $fuente  Nombre de archivo fuente
 * @param string $destino Nombre de archivo destino
 * @param string $modo    Modo para abrir archivo destino
 *
 * @return void
 */
function agrega_archivo($fuente, $destino, $modo = "w")
{
    if (!($fen = fopen($fuente, "r"))) {
        die ("No se pudo leer $fuente");
    }
    if (!($fsal = fopen($destino, $modo))) {
        die ("No se pudo escribir $destino");
    }
    $buf = "";
    while (!feof($fen)) {
        $ll = fread($fen, 1024);
        if (fwrite($fsal, $ll) === false) {
            die("No se pudo escribir completo $destino");
        }
    }
    fclose($fen);
    fclose($fsal);
}

/**
 * Lee estructura-dataobject.ini y estructura-dataobject.links.ini
 * del directorio $nd/DataObjects/ y agrega datos a
 * $dirap/DataObjects/$dbnombre.ini y $dirap/DataObjects/$dbnombre.links.ini
 *
 * @param string $nd       Ruta
 * @param string $dbnombre Nombre de la base de datos
 * @param string $dirap    Directorio de SIVeL
 * @param string $modo     Modo para abrir archivo que crea
 *
 * @return void
 */
function lee_escritura($nd, $dbnombre, $dirap, $modo)
{
    if (!file_exists("$nd/DataObjects/estructura-dataobject.ini")) {
        echo "No puede leerse "
            . htmlentities(
                "$nd/DataObjects/estructura-dataobject.ini",
                ENT_COMPAT, 'UTF-8'
            )
            . "<br>";
        return;
    }
    agrega_archivo(
        "$nd/DataObjects/estructura-dataobject.ini",
        "$dirap/DataObjects/$dbnombre.ini", $modo
    );

    if (!file_exists("$nd/DataObjects/estructura-dataobject.links.ini")) {
        die("No puede leerse $nd/DataObjects/estructura-dataobject.ini");
    }
    agrega_archivo(
        "$nd/DataObjects/estructura-dataobject.links.ini",
        "$dirap/DataObjects/$dbnombre.links.ini", $modo
    );
}

if (!isset($_SESSION['SIN_INDICES']) || !$_SESSION['SIN_INDICES']) {
    echo "Actualizando indices<br>";
    $na = "prepara_indices.sql";
    $r = consulta_archivo($db, $na, false, false, false);
}

echo "Actualizando módulos<br>";
$lm = explode(" ", $modulos);
foreach ($lm as $m) {
    if ($m != '') {
        echo_esc("  $m");
        include_once "$m/actualiza.php";
        if (!isset($_SESSION['SIN_INDICES']) || !$_SESSION['SIN_INDICES']) {
            $na = "$m/prepara_indices.sql";
            $r = consulta_archivo($db, $na, false, false, false);
        }
    }
}

$ap = $_SESSION['dirsitio'] . '/actualiza.php';
if (file_exists($ap)) {
    echo_esc("Actualizando personalización ($ap)");
    include_once $ap;
    if (!isset($_SESSION['SIN_INDICES']) || !$_SESSION['SIN_INDICES']) {
        $na = "$m/prepara_indices.sql";
        $r = consulta_archivo($db, $na, false, false, false);
    }
}

echo '<table width="100%"><td style="white-space: nowrap; '
    . 'background-color: #CCCCCC;" align="left" valign="top" colspan="2">'
    . '<b><div align=right><a href="index.php">'
    . _('Men&uacute; Principal') . '</a></div></b>'
    . '</td></table>';

pie_envia();
?>
