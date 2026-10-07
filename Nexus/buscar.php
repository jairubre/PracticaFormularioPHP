<?php
/*
 * buscar.php
 * Recoge los datos del formulario (index.html) mediante $_POST,
 * busca la cadena o subcadena en el array $nexus y muestra los resultados.
 */

/* ---------- 0. ARRAY TRIDIMENSIONAL ASOCIATIVO ---------- */
/*
 * Estructura: $nexus[sección][tipo][elemento] = "contenido"
 *   - historial -> partidas jugadas (módulo Pulse Matcher)
 *   - tienda    -> catálogo de productos (módulo Pulse Store)
 *   - buscador  -> personajes disponibles en el matchmaking (módulo Pulse Matcher)
 */

// ===== HISTORIAL: partidas jugadas por tipo de partida =====
$nexus["historial"]["ranked"]["partida1"] = "Victoria 5v5 - 32 min - MMR +24";
$nexus["historial"]["ranked"]["partida2"] = "Derrota 5v5 - 28 min - MMR -18";
$nexus["historial"]["ranked"]["partida3"] = "Victoria 5v5 - 41 min - MMR +21";
$nexus["historial"]["normal"]["partida1"] = "Victoria 5v5 - 25 min";
$nexus["historial"]["normal"]["partida2"] = "Derrota 5v5 - 35 min";
$nexus["historial"]["aram"]["partida1"] = "Victoria 5v5 - 18 min - Personajes aleatorios";

// ===== TIENDA: productos del catálogo =====
$nexus["tienda"]["skins"]["item1"] = "Cyber Ronin - 1350 Pulse Orbs";
$nexus["tienda"]["skins"]["item2"] = "Guardián Neón - 1820 Pulse Orbs";
$nexus["tienda"]["skins"]["item3"] = "Dragón de Escarcha - 975 Pulse Orbs";
$nexus["tienda"]["cromas"]["item1"] = "Croma Neón Rojo - 290 Pulse Orbs";
$nexus["tienda"]["cromas"]["item2"] = "Croma Oro - 290 Pulse Orbs";
$nexus["tienda"]["pase_batalla"]["item1"] = "Pase Temporada 1 - 950 Pulse Orbs";

// ===== BUSCADOR: personajes disponibles por tipo de partida =====
$nexus["buscador"]["ranked"]["personaje1"] = "Kael - Asesino - Pick & Ban";
$nexus["buscador"]["ranked"]["personaje2"] = "Lyra - Maga - Pick & Ban";
$nexus["buscador"]["ranked"]["personaje3"] = "Torvak - Tanque - Pick & Ban";
$nexus["buscador"]["normal"]["personaje1"] = "Kael - Asesino - Selección libre";
$nexus["buscador"]["normal"]["personaje2"] = "Sylas - Soporte - Selección libre";
$nexus["buscador"]["aram"]["personaje1"] = "Personaje aleatorio - Cualquier rol";

// Array donde se guardarán los resultados encontrados
$resultados = array();

// Mensaje de error (vacío si todo va bien)
$error = "";

// Tipos de búsqueda permitidos (sirve para validar lo que llega del formulario)
$tiposValidos = array("seccion", "modo", "elemento", "contenido", "combinacion");

// Nombres legibles de cada tipo de búsqueda, para mostrarlos en pantalla
$nombresTipo = array(
    "seccion"     => "la sección (1.ª clave)",
    "modo"        => "el tipo (2.ª clave)",
    "elemento"    => "el elemento (3.ª clave)",
    "contenido"   => "el contenido",
    "combinacion" => "la combinación de claves"
);

/* ---------- 1. RECOGER LOS DATOS DEL FORMULARIO CON $_POST ---------- */

// isset() comprueba que la variable existe; si no, se asigna una cadena vacía.
// trim() elimina los espacios en blanco del principio y del final.
$tipo   = isset($_POST["tipo"])   ? trim($_POST["tipo"])   : "";
$cadena = isset($_POST["cadena"]) ? trim($_POST["cadena"]) : "";
$clave1 = isset($_POST["clave1"]) ? trim($_POST["clave1"]) : "";
$clave2 = isset($_POST["clave2"]) ? trim($_POST["clave2"]) : "";
$clave3 = isset($_POST["clave3"]) ? trim($_POST["clave3"]) : "";

/* ---------- 2. VALIDAR LOS DATOS (estructuras alternativas) ---------- */

// in_array() comprueba si el tipo recibido está entre los permitidos
if (!in_array($tipo, $tiposValidos)) {
    $error = "Elige un tipo de búsqueda en el formulario.";
} elseif ($tipo == "combinacion") {
    // En la combinación hay que rellenar al menos una de las tres claves
    if ($clave1 == "" && $clave2 == "" && $clave3 == "") {
        $error = "Para buscar por combinación, rellena al menos una de las tres claves.";
    }
} elseif ($cadena == "") {
    // En el resto de búsquedas el texto es obligatorio
    $error = "Escribe un texto para buscar.";
}

/* ---------- 3. BUSCAR EN EL ARRAY (bucles) ---------- */

/*
 * stripos($pajar, $aguja) busca $aguja dentro de $pajar sin distinguir
 * mayúsculas de minúsculas. Devuelve la posición donde la encuentra
 * o false si no la encuentra.
 * IMPORTANTE: se compara con !== false porque si la coincidencia está
 * al principio, stripos devuelve 0, y 0 == false sería verdadero.
 */
if ($error == "") {

    // Recorremos las tres dimensiones del array con foreach anidados
    foreach ($nexus as $seccion => $modos) {              // 1.ª clave
        foreach ($modos as $modo => $elementos) {          // 2.ª clave
            foreach ($elementos as $elemento => $contenido) { // 3.ª clave y contenido

                $coincide = false;

                // Según el tipo elegido comparamos una cosa u otra
                switch ($tipo) {
                    case "seccion":
                        $coincide = stripos($seccion, $cadena) !== false;
                        break;

                    case "modo":
                        $coincide = stripos($modo, $cadena) !== false;
                        break;

                    case "elemento":
                        $coincide = stripos($elemento, $cadena) !== false;
                        break;

                    case "contenido":
                        $coincide = stripos($contenido, $cadena) !== false;
                        break;

                    case "combinacion":
                        // Cada clave vacía cuenta como coincidencia;
                        // las rellenas tienen que coincidir todas a la vez.
                        $ok1 = ($clave1 == "") || stripos($seccion, $clave1) !== false;
                        $ok2 = ($clave2 == "") || stripos($modo, $clave2) !== false;
                        $ok3 = ($clave3 == "") || stripos($elemento, $clave3) !== false;
                        $coincide = $ok1 && $ok2 && $ok3;
                        break;
                }

                // Si coincide, guardamos el resultado en el array $resultados
                if ($coincide) {
                    $resultados[] = array(
                        "seccion"   => $seccion,
                        "modo"      => $modo,
                        "elemento"  => $elemento,
                        "contenido" => $contenido
                    );
                }
            }
        }
    }
}

// Texto buscado, para mostrarlo en el resumen.
// En la combinación unimos las claves rellenas con implode().
if ($tipo == "combinacion") {
    $buscado = array();
    if ($clave1 != "") { $buscado[] = $clave1; }
    if ($clave2 != "") { $buscado[] = $clave2; }
    if ($clave3 != "") { $buscado[] = $clave3; }
    $textoBuscado = implode(" + ", $buscado);
} else {
    $textoBuscado = $cadena;
}

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
// para que lo que escriba el usuario no se interprete como código.

if ($error != "") {
    // Hay un error de validación
    echo "<div class='aviso'>" . $error . "</div>";

} elseif ($total == 0) {
    // La búsqueda no ha encontrado nada
    echo "<p class='resumen'>Has buscado <strong>" . htmlspecialchars($textoBuscado) . "</strong> en " . $nombresTipo[$tipo] . ".</p>";
    echo "<div class='aviso'>No hay resultados. Prueba con otra palabra o con una parte más corta.</div>";

} else {
    // Hay resultados: los mostramos en una tabla recorriendo el array con foreach
    echo "<p class='resumen'>Has buscado <strong>" . htmlspecialchars($textoBuscado) . "</strong> en " . $nombresTipo[$tipo] . ": ";
    echo "<strong>" . $total . "</strong> " . ($total == 1 ? "resultado" : "resultados") . ".</p>";

    echo "<div class='tabla'><table>";
    echo "<tr><th>Sección</th><th>Tipo</th><th>Elemento</th><th>Contenido</th></tr>";

    foreach ($resultados as $fila) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($fila["seccion"]) . "</td>";
        echo "<td>" . htmlspecialchars($fila["modo"]) . "</td>";
        echo "<td>" . htmlspecialchars($fila["elemento"]) . "</td>";
        echo "<td>" . htmlspecialchars($fila["contenido"]) . "</td>";
        echo "</tr>";
    }

    echo "</table></div>";
}
?>

    <a class="boton" href="web.html">Nueva búsqueda</a>
</main>
</body>
</html>