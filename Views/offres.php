
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Créer une offre</title>
</head>
<body>

<h2>Créer une nouvelle offre</h2>

<form action="../Controller/recupoffres.php" method="POST">

     <div class="input">
    <input type="hidden" name="voiture_id" value="1"> 

    <label for="voiture_id">Choisissez une voiture</label>
    <select id="voiture_id" name="voiture_id">
        <option value="1">Peugeot</option>
        <option value="2">audi</option>
        <option value="3">BMW</option>
        <option value="4">testa</option>
        <option value="5">ferrari</option>
        <option value="6">mercedes</option>
    </select>
    </div>
   
    <label for="prix">description</label>
    <div class="input">
    <input type="text" name="description" required>

    </div>



    <label for="prix">date debut</label>
    <div class="input">
    <input type="date" name="date_debut" required>
    </div>

    
    <label for="prix">date fin</label>
    <div class="input">
    <input type="date" name="date_fin" required>
    </div>
    
    <label for="prix">prix special</label>
    <div class="input">
        <input type="number" name="prix_special" required>
    </div>


    <div class="input">

      <input type="submit" name="valider" value="ajouter" />

      <input type="submit" name="valider" value="Annuler" />

    </div>
</form>

</body>
</html>
