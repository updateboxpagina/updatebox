<?php

include 'conexion.php';

if ($con) {
    $temperatura = $_POST['temperatura'] ?? null;
    $humedad = $_POST['humedad'] ?? null;
    $barometrica = $_POST['barometrica'] ?? null;
    $uv = $_POST['uv'] ?? null;

    if ($temperatura !== null || $humedad !== null || $barometrica !== null || $uv !== null) {
        date_default_timezone_set('America/Bogota');
        $fecha_actual = date("Y-m-d H:i:s");

        $consulta = "SELECT * FROM tb_sensores ORDER BY Id DESC LIMIT 1"; // Obtener el último registro
        $resultado = mysqli_query($con, $consulta);

        if ($resultado) {
            $ultimaFila = mysqli_fetch_assoc($resultado);

            $temperaturaUmbral = 5.0; // Cambia este valor según tu necesidad
            $humedadUmbral = 5.0; // Cambia este valor según tu necesidad
            $barometricaUmbral = 10.0; // Cambia este valor según tu necesidad
            $uvUmbral = 8.0; // Cambia este valor según tu necesidad

            $temperaturaCambio = abs($temperatura - $ultimaFila['Temperatura']);
            $humedadCambio = abs($humedad - $ultimaFila['Humedad']);
            $barometricaCambio = abs($barometrica - $ultimaFila['Barometrica']);
            $uvCambio = abs($uv - $ultimaFila['UV']);

            if (
                $temperaturaCambio >= $temperaturaUmbral ||
                $humedadCambio >= $humedadUmbral ||
                $barometricaCambio >= $barometricaUmbral ||
                $uvCambio >= $uvUmbral
            ) {
                $consulta = "INSERT INTO tb_sensores(Temperatura, Humedad, Barometrica, UV, fecha_actual) VALUES ('$temperatura','$humedad','$barometrica','$uv', '$fecha_actual')";
                $resultado = mysqli_query($con, $consulta);

                if ($resultado) {
                    echo "Registro en base de datos OK!";
                } else {
                    echo "Falla! Registro BD " . mysqli_error($con);
                }
            } else {
                echo "No hay cambios significativos en los datos.";
            }
        } else {
            echo "Falla! No se pudo obtener el último registro.";
        }
    } else {
        echo "No se recibieron datos válidos.";
    }
} else {
    echo "Falla! Conexion con Base de datos";
}

?>
