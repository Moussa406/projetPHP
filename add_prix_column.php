<?php
// Afficher les erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Connection à la base de données
define('SECURE_ACCESS', true);
require_once 'connection.php';
$pdo = getDBConnection();

echo "<h1>Ajout de la colonne prix à la table voitures</h1>";

try {
    // Vérifier si la colonne existe déjà
    $sql = "SHOW COLUMNS FROM voitures LIKE 'prix'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $prixColumn = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($prixColumn) {
        echo "<h2>La colonne 'prix' existe déjà dans la table 'voitures'</h2>";
        echo "<pre>";
        print_r($prixColumn);
        echo "</pre>";
    } else {
        // Ajouter la colonne
        $sql = "ALTER TABLE voitures ADD COLUMN prix DECIMAL(10,2) DEFAULT 0";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute();
        
        if ($result) {
            echo "<h2>La colonne 'prix' a été ajoutée avec succès à la table 'voitures'</h2>";
            // Vérifier la structure mise à jour
            $sql = "SHOW COLUMNS FROM voitures LIKE 'prix'";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $prixColumn = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo "<pre>";
            print_r($prixColumn);
            echo "</pre>";
        } else {
            echo "<h2>Erreur lors de l'ajout de la colonne 'prix'</h2>";
            echo "<pre>";
            print_r($stmt->errorInfo());
            echo "</pre>";
        }
    }
    
    // Vérifier la structure complète de la table
    $sql = "SHOW COLUMNS FROM voitures";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Structure complète de la table voitures:</h3>";
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>" . $column['Field'] . "</td>";
        echo "<td>" . $column['Type'] . "</td>";
        echo "<td>" . $column['Null'] . "</td>";
        echo "<td>" . $column['Key'] . "</td>";
        echo "<td>" . ($column['Default'] ?? 'NULL') . "</td>";
        echo "<td>" . $column['Extra'] . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
} catch (PDOException $e) {
    echo "<h2>Erreur lors de la modification de la structure de la table:</h2>";
    echo $e->getMessage();
}

echo "<p><a href='adminAdd.php'>Retour au formulaire d'ajout</a></p>";
?> 