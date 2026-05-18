/**
 * File animations.js
 *
 * Handles scroll-reveal animations using IntersectionObserver.
 * Elements with animation classes start at opacity: 0 and become visible
 * when they enter the viewport.
 */
(function(){
  var observer = new IntersectionObserver(function(entries) {
    entries.forEach(function(e) {
      if (e.isIntersecting) {
        e.target.classList.add('visible');
        observer.unobserve(e.target);
      }
    });
  }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });

  document.querySelectorAll(
    '.animate-on-scroll, .animate-slide-left, .animate-slide-right, .animate-scale'
  ).forEach(function(el) {
    observer.observe(el);
  });
})();
