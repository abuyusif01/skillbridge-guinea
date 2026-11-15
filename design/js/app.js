/* SkillBridge Guinée — prototype behaviour (navigation + demo feedback only) */
(function () {
  "use strict";

  function translate(message) {
    return window.SBI18n ? window.SBI18n.t(message) : message;
  }

  function menuLabel(isOpen) {
    return translate(isOpen ? "Fermer le menu" : "Ouvrir le menu");
  }

  function language() {
    return window.SBI18n ? window.SBI18n.lang : "fr";
  }

  var RATING_LABELS = {
    fr: { 5: "Excellent", 4: "Très bien", 3: "Bien", 2: "Moyen", 1: "Faible" },
    en: { 5: "Excellent", 4: "Very good", 3: "Good", 2: "Fair", 1: "Poor" }
  };

  function ratingText(value) {
    var lang = language();
    return value + " / 5 — " + (RATING_LABELS[lang] || RATING_LABELS.fr)[value];
  }

  /* Live caption under the review star picker */
  function syncReviewCaption() {
    var caption = document.querySelector(".rating-caption");
    if (!caption) return;
    var checked = document.querySelector(
      '.star-input input[name="rating"]:checked'
    );
    var value = checked ? parseInt(checked.value, 10) : 4;
    caption.textContent = ratingText(value);
  }

  /* Reflect the submitted rating on the thank-you screen */
  function syncThanksRating() {
    var stars = document.querySelector("[data-rating-stars]");
    if (!stars) return;

    var value = parseInt(
      new URLSearchParams(window.location.search).get("rating"),
      10
    );
    if (!(value >= 1 && value <= 5)) value = 4;

    stars.textContent = "";
    for (var i = 1; i <= 5; i += 1) {
      var star = document.createElement("span");
      star.textContent = "★";
      if (i > value) star.className = "star-empty";
      stars.appendChild(star);
    }

    stars.setAttribute(
      "aria-label",
      language() === "en"
        ? "Rating given: " + value + " out of 5 stars"
        : "Note attribuée : " + value + (value === 1 ? " étoile" : " étoiles") + " sur 5"
    );

    var valueEl = document.querySelector("[data-rating-value]");
    if (valueEl) valueEl.textContent = ratingText(value);
  }

  /* Give repeated "View profile", "Accept", "Suspend" controls a unique name */
  function addContextLabels() {
    document.querySelectorAll(".result-card").forEach(function (card) {
      var nameEl = card.querySelector(".result-title h3");
      if (!nameEl) return;
      var name = nameEl.textContent.trim();
      card.querySelectorAll(".result-actions a").forEach(function (link) {
        link.setAttribute("aria-label", link.textContent.trim() + " — " + name);
      });
    });

    document.querySelectorAll(".request").forEach(function (request) {
      var nameEl = request.querySelector(".request-who strong");
      if (!nameEl) return;
      var name = nameEl.textContent.trim();
      request
        .querySelectorAll(".request-actions a, .request-actions button")
        .forEach(function (control) {
          control.setAttribute(
            "aria-label",
            control.textContent.trim() + " — " + name
          );
        });
    });

    document.querySelectorAll("tbody tr").forEach(function (row) {
      var nameEl = row.querySelector(".cell-user strong");
      var name = nameEl
        ? nameEl.textContent.trim()
        : row.cells[0]
          ? row.cells[0].textContent.trim()
          : "";
      if (!name) return;
      row
        .querySelectorAll(".table-actions a, .table-actions button")
        .forEach(function (control) {
          control.setAttribute(
            "aria-label",
            control.textContent.trim() + " — " + name
          );
        });
    });
  }

  /* Label mobile table cells so rows can collapse into cards */
  function labelTables() {
    document.querySelectorAll("table").forEach(function (table) {
      table.classList.add("is-stacked");
      var wrap = table.closest(".table-wrap");
      if (wrap) wrap.classList.add("is-stacked");

      var heads = Array.prototype.map.call(
        table.querySelectorAll("thead th"),
        function (th) {
          return th.textContent.trim();
        }
      );

      table.querySelectorAll("tbody tr").forEach(function (row) {
        Array.prototype.forEach.call(row.children, function (cell, index) {
          if (heads[index]) cell.setAttribute("data-label", heads[index]);
        });
      });
    });
  }

  syncReviewCaption();
  syncThanksRating();
  addContextLabels();
  labelTables();

  var starInput = document.querySelector(".star-input");
  if (starInput) {
    starInput.addEventListener("change", syncReviewCaption);
  }

  window.addEventListener("sb:langchange", function () {
    syncReviewCaption();
    syncThanksRating();
    addContextLabels();
    labelTables();
  });

  /* Mobile menu */
  var toggle = document.querySelector(".nav-toggle");
  var nav = document.getElementById("site-nav");

  if (toggle && nav) {
    toggle.addEventListener("click", function () {
      var isOpen = nav.classList.toggle("open");
      toggle.setAttribute("aria-expanded", String(isOpen));
      toggle.setAttribute("aria-label", menuLabel(isOpen));
    });

    nav.addEventListener("click", function (event) {
      if (event.target.closest("a") && nav.classList.contains("open")) {
        nav.classList.remove("open");
        toggle.setAttribute("aria-expanded", "false");
        toggle.setAttribute("aria-label", menuLabel(false));
      }
    });

    window.addEventListener("sb:langchange", function () {
      if (nav.classList.contains("open")) {
        toggle.setAttribute("aria-label", menuLabel(true));
      }
    });
  }

  /* Collapse the results filters on small screens */
  var filterPanel = document.querySelector("details.filters");
  if (filterPanel) {
    var filterQuery = window.matchMedia("(max-width: 900px)");
    var syncFilters = function () {
      if (filterQuery.matches) {
        filterPanel.removeAttribute("open");
      } else {
        filterPanel.setAttribute("open", "");
      }
    };
    syncFilters();
    if (filterQuery.addEventListener) {
      filterQuery.addEventListener("change", syncFilters);
    }
  }

  /* Toast used by demo-only actions */
  var toast;
  var toastTimer;

  function showToast(message) {
    if (!toast) {
      toast = document.createElement("div");
      toast.className = "toast";
      toast.setAttribute("role", "status");
      toast.setAttribute("aria-live", "polite");
      document.body.appendChild(toast);
    }

    toast.textContent = message;
    toast.classList.add("show");
    window.clearTimeout(toastTimer);
    toastTimer = window.setTimeout(function () {
      toast.classList.remove("show");
    }, 2200);
  }

  document.querySelectorAll("[data-demo]").forEach(function (element) {
    element.addEventListener("click", function (event) {
      event.preventDefault();
      showToast(
        element.getAttribute("data-demo") ||
          translate("Action de démonstration")
      );
    });
  });

  /* Forms redirect to a static screen instead of posting anywhere */
  document.querySelectorAll("form[data-demo-submit]").forEach(function (form) {
    form.addEventListener("submit", function (event) {
      event.preventDefault();
      var target = form.getAttribute("data-demo-submit");
      var rating = form.querySelector('input[name="rating"]:checked');
      if (rating) {
        target +=
          (target.indexOf("?") === -1 ? "?" : "&") +
          "rating=" +
          encodeURIComponent(rating.value);
      }
      showToast(translate("Aperçu — redirection…"));
      window.setTimeout(function () {
        window.location.href = target;
      }, 450);
    });
  });
})();
