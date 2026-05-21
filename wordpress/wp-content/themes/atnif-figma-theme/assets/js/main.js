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

  var characters = document.querySelectorAll("[data-atnif-character]");

  characters.forEach(function (character) {
    var itemsNode = character.querySelector("[data-character-items]");
    var buttons = Array.prototype.slice.call(
      character.querySelectorAll("[data-character-button]")
    );
    var imageWrap = character.querySelector("[data-character-image-wrap]");
    var name = character.querySelector("[data-character-name]");
    var ruby = character.querySelector("[data-character-ruby]");
    var copy = character.querySelector("[data-character-copy]");
    var items = [];

    if (itemsNode) {
      try {
        items = JSON.parse(itemsNode.textContent || "[]");
      } catch (error) {
        items = [];
      }
    }

    function renderCharacter(index) {
      var item = items[index];

      if (!item) {
        return;
      }

      if (name) {
        name.textContent = item.name || "";
      }

      if (ruby) {
        ruby.textContent = item.ruby || "";
      }

      if (copy) {
        copy.textContent = item.copy || "";
      }

      if (imageWrap) {
        imageWrap.textContent = "";

        if (item.image) {
          var image = document.createElement("img");
          image.className = "character__image";
          image.src = item.image;
          image.alt = item.name || "";
          imageWrap.appendChild(image);
        }
      }

      buttons.forEach(function (button, buttonIndex) {
        button.classList.toggle("is-active", buttonIndex === index);
        button.setAttribute("aria-pressed", buttonIndex === index ? "true" : "false");
      });
    }

    buttons.forEach(function (button, index) {
      button.addEventListener("click", function () {
        renderCharacter(index);
      });
    });
  });
})();
