<?php
// admin_reservations.php — fokus tampilan + hapus (bulk & per baris)
require_once '../api/config.php';
date_default_timezone_set('Asia/Jakarta'); // <-- memastikan greeting ikut jam Indonesia

if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf_token'];

/**
 * Salam waktu otomatis: pagi / siang / sore / malam
 */
function greetingTime() {
    $h = (int)date('H');
    if ($h < 12) return 'pagi';
    if ($h < 15) return 'siang';
    if ($h < 18) return 'sore';
    return 'malam';
}

/**
 * Fallback tanggal + waktu reservasi
 */
function reservationDatetimeText($date, $time) {
    $date = $date ?: '';
    $time = $time ?: '';

    if ($date && $time) {
        return trim($date . ' ' . $time);
    } elseif ($date) {
        return $date;
    } elseif ($time) {
        return $time;
    }
    return 'waktu belum tercatat';
}

/**
 * Parse sesi dari kolom time + duration.
 * - Jika time sudah berupa string sesi "10:00-12:00, 12:00-14:00" -> pakai langsung
 * - Jika time hanya "10:00:00" dan duration > 0 -> rekonstruksi per 2 jam
 */
function getSessionsFromRow($time, $duration) {
    $time = trim((string)$time);
    $duration = (int)$duration;

    if ($time === '') return [];

    // kalau sudah mengandung "-" artinya format sesi, langsung pecah
    if (strpos($time, '-') !== false || strpos($time, ',') !== false) {
        $parts = array_filter(array_map('trim', explode(',', $time)));
        return $parts;
    }

    // kalau tidak ada "-", anggap ini jam start (TIME) dan kita susun sesi berdasarkan durasi
    if ($duration <= 0) {
        return [$time];
    }

    $startTs = strtotime($time);
    if ($startTs === false) {
        return [$time];
    }

    // asumsi 1 sesi = 2 jam
    $sessionCount = max(1, (int)round($duration / 2));
    $sessions = [];
    for ($i = 0; $i < $sessionCount; $i++) {
        $sTs = strtotime('+' . (2 * $i) . ' hour', $startTs);
        $eTs = strtotime('+' . (2 * ($i + 1)) . ' hour', $startTs);
        $sLabel = date('H:i', $sTs);
        $eLabel = date('H:i', $eTs);
        $sessions[] = $sLabel . '-' . $eLabel;
    }
    return $sessions;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1.0" />
  <title>Manajemen Reservasi | Admin Embun Slowbar</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet"/>

  <style>
    body { background:#f6f8fb; }
    .container-narrow { max-width: 1200px; }
    .card { border-radius: 14px; box-shadow: 0 6px 18px rgba(0,0,0,.08); }
    .btn-back {
      display:inline-block;background:#0d6efd;color:#fff;border-radius:10px;
      padding:10px 16px;text-decoration:none;font-weight:600;
      box-shadow:0 4px 12px rgba(13,110,253,.35);transition:.25s;
    }
    .btn-back:hover { background:#0b5ed7; transform: translateY(-2px); color:#fff; }
    .table thead th { white-space: nowrap; }
    .badge { text-transform: uppercase; letter-spacing: .4px; }
    .filter-btns .btn { font-weight:600; }

    .time-dropdown-toggle {
      font-size: 0.8rem;
      padding: 4px 10px;
    }
    .time-dropdown-menu {
      font-size: 0.8rem;
      min-width: 160px;
    }
  </style>
</head>
<body>

<div class="container container-narrow py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <a href="../panels/admin.php" class="btn-back">
      <i class="fa-solid fa-chevron-left me-2"></i>Kembali ke Admin
    </a>

    <div class="d-flex gap-2">
      <!-- 🔥 Tombol menuju Reservations History -->
      <a href="../history/reservations_history.php" class="btn btn-warning fw-semibold">
        🕑 Riwayat Reservasi
      </a>

      <!-- Bulk delete -->
      <form id="bulkDeleteForm" action="admin_delete_reservasi.php" method="POST" class="d-flex gap-2 m-0">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
        <input type="hidden" name="ids" id="bulk_ids">
        <button type="submit" class="btn btn-danger fw-semibold" id="btnBulkDelete" disabled>
          <i class="fa-solid fa-trash me-2"></i>Hapus Terpilih
        </button>
      </form>
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-body d-flex flex-wrap gap-2 align-items-center">
      <div class="filter-btns btn-group" role="group" aria-label="Filter">
        <button class="btn btn-outline-primary active" data-filter="all">Semua</button>
        <button class="btn btn-outline-primary" data-filter="room">Ruangan</button>
        <button class="btn btn-outline-primary" data-filter="book">Buku</button>
        <button class="btn btn-outline-primary" data-filter="both">Ruangan + Buku</button>
      </div>
      <div class="ms-auto">
        <input id="searchInput" class="form-control" placeholder="Cari nama / WA / ruangan / buku..." />
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-header bg-white">
      <h4 class="mb-0">
        <i class="fa-solid fa-calendar-days me-2 text-primary"></i>Data Reservasi
      </h4>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table id="reservationsTable" class="table table-bordered table-hover align-middle mb-0">
          <thead class="table-dark">
            <tr class="text-center">
              <th style="width:36px"><input type="checkbox" id="chkAll"></th>
              <th>#</th>
              <th>Tipe</th>
              <th>Nama</th>
              <th>WA</th>
              <th>Ruangan</th>
              <th>Tanggal</th>
              <th>Waktu (Sesi)</th>
              <th>Durasi</th>
              <th>Orang</th>
              <th>Buku</th>
              <th>Catatan</th>
              <th>Dibuat</th>
              <th style="width:90px">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php
            try {
              $sql = "SELECT r.*, b.title AS book_title
                      FROM reservations r
                      LEFT JOIN books b ON r.book_id = b.id
                      ORDER BY r.created_at DESC";
              $stmt = $pdo->query($sql);
              $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

              if ($rows && count($rows)) {
                $no = 1;
                foreach ($rows as $row) {
                  // --- Deteksi tipe reservasi ---
                  $hasBook = !is_null($row['book_id']) && $row['book_id'] !== '';
                  $hasRoomData = (
                    !empty($row['room']) ||
                    !empty($row['date']) ||
                    !empty($row['time']) ||
                    !empty($row['people']) ||
                    !empty($row['duration'])
                  );

                  if ($hasBook && $hasRoomData) {
                    $type = 'both';
                    $typeBadge = '<span class="badge bg-warning text-dark">ROOM + BOOK</span>';
                  } elseif ($hasBook) {
                    $type = 'book';
                    $typeBadge = '<span class="badge bg-info">BOOK</span>';
                  } else {
                    $type = 'room';
                    $typeBadge = '<span class="badge bg-success">ROOM</span>';
                  }

                  // Nama
                  $displayName = $row['user_id'] ?: '-';

                  // Nomor WhatsApp
                  $waRaw    = $row['whatsapp'] ?? null;
                  $waNumber = $waRaw ? preg_replace('/\D+/', '', $waRaw) : null;

                  $room     = $row['room'] ?? null;
                  $date     = $row['date'] ?? null;
                  $time     = $row['time'] ?? null;
                  $duration = $row['duration'] ?? null;
                  $people   = $row['people'] ?? null;
                  $book     = $row['book_title'] ?? null;
                  $notes    = $row['notes'] ?? null;
                  $created  = $row['created_at'] ?? null;

                  if ($created) {
                    $dt = date_create($created);
                    if ($dt) {
                      $created = date_format($dt, 'd M Y H:i');
                    }
                  }

                  $tdRoom     = $hasRoomData && $room     ? htmlspecialchars($room, ENT_QUOTES)            : '–';
                  $tdDate     = $hasRoomData && $date     ? htmlspecialchars($date, ENT_QUOTES)            : '–';

                  // ---- Sesi waktu (dropdown) ----
                  $sessions = [];
                  if ($hasRoomData) {
                      $sessions = getSessionsFromRow($time, $duration);
                  }

                  if ($sessions) {
                      $countLabel = count($sessions) . ' sesi';
                      $dropdownId = 'timeDrop_' . (int)$row['id'];

                      $dropdownHtml  = '<div class="dropdown d-inline-block">';
                      $dropdownHtml .= '  <button class="btn btn-sm btn-outline-secondary dropdown-toggle time-dropdown-toggle" ';
                      $dropdownHtml .= '          type="button" id="'.htmlspecialchars($dropdownId, ENT_QUOTES).'" ';
                      $dropdownHtml .= '          data-bs-toggle="dropdown" aria-expanded="false">';
                      $dropdownHtml .=        htmlspecialchars($countLabel, ENT_QUOTES);
                      $dropdownHtml .= '  </button>';
                      $dropdownHtml .= '  <ul class="dropdown-menu dropdown-menu-end time-dropdown-menu" aria-labelledby="'.htmlspecialchars($dropdownId, ENT_QUOTES).'">';
                      foreach ($sessions as $sess) {
                          $dropdownHtml .= '<li><span class="dropdown-item">'.htmlspecialchars($sess, ENT_QUOTES).'</span></li>';
                      }
                      $dropdownHtml .= '  </ul>';
                      $dropdownHtml .= '</div>';

                      $tdTime = $dropdownHtml;
                  } else {
                      $tdTime = '–';
                  }

                  $tdDuration = $hasRoomData && $duration !== null
                                  ? htmlspecialchars($duration . ' jam', ENT_QUOTES) : '–';
                  $tdPeople   = $hasRoomData && $people   !== null
                                  ? htmlspecialchars($people, ENT_QUOTES)            : '–';
                  $tdBook     = $hasBook && $book
                                  ? htmlspecialchars($book, ENT_QUOTES)              : ($hasBook ? '(ID: '.htmlspecialchars($row['book_id']).')' : '–');

                  // ===== Pesan WhatsApp (dengan judul buku) =====
                  if ($waNumber) {
                    $greet = greetingTime();
                    $bookName = $book ?: 'yang dipesan';

                    if ($type === 'book' && !$hasRoomData) {
                        // BOOK ONLY: + judul buku
                        if (!empty($row['date'])) {
                            $tglPinjam = $row['date'];
                        } elseif (!empty($row['created_at'])) {
                            $tglPinjam = substr($row['created_at'], 0, 10); // YYYY-MM-DD
                        } else {
                            $tglPinjam = 'tanggal belum tercatat';
                        }

                        $waMessage = "Halo {$displayName}, selamat {$greet}, mau konfirmasi {$displayName} pinjam buku {$bookName} pada {$tglPinjam}. Apakah benar?";
                    } elseif ($type === 'room' && $hasRoomData && $sessions) {
                        // ROOM ONLY
                        $when = $date
                            ? $date . ' pukul ' . implode(', ', $sessions)
                            : implode(', ', $sessions);

                        $waMessage = "Halo {$displayName}, selamat {$greet}, mau konfirmasi reservasi ruangan pada {$when}. Apakah benar?";
                    } elseif ($type === 'both' && $hasRoomData && $sessions) {
                        // ROOM + BOOK (pakai judul buku juga)
                        $when = $date
                            ? $date . ' pukul ' . implode(', ', $sessions)
                            : implode(', ', $sessions);

                        $waMessage = "Halo {$displayName}, selamat {$greet}, mau konfirmasi {$displayName} pinjam buku {$bookName} dan reservasi ruangan pada {$when}. Apakah benar?";
                    } else {
                        // Fallback umum
                        $when = reservationDatetimeText($date, $time);
                        $waMessage = "Halo {$displayName}, selamat {$greet}, mau konfirmasi reservasi pada {$when}. Apakah benar?";
                    }

                    $waHref = 'https://wa.me/' . $waNumber . '?text=' . urlencode($waMessage);

                    $tdWa = '<a href="' . htmlspecialchars($waHref, ENT_QUOTES) . '" target="_blank" ' .
                            'class="btn btn-sm btn-success">' .
                            '<i class="fa-brands fa-whatsapp me-1"></i>' .
                            htmlspecialchars($waRaw, ENT_QUOTES) .
                            '</a>';
                  } else {
                    $tdWa = '–';
                  }

                  echo '<tr data-type="'.$type.'">';
                  echo '<td class="text-center">
                          <input type="checkbox" class="rowChk" value="'.htmlspecialchars($row['id']).'">
                        </td>';
                  echo '<td class="text-center">'.htmlspecialchars($no).'</td>';
                  echo '<td class="text-center">'.$typeBadge.'</td>';
                  echo '<td>'.htmlspecialchars($displayName, ENT_QUOTES).'</td>';
                  echo '<td class="text-center">'.$tdWa.'</td>';
                  echo '<td>'.$tdRoom.'</td>';
                  echo '<td class="text-center">'.$tdDate.'</td>';
                  echo '<td class="text-center">'.$tdTime.'</td>';
                  echo '<td class="text-center">'.$tdDuration.'</td>';
                  echo '<td class="text-center">'.$tdPeople.'</td>';
                  echo '<td>'.$tdBook.'</td>';
                  echo '<td>'.($notes ? htmlspecialchars($notes, ENT_QUOTES) : '–').'</td>';
                  echo '<td class="text-center">'.($created ? htmlspecialchars($created, ENT_QUOTES) : '–').'</td>';

                  // Hapus per-baris (POST + CSRF)
                  echo '<td class="text-center">
                          <form action="admin_delete_reservasi.php"
                                method="POST"
                                onsubmit="return confirm(\'Hapus reservasi ini?\')"
                                style="display:inline">
                            <input type="hidden" name="csrf_token" value="'.htmlspecialchars($csrf).'">
                            <input type="hidden" name="ids" value="'.htmlspecialchars($row['id']).'">
                            <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                              <i class="fa-solid fa-trash"></i>
                            </button>
                          </form>
                        </td>';

                  echo '</tr>';
                  $no++;
                }
              } else {
                echo '<tr><td colspan="14" class="text-center text-muted py-4">Belum ada reservasi.</td></tr>';
              }
            } catch (PDOException $e) {
              error_log('DB error reservations: '.$e->getMessage());
              echo '<tr><td colspan="14" class="text-center text-danger py-4">Gagal mengambil data. Hubungi administrator.</td></tr>';
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Filter + cari + bulk delete (select all)
(function(){
  const table      = document.getElementById('reservationsTable');
  const rows       = Array.from(table.querySelectorAll('tbody tr'));
  const filterBtns = document.querySelectorAll('.filter-btns .btn');
  const searchInput= document.getElementById('searchInput');

  const chkAll   = document.getElementById('chkAll');
  const rowChks  = () => Array.from(document.querySelectorAll('.rowChk'));
  const bulkBtn  = document.getElementById('btnBulkDelete');
  const bulkIds  = document.getElementById('bulk_ids');
  const bulkForm = document.getElementById('bulkDeleteForm');

  let currentFilter = 'all';
  let currentQuery  = '';

  function matchFilter(row){
    const type = row.getAttribute('data-type') || 'room';
    if (currentFilter === 'all') return true;
    return type === currentFilter;
  }

  function matchSearch(row){
    if (!currentQuery) return true;
    const text = row.innerText.toLowerCase();
    return text.includes(currentQuery);
  }

  function apply(){
    rows.forEach(r => {
      const ok = matchFilter(r) && matchSearch(r);
      r.style.display = ok ? '' : 'none';
    });
  }

  filterBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      filterBtns.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      currentFilter = btn.dataset.filter;
      apply();
    });
  });

  searchInput.addEventListener('input', () => {
    currentQuery = searchInput.value.trim().toLowerCase();
    apply();
  });

  function updateBulkState(){
    const selected = rowChks().filter(c => c.checked).map(c => c.value);
    bulkBtn.disabled = selected.length === 0;
    bulkIds.value    = selected.join(',');
  }

  chkAll?.addEventListener('change', () => {
    rowChks().forEach(c => c.checked = chkAll.checked);
    updateBulkState();
  });

  document.addEventListener('change', (e) => {
    if (e.target.classList?.contains('rowChk')) {
      const checks = rowChks();
      chkAll.checked = checks.length > 0 && checks.every(c => c.checked);
      updateBulkState();
    }
  });

  bulkForm?.addEventListener('submit', (e) => {
    if (bulkBtn.disabled) { e.preventDefault(); return; }
    if (!confirm('Hapus semua reservasi terpilih?')) e.preventDefault();
  });
})();
</script>

</body>
</html>
