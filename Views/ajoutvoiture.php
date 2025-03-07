<!DOCTYPE html>
<html>

<head>


</head>

<body>
  <h1>Ajouter une Voiture</h1>

  <form method="POST" action="../Controller/recupvoiture.php" enctype="multipart/form-data">



    <div class="label">marque</div>
    <div class="input">
      <select name="marque">
        <option value="Peugeot">Peugeot<option>
        <option value="audi">audi<option>
        <option value="BMW">BMW <option>
        <option value="tesla">testa<option>
        <option value="ferrari">ferrari<option>
        <option value="mercedes">mercedes<option>
      </select>
    </div>
    <br>
    <div class="label">modele</div>
    <div class="input">
      <select name="modele">

        <optgroup label=Peugeot>
          <option value="peugeot-2008">peugeot-2008<option>
          <option value="peugeot-208">peugeot-208<option>
          <option value="peugeot-3008">peugeot-3008<option>
          <option value="peugeot-301">peugeot-301 <option>
          <option value="peugeot-308">peugeot-308 <option>
        </optgroup>

        <optgroup label=audi>

          <option value="Audi A3">Audi A3 <option>
          <option value="Audi Q3">Audi Q3 <option>
          <option value="Audi A4">Audi A4 <option>
          <option value="Audi TT">Audi TT <option>
          <option value="Audi A5 Sportback ">Audi A5 Sportback <option>
        </optgroup>

        <optgroup label=BMW>

          <option value="Mini ">Mini  <option>
          <option value=" Rolls-Royce"> Rolls-Royce <option>
          <option value="BMW iX1">BMW iX1 <option>
          <option value="Bmw Ix2">Bmw Ix2 <option>
          <option value="Bmw I7">Bmw I7 <option>
        </optgroup>
        <optgroup label=tesla>

          <option value="Tesla Model S">Tesla Model S <option>
          <option value="Tesla Model 3 ">Tesla Model 3 <option>
          <option value="Tesla Model X">Tesla Model X<option>
          <option value="Tesla Model Y">Tesla Model Y<option>
          <option value="Tesla Cybertruck">Tesla Cybertruck <option>
        </optgroup>
        <optgroup label=ferreri>
          <option value="Ferrari 12cilindri">Ferrari 12cilindri<option>
          <option value="Ferrari 296">Ferrari 296 <option>
          <option value="Ferrari 488">Ferrari 488 <option>
          <option value="Ferrari Daytona Sp3">Ferrari Daytona Sp3 <option>
          <option value="Ferrari 812 Gts">Ferrari 812 Gts <option>
        </optgroup>

        <optgroup label=mercedes>

          <option value="EQE Berline">EQE Berline <option>
          <option value="Classe A Berline">Classe A Berline <option>
          <option value="Classe E Berline">Classe E Berline <option>
          <option value="Classe S">Classe S <option>
          <option value="Classe S Limousine">Classe S Limousine <option>
        </optgroup>

      </select>
    </div>
    <br>
    <br>
    <div class="label">annee</div>
    <div class="input">
      <input type="date" name="annee" id='annee' value="1" />
    </div>
    <br>
    <div class="label">prix de location</div>
    <div class="input">
      <input type="number" name="prix_location" />
    </div>
    <br>

    <div class="label">code</div>
    <div class="input">
      <input type="text" name="code" id='code' />
    </div>



    <div class="label">categorie</div>

    <div class="input">
      <select name="categorie">
        <option value="categorie A">categorie A<option>
        <option value="categorie F">categorie F <option>
        <option value=" categorie E"> categorie E <option>
        <option value="categorie B">categorie B<option>
        <option value="categorie S ">categorie S<option>
        <option value="categorie C">categorie C <option>
      </select>
    </div>

    <div class="label">DESCRIPTION</div>
    <div class="input">
      <input type="texte" name="description" id="description" />
    </div>
    <br>
    <div class="label">Images</div>
    <input type="file" name="images[]" accept="image/*" multiple />


    <br>
    <br>

    <div class="input">

      <input type="submit" name="valider" value="ajouter" />

      <input type="submit" name="valider" value="Annuler" />

    </div>

  </form>
</body>