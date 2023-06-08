<?php
if (isset($_POST['submit'])) {
    $nombre = $_POST["nombre"];
    $apellido = $_POST["apellido"];
    $email = $_POST["email"];


    // Comprobar si los datos están vacíos
    if (empty($nombre) || empty($apellido) || empty($email)) {
        echo "Los campos no pueden estar vacíos, por favor vuelva atrás.";
        exit();
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo "El correo ingresado no es válido, por favor vuelve atrás y revísalo.";
            exit();
    }

    // Establecer la conexión con la base de datos
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "cursosql";

    $conn = new mysqli($servername, $username, $password, $dbname);

    // Verificar la conexión
    if ($conn->connect_error) {
        die("Error de conexión: " . $conn->connect_error);
    }

    // Obtener las longitudes máximas permitidas en la tabla
    $max_length_nombre = obtenerMaxLongitud($conn, "usuarios", "nombre");
    $max_length_apellido = obtenerMaxLongitud($conn, "usuarios", "apellido");
    $max_length_email = obtenerMaxLongitud($conn, "usuarios", "email");

    $campos_excedidos = array();

    // Validar las longitudes de los datos
    if (strlen($nombre) > $max_length_nombre) {
        $campos_excedidos[] = "Nombre (Límite: $max_length_nombre caracteres)";
    }
    if (strlen($apellido) > $max_length_apellido) {
        $campos_excedidos[] = "Apellido (Límite: $max_length_apellido caracteres)";
    }
    if (strlen($email) > $max_length_email) {
        $campos_excedidos[] = "Correo electrónico (Límite: $max_length_email caracteres)";
    }

    if (!empty($campos_excedidos)) {
        echo "<p>Error: Los siguientes campos exceden su longitud máxima:</p>";
        foreach ($campos_excedidos as $campo) {
            echo "<p>- $campo</p>";
        }
        echo "<p>Vuelva atrás y revise los campos excedidos antes de volver a enviar.</p>";
    } else {
        // Consulta SQL para insertar los datos
        $sql = "INSERT INTO usuarios (nombre, apellido, email) VALUES ('$nombre', '$apellido', '$email')";

        // Ejecutar la consulta SQL
        if ($conn->query($sql) === TRUE) {
            echo "<h1>Formulario enviado correctamente<h1>";
            echo "<h3>Los datos ingresados son: </h3>";
            echo "<p>Nombre: " . $nombre . "</p>";
            echo "<p>Apellido: " . $apellido . "</p>";
            echo "<p>Correo electrónico: " . $email . "</p>";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }

    // Cerrar la conexión
    $conn->close();
}

// Función para obtener la longitud máxima de un campo en una tabla
function obtenerMaxLongitud($conn, $tabla, $campo) {
    $sql = "SELECT CHARACTER_MAXIMUM_LENGTH FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '$tabla' AND COLUMN_NAME = '$campo'";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row["CHARACTER_MAXIMUM_LENGTH"];
    }
    return "No se pudo obtener la longitud máxima para el campo especificado"; // Mensaje de error
}
?>
