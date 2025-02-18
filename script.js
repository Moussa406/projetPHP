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

