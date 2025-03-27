<?php
// Afficher les erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Connection à la base de données
define('SECURE_ACCESS', true);
require_once 'connection.php';
$pdo = getDBConnection();

echo "<h1>Structure de la table voitures</h1>";

try {
    $sql = "SHOW COLUMNS FROM voitures";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
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
    
    // Vérification spécifique pour la colonne prix
    $sql = "SHOW COLUMNS FROM voitures LIKE 'prix'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $prixColumn = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($prixColumn) {
        echo "<h2>La colonne 'prix' existe dans la table 'voitures'</h2>";
        echo "<pre>";
        print_r($prixColumn);
        echo "</pre>";
    } else {
        echo "<h2>La colonne 'prix' N'EXISTE PAS dans la table 'voitures'</h2>";
        
        // Proposer une solution d'ajout de la colonne
        echo "<h3>Pour ajouter la colonne prix, exécutez la requête SQL suivante:</h3>";
        echo "<pre>ALTER TABLE voitures ADD COLUMN prix DECIMAL(10,2) DEFAULT 0;</pre>";
    }
    
} catch (PDOException $e) {
    echo "<h2>Erreur lors de la vérification de la structure de la table:</h2>";
    echo $e->getMessage();
}
?> 