<?php
include 'koneksi.php';

$acara = [];
$sql = "SELECT * FROM acara";
$result = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_assoc($result)) {
    $acara[] = [
        'title' => $row['judul'],
        'start' => $row['tanggal_mulai'],
        'end'   => $row['tanggal_selesai']
    ];
}
?>

<div id="calendar"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  var calendarEl = document.getElementById('calendar');
  if (!calendarEl) return;

  var calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: 'dayGridMonth',
    locale: 'id',
    events: <?= json_encode($acara) ?>,
    headerToolbar: {
      left: 'prev,next today',
      center: 'title',
      right: 'dayGridMonth,listWeek'
    },
    height: 400
  });

  calendar.render();
});
</script>
