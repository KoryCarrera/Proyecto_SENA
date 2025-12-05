<?php require_once "../../controllers/checkSession.php"; ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cerrar sesion</title>
</head>

<body>
    <form action="../../controllers/logout.php" method="POST">
        <button type="submit" name="logout" value="logout">Cerrar Sesion</button>
        <input type="hidden" name="csrf_token" id="csrf_token" value="<?php echo htmlspecialchars($token); ?>">
    </form>
</body>

</html>