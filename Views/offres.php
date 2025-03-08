<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Créer une offre</title>
    <style>
        h1,h2{
            text-align: center;
            margin-bottom: 10px;
            color: rgba(0,0,0,0.5);;
            font-weight: bold;
            font-size: 1.5em;
            text-decoration: underline;
            text-transform: uppercase;
            letter-spacing: 2px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);

        }
   body {
            text-align: center;
            background-color: #ccc;
        }
        table {
            border: 1px solid black;
            border-collapse: collapse;
            background-color: white;
            margin: auto;
            width: 80%;
            margin-bottom: 20px;
            font-size: 1.2em;
            margin-top: 20px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
            padding: 5px;
            border-radius: 10px;
            box-shadow: 2px 2px 4px rgba(0,0,0,0.5);

        }
        th {
            border: 1px solid black;
            color: black;
            padding: 5px;
            background-color: #f2f2f2;
            text-align: left;
            font-weight: bold;
            box-shadow: 2px 2px 4px rgba(0,0,0,0.5);
            letter-spacing: 1px;
           
            text-transform: uppercase;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }
        td, tr {
            border: 1px solid black;
            padding: 5px;
        }
      
    </style>
</head>
<body>

<h2>Créer une nouvelle offre</h2>

<?php
require_once __DIR__ . "/../Models/Offre.php";

// Récupérer toutes les offres directement ici
try {
    $conn = (new Connect())->getConnection(); // Connexion à la base de données
    if (!$conn) {
        echo "Connexion à la base de données échouée.";
        return false;
    }

    // Exécuter la requête pour récupérer toutes les offres
    $stmt = $conn->prepare("SELECT * FROM offres");
    $stmt->execute();
    $rest = $stmt->fetchAll(PDO::FETCH_ASSOC); // Récupérer toutes les lignes sous forme de tableau associatif
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
    return false;
}


// Affichage de tous les offres
echo"<h3>Affichage des informations des offres</h3>";
  // Exécution de la requête
$rek = "SELECT * FROM offres";
$res = $conn->query($rek);

if ($res) {
    echo "Nous avons " . $res->rowCount() . " lignes<br><br>";
    $rest = $res->fetchAll(PDO::FETCH_ASSOC);

    echo "<table border='1'>";
    echo "<thead>
            <tr>
                <th>id</th>
                <th>voiture_id</th>
                <th>description</th>
                <th>date_debut</th>
                <th>date_fin</th>
                <th>prix_special</th>
                <th>Actions</th>
            </tr>

          </thead>";
    echo "<tbody>";

    foreach ($rest as $li) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($li['id']) . "</td>";
        echo "<td>" . htmlspecialchars($li['voiture_id']) . "</td>";
        echo "<td>" . htmlspecialchars($li['description']) . "</td>";
        echo "<td>" . htmlspecialchars($li['date_debut']) . "</td>";
        echo "<td>" . htmlspecialchars($li['date_fin']) . "</td>";
        echo "<td>" . htmlspecialchars($li['prix_special']) . "</td>";

       
    }
    echo "<td>
    <form action='detailoffre.php' method='post'>
				<input hidden name='detail' value='detail'>
				<input type='submit' value='detail' name='detail'/>
			</form>

        </td>";
echo "</tr>";

    }
    echo "</tbody>";
    echo "</table>";
 //else
  //{
    //echo "Erreur lors de l'exécution de la requête.";
//}
?>

</body>

</html>