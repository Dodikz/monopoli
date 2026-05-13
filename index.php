<?php
session_start();
// Hapus baris require 'questions.php' sementara jika kamu belum membuat filenya, 
// atau biarkan jika file questions.php dari jawaban sebelumnya sudah ada.
@include 'questions.php'; 

// Reset Game
if (isset($_POST['reset'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

// Inisialisasi State Game
if (!isset($_SESSION['player_pos'])) {
    $_SESSION['player_pos'] = 0;
    $_SESSION['player_score'] = 0;
    $_SESSION['pesan'] = "Selamat datang di MONIKA! Ayo mulai perjalanan keliling duniamu.";
    $_SESSION['pertanyaan_aktif'] = null;
}

// Layout Papan dengan Tema Keliling Dunia & Ikon FontAwesome
$board = [
    0 => ['nama' => 'START', 'tipe' => 'aman', 'ikon' => 'fa-plane-departure', 'warna' => 'bg-secondary text-white'],
    1 => ['nama' => 'Tokyo (Kuartil)', 'tipe' => 'K', 'ikon' => 'fa-torii-gate', 'warna' => 'bg-info bg-opacity-25'],
    2 => ['nama' => 'Seoul (Kuartil)', 'tipe' => 'K', 'ikon' => 'fa-yin-yang', 'warna' => 'bg-info bg-opacity-25'],
    3 => ['nama' => 'Dana Umum', 'tipe' => 'bonus', 'ikon' => 'fa-suitcase-rolling', 'warna' => 'bg-warning bg-opacity-50'],
    4 => ['nama' => 'Paris (Median)', 'tipe' => 'M', 'ikon' => 'fa-archway', 'warna' => 'bg-success bg-opacity-25'],
    5 => ['nama' => 'London (Median)', 'tipe' => 'M', 'ikon' => 'fa-bus-simple', 'warna' => 'bg-success bg-opacity-25'],
    6 => ['nama' => 'Remedial', 'tipe' => 'aman', 'ikon' => 'fa-bed', 'warna' => 'bg-danger text-white'],
    7 => ['nama' => 'New York (Modus)', 'tipe' => 'D', 'ikon' => 'fa-city', 'warna' => 'bg-primary bg-opacity-25'],
    8 => ['nama' => 'Rio (Modus)', 'tipe' => 'D', 'ikon' => 'fa-umbrella-beach', 'warna' => 'bg-primary bg-opacity-25'],
    9 => ['nama' => 'Koper Misteri', 'tipe' => 'bonus', 'ikon' => 'fa-box-open', 'warna' => 'bg-warning bg-opacity-50'],
    10 => ['nama' => 'Kairo (Jangkauan)', 'tipe' => 'J', 'ikon' => 'fa-monument', 'warna' => 'bg-danger bg-opacity-25'],
    11 => ['nama' => 'Sydney (Rata-rata)', 'tipe' => 'R', 'ikon' => 'fa-bridge-water', 'warna' => 'bg-info bg-opacity-50'],
];

// Logika Lempar Dadu
if (isset($_POST['roll_dice']) && $_SESSION['pertanyaan_aktif'] === null) {
    $dadu = rand(1, 4); // Angka dadu 1-4
    $_SESSION['player_pos'] += $dadu;
    
    if ($_SESSION['player_pos'] >= count($board)) {
        $_SESSION['player_pos'] = $_SESSION['player_pos'] % count($board);
        $_SESSION['player_score'] += 20;
        $_SESSION['pesan'] = "Melewati Bandara START! (+20 Koin). ";
    } else {
        $_SESSION['pesan'] = "";
    }

    $petak = $board[$_SESSION['player_pos']];
    $_SESSION['pesan'] .= "Dadu: $dadu. Kamu mendarat di " . $petak['nama'] . ".";

    if ($petak['tipe'] == 'bonus') {
        $_SESSION['player_score'] += 15;
        $_SESSION['pesan'] .= " Yey! Dapat bonus 15 Koin.";
    } elseif ($petak['tipe'] != 'aman') {
        // Fallback jika belum ada file questions.php
        if(isset($questions)) {
            $tipe = $petak['tipe'];
            $_SESSION['pertanyaan_aktif'] = $questions[$tipe][array_rand($questions[$tipe])];
        } else {
            $_SESSION['pesan'] .= " (Soal belum tersedia, file questions.php belum terhubung).";
        }
    }
}

// Logika Menjawab (Bisa dimodifikasi nanti sesuai database soal)
if (isset($_POST['jawab'])) {
    $jawaban_user = trim($_POST['jawaban_user']);
    $jawaban_kunci = trim($_SESSION['pertanyaan_aktif']['jawaban_kunci']);
    $poin = $_SESSION['pertanyaan_aktif']['poin'];

    if (strtolower($jawaban_user) == strtolower($jawaban_kunci)) {
        $_SESSION['player_score'] += $poin;
        $_SESSION['pesan'] = "BENAR! +$poin Koin.";
    } else {
        $_SESSION['pesan'] = "SALAH! Jawaban yang benar: $jawaban_kunci.";
    }
    $_SESSION['pertanyaan_aktif'] = null;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MONIKA - Monopoli Statistika</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            background-color: #f0f2f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        /* CSS GRID RESPONSIVE */
        .board-grid {
            display: grid;
            gap: 10px;
            padding: 15px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        /* Mode Desktop: 4 Kolom ke samping */
        @media (min-width: 768px) {
            .board-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        /* Mode Mobile: 2 Kolom ke samping agar UI tidak kekecilan */
        @media (max-width: 767px) {
            .board-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        .tile {
            position: relative;
            min-height: 100px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            text-align: center;
            padding: 10px;
            transition: transform 0.2s;
        }

        .tile i {
            font-size: 2rem;
            margin-bottom: 8px;
            color: #495057;
        }

        .tile-name {
            font-size: 0.85rem;
            font-weight: 600;
        }

        .tile.active {
            border-color: #0d6efd;
            box-shadow: 0 0 15px rgba(13, 110, 253, 0.4);
            transform: scale(1.03);
        }

        /* Bidak Pemain */
        .player-pin {
            width: 30px;
            height: 30px;
            background-color: #dc3545;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            position: absolute;
            top: -10px;
            right: -10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.3);
            border: 2px solid white;
        }
    </style>
</head>
<body>

<div class="container py-4">
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <h2 class="fw-bold text-primary"><i class="fa-solid fa-earth-americas"></i> MONIKA</h2>
            <p class="text-muted mb-0">Monopoli Statistika - Edisi Keliling Dunia</p>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <div class="card bg-success text-white shadow-sm">
                <div class="card-body py-2">
                    <h5 class="mb-0"><i class="fa-solid fa-coins"></i> Koin: <?= $_SESSION['player_score'] ?></h5>
                </div>
            </div>
        </div>
    </div>

    <?php if ($_SESSION['pesan']): ?>
        <div class="alert alert-info shadow-sm" role="alert">
            <i class="fa-solid fa-circle-info"></i> <?= $_SESSION['pesan'] ?>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-8">
            <div class="board-grid">
                <?php foreach ($board as $index => $petak): ?>
                    <div class="tile <?= $petak['warna'] ?> <?= ($index == $_SESSION['player_pos']) ? 'active' : '' ?>">
                        <i class="fa-solid <?= $petak['ikon'] ?>"></i>
                        <span class="tile-name"><?= $petak['nama'] ?></span>
                        
                        <?php if ($index == $_SESSION['player_pos']): ?>
                            <div class="player-pin">
                                <i class="fa-solid fa-user-astronaut fa-xs"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-body text-center">
                    <?php if ($_SESSION['pertanyaan_aktif'] === null): ?>
                        <h5 class="card-title mb-3">Giliranmu!</h5>
                        <form method="POST">
                            <button type="submit" name="roll_dice" class="btn btn-primary btn-lg w-100 rounded-pill">
                                <i class="fa-solid fa-dice"></i> Lempar Dadu
                            </button>
                        </form>
                    <?php else: ?>
                        <h5 class="text-warning fw-bold mb-3"><i class="fa-solid fa-triangle-exclamation"></i> TANTANGAN!</h5>
                        <p class="border p-3 rounded bg-light"><?= $_SESSION['pertanyaan_aktif']['soal'] ?></p>
                        <form method="POST">
                            <input type="text" name="jawaban_user" class="form-control text-center mb-3" placeholder="Ketik jawabanmu..." required autocomplete="off">
                            <button type="submit" name="jawab" class="btn btn-success w-100 rounded-pill">Kunci Jawaban!</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <form method="POST">
                <button type="submit" name="reset" class="btn btn-outline-danger w-100">Mulai Ulang Game</button>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>