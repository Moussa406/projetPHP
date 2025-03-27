<?php
echo '<pre>';
var_dump($_POST);
echo '</pre>';

// Connection à la base de données
define('SECURE_ACCESS', true);
require_once 'connection.php';
$pdo = getDBConnection();

$idVoiture = null;


if (isset($_POST['id']) and $_POST['id'] != "") {
    $idVoiture = $_POST['id'];
    
    // Mise à jour des informations de base de la voiture
    updateVoitureInfos($pdo, $idVoiture);
    
    if (isset($_POST['moteur'])) {
        echo "moteur";
        updateVoitureOptions($pdo, $idVoiture, 'moteur', $_POST);
    }else{
        delVoitureOptions($pdo, $idVoiture, "voitures_moteurs");
    }
    if (isset($_POST['couleur'])) {
        updateVoitureOptions($pdo, $idVoiture, 'couleur', $_POST);
    }else{
        delVoitureOptions($pdo, $idVoiture, "voitures_couleurs");
    }
    if (isset($_POST['jante'])) {
        updateVoitureOptions($pdo, $idVoiture, 'jante', $_POST);
    }else{
        delVoitureOptions($pdo, $idVoiture, "voitures_jantes");
    }
    updateVoitureDescription($pdo, $idVoiture);
    
    header("Location:admin.php");
    exit();
}

function updateVoitureDescription($pdo, $idVoiture){
    if(isset($_POST['description'])){
        $txt = $_POST['description'];
        $sql = "UPDATE  voitures
                   SET description = ? 
                   WHERE ID = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$txt, $idVoiture]);
    }
}

function updateVoitureInfos($pdo, $idVoiture) {
    echo "<h3>Debug updateVoitureInfos:</h3>";
    echo "POST data: ";
    var_dump($_POST);
    
    if (isset($_POST['model'], $_POST['marque'], $_POST['type'], $_POST['date'], $_POST['lePrix'])) {
        $sql = "UPDATE voitures 
                SET nom = ?,
                    id_type = ?,
                    id_marque = ?,
                    date_sortie = ?,
                    prix = ?
                WHERE ID = ?";
        
        echo "<br>SQL Query: " . $sql;
        echo "<br>Parameters: ";
        $params = [
            $_POST['model'],
            $_POST['type'],
            $_POST['marque'],
            $_POST['date'],
            $_POST['lePrix'],
            $idVoiture
        ];
        var_dump($params);
        
        try {
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute($params);
            echo "<br>Update result: ";
            var_dump($result);
            
            if (!$result) {
                echo "<br>PDO Error Info: ";
                var_dump($stmt->errorInfo());
            }
        } catch (PDOException $e) {
            echo "<br>PDO Exception: " . $e->getMessage();
        }
    } else {
        echo "<br>Missing required fields!";
        echo "<br>Required fields status:";
        echo "<br>model: " . isset($_POST['model']);
        echo "<br>marque: " . isset($_POST['marque']);
        echo "<br>type: " . isset($_POST['type']);
        echo "<br>date: " . isset($_POST['date']);
        echo "<br>lePrix: " . isset($_POST['lePrix']);
    }
}

function updateVoitureOptions($pdo, $idVoiture, $type, $data) {
    // Configuration des paramètres selon le type
    $config = [
        'moteur' => [
            'table' => 'voitures_moteurs',
            'id_column' => 'id_moteur',
            'prix_field' => 'prixmoteur'
        ],
        'couleur' => [
            'table' => 'voitures_couleurs',
            'id_column' => 'id_couleur',
            'prix_field' => 'prixcouleur'
        ],
        'jante' => [
            'table' => 'voitures_jantes',
            'id_column' => 'id_jante',
            'prix_field' => 'prixjante'
        ]
    ];

    if (!isset($config[$type])) {
        throw new Exception("Type d'option non valide");
    }

    $table = $config[$type]['table'];
    $id_column = $config[$type]['id_column'];
    $prix_field = $config[$type]['prix_field'];

    


    // Récupérer les options actuelles
    $sql_actuels = "SELECT $id_column FROM $table WHERE id_voiture = ?";
    $stmt = $pdo->prepare($sql_actuels);
    $stmt->execute([$idVoiture]);
    $options_actuelles = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Pour chaque option cochée
    foreach ($data[$type] as $idOption) {
        $prix = intval($data[$prix_field][$idOption]);

        if (in_array($idOption, $options_actuelles)) {
            // Mise à jour du prix si existe déjà
            $sql = "UPDATE $table 
                   SET prix = ? 
                   WHERE id_voiture = ? AND $id_column = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$prix, $idVoiture, $idOption]);
        } else {
            // Insertion si nouveau
            $sql = "INSERT INTO $table (id_voiture, $id_column, prix) 
                   VALUES (?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$idVoiture, $idOption, $prix]);
        }
    }
    
    // Supprimer les options qui ne sont plus cochées
    $options_a_garder = array_map('intval', array_keys($data[$type]));
    if (!empty($options_a_garder)) {
        $sql = "DELETE FROM $table 
                WHERE id_voiture = ? 
                AND $id_column NOT IN (" . implode(',', $options_a_garder) . ")";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$idVoiture]);
    }
    
}

function delVoitureOptions($pdo, $idVoiture, $table){
        $sql = "DELETE FROM $table WHERE id_voiture = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$idVoiture]);
}


