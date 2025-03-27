<?php
// Afficher les erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Connection à la base de données
define('SECURE_ACCESS', true);
require_once 'connection.php';
$pdo = getDBConnection();

// Inportation des fonctions
require_once 'functions/functionsAdmin.php';

// Champs attendu pour l'ajout d'un nouveau model
$requiredFields = ['model', 'marque', 'type', 'date', 'description', 'lePrix'];

// Initialisation des variables
$model = null;
$marque = null;
$type = null;
$date = null;
$description = null;
$lePrix = null;

$max_file_size = 1 * 1024 * 1024; // 5 MB
$allowed_types = ['image/jpeg', 'image/png', 'image/webp'];

// Affectation des valeurs
function setValuesAddCar()
{
    $model = htmlspecialchars($_POST["model"] ?? '');
    $marque = filter_input(INPUT_POST, 'marque', FILTER_VALIDATE_INT);
    $type = filter_input(INPUT_POST, 'type', FILTER_VALIDATE_INT);
    $date = htmlspecialchars($_POST["date"] ?? '');
    $description = htmlspecialchars($_POST["description"] ?? '');
    $lePrix = filter_input(INPUT_POST, 'lePrix', FILTER_VALIDATE_FLOAT);
    
    if ($lePrix === null || $lePrix === false) {
        $lePrix = floatval($_POST['lePrix'] ?? 0);
    }
    
    return [
        'model' => $model,
        'marque' => $marque,
        'type' => $type,
        'date' => $date,
        'description' => $description,
        'lePrix' => $lePrix,
    ];
}

// Vérification de la présence des champs
function checkRequiredFields($requiredFields)
{
    $missingFields = [];
    foreach ($requiredFields as $field) {
        if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
            $missingFields[] = $field;
        }
    }
    return $missingFields;
}

// Ajout d'une voiture
function addNewCar($pdo, $requiredFields)
{
    $missingFields = checkRequiredFields($requiredFields);
    if (!empty($missingFields)) {
        return [
            'success' => false,
            'message' => 'Champs manquants : ' . implode(', ', $missingFields)
        ];
    }

    $data = setValuesAddCar();
    $message = ajoutVoiture(
        $pdo, 
        $data['model'],      // nom
        $data['type'],       // type 
        $data['marque'],     // marque
        $data['description'], // description
        $data['date'],       // date
        $data['lePrix']      // prix
    );

    if ($message['success']) {
        $idVoiture = intval($message['value']);
        addOptions($idVoiture, ['motorisation', 'prixmotorisation'], 'voitures_moteurs', 'id_moteur');
        addOptions($idVoiture, ['couleur', 'prixcouleur'], 'voitures_couleurs', 'id_couleur');
        addOptions($idVoiture, ['jante', 'prixjante'], 'voitures_jantes', 'id_jante');
        addImages($idVoiture);

        return [
            'success' => true,
            'message' => 'Voiture ajoutée avec succès',
            'idVoiture' => $idVoiture
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Erreur lors de l\'ajout de la voiture : ' . $message['message']
        ];
    }
}

// Ajout des options
function addOptions($idVoiture, $requiredFields, $table, $champs)
{
    global $pdo;
    
    if (isset($_POST[$requiredFields[0]]) && isset($_POST[$requiredFields[1]])) {
        $items = $_POST[$requiredFields[0]];
        $prixItems = $_POST[$requiredFields[1]];
        
        foreach ($items as $key => $value) {
            $price = $prixItems[$key] ?? 0;
            ajoutOptionVoiture($pdo, $table, $idVoiture, $champs, $value, $price);
        }
    }
}

// Ajout des images
function addImages($idVoiture)
{
    global $pdo;
    $max_file_size = 1 * 1024 * 1024; // 1 MB
    $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];

    if (isset($_FILES['imagesGalerie']) && is_array($_FILES['imagesGalerie']['name'])) {
        foreach ($_FILES['imagesGalerie']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['imagesGalerie']['error'][$key] === UPLOAD_ERR_OK) {
                $file_name = $_FILES['imagesGalerie']['name'][$key];
                $file_size = $_FILES['imagesGalerie']['size'][$key];
                $file_type = $_FILES['imagesGalerie']['type'][$key];

                if ($file_size <= $max_file_size && in_array($file_type, $allowed_types)) {
                    $new_name = uniqid() . '_' . $file_name;
                    $upload_path = 'img/' . $new_name;

                    if (move_uploaded_file($tmp_name, $upload_path)) {
                        // D'abord ajouter la photo dans la table photos
                        $photoResult = ajoutPhoto($pdo, $new_name);
                        if ($photoResult['success']) {
                            // Ensuite lier la photo à la voiture
                            ajoutPhotoVoiture($pdo, $idVoiture, $photoResult['value']);
                        }
                    }
                }
            }
        }
    }
}

// Exécution de l'ajout
$result = addNewCar($pdo, $requiredFields);

// Redirection vers la page d'administration avec un message
header("Location: admin.php?message=" . urlencode($result['message']));
exit();




function varDump($var)
{
    echo '<pre>';
    var_dump($var);
    echo '</pre>';
}


