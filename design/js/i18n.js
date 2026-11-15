/* Bilingual UI controller.
   French is the source language in the markup; JS swaps to English using
   js/i18n-data.js. The choice is stored in localStorage. */
(function () {
  "use strict";

  var STORAGE_KEY = "sb-lang";
  var DICT = window.SB_EN || {};
  var ATTRS = ["placeholder", "aria-label", "title", "data-demo", "alt"];
  var textOriginals = new WeakMap();
  var attrOriginals = new WeakMap();
  var metaEl = document.querySelector('meta[name="description"]');
  var titleOriginal = document.title;
  var metaOriginal = metaEl ? metaEl.getAttribute("content") : null;
  var current = "fr";

  function has(dict, key) {
    return Object.prototype.hasOwnProperty.call(dict, key);
  }

  function readStored() {
    try {
      var value = localStorage.getItem(STORAGE_KEY);
      return value === "en" || value === "fr" ? value : null;
    } catch (error) {
      return null;
    }
  }

  function translate(source) {
    return current === "en" && has(DICT, source) ? DICT[source] : source;
  }

  function translateTextNode(node) {
    var original;
    if (textOriginals.has(node)) {
      original = textOriginals.get(node);
    } else {
      original = node.nodeValue;
      textOriginals.set(node, original);
    }

    if (current !== "en") {
      node.nodeValue = original;
      return;
    }

    var trimmed = original.trim();
    if (!trimmed || !has(DICT, trimmed)) {
      node.nodeValue = original;
      return;
    }

    var start = original.indexOf(trimmed);
    node.nodeValue =
      original.slice(0, start) +
      DICT[trimmed] +
      original.slice(start + trimmed.length);
  }

  function applyText() {
    var walker = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT, {
      acceptNode: function (node) {
        var parent = node.parentElement;
        if (!parent) return NodeFilter.FILTER_REJECT;
        if (parent.closest("script, style, noscript")) return NodeFilter.FILTER_REJECT;
        if (!node.nodeValue.trim()) return NodeFilter.FILTER_REJECT;
        return NodeFilter.FILTER_ACCEPT;
      }
    });

    var node;
    while ((node = walker.nextNode())) translateTextNode(node);
  }

  function storeAttributes() {
    document
      .querySelectorAll("[placeholder], [aria-label], [title], [data-demo], [alt]")
      .forEach(function (el) {
        var map = attrOriginals.get(el);
        if (!map) {
          map = {};
          attrOriginals.set(el, map);
        }
        ATTRS.forEach(function (name) {
          if (el.hasAttribute(name) && !has(map, name)) {
            map[name] = el.getAttribute(name);
          }
        });
      });
  }

  function applyAttributes() {
    document
      .querySelectorAll("[placeholder], [aria-label], [title], [data-demo], [alt]")
      .forEach(function (el) {
        var map = attrOriginals.get(el);
        if (!map) return;
        ATTRS.forEach(function (name) {
          if (!has(map, name)) return;
          el.setAttribute(name, translate(map[name]));
        });
      });
  }

  function applySpecials() {
    document.documentElement.lang = current;

    document.title = translate(titleOriginal);
    if (metaEl && metaOriginal !== null) {
      metaEl.setAttribute("content", translate(metaOriginal));
    }
  }

  function updateButtons() {
    document.querySelectorAll(".lang-switch [data-lang]").forEach(function (button) {
      var active = button.getAttribute("data-lang") === current;
      button.classList.toggle("active", active);
      button.setAttribute("aria-pressed", active ? "true" : "false");
    });
  }

  function apply(lang) {
    current = lang === "en" ? "en" : "fr";
    applyText();
    applyAttributes();
    applySpecials();
    updateButtons();

    try {
      localStorage.setItem(STORAGE_KEY, current);
    } catch (error) {
      /* storage may be unavailable — the language still applies for this page */
    }

    window.dispatchEvent(
      new CustomEvent("sb:langchange", { detail: { lang: current } })
    );
  }

  storeAttributes();
  apply(readStored() || "fr");
  document.documentElement.classList.remove("i18n-pending");

  document.querySelectorAll(".lang-switch [data-lang]").forEach(function (button) {
    button.addEventListener("click", function () {
      apply(button.getAttribute("data-lang"));
    });
  });

  window.SBI18n = {
    t: translate,
    apply: apply,
    get lang() {
      return current;
    }
  };
})();
