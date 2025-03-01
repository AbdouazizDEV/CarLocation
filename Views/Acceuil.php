<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    
<!-- message de bienvenue -->
 Bienvenue <?php echo $_SESSION['user']['prenom'];?>!

<!-- formulaire de déconnexion -->
<form method="POST" action="../Controller/Authentication.php">
    <button type="submit" name="logout">Se d&eacute;connecter</button>
</form>
</body>
</html>