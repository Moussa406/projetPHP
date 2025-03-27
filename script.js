function updateModalImage(src) {
    document.getElementById('modalImage').src = src;
}

// Ajout de la navigation avec les flèches du clavier dans le modal
document.addEventListener('keydown', function(event) {
    if (document.querySelector('#imageModal.show')) {
        if (event.key === 'ArrowLeft') {
            document.querySelector('#carrouselVoiture .carousel-control-prev').click();
            const newSrc = document.querySelector('#carrouselVoiture .carousel-item.active img').src;
            updateModalImage(newSrc);
        }
        if (event.key === 'ArrowRight') {
            document.querySelector('#carrouselVoiture .carousel-control-next').click();
            const newSrc = document.querySelector('#carrouselVoiture .carousel-item.active img').src;
            updateModalImage(newSrc);
        }
        if (event.key === 'Escape') {
            document.querySelector('#imageModal .btn-close').click();
        }
    }
});

// Sélection des éléments du DOM
const couleurSelect = document.getElementById('couleur');
const jantesSelect = document.getElementById('jantes');
const motorisationSelect = document.getElementById('motorisation');
const totalSpan = document.getElementById('total');
const ajouterButton = document.getElementById('ajouter');
const panierDiv = document.getElementById('panier');

// État du panier
let panier = {
    couleur: null,
    jantes: null,
    motorisation: null
};

// Fonction pour mettre à jour le total
function updateTotal() {
    // Débogage du prix de base
    console.log('PRIX_BASE dans updateTotal:', window.PRIX_BASE);
    
    let total = parseFloat(window.PRIX_BASE) || 0;
    
    // Ajouter le prix des options sélectionnées
    if (panier.couleur) {
        total += parseFloat(panier.couleur.prix) || 0;
    }
    if (panier.jantes) {
        total += parseFloat(panier.jantes.prix) || 0;
    }
    if (panier.motorisation) {
        total += parseFloat(panier.motorisation.prix) || 0;
    }

    // Mettre à jour l'affichage du total
    totalSpan.textContent = total.toLocaleString();
    return total;
}

// Fonction pour retirer une option
function removeOption(type) {
    panier[type] = null;
    
    // Réinitialiser le select correspondant
    switch(type) {
        case 'couleur':
            couleurSelect.selectedIndex = 0;
            break;
        case 'jantes':
            jantesSelect.selectedIndex = 0;
            break;
        case 'motorisation':
            motorisationSelect.selectedIndex = 0;
            break;
    }
    
    updateTotal();
    afficherPanier();
    sauvegarderPanier();
}

// Fonction pour mettre à jour le panier
function updatePanier() {
    // Mise à jour du panier avec les options sélectionnées
    panier = {
        couleur: couleurSelect.selectedIndex > 0 && couleurSelect.value !== "" ? {
            id: couleurSelect.value,
            nom: couleurSelect.selectedOptions[0].text.split(' (+')[0], // Enlever le prix du nom
            prix: parseFloat(couleurSelect.selectedOptions[0].dataset.price || 0)
        } : null,
        jantes: jantesSelect.selectedIndex > 0 && jantesSelect.value !== "" ? {
            id: jantesSelect.value,
            nom: jantesSelect.selectedOptions[0].text.split(' (+')[0], // Enlever le prix du nom
            prix: parseFloat(jantesSelect.selectedOptions[0].dataset.price || 0)
        } : null,
        motorisation: motorisationSelect.selectedIndex > 0 && motorisationSelect.value !== "" ? {
            id: motorisationSelect.value,
            nom: motorisationSelect.selectedOptions[0].text.split(' (+')[0], // Enlever le prix du nom
            prix: parseFloat(motorisationSelect.selectedOptions[0].dataset.price || 0)
        } : null
    };

    // Mettre à jour l'affichage du panier
    afficherPanier();
}

// Fonction pour afficher le contenu du panier
function afficherPanier() {
    let panierHTML = `
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Votre configuration</h5>
                <p class="card-text">Prix de base: ${window.PRIX_BASE.toLocaleString()}€</p>
                <ul class="list-group list-group-flush">
    `;

    if (panier.couleur) {
        panierHTML += `
            <li class="list-group-item d-flex justify-content-between align-items-center">
                ${panier.couleur.nom}
                <div>
                    <span class="badge bg-primary rounded-pill me-2">+${panier.couleur.prix.toLocaleString()}€</span>
                    <button class="btn btn-sm btn-danger" onclick="removeOption('couleur')">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </li>
        `;
    }

    if (panier.jantes) {
        panierHTML += `
            <li class="list-group-item d-flex justify-content-between align-items-center">
                ${panier.jantes.nom}
                <div>
                    <span class="badge bg-primary rounded-pill me-2">+${panier.jantes.prix.toLocaleString()}€</span>
                    <button class="btn btn-sm btn-danger" onclick="removeOption('jantes')">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </li>
        `;
    }

    if (panier.motorisation) {
        panierHTML += `
            <li class="list-group-item d-flex justify-content-between align-items-center">
                ${panier.motorisation.nom}
                <div>
                    <span class="badge bg-primary rounded-pill me-2">+${panier.motorisation.prix.toLocaleString()}€</span>
                    <button class="btn btn-sm btn-danger" onclick="removeOption('motorisation')">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </li>
        `;
    }

    panierHTML += `
                </ul>
            </div>
        </div>
    `;

    panierDiv.innerHTML = panierHTML;
}

// Fonction pour sauvegarder le panier
function sauvegarderPanier() {
    localStorage.setItem('voiturePanier', JSON.stringify({
        options: panier,
        total: updateTotal(),
        dateAjout: new Date().toISOString()
    }));
}

// Ajout des écouteurs d'événements
couleurSelect.addEventListener('change', () => {
    updatePanier();
    updateTotal();
});

jantesSelect.addEventListener('change', () => {
    updatePanier();
    updateTotal();
});

motorisationSelect.addEventListener('change', () => {
    updatePanier();
    updateTotal();
});

ajouterButton.addEventListener('click', () => {
    updatePanier();
    sauvegarderPanier();
    
    // Notification toast
    const toast = `
        <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
            <div class="toast show" role="alert">
                <div class="toast-header">
                    <strong class="me-auto">Panier mis à jour</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">
                    Configuration sauvegardée avec succès !
                </div>
            </div>
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', toast);
    
    setTimeout(() => {
        const toastElement = document.querySelector('.toast');
        if (toastElement) {
            toastElement.remove();
        }
    }, 3000);
});

// Fonction pour réinitialiser le panier
function resetPanier() {
    panier = {
        couleur: null,
        jantes: null,
        motorisation: null
    };
    
    // Réinitialiser les sélecteurs à l'option par défaut
    couleurSelect.selectedIndex = 0;
    jantesSelect.selectedIndex = 0;
    motorisationSelect.selectedIndex = 0;
    
    // Mettre à jour l'affichage
    afficherPanier();
    updateTotal();
    
    // Supprimer le panier sauvegardé
    localStorage.removeItem('voiturePanier');
}

// Initialisation au chargement de la page
window.addEventListener('load', () => {
    // Réinitialiser le panier au chargement de la page
    resetPanier();
    
    // Mettre à jour le panier et le total initial
    updatePanier();
    updateTotal();
});