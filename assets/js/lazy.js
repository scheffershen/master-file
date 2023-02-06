'use strict';

let lazyImageObserver = new IntersectionObserver(function(entries, observer) {
    entries.forEach(function(entry) {
        if (entry.isIntersecting) {
            let lazyImage = entry.target;
            lazyImage.src = lazyImage.dataset.src;
            lazyImage.classList.remove("lazy");
            lazyImageObserver.unobserve(lazyImage);
        }
    });
});

$( () => {

	// Tell our observer to observe all img elements with a "lazy" class
	var lazyImages = document.querySelectorAll('img.lazy');
	lazyImages.forEach(img => {
	  lazyImageObserver.observe(img);
	});

});