<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit();
}

$nama = $_SESSION['nama'];
$role = $_SESSION['role'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Kalender Kegiatan</title>
  <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css' rel='stylesheet' />
  <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      padding: 30px;
      background: linear-gradient(to bottom right, #e3f2fd, #ffffff);
    }
    .container {
      max-width: 1000px;
      margin: auto;
      background: white;
      padding: 25px;
      border-radius: 10px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    }
    h2 {
      text-align: center;
      color: #11698e;
      margin-bottom: 20px;
    }
    .topbar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
    }
    .back-link {
      padding: 8px 14px;
      background-color: #11698e;
      color: white;
      text-decoration: none;
      border-radius: 6px;
    }
    .back-link:hover {
      background-color: #0d536b;
    }
    .filter {
      display: flex;
      gap: 10px;
    }
    select, button {
      padding: 8px 12px;
      border-radius: 6px;
      border: 1px solid #ccc;
      font-size: 14px;
    }
    button {
      background-color: #11698e;
      color: white;
      cursor: pointer;
    }
    button:hover {
      background-color: #0d536b;
    }
    #calendar {
      margin-top: 20px;
    }
    .fc-event-title, .fc-event-time {
      display: block;
      white-space: normal;
    }
    #eventModal {
      display: none;
      position: fixed;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      background: white;
      padding: 25px;
      border-radius: 12px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
      z-index: 9999;
      width: 90%;
      max-width: 400px;
      animation: fadeIn 0.3s ease-in-out;
    }
    #eventModal form > div {
      margin-bottom: 12px;
    }
    #eventModal label {
      font-weight: bold;
      display: block;
      margin-bottom: 4px;
    }
    #eventModal input[type="text"],
    #eventModal input[type="date"],
    #eventModal input[type="color"],
    #eventModal textarea {
      width: 100%;
      padding: 8px 10px;
      border-radius: 6px;
      border: 1px solid #ccc;
      font-size: 14px;
    }
    #eventModal textarea {
      resize: vertical;
      min-height: 60px;
    }
    #eventModal button {
      padding: 8px 14px;
      margin-right: 6px;
      border: none;
      border-radius: 6px;
      font-size: 14px;
    }
    #eventModal button[type="submit"] {
      background-color: #11698e;
      color: white;
    }
    #eventModal button[type="button"] {
      background-color: #ccc;
      color: black;
    }
    #deleteBtn {
      background-color: #e53935;
      color: white;
    }
    @keyframes fadeIn {
      from { opacity: 0; transform: translate(-50%, -45%); }
      to { opacity: 1; transform: translate(-50%, -50%); }
    }
  </style>
</head>
<body>
<div class="container">
  <div class="topbar">
    <a class="back-link" href="index.php">← Kembali ke Beranda</a>
    <div class="filter">
      <select id="filterYear"></select>
      <select id="filterMonth"></select>
      <button onclick="applyFilter()">Tampilkan</button>
    </div>
  </div>
  <h2>Kalender Kegiatan Generus</h2>
  <div id="calendar"></div>
</div>

<div id="eventModal">
  <form id="eventForm">
    <input type="hidden" id="eventId">
    <div>
      <label>Judul</label>
      <input type="text" id="title" required>
    </div>
    <div>
      <label>Mulai</label>
      <input type="date" id="start" required>
    </div>
    <div>
      <label>Selesai</label>
      <input type="date" id="end" required>
    </div>
    <div>
      <label>Deskripsi</label>
      <textarea id="description"></textarea>
    </div>
    <div>
      <label for="color">Warna</label>
      <input type="color" id="color">
    </div>
    <div style="margin-top:10px; text-align:right;">
      <button type="submit">Simpan</button>
      <button type="button" id="deleteBtn">Hapus</button>
      <button type="button" onclick="closeModal()">Batal</button>
    </div>
  </form>
</div>

<script>
let calendar;

function applyFilter() {
  const year = document.getElementById('filterYear').value;
  const month = document.getElementById('filterMonth').value;
  if (year && month && calendar) {
    calendar.gotoDate(`${year}-${month}-01`);
  }
}

function populateFilters() {
  const yearSelect = document.getElementById('filterYear');
  const monthSelect = document.getElementById('filterMonth');
  const now = new Date();
  for (let y = now.getFullYear() - 10; y <= now.getFullYear() + 10; y++) {
    const opt = document.createElement('option');
    opt.value = y;
    opt.textContent = y;
    if (y === now.getFullYear()) opt.selected = true;
    yearSelect.appendChild(opt);
  }
  for (let m = 1; m <= 12; m++) {
    const opt = document.createElement('option');
    opt.value = m.toString().padStart(2, '0');
    opt.textContent = new Date(0, m - 1).toLocaleString('id', { month: 'long' });
    if (m === now.getMonth() + 1) opt.selected = true;
    monthSelect.appendChild(opt);
  }
}

document.addEventListener('DOMContentLoaded', function () {
  populateFilters();
  const calendarEl = document.getElementById('calendar');
  calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: 'dayGridMonth',
    headerToolbar: {
      left: 'prev,next today',
      center: 'title',
      right: 'dayGridMonth,listMonth'
    },
    locale: 'id',
    events: 'kg_ambil_data_event.php',
    eventColor: '#3788D8',
    eventDisplay: 'block',
    eventDidMount: function(info) {
      if (info.event.extendedProps.description) {
        const descEl = document.createElement('div');
        descEl.style.fontSize = '0.8em';
        descEl.style.marginTop = '2px';
        descEl.style.color = '#555';
        descEl.textContent = info.event.extendedProps.description;
        info.el.appendChild(descEl);
      }
    },
    editable: <?= json_encode($role === 'admin') ?>,
    selectable: <?= json_encode($role === 'admin') ?>,
    select: function(info) {
      <?php if ($role === 'admin'): ?>
      openModal('', info.startStr, info.endStr, '', '', '#3788D8');
      <?php endif; ?>
    },
    dateClick: function(info) {
      <?php if ($role === 'admin'): ?>
      openModal('', info.dateStr, info.dateStr, '', '', '#3788D8');
      <?php endif; ?>
    },
    eventClick: function(info) {
      fetch('kg_ambil_data_event.php?id=' + info.event.id)
        .then(res => res.json())
        .then(data => {
          openModal(data.title, data.start, data.end, data.description, data.id, data.color);
        });
    }
  });
  calendar.render();

  const modal = document.getElementById('eventModal');
  const form = document.getElementById('eventForm');
  const deleteBtn = document.getElementById('deleteBtn');

  window.openModal = function(title, start, end, desc, id = '', color = '#3788D8') {
    document.getElementById('title').value = title;
    document.getElementById('start').value = start;
    document.getElementById('end').value = end;
    document.getElementById('description').value = desc;
    document.getElementById('eventId').value = id;
    document.getElementById('color').value = color || '#3788D8';
    document.querySelector('label[for=color]').style.color = color || '#3788D8';
    deleteBtn.style.display = (id && <?= json_encode($role === 'admin') ?>) ? 'inline-block' : 'none';
    modal.style.display = 'block';
  }

  window.closeModal = function () {
    modal.style.display = 'none';
  }

  form.onsubmit = function (e) {
    e.preventDefault();
    <?php if ($role === 'admin'): ?>
    const id = document.getElementById('eventId').value;
    const data = {
      id,
      title: document.getElementById('title').value,
      start: document.getElementById('start').value,
      end: document.getElementById('end').value,
      description: document.getElementById('description').value,
      color: document.getElementById('color').value
    };
    fetch('kg_simpan_event.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data)
    }).then(() => {
      calendar.refetchEvents();
      closeModal();
    });
    <?php endif; ?>
  }

  deleteBtn.onclick = function () {
    const id = document.getElementById('eventId').value;
    if (confirm('Yakin ingin menghapus acara ini?')) {
      fetch('kg_hapus_event.php?id=' + id)
        .then(() => {
          calendar.refetchEvents();
          closeModal();
        });
    }
  }
});
</script>
</body>
</html>