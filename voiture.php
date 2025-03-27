<?php
$pageTitle = "Description voiture";
require_once 'includes/header.php';


$id_voiture = $_GET['idVoiture']; // Correction du paramètre

// Récupération du prix de la voiture
$pdo = getDBConnection();
$sql = "SELECT prix FROM voitures WHERE ID = :id_voiture";
$stmt = $pdo->prepare($sql);
$stmt->execute(['id_voiture' => $id_voiture]);
$prixVoiture = $stmt->fetch(PDO::FETCH_ASSOC)['prix'] ?? 0;

// Débogage du prix
echo "<!-- Prix de la voiture: " . $prixVoiture . " -->";

//Recupere les informations des voitures depuis la page index.php
function getVoitureCaracteristiques($id_voiture, $caracteristique) {
    try {
        $pdo = getDBConnection();
        
        $tables = [
            'couleur' => ['table' => 'voitures_couleurs', 'join' => 'couleurs', 'id' => 'id_couleur'],
            'jante' => ['table' => 'voitures_jantes', 'join' => 'jantes', 'id' => 'id_jante'],
            'moteur' => ['table' => 'voitures_moteurs', 'join' => 'moteurs', 'id' => 'id_moteur']
        ];
        
        if (!isset($tables[$caracteristique])) {
            return ['error' => "Caractéristique invalide."];
        }
        
        $table = $tables[$caracteristique]['table'];
        $join = $tables[$caracteristique]['join'];
        $id = $tables[$caracteristique]['id'];
        
        $sql = "SELECT vc.$id, c.nom, vc.prix FROM $table vc INNER JOIN $join c ON vc.$id = c.id WHERE vc.id_voiture = :id_voiture";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id_voiture' => $id_voiture]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return ['error' => "Erreur SQL : " . $e->getMessage()];
    }
}

//Récupération de la partie photo depuis la page indexp.php
function getCarPhotos($id_voiture) {
    try {
        $pdo = getDBConnection();
        
        $sql = "SELECT p.nom AS photo FROM voitures_photos vp 
                INNER JOIN photos p ON vp.id_photo = p.ID 
                WHERE vp.id_voiture = :id_voiture";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id_voiture' => $id_voiture]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // 🔥 Récupère toutes les images (pas juste une)
    } catch (PDOException $e) {
        return ['error' => "Erreur SQL : " . $e->getMessage()];
    }
}
$couleurs = getVoitureCaracteristiques($id_voiture, 'couleur');
$jantes = getVoitureCaracteristiques($id_voiture, 'jante');
$moteurs = getVoitureCaracteristiques($id_voiture, 'moteur');
$photos = getCarPhotos($id_voiture);

?>
<div id="pageVoiture">
    <div class="container min-vh-100 d-flex justify-content-center align-items-center">
        <div class="row w-75 d-flex align-items-stretch">
            <!-- Colonne gauche : Carrousel -->
            <div class="col-md-6">
                <div class="card p-3 w-100 h-100 border bg-light">
                    <div id="carrouselVoiture" class="carousel slide" data-bs-ride="carousel">
                        <!-- Indicateurs -->
                        <div class="carousel-indicators">
                            <?php if (!empty($photos) && is_array($photos)) : ?>
                                <?php foreach ($photos as $index => $photo) : ?>
                                    <button type="button" 
                                            data-bs-target="#carrouselVoiture" 
                                            data-bs-slide-to="<?= $index ?>" 
                                            <?= $index === 0 ? 'class="active"' : '' ?>>
                                    </button>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <!-- Images du carrousel -->
                        <div class="carousel-inner">
                            <?php if (!empty($photos) && is_array($photos)) : ?>
                                <?php foreach ($photos as $index => $photo) : ?>
                                    <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                        <img src="img/<?= htmlspecialchars($photo['photo']) ?>" 
                                             alt="Voiture" 
                                             class="voiture-photo d-block w-100 cursor-pointer"
                                             data-bs-toggle="modal"
                                             data-bs-target="#imageModal"
                                             onclick="updateModalImage(this.src)">
                                    </div>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <div class="carousel-item active">
                                    <img src="img/default.jpg" 
                                         alt="Photo non disponible" 
                                         class="voiture-photo d-block w-100">
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Boutons de navigation -->
                        <button class="carousel-control-prev" type="button" data-bs-target="#carrouselVoiture" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Précédent</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#carrouselVoiture" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Suivant</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Colonne droite : Personnalisation (inchangée) -->
            <div class="col-md-6">
                <div class="card p-4 w-100 h-100 border bg-light">
                    <h2 class="mb-3 text-center">Personnalisez votre voiture</h2>
                    <label for="couleur" class="form-label">Couleur :</label>
                    <select id="couleur" class="form-select mb-2">
                        <option value="" disabled selected>Sélectionnez une couleur</option>
                        <?php foreach ($couleurs as $couleur) : ?>
                            <option value="<?= $couleur['id_couleur'] ?>" data-price="<?= $couleur['prix']; ?>">
                                <?= $couleur['nom'] . ' (+ ' . $couleur['prix'] . '€)'; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <label for="jantes" class="form-label">Jantes :</label>
                    <select id="jantes" class="form-select mb-2">
                        <option value="" disabled selected>Sélectionnez des jantes</option>
                        <?php foreach ($jantes as $jante) : ?>
                            <option value="<?= $jante['id_jante'] ?>" data-price="<?= $jante['prix']; ?>">
                                <?= $jante['nom'] . ' (+ ' . $jante['prix'] . '€)'; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <label for="motorisation" class="form-label">Motorisation :</label>
                    <select id="motorisation" class="form-select mb-2">
                        <option value="" disabled selected>Sélectionnez une motorisation</option>
                        <?php foreach ($moteurs as $moteur) : ?>
                            <option value="<?= $moteur['id_moteur'] ?>" data-price="<?= $moteur['prix']; ?>">
                                <?= $moteur['nom'] . ' (+ ' . $moteur['prix'] . '€)'; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <div id="panier" class="mt-3"></div>
                    <!--<button id="ajouter" class="btn btn-primary w-100 mt-3">Ajouter au panier</button> -->
                    <h3 class="text-center mt-3">Total: <span id="total">0</span>€</h3>
                </div>
            </div>
        </div>
        <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body p-0">
                <img id="modalImage" src="" alt="Image agrandie" class="w-100">
            </div>
        </div>
    </div>
</div>




<!-- <div class="avis-section">
    <h2>Avis clients</h2>
    <form action="ajouter_avis.php" method="POST">
        <input type="text" name="nom" placeholder="Votre nom" required>
        <textarea name="commentaire" placeholder="Votre commentaire" required></textarea>
        <select name="note" required>
            <?php for($i = 1; $i <= 5; $i++): ?>
                <option value="<?= $i ?>"><?= $i ?> étoiles</option>
            <?php endfor; ?>
        </select>
        <button type="submit">Publier l'avis</button>
    </form>
</div> -->
<script>
    // Débogage du prix dans la console
    console.log('Prix de la voiture:', <?php echo $prixVoiture; ?>);
    window.PRIX_BASE = <?php echo $prixVoiture; ?>;
</script>
<script src="script.js"></script>
<?php require_once 'includes/footer.php'; ?>
