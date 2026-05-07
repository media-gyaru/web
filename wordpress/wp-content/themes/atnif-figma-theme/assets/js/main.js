(function () {
  var sliders = document.querySelectorAll("[data-atnif-slider]");

  sliders.forEach(function (slider) {
    var dots = Array.prototype.slice.call(slider.querySelectorAll("[data-slider-dot]"));
    var activeIndex = 0;

    function render(index) {
      activeIndex = (index + dots.length) % dots.length;
      dots.forEach(function (dot, dotIndex) {
        dot.classList.toggle("is-active", dotIndex === activeIndex);
      });
    }

    var prev = slider.querySelector("[data-slider-prev]");
    var next = slider.querySelector("[data-slider-next]");

    if (prev) {
      prev.addEventListener("click", function () {
        render(activeIndex - 1);
      });
    }

    if (next) {
      next.addEventListener("click", function () {
        render(activeIndex + 1);
      });
    }

    dots.forEach(function (dot, index) {
      dot.addEventListener("click", function () {
        render(index);
      });
    });
  });
})();
