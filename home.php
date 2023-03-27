<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="assets/media/logo.png">
    <link rel="stylesheet" href="assets/style/style.css">
    <link href="https://fonts.googleapis.com/css?family=Proxima+Nova:400,700&display=swap" rel="stylesheet">

    <title>Accueil</title>
</head>

<body>
    <a href="projection.php">Projections</a>
    <div class="search-bar">
        <form method="POST" action="home.php">
            <label for="title-input">Titre :</label>
            <input type="text" name="txttitre" />
            
            <label for="actor-input">Acteur :</label>
            <input type="text" name="txtact" />
            <label for="director-input">Réalisateur :</label>
            <input type="text" name="txtreal" />
            <label for="public-select">Public :</label>
            <select name="cbopublic">
                <option value="" disabled selected hidden>Sélectionner un public</option>
                <?php
                $bdd = new PDO("mysql:host=localhost;dbname=bdcinevieillard-lepers;charset=utf8", "root", "");

                $req = $bdd->prepare("select * from public");
                $req->execute();
                $uneligne = $req->fetch();
                while ($uneligne!=null)
                {
                    if (isset($_POST["cbopublic"])==true && $_POST["cbopublic"]==$uneligne["nopublic"]){
                        echo ("<option value=$uneligne[nopublic] selected>$uneligne[libpublic]</option>");
                    }
                    else 
                    {
                        echo ("<option value=$uneligne[nopublic]>$uneligne[libpublic]</option>");
                    }
                    $uneligne = $req->fetch();

                }
                
                $req->closeCursor();
                $bdd=null;
             ?>
            </select>
            <label for="genre-select">Genre :</label>
            <select name="cbogenres[]" multiple>
                <?php
                $bdd = new PDO("mysql:host=localhost;dbname=bdcinevieillard-lepers;charset=utf8", "root", "");

                $req = $bdd->prepare("select * from genre");
                $req->execute();
                $uneligne = $req->fetch();
                while ($uneligne!=null)
                {
                    echo ("<option value='$uneligne[nogenre]'>$uneligne[libgenre]</option>");
                    $uneligne = $req->fetch();

                }
                
                $req->closeCursor();
                $bdd=null;
             ?>
            </select>


            <input type="submit" name="btnvalider" value="Rechercher">
        </form>
    </div>
    <?php 
    $bdd = new PDO("mysql:host=localhost;dbname=bdcinevieillard-lepers;charset=utf8", "root", "");           
    if(isset($_POST["btnvalider"])==true) {
        //Génération de la première requête dans une variable en string avec uniquement le titre, les réalisateurs et acteurs
        $requete = ("select distinct film.* from film natural join concerner where titre like'%".$_POST['txttitre']."%' and acteurs like'%".$_POST['txtact']."%'
                                 and realisateurs like'%".$_POST['txtreal']."%' ");
        //Si un public est renseigné, ajoute la recherche du public à la requête
        if(isset($_POST["cbopublic"]) == true) {
            $requete.= (" and nopublic='$_POST[cbopublic]' ");
        }
        // Pour chaques genres sélectionnés, ceux-ci sont rajoutés à la requête
        if(isset($_POST["cbogenres"]) == true) {
            $requete.= (" and nogenre IN (");
            for ($i=0;$i<count($_POST["cbogenres"]);$i++)  
            {
                $requete.= ($_POST["cbogenres"][$i].", ");
            }
            $requete = substr($requete, 0, -2).")";
        }
        // Préparation de la requête en utilisant la variable préparée auparavant
        $req = $bdd->prepare($requete);
        $req->execute();
        // Recherche pour chaque film de la base de données si ses caractéristiques correspondent à celles recherchées
        $uneligne = $req->fetch();

        if (!$uneligne){
            echo "Aucun film ne correspond à votre recherche";
        }
        while ($uneligne)
        {
            echo "<label>";
            echo "<table cellpadding='5' onclick=\"document.getElementById('form_$uneligne[nofilm]').submit();\">";
            echo "<tr>";
            echo "<td rowspan='7'><img src='assets/media/affiches/$uneligne[imgaffiche]' width=100px></td>";
            echo "<td>$uneligne[titre]</td>";
            echo "</tr>";
            echo "<tr><td>Durée : ".str_replace(":","h",date('G:i', strtotime($uneligne["duree"])))."</td></tr>";
            echo "<tr><td>$uneligne[realisateurs]</td></tr>";
            echo "<tr><td>$uneligne[acteurs]</td></tr>";
            echo "</table>";
            echo "<form id='form_$uneligne[nofilm]' method='post' action='film.php'>";
            echo "<input type='hidden' name='nofilm' value='$uneligne[nofilm]'>";
            echo "<input type='hidden' name='titre' value='".urlencode($uneligne["titre"])."'>";
            echo "</form>";
            echo "</label>";
            $uneligne = $req->fetch();
        }
        $req->closeCursor();
    }
    $bdd=null;

?>
</body>

</html>