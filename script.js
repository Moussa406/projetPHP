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

// État du panier
let panier = {
    couleur: null,
    jantes: null,
    motorisation: null
};

// Fonction globale pour retirer une option
function removeOption(type) {
    const couleurSelect = document.getElementById('couleur');
    const jantesSelect = document.getElementById('jantes');
    const motorisationSelect = document.getElementById('motorisation');
    const totalSpan = document.getElementById('total');
    const panierDiv = document.getElementById('panier');

    panier[type] = null;
    
    // Réinitialiser le select correspondant
    switch(type) {
        case 'couleur':
            if (couleurSelect) couleurSelect.selectedIndex = 0;
            break;
        case 'jantes':
            if (jantesSelect) jantesSelect.selectedIndex = 0;
            break;
        case 'motorisation':
            if (motorisationSelect) motorisationSelect.selectedIndex = 0;
            break;
    }
    
    // Mettre à jour le total
    if (totalSpan) {
        let total = window.PRIX_BASE;
        if (panier.couleur) total += parseFloat(panier.couleur.prix) || 0;
        if (panier.jantes) total += parseFloat(panier.jantes.prix) || 0;
        if (panier.motorisation) total += parseFloat(panier.motorisation.prix) || 0;
        totalSpan.textContent = total.toLocaleString();
    }

    // Mettre à jour l'affichage du panier
    if (panierDiv) {
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

    // Sauvegarder dans le localStorage
    const total = parseFloat(totalSpan ? totalSpan.textContent.replace(/\s/g, '') : window.PRIX_BASE);
    localStorage.setItem('voiturePanier', JSON.stringify({
        options: panier,
        total: total,
        dateAjout: new Date().toISOString()
    }));
}

// Attendre que le DOM soit chargé
document.addEventListener('DOMContentLoaded', () => {
    // Vérifier si nous sommes sur la page de personnalisation
    const isPersonnalisationPage = document.getElementById('pageVoiture') !== null;
    
    if (!isPersonnalisationPage) return;

    // Sélection des éléments du DOM
    const couleurSelect = document.getElementById('couleur');
    const jantesSelect = document.getElementById('jantes');
    const motorisationSelect = document.getElementById('motorisation');
    const totalSpan = document.getElementById('total');
    const ajouterButton = document.getElementById('ajouter');
    const panierDiv = document.getElementById('panier');

    console.log('DOMContentLoaded - Prix de base:', window.PRIX_BASE);
    
    // Afficher le prix de base initial
    if (panierDiv) {
        let panierHTML = `
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Votre configuration</h5>
                    <p class="card-text">Prix de base: ${window.PRIX_BASE.toLocaleString()}€</p>
                    <ul class="list-group list-group-flush">
                    </ul>
                </div>
            </div>
        `;
        panierDiv.innerHTML = panierHTML;
    }
    
    // Afficher le total initial
    if (totalSpan) {
        totalSpan.textContent = window.PRIX_BASE.toLocaleString();
    }
    
    // Réinitialiser le panier
    panier = {
        couleur: null,
        jantes: null,
        motorisation: null
    };
    
    // Réinitialiser les sélecteurs
    if (couleurSelect) couleurSelect.selectedIndex = 0;
    if (jantesSelect) jantesSelect.selectedIndex = 0;
    if (motorisationSelect) motorisationSelect.selectedIndex = 0;
    
    // Ajouter les écouteurs d'événements
    if (couleurSelect) {
        couleurSelect.addEventListener('change', () => {
            updatePanier(couleurSelect, jantesSelect, motorisationSelect, panierDiv, totalSpan);
            updateTotal(totalSpan);
        });
    }

    if (jantesSelect) {
        jantesSelect.addEventListener('change', () => {
            updatePanier(couleurSelect, jantesSelect, motorisationSelect, panierDiv, totalSpan);
            updateTotal(totalSpan);
        });
    }

    if (motorisationSelect) {
        motorisationSelect.addEventListener('change', () => {
            updatePanier(couleurSelect, jantesSelect, motorisationSelect, panierDiv, totalSpan);
            updateTotal(totalSpan);
        });
    }

    if (ajouterButton) {
        ajouterButton.addEventListener('click', () => {
            updatePanier(couleurSelect, jantesSelect, motorisationSelect, panierDiv, totalSpan);
            sauvegarderPanier(totalSpan);
        });
    }

    // Fonctions avec les éléments DOM passés en paramètres
    function updateTotal(totalSpan) {
        console.log('updateTotal - Prix de base:', window.PRIX_BASE);
        let total = window.PRIX_BASE;
        
        if (panier.couleur) {
            total += parseFloat(panier.couleur.prix) || 0;
        }
        if (panier.jantes) {
            total += parseFloat(panier.jantes.prix) || 0;
        }
        if (panier.motorisation) {
            total += parseFloat(panier.motorisation.prix) || 0;
        }

        if (totalSpan) {
            totalSpan.textContent = total.toLocaleString();
        }
        return total;
    }

    function updatePanier(couleurSelect, jantesSelect, motorisationSelect, panierDiv, totalSpan) {
        panier = {
            couleur: couleurSelect && couleurSelect.selectedIndex > 0 && couleurSelect.value !== "" ? {
                id: couleurSelect.value,
                nom: couleurSelect.selectedOptions[0].text.split(' (+')[0],
                prix: parseFloat(couleurSelect.selectedOptions[0].dataset.price || 0)
            } : null,
            jantes: jantesSelect && jantesSelect.selectedIndex > 0 && jantesSelect.value !== "" ? {
                id: jantesSelect.value,
                nom: jantesSelect.selectedOptions[0].text.split(' (+')[0],
                prix: parseFloat(jantesSelect.selectedOptions[0].dataset.price || 0)
            } : null,
            motorisation: motorisationSelect && motorisationSelect.selectedIndex > 0 && motorisationSelect.value !== "" ? {
                id: motorisationSelect.value,
                nom: motorisationSelect.selectedOptions[0].text.split(' (+')[0],
                prix: parseFloat(motorisationSelect.selectedOptions[0].dataset.price || 0)
            } : null
        };

        afficherPanier(panierDiv);
    }

    function afficherPanier(panierDiv) {
        if (!panierDiv) return;

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

    function sauvegarderPanier(totalSpan) {
        const total = updateTotal(totalSpan);
        localStorage.setItem('voiturePanier', JSON.stringify({
            options: panier,
            total: total,
            dateAjout: new Date().toISOString()
        }));
    }
});