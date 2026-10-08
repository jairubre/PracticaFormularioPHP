<?php
/*
 * buscar.php
 * Recoge los filtros del formulario (web.html) mediante $_POST,
 * recorre el array tridimensional $nexus y muestra las entradas que cumplen
 * todos los filtros elegidos.
 */

/* ---------- 0. ARRAY TRIDIMENSIONAL ASOCIATIVO ---------- */
/*
 * Historial de partidas de Nexus Pulse (módulo Pulse Matcher).
 * Estructura: $nexus[modo de juego][partida][estadística] = "valor"
 */
$historial_partidas["ranked"]["partida1"]["Daño"] = "15000";
$historial_partidas["ranked"]["partida1"]["Bajas"] = "10";
$historial_partidas["ranked"]["partida1"]["muertes"] = "5";
$historial_partidas["ranked"]["partida2"]["Daño"] = "1000";
$historial_partidas["ranked"]["partida2"]["Bajas"] = "3";
$historial_partidas["ranked"]["partida2"]["muertes"] = "25";

$historial_partidas["normal"]["partida1"]["Daño"] = "30000";
$historial_partidas["normal"]["partida1"]["Bajas"] = "20";
$historial_partidas["normal"]["partida1"]["muertes"] = "5";
$historial_partidas["normal"]["partida2"]["Daño"] = "15000";
$historial_partidas["normal"]["partida2"]["Bajas"] = "9";
$historial_partidas["normal"]["partida2"]["muertes"] = "3";

$historial_partidas["aram"]["partida1"]["Daño"] = "5000";
$historial_partidas["aram"]["partida1"]["Bajas"] = "13";
$historial_partidas["aram"]["partida1"]["muertes"] = "2";
$historial_partidas["aram"]["partida2"]["Daño"] = "13000";
$historial_partidas["aram"]["partida2"]["Bajas"] = "7";
$historial_partidas["aram"]["partida2"]["muertes"] = "8";

// Array donde se guardarán los resultados encontrados
$resultados = array();

// Mensaje de error (vacío si todo va bien)
$error = "";

/* ---------- 1. RECOGER LOS DATOS DEL FORMULARIO CON $_POST ---------- */

// isset() comprueba que la variable existe; si no, se asigna una cadena vacía.
// trim() elimina los espacios en blanco del principio y del final.
// Una cadena vacía significa que el usuario dejó ese campo sin rellenar.
$clave1    = $_POST["clave1"]    ? trim($_POST["clave1"])    : "";
$clave2    = $_POST["clave2"]    ? trim($_POST["clave2"])    : "";
$clave3    = $_POST["clave3"]    ? trim($_POST["clave3"])    : "";
$contenido = $_POST["contenido"] ? trim($_POST["contenido"]) : "";

/* ---------- 2. VALIDAR LOS DATOS (estructuras alternativas) ---------- */

// Hay que rellenar al menos un campo; si no, no se busca nada
if ($clave1 == "" && $clave2 == "" && $clave3 == "" && $contenido == "") {
    $error = "Escribe al menos un filtro en el formulario.";
}

/* ---------- 3. FILTRAR EL ARRAY (bucles) ---------- */


/* stripos($pajar, $aguja) busca $aguja dentro de $pajar sin distinguir
 * mayúsculas de minúsculas. Devuelve la posición donde la encuentra
 * o false si no la encuentra. Así se pueden buscar cadenas y subcadenas.
 * IMPORTANTE: se compara con !== false porque si la coincidencia está
 * al principio, stripos devuelve 0, y 0 == false sería verdadero.
 *
 * Cada campo vacío cuenta como coincidencia; los campos rellenos
 * tienen que cumplirse todos a la vez.
 */

if ($error == "") {

    // Recorremos las tres dimensiones del array con foreach anidados
    foreach ($historial_partidas as $modo => $partidas) {
        foreach ($partidas as $partida => $estadisticas) {
            foreach ($estadisticas as $estadistica => $valor) {

                $valida = true;   // de momento la fila vale

                // Filtro 1: modo
                if ($clave1 != "" && strtolower($modo) !== strtolower($clave1)) {
                    $valida = false;
                }

                // Filtro 2: partida
                if ($clave2 != "" && strtolower($partida) !== strtolower($clave2)) {
                    $valida = false;
                }

                // Filtro 3: accion
                if ($clave3 != "" && strtolower($estadistica) !== strtolower($clave3)) {
                    $valida = false;
                }

                // Filtro 4: valor
                if ($contenido != "" && strtolower($valor) !== strtolower($contenido)) {
                    $valida = false;
                }

                // Si ha pasado todos los filtros, la guardamos
                if ($valida) {
                    $resultados[] = [
                        "modo"        => $modo,
                        "partida"     => $partida,
                        "estadistica" => $estadistica,
                        "valor"       => $valor
                    ];
                }
            }
        }
    }
}

// Texto con los filtros elegidos, para mostrarlo en el resumen.
// Se guardan en un array y se unen con implode().
$filtros = array();
if ($clave1 != "") {
    $filtros[] = "1.ª clave = " . $clave1;
}
if ($clave2 != "") {
    $filtros[] = "2.ª clave = " . $clave2;
}
if ($clave3 != "") {
    $filtros[] = "3.ª clave = " . $clave3;
}
if ($contenido != "") {
    $filtros[] = "contenido = " . $contenido;
}
$textoFiltros = implode(" · ", $filtros);

// count() devuelve el número de resultados encontrados
$total = count($resultados);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Resultados - Buscador Nexus Pulse</title>
    <link rel="stylesheet" href="estilos.css">
</head>

<body>
    <main>
        <h1>Nexus <span>Pulse</span></h1>

        <?php
        /* ---------- 4. MOSTRAR LOS RESULTADOS (sentencias de visualización) ---------- */

        // htmlspecialchars() convierte caracteres especiales (<, >, &, ") en entidades HTML
        // para que lo que escribe el usuario no se interprete como código.

        if ($error != "") {
            // No se ha rellenado ningún campo
            echo "<div class='aviso'>" . $error . "</div>";
        } elseif ($total == 0) {
            // La combinación de filtros no existe en el array
            echo "<p class='resumen'>Filtros: <strong>" . htmlspecialchars($textoFiltros) . "</strong></p>";
            echo "<div class='aviso'>No hay ninguna entrada que cumpla todos estos filtros a la vez.</div>";
        } else {
            // Hay resultados: los mostramos en una tabla recorriendo el array con foreach
            echo "<p class='resumen'>Filtros: <strong>" . htmlspecialchars($textoFiltros) . "</strong> · ";
            echo "<strong>" . $total . "</strong> " . ($total == 1 ? "resultado" : "resultados") . "</p>";

            echo "<div class='tabla'><table>";
            echo "<tr><th>1.ª clave · Modo</th><th>2.ª clave · Partida</th><th>3.ª clave · Estadística</th><th>Contenido · Valor</th></tr>";

            foreach ($resultados as $fila) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($fila["modo"]) . "</td>";
                echo "<td>" . htmlspecialchars($fila["partida"]) . "</td>";
                echo "<td>" . htmlspecialchars($fila["estadistica"]) . "</td>";
                echo "<td>" . htmlspecialchars($fila["valor"]) . "</td>";
                echo "</tr>";
            }

            echo "</table></div>";
        }
        ?>

        <a class="boton" href="web.html">Nueva búsqueda</a>
    </main>
</body>

</html>