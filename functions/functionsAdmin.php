<?php
function getItem($pdo, $item)
{
    try {
        $sql = "SELECT COUNT(*) OVER() as total, nom as nom, id as id FROM {$item}";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($result)) {
            return [
                'sucess' => true,
                'message' => 'requette vide',
                'data' => ['total' => 0, 'noms' => []]
            ];
        }

        $noms = array_combine(
            array_column($result, 'id'),
            array_column($result, 'nom')
        );

        $response = [
            'sucess' => true,
            'message' => 'requette ok',
            'data' => ['total' => $result[0]['total'], 'noms' => $noms]
        ];
    } catch (PDOException $e) {
        $response = [
            'sucess' => false,
            'message' => 'requette KO',
            'data' => $e->getCode()
        ];
    }

    return $response;
}

function ajoutValue($pdo, $item, $values)
{
    // Construire les placeholders pour les valeurs
    $placeholders = implode(',', array_fill(0, count($values), '(?)'));

    // Construire la requête SQL avec le nom de la table dynamique
    $query = "INSERT INTO $item (nom) VALUES $placeholders";

    // Préparer la requête
    $stmt = $pdo->prepare($query);

    // Exécuter la requête avec les valeurs
    $stmt->execute($values);

    return $pdo;
}

function ajoutVoiture($pdo, $nom, $type, $marque, $description, $date, $lePrix)
{
    echo "<h3>Dans ajoutVoiture (functionsAdmin.php):</h3>";
    echo "nom: $nom<br>";
    echo "type: $type<br>";
    echo "marque: $marque<br>";
    echo "date: $date<br>";
    echo "prix: $lePrix<br>";
    echo "Prix après conversion: " . floatval($lePrix) . "<br>";
    
    try {
        // Préparation de la requête
        $sql = "INSERT INTO voitures (nom, id_type, id_marque, description, date_sortie, prix) VALUES (:nom, :id_type, :id_marque, :description, :date_sortie, :lePrix)";
        echo "Requête SQL: $sql<br>";
        
        $stmt = $pdo->prepare($sql);
        
        // Paramètres pour l'exécution
        $params = [
            ':nom' => $nom,
            ':id_type' => $type,
            ':id_marque' => $marque,
            ':description' => $description,
            ':date_sortie' => $date,
            ':lePrix' => floatval($lePrix)
        ];
        
        echo "<pre>Paramètres d'exécution: ";
        print_r($params);
        echo "</pre>";
        
        // Exécution de la requête avec les paramètres
        $result = $stmt->execute($params);
        
        echo "Résultat de l'exécution: " . ($result ? "SUCCÈS" : "ÉCHEC") . "<br>";
        
        if ($result) {
            $lastId = $pdo->lastInsertId();
            echo "Dernier ID inséré: $lastId<br>";
            
            $response = [
                'success' => true,
                'message' => "L'ajout de voiture a réussi",
                'value' => $lastId
            ];
        } else {
            $errorInfo = $stmt->errorInfo();
            echo "<pre>Erreur SQL: ";
            print_r($errorInfo);
            echo "</pre>";
            
            $response = [
                'success' => false,
                'message' => "L'ajout de voiture a échoué: " . $errorInfo[2]
            ];
        }
    } catch (PDOException $e) {
        echo "Exception PDO: " . $e->getMessage() . "<br>";
        
        $response = [
            'success' => false,
            'message' => "Erreur BDD : " . $e->getMessage()
        ];
    }
    
    echo "<pre>Réponse finale: ";
    print_r($response);
    echo "</pre>";
    
    return $response;
}

function ajoutOptionVoiture($pdo, $table, $idVoiture, $nomOption, $idOption, $prix)
{
    try {
        // Préparation de la requête
        $sql = "INSERT INTO {$table} (id_voiture, {$nomOption}, prix) VALUES (:idVoiture, :nomOption, :prix)";
        $stmt = $pdo->prepare($sql);

        // Exécution de la requête avec les paramètres
        $result = $stmt->execute([
            ':idVoiture' => $idVoiture,
            ':nomOption' => $idOption,
            ':prix' => $prix
        ]);

        if ($result) {
            $response = [
                'success' => true,
                'message' => "L'ajout de l'option a réussi"
            ];
            //$message = true;
        } else {
            $response = [
                'success' => false,
                'message' => "L'ajout de l'option a échoué"
            ];
        }
    } catch (PDOException $e) {
        $response = [
            'success' => false,
            'message' => "Erreur BDD : " . $e->getMessage()
        ];
    }
    return $response;
}

function ajoutPhoto($pdo, $name)
{
    echo "j'ajoute photo";
    try {
        // Préparation de la requête
        $sql = "INSERT INTO photos (nom) VALUES (:nom)";
        $stmt = $pdo->prepare($sql);

        // Exécution de la requête avec les paramètres
        $result = $stmt->execute([':nom' => $name]);

        if ($result) {
            $response = [
                'success' => true,
                'message' => "L'ajout de la photo a réussi",
                'value' => $pdo->lastInsertId()
            ];
            //$message = true;
        } else {
            $response = [
                'success' => false,
                'message' => "L'ajout de la photo a échoué"
            ];
        }
    } catch (PDOException $e) {
        $response = [
            'success' => false,
            'message' => "Erreur BDD : " . $e->getMessage()
        ];
    }
    return $response;
}

function ajoutPhotoVoiture($pdo, $idVoiture, $idPhoto)
{
    try {
        // Préparation de la requête
        $sql = "INSERT INTO voitures_photos (id_voiture, id_photo) VALUES (:idVoiture, :idPhoto)";
        $stmt = $pdo->prepare($sql);

        // Exécution de la requête avec les paramètres
        $result = $stmt->execute([
            ':idVoiture' => $idVoiture,
            ':idPhoto' => $idPhoto
        ]);

        if ($result) {
            $response = [
                'success' => true,
                'message' => "L'ajout a réussi"
            ];
            //$message = true;
        } else {
            $response = [
                'success' => false,
                'message' => "L'ajout a échoué"
            ];
        }
    } catch (PDOException $e) {
        $response = [
            'success' => false,
            'message' => "Erreur BDD : " . $e->getMessage()
        ];
    }
    return $response;
}

function getVoitures($pdo)
{
    try {
        $sql = "SELECT COUNT(*) OVER() as total, 
                voitures.ID, 
                voitures.nom as nom_voiture, 
                marques.nom as nom_marque 
                FROM voitures 
                INNER JOIN marques ON voitures.id_marque = marques.ID";

        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($result)) {
            return [
                'success' => true,
                'message' => 'requête vide',
                'data' => ['total' => 0, 'items' => []]
            ];
        }

        $response = [
            'success' => true,
            'message' => 'requête ok',
            'data' => [
                'total' => $result[0]['total'],
                'items' => $result
            ]
        ];
    } catch (PDOException $e) {
        $response = [
            'success' => false,
            'message' => 'requête KO',
            'data' => $e->getCode()
        ];
    }

    return $response;
}

function deleteVoiture($pdo, $id)
{
    try {
        $sql = "DELETE FROM voitures WHERE ID = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $response = [
            'success' => true,
            'message' => 'Voiture supprimée avec succès',
            'data' => $id
        ];
    } catch (PDOException $e) {
        $response = [
            'success' => false,
            'message' => 'Erreur lors de la suppression',
            'data' => $e->getCode()
        ];
    }

    return $response;
}


function getItemAndPrice($pdo, $table, $champs, $idVoiture)
{
    try {
        $sql = "SELECT {$champs}, prix FROM {$table} WHERE id_voiture = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$idVoiture]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return [
            'success' => true,
            'message' => 'Données récupérées avec succès',
            'data' => $result
        ];
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => 'Erreur : ' . $e->getMessage(),
            'data' => []
        ];
    }
}

function getDescription($pdo, $id){
    try{
        $sql = "SELECT description FROM voitures WHERE ID = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if($result){
            return[
                'success' => true,
                'message' => 'Données récupérées avec succès',
                'data' => $result
            ];
        }else{
            return[
                'success' => false,
                'message' => 'Rien de trouvé'
            ];
        }
    }catch(PDOException $e){
        return [
            'success' => false,
            'message' => 'Erreur : ' . $e->getMessage(),
            'data' => []
        ];
    }
}

function getVoitureDetails($pdo, $id) {
    try {
        // Récupération des détails de la voiture
        $sql = "SELECT v.*, 
                GROUP_CONCAT(DISTINCT p.ID, ':', p.nom) as photos
                FROM voitures v
                LEFT JOIN voitures_photos vp ON v.ID = vp.id_voiture
                LEFT JOIN photos p ON vp.id_photo = p.ID
                WHERE v.ID = ?
                GROUP BY v.ID";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            // Traitement des photos
            $photos = [];
            if (!empty($result['photos'])) {
                $photosList = explode(',', $result['photos']);
                foreach ($photosList as $photo) {
                    $photoData = explode(':', $photo);
                    if (count($photoData) == 2) {
                        $photos[] = [
                            'id' => $photoData[0],
                            'nom' => $photoData[1]
                        ];
                    }
                }
            }
            unset($result['photos']);
            $result['photos'] = $photos;
            
            return [
                'success' => true,
                'message' => 'Détails de la voiture récupérés avec succès',
                'data' => $result
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Voiture non trouvée'
            ];
        }
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => 'Erreur lors de la récupération des détails : ' . $e->getMessage()
        ];
    }
}

function getVoitureOptions($pdo, $idVoiture, $table, $idColumn) {
    try {
        $sql = "SELECT $idColumn, prix FROM $table WHERE id_voiture = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$idVoiture]);
        
        // Récupérer tous les IDs des options et leurs prix
        $options = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $options[$row[$idColumn]] = $row['prix'];
        }
        
        return $options;
    } catch (PDOException $e) {
        return [];
    }
}
