<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subir Favicon</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <h1>Subir Favicon</h1>
    <?php
    if (isset($_GET['message'])) {
        echo '<p style="color: green;">' . htmlspecialchars($_GET['message']) . '</p>';
    }
    if (isset($_GET['error'])) {
        echo '<p style="color: red;">' . htmlspecialchars($_GET['error']) . '</p>';
    }
    ?>
    <form action="../api/upload_favicon.php" method="post" enctype="multipart/form-data">
        <label for="favicon">Seleccionar archivo de favicon:</label>
        <input type="file" name="favicon" id="favicon" accept="image/x-icon, image/png, image/jpeg">
        <input type="submit" value="Subir" name="submit">
    </form>
</body>
</html>