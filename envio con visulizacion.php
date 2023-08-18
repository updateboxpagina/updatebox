<?php
include 'conexion.php';

// Obtiene los datos recién enviados desde el ESP
$temperatura = $_POST['temperatura'] ?? null;
$humedad = $_POST['humedad'] ?? null;
$barometrica = $_POST['barometrica'] ?? null;
$uv = $_POST['uv'] ?? null;

// Obtiene los últimos datos de los sensores desde la base de datos
function obtenerUltimosDatosSensores($con) {
    $consulta = "SELECT * FROM Tb_Sensores ORDER BY Id DESC LIMIT 1";
    $resultado = mysqli_query($con, $consulta);

    if ($resultado && mysqli_num_rows($resultado) > 0) {
        return mysqli_fetch_assoc($resultado);
    }

    return null;
}

// Obtiene los últimos datos de los sensores desde la base de datos
$ultimosDatos = obtenerUltimosDatosSensores($con);

// Actualiza los datos en la base de datos si es necesario
if ($temperatura !== null || $humedad !== null || $barometrica !== null || $uv !== null) {
    // Define tus umbrales aquí
    $temperaturaUmbral = 5.0;
    $humedadUmbral = 5.0;
    $barometricaUmbral = 10.0;
    $uvUmbral = 8.0;

    // Verifica si los cambios superan los umbrales
    $temperaturaCambio = abs($temperatura - $ultimosDatos['Temperatura']);
    $humedadCambio = abs($humedad - $ultimosDatos['Humedad']);
    $barometricaCambio = abs($barometrica - $ultimosDatos['Barometrica']);
    $uvCambio = abs($uv - $ultimosDatos['UV']);

    $superanUmbrales = (
        $temperaturaCambio >= $temperaturaUmbral ||
        $humedadCambio >= $humedadUmbral ||
        $barometricaCambio >= $barometricaUmbral ||
        $uvCambio >= $uvUmbral
    );

    // Inserta los datos en la base de datos si superan los umbrales
    if ($superanUmbrales) {
        date_default_timezone_set('America/Santiago');
        $fecha_actual = date("Y-m-d H:i:s");
        $consulta = "INSERT INTO Tb_Sensores(Temperatura, Humedad, Barometrica, UV, fecha_actual) VALUES ('$temperatura','$humedad','$barometrica','$uv', '$fecha_actual')";
        $resultado = mysqli_query($con, $consulta);

        if ($resultado) {
            echo "Registro en base de datos OK!";
        } else {
            echo "Falla! Registro BD " . mysqli_error($con);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoreo de Sensores</title>
    <script>
        // Función para actualizar los datos de los sensores
        function actualizarDatosSensores() {
            // Realiza una petición AJAX para obtener los datos de los sensores
            var xhttp = new XMLHttpRequest();
            xhttp.onreadystatechange = function() {
                if (this.readyState === 4 && this.status === 200) {
                    document.getElementById("sensor-data").innerHTML = this.responseText;
                }
            };
            xhttp.open("GET", "<?php echo $_SERVER['PHP_SELF']; ?>?timestamp=" + new Date().getTime(), true); // Agregar un parámetro timestamp para evitar caché
            xhttp.send();
        }

        // Actualiza los datos cada 1 segundo
        setInterval(actualizarDatosSensores, 1000);
    </script>
</head>
<body>
    <div id="sensor-data">
        <?php
        if ($temperatura !== null || $humedad !== null || $barometrica !== null || $uv !== null) {
            echo "Temperatura: {$temperatura} °C<br>";
            echo "Humedad: {$humedad} %<br>";
            echo "Presión Barométrica: {$barometrica} hPa<br>";
            echo "Radiación UV: {$uv}<br>";
        } elseif ($ultimosDatos) {
            echo "Temperatura: {$ultimosDatos['Temperatura']} °C<br>";
            echo "Humedad: {$ultimosDatos['Humedad']} %<br>";
            echo "Presión Barométrica: {$ultimosDatos['Barometrica']} hPa<br>";
            echo "Radiación UV: {$ultimosDatos['UV']}<br>";
            echo "Fecha: {$ultimosDatos['fecha_actual']}";
        } else {
            echo "No se encontraron datos de sensores.";
        }
        ?>
    </div>
</body>
</html>