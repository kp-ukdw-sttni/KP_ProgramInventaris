(function () {
  "use strict";

  const STORAGE_KEY = "sttni_inventory_v1";
  const COMMON_CONDITIONS = ["Baik", "Kurang Baik", "Rusak", "Mati", "Pengecatan", "Pindah SekUm", "Tdk dipakai", "-"];

  const state = {
    query: "",
    room: "all",
    condition: "all",
    editingId: null
  };

  let inventory = loadInventory();

  const roomListEl = document.getElementById("roomList");
  const statsEl = document.getElementById("stats");
  const tableBodyEl = document.getElementById("tableBody");
  const searchInputEl = document.getElementById("searchInput");
  const conditionFilterEl = document.getElementById("conditionFilter");
  const clearBtnEl = document.getElementById("clearBtn");
  const resetDataBtnEl = document.getElementById("resetDataBtn");
  const addBtnEl = document.getElementById("addBtn");
  const resultCountEl = document.getElementById("resultCount");

  const modalOverlayEl = document.getElementById("modalOverlay");
  const modalTitleEl = document.getElementById("modalTitle");
  const modalFormEl = document.getElementById("modalForm");
  const modalCloseBtnEl = document.getElementById("modalCloseBtn");
  const modalCancelBtnEl = document.getElementById("modalCancelBtn");
  const fRoomEl = document.getElementById("fRoom");
  const fNameEl = document.getElementById("fName");
  const fQtyEl = document.getElementById("fQty");
  const fCodeEl = document.getElementById("fCode");
  const fConditionEl = document.getElementById("fCondition");
  const roomDataListEl = document.getElementById("roomDataList");
  const conditionDataListEl = document.getElementById("conditionDataList");

  function loadInventory() {
    try {
      const raw = localStorage.getItem(STORAGE_KEY);
      if (raw) {
        const parsed = JSON.parse(raw);
        if (Array.isArray(parsed)) return parsed;
      }
    } catch (e) {}
    return seedData();
  }

  function seedData() {
    return INVENTORY_DATA.map(function (item, i) {
      return Object.assign({ id: "seed-" + i }, item);
    });
  }

  function saveInventory() {
    try {
      localStorage.setItem(STORAGE_KEY, JSON.stringify(inventory));
    } catch (e) {}
  }

  function nextId() {
    return Date.now().toString(36) + Math.random().toString(36).slice(2, 8);
  }

  function normalize(s) {
    return (s || "").trim().toLowerCase();
  }

  function conditionBucket(c) {
    const s = normalize(c);
    if (s === "" || s === "-") return "N/A";
    if (s === "baik") return "Baik";
    if (s.includes("kurang") || s.includes("kuran")) return "Kurang Baik";
    if (s.includes("rusak") || s.includes("mati")) return "Rusak";
    return "Lainnya";
  }

  function conditionLabel(c) {
    return c.trim() || "-";
  }

  function parseQty(q) {
    const m = String(q || "").trim().match(/^(\d+)/);
    return m ? parseInt(m[1], 10) : 0;
  }

  function rooms() {
    const map = new Map();
    inventory.forEach(function (item) {
      if (!map.has(item.room)) map.set(item.room, 0);
      map.set(item.room, map.get(item.room) + 1);
    });
    return Array.from(map.entries()).sort(function (a, b) {
      return a[0].localeCompare(b[0], "id");
    });
  }

  function filteredItems() {
    const q = normalize(state.query);
    return inventory.filter(function (item) {
      if (state.room !== "all" && item.room !== state.room) return false;
      if (state.condition !== "all" && conditionBucket(item.condition) !== state.condition) return false;
      if (q) {
        const haystack = normalize(item.room + " " + item.name + " " + item.code + " " + item.condition);
        if (haystack.indexOf(q) === -1) return false;
      }
      return true;
    });
  }

  function renderSidebar() {
    const list = rooms();
    const total = inventory.length;

    let html = '<li><button data-room="all" class="' + (state.room === "all" ? "active" : "") + '">' +
      '<span class="room-name">Semua Lokasi</span>' +
      '<span class="room-count">' + total + '</span></button></li>';

    list.forEach(function (entry) {
      const isActive = state.room === entry[0] ? "active" : "";
      html += '<li><button data-room="' + escapeAttr(entry[0]) + '" class="' + isActive + '">' +
        '<span class="room-name">' + escapeHtml(entry[0]) + '</span>' +
        '<span class="room-count">' + entry[1] + '</span></button></li>';
    });

    roomListEl.innerHTML = html;

    Array.prototype.forEach.call(roomListEl.querySelectorAll("button"), function (btn) {
      btn.addEventListener("click", function () {
        state.room = btn.getAttribute("data-room");
        render();
      });
    });
  }

  function renderStats(allItems) {
    const roomsCount = rooms().length;
    const typesCount = allItems.length;
    const units = allItems.reduce(function (sum, item) {
      return sum + parseQty(item.qty);
    }, 0);

    const buckets = { "Baik": 0, "Kurang Baik": 0, "Rusak": 0, "Lainnya": 0 };
    allItems.forEach(function (item) {
      const b = conditionBucket(item.condition);
      if (buckets[b] !== undefined) buckets[b]++;
      else buckets["Lainnya"]++;
    });

    statsEl.innerHTML =
      statCard(roomsCount, "Lokasi / Ruangan") +
      statCard(typesCount, "Jenis Fasilitas") +
      statCard(units, "Total Unit") +
      statCard(buckets["Baik"], "Kondisi Baik", "cond-baik") +
      statCard(buckets["Kurang Baik"], "Kurang Baik", "cond-kurang") +
      statCard(buckets["Rusak"], "Rusak", "cond-rusak") +
      statCard(buckets["Lainnya"], "Lainnya / N/A", "cond-lain");
  }

  function statCard(value, label, extraClass) {
    return '<div class="stat-card ' + (extraClass || "") + '">' +
      '<div class="stat-value">' + value + '</div>' +
      '<div class="stat-label">' + escapeHtml(label) + '</div></div>';
  }

  function badgeClass(bucket) {
    if (bucket === "Baik") return "b-baik";
    if (bucket === "Kurang Baik") return "b-kurang";
    if (bucket === "Rusak") return "b-rusak";
    return "b-lain";
  }

  function renderTable(items) {
    if (items.length === 0) {
      tableBodyEl.innerHTML = '<tr><td colspan="7" class="empty-state">Tidak ada data yang cocok.</td></tr>';
      resultCountEl.textContent = "0 hasil";
      return;
    }

    let html = "";
    items.forEach(function (item, idx) {
      const bucket = conditionBucket(item.condition);
      html += "<tr>" +
        '<td class="num">' + (idx + 1) + "</td>" +
        '<td class="room-cell">' + escapeHtml(item.room) + "</td>" +
        "<td>" + escapeHtml(item.name) + "</td>" +
        "<td>" + escapeHtml(item.qty || "-") + "</td>" +
        '<td class="code-cell">' + escapeHtml(item.code || "-") + "</td>" +
        '<td><span class="badge ' + badgeClass(bucket) + '">' + escapeHtml(conditionLabel(item.condition)) + "</span></td>" +
        '<td class="actions-cell">' +
          '<button class="btn-row btn-edit" data-action="edit" data-id="' + escapeAttr(item.id) + '">Edit</button>' +
          '<button class="btn-row btn-danger" data-action="delete" data-id="' + escapeAttr(item.id) + '">Hapus</button>' +
        "</td>" +
        "</tr>";
    });

    tableBodyEl.innerHTML = html;
    resultCountEl.textContent = items.length + " hasil";
  }

  function renderDatalists() {
    const roomSet = new Set();
    inventory.forEach(function (x) {
      roomSet.add(x.room);
    });
    roomDataListEl.innerHTML = Array.from(roomSet)
      .sort(function (a, b) { return a.localeCompare(b, "id"); })
      .map(function (r) { return '<option value="' + escapeAttr(r) + '"></option>'; })
      .join("");

    conditionDataListEl.innerHTML = COMMON_CONDITIONS.map(function (c) {
      return '<option value="' + escapeAttr(c) + '"></option>';
    }).join("");
  }

  function render() {
    const items = filteredItems();
    renderSidebar();
    renderStats(items);
    renderTable(items);
    renderDatalists();
  }

  function escapeHtml(s) {
    return String(s)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;");
  }

  function escapeAttr(s) {
    return escapeHtml(s).replace(/'/g, "&#39;");
  }

  function addItem(data) {
    inventory.push(Object.assign({ id: nextId() }, data));
    saveInventory();
    focusRoom(data.room);
  }

  function updateItem(id, data) {
    const idx = inventory.findIndex(function (x) { return x.id === id; });
    if (idx === -1) return;
    inventory[idx] = Object.assign({}, inventory[idx], data);
    saveInventory();
    focusRoom(data.room);
  }

  function deleteItem(id) {
    const item = inventory.find(function (x) { return x.id === id; });
    if (!item) return;
    if (!confirm('Hapus "' + item.name + '" dari ' + item.room + "?")) return;
    inventory = inventory.filter(function (x) { return x.id !== id; });
    saveInventory();
    render();
  }

  function focusRoom(room) {
    state.room = room;
    state.query = "";
    state.condition = "all";
    searchInputEl.value = "";
    conditionFilterEl.value = "all";
    render();
  }

  function openModal(mode, id) {
    const item = mode === "edit" ? inventory.find(function (x) { return x.id === id; }) : null;
    state.editingId = item ? item.id : null;
    modalTitleEl.textContent = item ? "Edit Fasilitas" : "Tambah Fasilitas";
    fRoomEl.value = item ? item.room : "";
    fNameEl.value = item ? item.name : "";
    fQtyEl.value = item ? item.qty || "" : "";
    fCodeEl.value = item ? item.code || "" : "";
    fConditionEl.value = item ? item.condition || "" : "";
    modalOverlayEl.classList.remove("hidden");
    fNameEl.focus();
  }

  function closeModal() {
    modalOverlayEl.classList.add("hidden");
    state.editingId = null;
  }

  searchInputEl.addEventListener("input", function () {
    state.query = searchInputEl.value;
    render();
  });

  conditionFilterEl.addEventListener("change", function () {
    state.condition = conditionFilterEl.value;
    render();
  });

  clearBtnEl.addEventListener("click", function () {
    state.query = "";
    state.room = "all";
    state.condition = "all";
    searchInputEl.value = "";
    conditionFilterEl.value = "all";
    render();
  });

  resetDataBtnEl.addEventListener("click", function () {
    if (!confirm("Kembalikan semua data ke kondisi awal dari file docx? Perubahan yang Anda buat akan hilang.")) return;
    inventory = seedData();
    saveInventory();
    render();
  });

  addBtnEl.addEventListener("click", function () {
    openModal("add");
  });

  modalCloseBtnEl.addEventListener("click", closeModal);
  modalCancelBtnEl.addEventListener("click", closeModal);

  modalOverlayEl.addEventListener("click", function (e) {
    if (e.target === modalOverlayEl) closeModal();
  });

  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape" && !modalOverlayEl.classList.contains("hidden")) closeModal();
  });

  modalFormEl.addEventListener("submit", function (e) {
    e.preventDefault();
    const data = {
      room: fRoomEl.value.trim(),
      name: fNameEl.value.trim(),
      qty: fQtyEl.value.trim(),
      code: fCodeEl.value.trim(),
      condition: fConditionEl.value.trim()
    };
    if (!data.room || !data.name) return;
    if (state.editingId) updateItem(state.editingId, data);
    else addItem(data);
    closeModal();
  });

  tableBodyEl.addEventListener("click", function (e) {
    const btn = e.target.closest("button[data-action]");
    if (!btn) return;
    const id = btn.getAttribute("data-id");
    if (btn.getAttribute("data-action") === "edit") openModal("edit", id);
    else if (btn.getAttribute("data-action") === "delete") deleteItem(id);
  });

  render();
})();
