/* Theme controller — loaded in <head> so the saved theme applies before paint. */
(function () {
  "use strict";

  var KEY = "sb-theme";
  var root = document.documentElement;

  function readStored() {
    try {
      var value = localStorage.getItem(KEY);
      return value === "dark" || value === "light" ? value : null;
    } catch (error) {
      return null;
    }
  }

  function preferred() {
    if (window.matchMedia && window.matchMedia("(prefers-color-scheme: dark)").matches) {
      return "dark";
    }
    return "light";
  }

  var initial = readStored() || preferred();
  root.setAttribute("data-theme", initial);

  /* Avoid a French flash: English is the default and the saved choice wins. */
  try {
    var storedLang = localStorage.getItem("sb-lang");
    var serverLang = root.getAttribute("data-server-locale") || "en";
    var uiLang =
      storedLang === "fr" || storedLang === "en" ? storedLang : serverLang;

    if (uiLang === "en") {
      root.lang = "en";
      root.classList.add("i18n-pending");
      window.setTimeout(function () {
        root.classList.remove("i18n-pending");
      }, 1500);
    }
  } catch (error) {
    /* storage unavailable — the page still renders in French */
  }

  function current() {
    return root.getAttribute("data-theme") || "light";
  }

  function isEnglish() {
    return root.lang === "en";
  }

  function syncButton() {
    var button = document.querySelector(".theme-toggle");
    if (!button) return;

    var dark = current() === "dark";
    var label;

    if (isEnglish()) {
      label = dark ? "Switch to light mode" : "Switch to dark mode";
    } else {
      label = dark ? "Activer le mode clair" : "Activer le mode sombre";
    }

    button.setAttribute("aria-pressed", dark ? "true" : "false");
    button.setAttribute("aria-label", label);
    button.setAttribute("title", label);

    var meta = document.querySelector('meta[name="theme-color"]');
    if (meta) meta.setAttribute("content", dark ? "#0b1220" : "#1d4ed8");
  }

  function apply(theme, persist) {
    root.setAttribute("data-theme", theme);

    if (persist !== false) {
      try {
        localStorage.setItem(KEY, theme);
      } catch (error) {
        /* storage may be unavailable — the theme still applies for this page */
      }
    }

    syncButton();
    window.dispatchEvent(new CustomEvent("sb:themechange", { detail: { theme: theme } }));
  }

  function toggle() {
    apply(current() === "dark" ? "light" : "dark", true);
  }

  window.SBTheme = {
    get: current,
    set: function (theme) {
      apply(theme, true);
    },
    toggle: toggle
  };

  document.addEventListener("DOMContentLoaded", function () {
    var button = document.querySelector(".theme-toggle");
    if (button) button.addEventListener("click", toggle);
    syncButton();
  });

  window.addEventListener("sb:langchange", syncButton);

  if (window.matchMedia) {
    var query = window.matchMedia("(prefers-color-scheme: dark)");
    if (query.addEventListener) {
      query.addEventListener("change", function (event) {
        if (!readStored()) apply(event.matches ? "dark" : "light", false);
      });
    }
  }
})();
