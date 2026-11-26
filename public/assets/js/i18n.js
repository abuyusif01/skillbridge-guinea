/* Bilingual UI controller.
   French is the source language in the markup; JS swaps to English using
   js/i18n-data.js. English is the default. The choice is stored in localStorage
   and mirrored to a cookie so the server can localise dynamic labels. */
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
  var serverLocale = document.documentElement.getAttribute("data-server-locale") || "en";
  var current = "en";

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

  var PATTERNS = [
    [/^(\d+)\s+professionnels? disponibles?$/, "$1 professionals available"],
    [/^(\d+)\s+professionnels?$/, "$1 professionals"],
    [/^Page\s+(\d+)\s+sur\s+(\d+)$/, "Page $1 of $2"],
    [/^(\d+)\s+avis$/, "$1 reviews"],
    [/^(\d+)\s+utilisateurs? récents?$/, "$1 recent users"],
    [/^(\d+)\s+réservations? en attente$/, "$1 pending bookings"],
    [/^(\d+)\s+avis récents?$/, "$1 recent reviews"],
    [/^(\d+)\s+notification(?:s)? non lue(?:s)?\.?$/, "$1 unread notifications"],
    [/^Dès\s+([\d\s,]+)\s+GNF$/, "From $1 GNF"],
    [/^Terminé le\s+(.+)$/, "Completed on $1"],
    [/^Réserver\s+(.+)\s+—\s+SkillBridge Guinea$/, "Book $1 — SkillBridge Guinea"]
  ];

  function translate(source) {
    if (current !== "en") return source;
    if (has(DICT, source)) return DICT[source];

    for (var i = 0; i < PATTERNS.length; i += 1) {
      if (PATTERNS[i][0].test(source)) {
        return source.replace(PATTERNS[i][0], PATTERNS[i][1]);
      }
    }

    return source;
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
    if (!trimmed) {
      node.nodeValue = original;
      return;
    }

    var translated = translate(trimmed);
    var start = original.indexOf(trimmed);
    node.nodeValue =
      original.slice(0, start) +
      translated +
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

    try {
      document.cookie =
        "skillbridge_locale=" + current + "; path=/; max-age=31536000; samesite=lax";
    } catch (error) {
      /* cookies may be unavailable — the language still applies for this page */
    }

    window.dispatchEvent(
      new CustomEvent("sb:langchange", { detail: { lang: current } })
    );
  }

  storeAttributes();
  var initial = readStored() || serverLocale;
  apply(initial);

  // Keep the server-rendered dynamic labels in sync with the stored choice.
  if (initial !== serverLocale) {
    try {
      if (!sessionStorage.getItem("sb-locale-sync")) {
        sessionStorage.setItem("sb-locale-sync", "1");
        window.location.reload();
      }
    } catch (error) {
      /* storage unavailable — keep the client-side translation */
    }
  }

  document.documentElement.classList.remove("i18n-pending");

  document.querySelectorAll(".lang-switch [data-lang]").forEach(function (button) {
    button.addEventListener("click", function () {
      var lang = button.getAttribute("data-lang");
      apply(lang);

      // Dynamic labels (trade names, statuses, validation) are rendered by
      // Laravel using the server locale. Reload so those follow the toggle.
      if (lang !== serverLocale) {
        window.location.reload();
      }
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
