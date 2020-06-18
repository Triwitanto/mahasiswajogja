<!DOCTYPE html>
<html lang="en-US">

<head>
    <meta charset='utf-8' />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="description" content="Aplikasi untuk pendataan alumni mahasiswa STMIK AKAKOM">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-title" content="Pendataan Alumni">
    <meta name="theme-color" content="#18a1d4" />
    <title>Pendataan Alumni</title>
    <link rel="manifest" href="manifest.json">
    <link rel="icon" href="img/icons/icon-152x152.png">
    <link rel="apple-touch-icon" href="img/icons/icon-152x152.png">
    <link rel="stylesheet" href="iconfont/material-icons.css">
    <link rel="stylesheet" href="css/materialize.min.css">
    <style>
        input[type=button] {
            background-color: #ff0000;
            color: white;
            padding: 5px;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <nav class="light-blue accent-4">
        <div class="nav-wrapper">
            <a href="/pwm" class="brand-logo center">Pendataan Alumni</a>
        </div>
    </nav>
    <div class="container">
        <div class="row">
            <div class="col s12 m12 l5">
                <div class="card-panel">
                    <h5 align="center">Tambah Data</h5>
                    <div class="row">
                        <form class="col s12" id='frmUtama'>
                            <div class="row">
                                <div class="input-field col s12">
                                    <i class="material-icons prefix">perm_identity</i>
                                    <input id="nim" name="nim" type="number" min="0" required />
                                    <label for="nim">NIM</label>
                                </div>
                            </div>
                            <div class="row">
                                <div class="input-field col s12">
                                    <i class="material-icons prefix">person</i>
                                    <input id="nama" name="nama" type="text" maxlength="50" required>
                                    <label for="nama">Nama</label>
                                </div>
                            </div>
                            <div class="row">
                                <div class="input-field col s12">
                                    <i class="material-icons prefix">turned_in</i>&nbsp;
                                    <input id="tahunLulus" name="tahunLulus" type="number" maxlength="4" pattern="[0-9]{4}" required>
                                    <label for="tahunLulus">Tahun Lulus</label>
                                </div>
                            </div>
                            <div class="row">
                                <div class="input-field col s12">
                                    <i class="material-icons prefix">grade</i>
                                    <input id="ipk" name="ipk" type="number" maxlength="3" step="0.1" min="0" max="4" required>
                                    <label for="ipk">IPK</label>
                                </div>
                                <div class="row">
                                    <div class="col s12">
                                        <button class="btn grey darken-4 waves-effect waves-light right" id='btnTambah' type="submit"><i class="material-icons left">add</i>Tambah</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="card-panel col s12 m12 l7">
                <h5 align="center">Data Alumni</h5>
                <div class="row" style="margin: 10px;">
                    <table class="bordered highlight">
                        <thead>
                            <tr>
                                <th>NIM</th>
                                <th>Nama</th>
                                <th>Tahun Lulus</th>
                                <th>IPK</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="tabel"></tbody>
                    </table>
                </div>
                <div class="row">
                    <div class="col s12">
                        <button class="btn red accent-4 waves-effect waves-light right" id='hapusData' type="submit"><i class="material-icons left">delete_forever</i>Hapus Semua</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="js/jquery-3.2.1.min.js"></script>
    <script src="js/materialize.min.js"></script>
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('service-worker.js').then(function(registration) {
                    // Registration was successful
                    // console.log('ServiceWorker registration successful with scope: ', registration.scope);
                }, function(err) {
                    // registration failed :(
                    // console.log('ServiceWorker registration failed: ', err);
                });
            });
        }

        var tabel = document.getElementById('tabel'),
            form = document.getElementById('frmUtama'),
            nim = document.getElementById('nim'),
            nama = document.getElementById('nama'),
            tahunLulus = document.getElementById('tahunLulus'),
            ipk = document.getElementById('ipk'),
            hapusData = document.getElementById('hapusData');

        buatDatabase();
        form.addEventListener('submit', tambahBaris);
        tabel.addEventListener('click', hapusBaris);
        hapusData.addEventListener('click', clearData);

        function buatDatabase() {
            if (!('indexedDB' in window)) {
                alert('Web Browser Anda tidak mendukung IndexedDB');
                return;
            }
            var request = window.indexedDB.open('latihan', 1);
            request.onerror = kesalahanHandler;
            request.onupgradeneeded = function(e) {
                var db = e.target.result;
                db.onerror = kesalahanHandler;
                var objectstore = db.createObjectStore('mahasiswa', {
                    keyPath: 'nim'
                });
                // console.log('Object store mahasiswa berhasil dibuat.');
            }
            request.onsuccess = function(e) {
                db = e.target.result;
                db.onerror = kesalahanHandler;
                // console.log('Berhasil melakukan koneksi ke database lokal.');
                bacaDariDatabase();
            }
        }

        function kesalahanHandler(e) {
            // console.log('Error Database: ' + e.target.errorCode);
        }

        function tambahBaris(e) {
            // Periksa apakah NIM sudah ada
            if (tabel.rows.namedItem(nim.value)) {
                alert('Error: Nim sudah terdaftar!');
                e.preventDefault();
                return;
            }
            // Tambah ke database
            tambahKeDatabase({
                nim: nim.value,
                nama: nama.value,
                tahunLulus: tahunLulus.value,
                ipk: ipk.value
            });
            // Membuat baris baru
            var baris = tabel.insertRow();
            baris.id = nim.value;
            baris.insertCell().appendChild(document.createTextNode(nim.value));
            baris.insertCell().appendChild(document.createTextNode(nama.value));
            baris.insertCell().appendChild(document.createTextNode(tahunLulus.value));
            baris.insertCell().appendChild(document.createTextNode(ipk.value));

            // Membuat tombol hapus 
            var btnHapus = document.createElement('input');
            btnHapus.type = 'button';
            btnHapus.className = 'btn red accent-4 right';
            btnHapus.value = 'Hapus';
            btnHapus.id = nim.value;
            baris.insertCell().appendChild(btnHapus);
            e.preventDefault();
        }

        function tambahKeDatabase(mahasiswa) {
            var objectstore = buatTransaksi().objectStore('mahasiswa');
            var request = objectstore.add(mahasiswa);
            request.onerror = kesalahanHandler;
            // request.onsuccess = console.log('Mahasiswa [' + mahasiswa.nim + '] telah ditambahkan ke database lokal.');
        }

        function hapusBaris(e) {
            if (e.target.type == 'button') {
                var hapus = confirm('Delete Record?');
                if (hapus) {
                    tabel.deleteRow(tabel.rows.namedItem(e.target.id).sectionRowIndex);
                    hapusDariDatabase(e.target.id);
                }
            }
        }
        // Hapus record dari database
        function hapusDariDatabase(nim) {
            var objectstore = buatTransaksi().objectStore('mahasiswa');
            var request = objectstore.delete(nim);
            request.onerror = kesalahanHandler;
            // request.onsuccess = console.log('Mahasiswa [' + nim + '] berhasil dihapus dari database lokal.');
        }

        function buatTransaksi() {
            var transaction = db.transaction(['mahasiswa'], 'readwrite');
            transaction.onerror = kesalahanHandler;
            // transaction.oncomplete = console.log('Transaksi baru saja diselesaikan.');
            return transaction;
        }

        // Menampilkan dari database
        function bacaDariDatabase() {
            var objectstore = buatTransaksi().objectStore('mahasiswa');
            objectstore.openCursor().onsuccess = function(e) {
                var result = e.target.result;
                if (result) {
                    // console.log('Membaca mahasiswa [' + result.value.nim + '] dari database.');
                    var baris = tabel.insertRow();
                    baris.id = result.value.nim;
                    baris.insertCell().appendChild(document.createTextNode(result.value.nim));
                    baris.insertCell().appendChild(document.createTextNode(result.value.nama));
                    baris.insertCell().appendChild(document.createTextNode(result.value.tahunLulus));
                    baris.insertCell().appendChild(document.createTextNode(result.value.ipk));
                    var btnHapus = document.createElement('input');
                    btnHapus.type = 'button';
                    btnHapus.className = 'btn red accent-4 right';
                    btnHapus.value = 'Hapus';
                    btnHapus.id = result.value.nim;
                    baris.insertCell().appendChild(btnHapus);
                    result.continue();
                }
            }
        }

        // Menghapus ObjectStore dari database IndexedDB
        function clearData() {
            var DBOpenRequest = window.indexedDB.open("latihan", 1);
            DBOpenRequest.onsuccess = function(e) {
                // console.log('Database terbuka.');
                db = DBOpenRequest.result;
                var transaction = db.transaction(["mahasiswa"], "readwrite");
                transaction.oncomplete = function(e) {
                    // console.log('Transaksi komplit.');
                };
                transaction.onerror = function(e) {
                    // console.log('Transaksi error: ' + transaction.error);
                };
                var objectStore = transaction.objectStore("mahasiswa");
                var objectStoreRequest = objectStore.clear();
                objectStoreRequest.onsuccess = function(e) {
                    // console.log('Request hapus data telah sukses.');
                };
                location.reload();
            };
        }
    </script>

</body>

</html>