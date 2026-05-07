(function () {
  var sliders = document.querySelectorAll("[data-atnif-slider]");

  sliders.forEach(function (slider) {
    var dots = Array.prototype.slice.call(slider.querySelectorAll("[data-slider-dot]"));
    var title = slider.querySelector("[data-slider-title]");
    var text = slider.querySelector("[data-slider-text]");
    var itemsNode = slider.querySelector("[data-slider-items]");
    var items = [];
    var activeIndex = 0;

    if (itemsNode) {
      try {
        items = JSON.parse(itemsNode.textContent || "[]");
      } catch (error) {
        items = [];
      }
    }

    function render(index) {
      activeIndex = (index + dots.length) % dots.length;

      if (items[activeIndex]) {
        if (title) {
          title.textContent = items[activeIndex].title || "";
        }

        if (text) {
          text.textContent = items[activeIndex].text || "";
        }
      }

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
