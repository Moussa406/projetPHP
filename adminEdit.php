<?php
$pageTitle = "Page Admin";
require_once 'includes/header.php';
require_once 'functions/functionsAdmin.php';

// Vérification si est admin
if ($_SESSION["admin"] === 1) {
} else {
    header("Location:index.php");
    exit();
}

require_once 'functions/functionsAdmin.php';

// Fonction qui check la présence des champs
function checkRequiredFields($requiredFields)
{
    foreach ($requiredFields as $field) {
        if (!isset($_GET[$field]) || empty(trim($_GET[$field]))) {
            return false;
        }
    }
    return true;
}

//Initatilsation des variables
$couleurs = [];
$jantes = [];
$motorisation = [];
$description = null;
$model = null;
$marque = null;
$type = null;
$date = null;
$prix = null;
$photos = [];

// Récupération des informations de la voiture
if (checkRequiredFields(['id', 'model', 'marque'])) {
    $id = $_GET['id'];
    $model = $_GET['model'];
    $marque = $_GET['marque'];
    
    // Récupération des détails de la voiture
    $voiture = getVoitureDetails($pdo, $id);
    if ($voiture['success']) {
        $description = $voiture['data']['description'];
        $type = $voiture['data']['id_type'];
        $date = $voiture['data']['date_sortie'];
        $prix = $voiture['data']['prix'];
        $photos = $voiture['data']['photos'];
    }
}

function chargeItemBdd($item)
{
    global $pdo;
    $items = getItem($pdo, $item);
    $itemCount = $items['data']['total'];

    //On remplie avec les valeurs
    if ($itemCount > 0) {
        $return = $items['data']['noms'];
        return $return;
    }
}

function genererBlocSelection($titre, $elements, $elementsAssocies, $type)
{
    echo "<div class='col-md-4'>";
    echo "<div class='card p-3 admin'>";
    echo "<h6 class='card-title'>$titre</h6>";
    echo "<div class='form-group'>";
    echo "<select class='form-control' name='{$type}[]' multiple>";
    foreach ($elements as $key => $value) {
        $selected = in_array($key, $elementsAssocies) ? 'selected' : '';
        echo "<option value='$key' $selected>$value</option>";
    }
    echo "</select>";
    echo "</div>";
    echo "</div>";
    echo "</div>";
}

//Chargement des données des tables
function initialisation()
{
    global $couleurs, $jantes, $motorisation, $moteursAssocies, $couleursAssocies, $jantesAssocies;
    $couleurs = chargeItemBdd("couleurs");
    $jantes = chargeItemBdd("jantes");
    $motorisation = chargeItemBdd("moteurs");
    $moteursAssocies = getVoitureOptions($pdo, $id, "voitures_moteurs", "id_moteur");
    $couleursAssocies = getVoitureOptions($pdo, $id, "voitures_couleurs", "id_couleur");
    $jantesAssocies = getVoitureOptions($pdo, $id, "voitures_jantes", "id_jante");
}

initialisation();
?>

<!-- Début du contenu de la page -->
<div class="d-flex justify-content-center align-items-center vh-100">
    <div class="card p-3 border bg-light admin">
        <h4 class="card-title">Modifier les "<b><?php echo $marque . ' ' . $model ?></b>" dans la BDD</h4>
        <div class="form-group mb-1">
            <form action="adminEditResult.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?php echo $id ?>">
                <div class="row">
                    <div class="col">
                        <div class="form-group mb-2">
                            <label class="form-label" for="inputModel">Modèle</label>
                            <input class="form-control" name="model" id="inputModel" value="<?php echo htmlspecialchars($model) ?>" required>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group mb-2">
                            <label class="form-label" for="selectMarque">Marque</label>
                            <select class="form-control" name="marque" id="selectMarque" required>
                                <?php foreach ($marques as $key => $value) : ?>
                                    <option value="<?php echo $key ?>" <?php echo $key == $marque ? 'selected' : '' ?>><?php echo $value ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group mb-2">
                            <label class="form-label" for="inputPrix">Prix</label>
                            <input class="form-control" name="lePrix" id="inputPrix" type="number" step="0.01" value="<?php echo htmlspecialchars($prix) ?>" required>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group mb-2">
                            <label class="form-label" for="selectType">Type</label>
                            <select class="form-control" name="type" id="selectType" required>
                                <?php foreach ($types as $key => $value) : ?>
                                    <option value="<?php echo $key ?>" <?php echo $key == $type ? 'selected' : '' ?>><?php echo $value ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group mb-2">
                            <label class="form-label" for="inputDate">Date de sortie</label>
                            <input class="form-control" name="date" id="inputDate" type="date" value="<?php echo htmlspecialchars($date) ?>" required>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <div class="form-group mb-2">
                            <label class="form-label" for="inputDescription">Description</label>
                            <textarea class="form-control" name="description" id="inputDescription"><?php echo htmlspecialchars($description) ?></textarea>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <div class="card container p-3 admin">
                            <h6 class="card-title">Images du véhicule</h6>
                            <?php if (!empty($photos)) : ?>
                                <div class="mb-3">
                                    <h6>Photos actuelles :</h6>
                                    <div class="row">
                                        <?php foreach ($photos as $photo) : ?>
                                            <div class="col-md-3 mb-2">
                                                <div class="card">
                                                    <img src="img/<?php echo htmlspecialchars($photo['nom']) ?>" class="card-img-top" alt="Photo du véhicule">
                                                    <div class="card-body">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="delete_photos[]" value="<?php echo $photo['id'] ?>" id="delete_photo_<?php echo $photo['id'] ?>">
                                                            <label class="form-check-label" for="delete_photo_<?php echo $photo['id'] ?>">
                                                                Supprimer
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach ?>
                                    </div>
                                </div>
                            <?php endif ?>
                            <div class="mb-3">
                                <label class="form-label">Ajouter de nouvelles photos</label>
                                <input type="file" class="form-control" name="imagesGalerie[]" id="imagesGalerie" accept="image/jpeg,image/png,image/webp" multiple>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <?php
                    genererBlocSelection("Motorisations", $motorisation, $moteursAssocies, "moteur");
                    genererBlocSelection("Couleurs", $couleurs, $couleursAssocies, "couleur");
                    genererBlocSelection("Jantes", $jantes, $jantesAssocies, "jante");
                    ?>
                </div>
                <div class="row">
                    <div class="mb-1">
                        <button type="submit" class="btn btn-primary">Valider</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Fin du contenu de la page -->
<?php
require_once 'includes/footer.php';
?>