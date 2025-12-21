(function () {
  const KEY = "pk_dark_mode";
  const btn = document.getElementById("darkModeToggle");
  const body = document.body;

  function setButton(isDark) {
    if (!btn) return;
    const icon = btn.querySelector("i");
    const text = btn.querySelector(".dm-text");

    if (isDark) {
      btn.classList.remove("btn-outline-secondary");
      btn.classList.add("btn-outline-light");
      if (icon) icon.className = "bi bi-sun me-1";
      if (text) text.textContent = "Terang";
    } else {
      btn.classList.remove("btn-outline-light");
      btn.classList.add("btn-outline-secondary");
      if (icon) icon.className = "bi bi-moon-stars me-1";
      if (text) text.textContent = "Gelap";
    }
  }

  function apply(isDark) {
    body.classList.toggle("dark-mode", isDark);
    setButton(isDark);
  }

  // init
  const saved = localStorage.getItem(KEY);
  const isDark = saved === "1";
  apply(isDark);

  // click
  if (btn) {
    btn.addEventListener("click", function () {
      const next = !body.classList.contains("dark-mode");
      localStorage.setItem(KEY, next ? "1" : "0");
      apply(next);
    });
  }
})();
