@php
    $currentRouteName = request()->route() ? request()->route()->getName() : '';
    $path = request()->path();

    // Determine current page key
    $pageKey = 'overview';
    if (str_contains($currentRouteName, 'logs.show') || str_contains($path, 'logs/')) {
        $pageKey = 'log-detail';
    } elseif (str_contains($currentRouteName, 'logs') || str_contains($path, 'logs')) {
        $pageKey = 'logs';
    } elseif (str_contains($currentRouteName, 'threat-map') || str_contains($path, 'threat-map')) {
        $pageKey = 'threat-map';
    } elseif (str_contains($currentRouteName, 'blocked-ips.show') || (str_contains($path, 'blocked-ips/') && !str_contains($path, 'toggle'))) {
        $pageKey = 'blocked-ip-detail';
    } elseif (str_contains($currentRouteName, 'blocked-ips') || str_contains($path, 'blocked-ips')) {
        $pageKey = 'blocked-ips';
    } elseif (str_contains($currentRouteName, 'allowed-ips') || str_contains($path, 'allowed-ips')) {
        $pageKey = 'allowed-ips';
    } elseif (str_contains($currentRouteName, 'server') || str_contains($path, 'server')) {
        $pageKey = 'server';
    } elseif (str_contains($currentRouteName, 'sessions') || str_contains($path, 'sessions')) {
        $pageKey = 'sessions';
    } elseif (str_contains($currentRouteName, 'tickets.show') || str_contains($path, 'tickets/')) {
        $pageKey = 'ticket-detail';
    } elseif (str_contains($currentRouteName, 'tickets') || str_contains($path, 'tickets')) {
        $pageKey = 'tickets';
    } elseif (str_contains($currentRouteName, 'settings') || str_contains($path, 'settings')) {
        $pageKey = 'settings';
    }

    $pageGuides = [
        'overview' => [
            'icon' => '📊',
            'title' => [
                'en' => 'Security Overview Dashboard',
                'id' => 'Ringkasan Keamanan (Security Overview)',
            ],
            'badge' => [
                'en' => 'Executive Telemetry & Threat Trends',
                'id' => 'Telemetri Eksekutif & Tren Ancaman',
            ],
            'purpose' => [
                'en' => 'The executive command center for your Linux VPS & web fleet security. It delivers instant visibility into live attack traffic, today\'s high-severity threats, active IP quarantines, and geographic origins of malicious actors.',
                'id' => 'Pusat komando eksekutif pertahanan Linux VPS & web fleet Anda. Halaman ini menyajikan visibilitas seketika terhadap lalu lintas serangan real-time, ancaman kritis hari ini, jumlah IP/perangkat yang dikarantina aktif, dan asal negara penyerang.',
            ],
            'features' => [
                'en' => [
                    ['title' => 'Real-Time KPI Metric Cards', 'desc' => 'Track logs recorded today, critical threat counts, active WAF quarantines, and total unique attacker IPs.'],
                    ['title' => 'Attack Trend Timeline', 'desc' => 'Interactive attack trend analysis with 7-day, 30-day, and 12-month views identifying attack volume and peak hours.'],
                    ['title' => 'Threats by Severity & Top Origins', 'desc' => 'Visual breakdown of threats across Critical, High, Medium, and Low tiers alongside the top foreign ASNs and countries.'],
                    ['title' => 'Live Incident Stream', 'desc' => 'Latest 10 security violations with instant client IP, HTTP target path, detection rules, and quick-action quarantine buttons.'],
                ],
                'id' => [
                    ['title' => 'Kartu Metrik KPI Real-Time', 'desc' => 'Pantau total log hari ini, serangan level kritis, karantina WAF aktif, dan jumlah IP penyerang unik.'],
                    ['title' => 'Grafik Tren Waktu Serangan', 'desc' => 'Analisis tren interaktif dengan pilihan rentang 7 hari, 30 hari, dan 12 bulan untuk melihat fluktuasi dan jam puncak serangan.'],
                    ['title' => 'Distribusi Tingkat Keparahan & Asal Negara', 'desc' => 'Pengelompokan ancaman berdasarkan level Kritis, Tinggi, Sedang, dan Rendah serta negara/ASN sumber serangan terbanyak.'],
                    ['title' => 'Stream Insiden Keamanan Terbaru', 'desc' => '10 serangan paling mutakhir lengkap dengan IP penyerang, path target HTTP, aturan pemicu, dan tombol karantina instan.'],
                ],
            ],
            'actions' => [
                'en' => [
                    ['title' => 'Timeframe Filter', 'desc' => 'Switch between Week (7d), Month (30d), and Year (12m) to inspect attack escalation patterns.'],
                    ['title' => 'Quarantine Suspicious IP', 'desc' => 'Click "Quarantine" on any active incident to instantly enforce Linux kernel or WAF blocking on that IP.'],
                    ['title' => 'Global Cache Clear & Refresh', 'desc' => 'Trigger the top Refresh button to flush framework view/route caches and retrieve fresh telemetry.'],
                ],
                'id' => [
                    ['title' => 'Filter Rentang Waktu', 'desc' => 'Beralih antara Minggu (7h), Bulan (30h), dan Tahun (12b) untuk mengamati pola eskalasi serangan siber.'],
                    ['title' => 'Karantina IP Cepat', 'desc' => 'Klik tombol "Karantina" pada baris insiden untuk menjatuhkan blokir WAF / kernel seketika terhadap IP tersebut.'],
                    ['title' => 'Refresh Global & Bersihkan Cache', 'desc' => 'Gunakan tombol Refresh di header atas untuk membersihkan cache framework dan memuat data terbaru.'],
                ],
            ],
            'tips' => [
                'en' => 'Review sudden spikes in Critical/High threats immediately. Combine Passive Monitoring with Auto-Block in Settings once normal baseline traffic is established.',
                'id' => 'Waspadai lonjakan mendadak pada kategori Kritis/Tinggi. Setelah pola lalu lintas normal terbentuk, pertimbangkan untuk mengaktifkan Auto-Block di menu Pengaturan.',
            ],
        ],
        'logs' => [
            'icon' => '📜',
            'title' => [
                'en' => 'Security Audit Logs',
                'id' => 'Log Keamanan & Forensik',
            ],
            'badge' => [
                'en' => 'Raw Telemetry & Evidence Inspector',
                'id' => 'Inspektor Forensik & Bukti Serangan',
            ],
            'purpose' => [
                'en' => 'Forensic investigation ledger logging every suspicious HTTP request, path traversal, SQL injection, scanner probe, or authentication anomaly intercepted by Pasak sensors.',
                'id' => 'Buku besar forensik yang mencatat setiap request HTTP mencurigakan, upaya path traversal, SQL injection, scanner otomatis, maupun anomali autentikasi yang diintersepsi sensor Pasak.',
            ],
            'features' => [
                'en' => [
                    ['title' => 'Multi-Criteria Filter Engine', 'desc' => 'Filter by threat level (Critical, High, Medium, Low), rule name, search query, date ranges, and custom page size.'],
                    ['title' => 'Forensic Attack Signature', 'desc' => 'Examine triggered rules, matched patterns, HTTP method, client user-agent, and incoming payload parameters.'],
                    ['title' => 'Device ID & Local IP Extraction', 'desc' => 'Extracts client device fingerprint and local subnet IP, enabling WiFi-safe granular blocking.'],
                    ['title' => 'Attack Trend Mini-HUD', 'desc' => 'Header statistics showing today\'s log distribution across severity levels.'],
                ],
                'id' => [
                    ['title' => 'Mesin Pencarian & Filter Bertingkat', 'desc' => 'Saring log berdasarkan level ancaman (Kritis, Tinggi, Sedang, Rendah), nama rule, kata kunci, tanggal, dan jumlah baris.'],
                    ['title' => 'Signature & Bukti Forensik Serangan', 'desc' => 'Teliti rule yang terpicu, pola muatan yang cocok, metode HTTP, user-agent penyerang, dan parameter payload.'],
                    ['title' => 'Ekstraksi Device ID & IP Lokal', 'desc' => 'Mendeteksi sidik jari perangkat dan IP lokal penyerang untuk memungkinkan pemblokiran perangkat yang aman pada WiFi bersama.'],
                    ['title' => 'Statistik Mini Tingkat Keparahan', 'desc' => 'Ringkasan jumlah serangan pada masing-masing level (Critical, High, Medium, Low) secara seketika.'],
                ],
            ],
            'actions' => [
                'en' => [
                    ['title' => 'Inspect Evidence Detail', 'desc' => 'Click "Detail" to open full forensic analysis including raw headers, payload dump, and timeline.'],
                    ['title' => 'Quarantine Modal', 'desc' => 'Click "Block IP" to quarantine the attacker with custom duration (e.g. 1h, 24h, permanent) and scope (IP vs Device).'],
                    ['title' => 'Purge Security Logs', 'desc' => 'Clean old logs using the prune action or configure automated database retention in Settings.'],
                ],
                'id' => [
                    ['title' => 'Periksa Detail Bukti', 'desc' => 'Klik tombol "Detail" untuk membuka analisis forensik menyeluruh mencakup raw header, dump payload, dan riwayat.'],
                    ['title' => 'Modal Karantina Terpadu', 'desc' => 'Klik "Karantina" untuk memblokir penyerang dengan durasi fleksibel (1 jam, 24 jam, permanen) dan pilihan lingkup IP/Perangkat.'],
                    ['title' => 'Pembersihan Riwayat Log', 'desc' => 'Bersihkan log lawas secara manual atau atur masa retensi database otomatis di menu Pengaturan.'],
                ],
            ],
            'tips' => [
                'en' => 'Hover over matched evidence text to see truncated payload data. Use the "Device" scope when blocking attackers on public or shared office networks to protect legitimate users.',
                'id' => 'Arahkan kursor ke teks bukti untuk melihat muatan payload lengkap. Gunakan lingkup "Perangkat" jika penyerang berada di jaringan WiFi kantor/publik agar pengguna lain tidak terdampak.',
            ],
        ],
        'threat-map' => [
            'icon' => '🌐',
            'title' => [
                'en' => 'Live Global Threat Map',
                'id' => 'Peta Ancaman Siber Real-Time',
            ],
            'badge' => [
                'en' => '3D Globe & 2D Tactical Geospatial Monitor',
                'id' => 'Monitor Spasial Globe 3D & Peta Datar 2D',
            ],
            'purpose' => [
                'en' => 'Visualizes live cyber attacks targeting your server on a geospatial canvas. Switch seamlessly between a 3D orthographic globe with real-time missile arc trajectories and a 2D high-density tactical flat map for SOC display wallboards.',
                'id' => 'Memvisualisasikan serangan siber yang menargetkan server Anda ke dalam kanvas geospasial interaktif. Anda dapat berpindah antara Globe 3D ortografis dengan lintasan serangan melengkung dan Peta Datar 2D resolusi tinggi untuk layar monitoring SOC.',
            ],
            'features' => [
                'en' => [
                    ['title' => '3D Globe & 2D Flat Projections', 'desc' => 'Interactive Canvas/WebGL rendering with smooth rotation, inertia drag, zooming, and auto-rotation toggle.'],
                    ['title' => 'Real-Time Server Target Node', 'desc' => 'Highlights your protected server\'s location as the central defense fortress with animated pulse waves.'],
                    ['title' => 'Attacker Geolocation Arcs', 'desc' => 'Arcs connecting attacker coordinates to your server color-coded by threat severity (Red = Critical, Yellow = Medium).'],
                    ['title' => 'Live Threat HUD Overlay', 'desc' => 'On-screen heads-up display showing live attack velocity, unique country breakdown, and recent hit log stream.'],
                ],
                'id' => [
                    ['title' => 'Proyeksi Globe 3D & Peta Datar 2D', 'desc' => 'Render interaktif Canvas dengan rotasi halus, drag inersia, pembesaran (zoom), dan saklar putar otomatis.'],
                    ['title' => 'Node Target Server Terlindungi', 'desc' => 'Menandai lokasi geografis server Anda sebagai pusat pertahanan utama dengan gelombang denyut pulsa animasi.'],
                    ['title' => 'Lintasan Busur Geolokasi Penyerang', 'desc' => 'Busur bercahaya yang menghubungkan koordinat penyerang ke server dengan warna sesuai tingkat keparahan.'],
                    ['title' => 'Panel HUD Telemetri Langsung', 'desc' => 'Panel status transparan yang menampilkan kecepatan serangan, daftar negara sumber ancaman, dan stream log terkini.'],
                ],
            ],
            'actions' => [
                'en' => [
                    ['title' => 'Projection Toggle', 'desc' => 'Toggle between 3D Globe and 2D Tactical Flat Map via the projection control button.'],
                    ['title' => 'Focus Target / Reset View', 'desc' => 'Click "Center on Server" or "Center on Indonesia" to immediately recalibrate camera view.'],
                    ['title' => 'Fullscreen SOC Mode', 'desc' => 'Enter fullscreen mode ideal for dedicated security operations center wall displays.'],
                ],
                'id' => [
                    ['title' => 'Ganti Mode Proyeksi', 'desc' => 'Beralih antara Globe 3D dan Peta Datar 2D secara instan melalui tombol proyeksi di sudut canvas.'],
                    ['title' => 'Pusatkan Pandangan', 'desc' => 'Gunakan tombol fokus untuk memusatkan kamera kembali ke koordinat server Anda atau wilayah Indonesia.'],
                    ['title' => 'Mode Layar Penuh (SOC Wallboard)', 'desc' => 'Masuk ke mode layar penuh tanpa gangguan navigasi, sangat cocok untuk layar monitor di Command Center/SOC.'],
                ],
            ],
            'tips' => [
                'en' => 'Ensure your server\'s latitude and longitude are configured in Settings or config/pasak.php so threat arcs land precisely on your true location.',
                'id' => 'Pastikan koordinat lintang dan bujur server Anda sudah diatur di Pengaturan agar pusat target pertahanan tepat berada di lokasi datacenter Anda.',
            ],
        ],
        'blocked-ips' => [
            'icon' => '🚫',
            'title' => [
                'en' => 'Quarantined & Blocked IPs',
                'id' => 'IP & Perangkat Diblokir (Quarantine)',
            ],
            'badge' => [
                'en' => 'Active WAF & Linux Kernel Quarantine',
                'id' => 'Karantina Aktif WAF & Kernel Linux',
            ],
            'purpose' => [
                'en' => 'Central repository of all quarantined IP addresses and device fingerprints. Blocked visitors receive immediate HTTP 403 responses and are halted before consuming application resources.',
                'id' => 'Pusat pengelolaan seluruh alamat IP dan fingerprint perangkat yang sedang dikarantina. Setiap permintaan dari entitas terblokir langsung ditolak dengan status HTTP 403 sebelum membebani aplikasi Anda.',
            ],
            'features' => [
                'en' => [
                    ['title' => 'Dual Quarantine Scopes', 'desc' => 'Supports blocking entire public IP addresses or isolating only malicious devices (WiFi-Safe isolation).'],
                    ['title' => 'Timestamp & Expiration Audit', 'desc' => 'Shows exact date and time when the block was enforced, plus expiration date or permanent status.'],
                    ['title' => 'Attack Hit Counter', 'desc' => 'Tracks how many unauthorized requests were dropped by the WAF after the block was enacted.'],
                    ['title' => 'Manual Block Creation', 'desc' => 'Quick form to immediately enforce a block with custom duration (e.g. 1h, 24h, permanent) and operational reason.'],
                ],
                'id' => [
                    ['title' => 'Dua Lingkup Karantina (IP vs Perangkat)', 'desc' => 'Mendukung pemblokiran seluruh IP publik router atau isolasi khusus perangkat penyerang (WiFi-Safe).'],
                    ['title' => 'Audit Waktu Pemblokiran & Kedaluwarsa', 'desc' => 'Menampilkan tanggal dan jam persis kapan IP diblokir beserta waktu kedaluwarsa atau status permanen.'],
                    ['title' => 'Penghitung Serangan Lanjutan (Hits)', 'desc' => 'Menghitung berapa kali request dari IP tersebut berhasil ditolak sistem setelah hukuman blokir dijatuhkan.'],
                    ['title' => 'Formulir Pemblokiran Manual', 'desc' => 'Tambahkan pemblokiran manual secara cepat dengan durasi fleksibel (mis. 24 jam atau permanen) dan alasan administratif.'],
                ],
            ],
            'actions' => [
                'en' => [
                    ['title' => 'Enable / Disable Block', 'desc' => 'Temporarily pause or reactivate quarantine for an IP without deleting its audit history.'],
                    ['title' => 'Permanent Delete', 'desc' => 'Permanently remove a block record from database to completely restore visitor access.'],
                    ['title' => 'View Intelligence Detail', 'desc' => 'Open the Block Detail page to inspect pre-block attack chronology, geolocation, and appeal tickets.'],
                ],
                'id' => [
                    ['title' => 'Aktifkan / Nonaktifkan Status Blokir', 'desc' => 'Jeda sementara atau aktifkan kembali pemblokiran tanpa menghapus riwayat insiden dari database.'],
                    ['title' => 'Hapus Catatan Permanen', 'desc' => 'Hapus entri blokir secara permanen untuk mengembalikan akses penuh bagi pengguna bersangkutan.'],
                    ['title' => 'Buka Detail Intelijen', 'desc' => 'Buka halaman detail untuk meninjau kronologi serangan pemicu blokir, geolokasi, dan tiket banding.'],
                ],
            ],
            'tips' => [
                'en' => 'When a legitimate user is locked out, check their device ID or IP, click "Disable" or delete the block, or advise them to submit a self-service unblock appeal ticket.',
                'id' => 'Jika pengguna sah terblokir tidak sengaja, cari IP mereka lalu klik "Nonaktifkan" atau arahkan mereka untuk mengajukan tiket banding melalui halaman error 403.',
            ],
        ],
        'allowed-ips' => [
            'icon' => '✅',
            'title' => [
                'en' => 'Allowed & Whitelisted IPs',
                'id' => 'Daftar IP Diizinkan (Allowlist / Whitelist)',
            ],
            'badge' => [
                'en' => 'Zero-Inspection Trusted Network Rules',
                'id' => 'Aturan Jaringan Terpercaya Bebas Inspeksi',
            ],
            'purpose' => [
                'en' => 'Defines trusted network addresses, subnets, and wildcard ranges that completely bypass Pasak security inspection and can never be blocked by automated rate limiting or honeypots.',
                'id' => 'Menentukan alamat IP, subnet kantor, atau rentang wildcard terpercaya yang sepenuhnya dibebaskan dari inspeksi WAF dan tidak akan pernah terblokir oleh sensor otomatis maupun honeypot.',
            ],
            'features' => [
                'en' => [
                    ['title' => 'Flexible IP Notation', 'desc' => 'Accepts single IPs (203.0.113.10), wildcard masks (192.168.1.*), or standard CIDR subnets (10.0.0.0/8).'],
                    ['title' => 'Time-Bound Whitelisting', 'desc' => 'Specify whitelist duration in hours (e.g. 8h for temporary maintenance) or leave 0 for permanent trust.'],
                    ['title' => 'Custom Descriptive Labels', 'desc' => 'Tag entries with labels like "HQ Office VPN", "Staging Server", or "Third-Party Webhook Provider".'],
                    ['title' => 'Allowed-At Audit Trail', 'desc' => 'Monitors the exact creation timestamp and remaining valid duration of every whitelist rule.'],
                ],
                'id' => [
                    ['title' => 'Notasi Pola IP Fleksibel', 'desc' => 'Menerima IP tunggal (203.0.113.10), wildcard bintang (192.168.1.*), maupun subnet CIDR (10.0.0.0/8).'],
                    ['title' => 'Durasi Izin Terbatas Waktu', 'desc' => 'Tentukan durasi izin dalam satuan jam (mis. 8 jam saat maintenance) atau isi 0 untuk izin permanen.'],
                    ['title' => 'Label Keterangan Khusus', 'desc' => 'Sematkan label pengenal seperti "VPN Kantor Pusat", "Server Webhook Payment Gateway", dsb.'],
                    ['title' => 'Jejak Audit Waktu Diizinkan', 'desc' => 'Mencatat tanggal dan jam pasti saat IP diizinkan serta menghitung sisa waktu kedaluwarsa.'],
                ],
            ],
            'actions' => [
                'en' => [
                    ['title' => 'Add Whitelist Entry', 'desc' => 'Submit IP pattern, optional label, and duration to immediately grant unmonitored access.'],
                    ['title' => 'Toggle Active Status', 'desc' => 'Temporarily suspend whitelist privilege without removing the rule configuration.'],
                    ['title' => 'Delete Entry', 'desc' => 'Remove the entry to restore normal security inspection for that network range.'],
                ],
                'id' => [
                    ['title' => 'Tambah Entri Izin', 'desc' => 'Kirimkan pola IP, label, dan durasi untuk segera memberikan hak akses bebas hambatan.'],
                    ['title' => 'Ubah Status Aktif/Nonaktif', 'desc' => 'Bekukan sementara hak akses istimewa tanpa perlu menghapus konfigurasi aturan.'],
                    ['title' => 'Hapus Aturan', 'desc' => 'Hapus entri dari daftar izin untuk mengembalikan pengawasan WAF normal pada rentang IP tersebut.'],
                ],
            ],
            'tips' => [
                'en' => 'Never whitelist broad public CIDRs (like 0.0.0.0/0). Restrict whitelists to known internal VPNs, server orchestrators, or static office gateway IPs.',
                'id' => 'Jangan pernah mengizinkan subnet publik yang terlalu luas (seperti 0.0.0.0/0). Batasi whitelist hanya untuk VPN kantor, server webhook resmi, atau IP statis terpercaya.',
            ],
        ],
        'server' => [
            'icon' => '🛡️',
            'title' => [
                'en' => 'Server Security & Integrity Monitor',
                'id' => 'Keamanan Server & Integritas Berkas',
            ],
            'badge' => [
                'en' => 'FIM Baseline & Suspicious File Scanner',
                'id' => 'Pemantau Integritas File & Anti-Webshell',
            ],
            'purpose' => [
                'en' => 'Deep file integrity monitoring (FIM) and suspicious code analysis. Detects unauthorized PHP files, hidden webshells, polyglot JPEG payloads, and unexpected file modifications across your codebase.',
                'id' => 'Pemantauan integritas berkas (FIM) mendalam dan analisis kode berbahaya. Mendeteksi file PHP liar, webshell terselubung, payload polyglot JPEG, dan modifikasi kode yang tidak sah.',
            ],
            'features' => [
                'en' => [
                    ['title' => 'Cryptographic Baseline Snapshots', 'desc' => 'Generates SHA-256 hashes of tracked application files to spot newly injected or modified files instantly.'],
                    ['title' => 'Polyglot & Webshell Scanner', 'desc' => 'Heuristic inspection targeting obfuscated PHP, eval/exec/passthru patterns, base64 payloads, and backdoor stubs.'],
                    ['title' => 'Suspicious File Alert Ledger', 'desc' => 'Lists flagged files with file size, modification timestamp, match rationale, and verified safe status.'],
                    ['title' => 'Host Environment Diagnostics', 'desc' => 'Audits Linux environment, storage permissions, PHP configuration, and active firewall subsystems.'],
                ],
                'id' => [
                    ['title' => 'Snapshot Baseline Kriptografis', 'desc' => 'Membuat hash SHA-256 dari seluruh berkas aplikasi untuk mendeteksi file sisipan atau modifikasi tak sah.'],
                    ['title' => 'Pemindai Webshell & Polyglot', 'desc' => 'Pemeriksaan heuristik terhadap fungsi berbahaya PHP (eval/exec/passthru), payload base64, dan pintu belakang.'],
                    ['title' => 'Daftar Peringatan Berkas Mencurigakan', 'desc' => 'Menyajikan berkas yang mencurigakan lengkap dengan ukuran, waktu perubahan, alasan deteksi, dan status verifikasi.'],
                    ['title' => 'Diagnostik Lingkungan Server', 'desc' => 'Memeriksa konfigurasi keamanan PHP, hak akses direktori storage, dan kesiapan subsistem pertahanan.'],
                ],
            ],
            'actions' => [
                'en' => [
                    ['title' => 'Rebuild Baseline', 'desc' => 'Update the file integrity baseline snapshot after legitimate deployments or code updates.'],
                    ['title' => 'Verify Safe / Delete File', 'desc' => 'Mark verified benign files as safe to suppress warnings, or permanently delete confirmed webshells.'],
                    ['title' => 'Download Suspicious File', 'desc' => 'Download the quarantined file safely to analyze its payload offline in an isolated sandbox.'],
                    ['title' => 'Global Cache Clearance', 'desc' => 'Trigger optimize:clear to reset view, config, and route caches safely.'],
                ],
                'id' => [
                    ['title' => 'Buat Ulang Baseline Integritas', 'desc' => 'Perbarui snapshot baseline berkas setelah Anda melakukan deployment resmi atau update kode aplikasi.'],
                    ['title' => 'Tandai Aman / Hapus Berkas', 'desc' => 'Tandai berkas aman untuk membungkam peringatan, atau hapus permanen berkas webshell yang berbahaya.'],
                    ['title' => 'Unduh Berkas untuk Analisis', 'desc' => 'Unduh berkas mencurigakan secara aman untuk diteliti payload-nya di lingkungan sandbox terisolasi.'],
                    ['title' => 'Bersihkan Cache (Optimize:clear)', 'desc' => 'Jalankan pembersihan cache menyeluruh untuk menyegarkan route, config, dan views framework.'],
                ],
            ],
            'tips' => [
                'en' => 'Always click "Rebuild Baseline" immediately after running git pull or deploying new releases so legitimate code changes are not flagged as intrusions.',
                'id' => 'Selalu perbarui baseline file setelah selesai melakukan rilis kode (git pull / composer install) agar perubahan kode yang sah tidak dianggap sebagai intrusi.',
            ],
        ],
        'sessions' => [
            'icon' => '👥',
            'title' => [
                'en' => 'User Sessions & Device Audit',
                'id' => 'Sesi Pengguna & Riwayat Login',
            ],
            'badge' => [
                'en' => 'Credential Shield & Account Takeover Defense',
                'id' => 'Pelindung Akun & Pertahanan Brute-Force',
            ],
            'purpose' => [
                'en' => 'Manages authenticated user sessions, tracks login device fingerprints, prevents account takeovers, and enforces brute-force lockout protections.',
                'id' => 'Mengelola sesi pengguna terotentikasi, melacak riwayat perangkat login, mencegah pengambilalihan akun (account takeover), dan menerapkan proteksi brute-force bertingkat.',
            ],
            'features' => [
                'en' => [
                    ['title' => 'Active Sessions & Device Fingerprinting', 'desc' => 'Monitors online users within the last 5 minutes, client browser, operating system, and IP address.'],
                    ['title' => 'Login History Audit Trail', 'desc' => 'Complete chronological record of all successful and failed authentication attempts across the fleet.'],
                    ['title' => 'Trusted IP Registration', 'desc' => 'Allows administrators to register their home/office IP as trusted, granting administrative immunity.'],
                    ['title' => 'Adaptive Brute-Force Lockouts', 'desc' => 'Tiered cooldown lockouts (1 min, 5 min, 15 min, up to 24 hours) after repeated failed login attempts.'],
                ],
                'id' => [
                    ['title' => 'Sesi Login Aktif & Sidik Jari Perangkat', 'desc' => 'Memantau pengguna online dalam 5 menit terakhir beserta browser, sistem operasi, dan alamat IP klien.'],
                    ['title' => 'Audit Riwayat Otentikasi', 'desc' => 'Catatan kronologis seluruh percobaan login yang berhasil maupun yang gagal di seluruh aplikasi.'],
                    ['title' => 'Registrasi IP Terpercaya', 'desc' => 'Memungkinkan administrator menyimpan IP saat ini sebagai IP terpercaya untuk akun mereka.'],
                    ['title' => 'Kunci Bertingkat Brute-Force', 'desc' => 'Menerapkan pendinginan bertingkat (cooldown 1m, 5m, 15m, hingga 24 jam) saat terdeteksi brute force login.'],
                ],
            ],
            'actions' => [
                'en' => [
                    ['title' => 'Force Logout Session', 'desc' => 'Immediately terminate an active session token to instantly kick a suspicious device off the application.'],
                    ['title' => 'Trust Current IP', 'desc' => 'Click "Trust my current IP" to store your administrative IP address safely in your profile.'],
                    ['title' => 'Delete Login Record', 'desc' => 'Permanently remove individual authentication audit logs when necessary.'],
                ],
                'id' => [
                    ['title' => 'Paksa Logout Sesi', 'desc' => 'Putus token sesi secara instan untuk mengeluarkan perangkat mencurigakan dari aplikasi seketika.'],
                    ['title' => 'Simpan IP Terpercaya', 'desc' => 'Klik "Simpan IP ini sebagai terpercaya" untuk mendaftarkan IP Anda agar terbebas dari salah blokir.'],
                    ['title' => 'Hapus Rekaman Riwayat', 'desc' => 'Hapus catatan audit sesi login secara permanen jika diperlukan pembersihan data.'],
                ],
            ],
            'tips' => [
                'en' => 'If an admin account is accidentally blocked by a honeypot, logging in successfully from a recognized browser will automatically lift the block.',
                'id' => 'Jika akun administrator tidak sengaja terblokir, login yang berhasil dari browser terverifikasi akan secara otomatis membuka blokir IP tersebut.',
            ],
        ],
        'tickets' => [
            'icon' => '🎫',
            'title' => [
                'en' => 'Unblock Appeals & Support Tickets',
                'id' => 'Tiket Banding Pembukaan Blokir',
            ],
            'badge' => [
                'en' => 'Visitor Self-Service Quarantine Appeal Desk',
                'id' => 'Layanan Banding Mandiri Pengguna Terblokir',
            ],
            'purpose' => [
                'en' => 'A structured self-service appeal system allowing quarantined legitimate visitors who encountered an HTTP 403 page to submit unblock requests with explanations for admin review.',
                'id' => 'Sistem penanganan banding mandiri yang memungkinkan pengunjung yang terkena halaman error 403 untuk mengirimkan permohonan pembukaan blokir beserta penjelasannya kepada administrator.',
            ],
            'features' => [
                'en' => [
                    ['title' => 'Public Appeal Submission', 'desc' => 'Blocked users submit their name, email address, IP address, and explanation directly from the 403 block page.'],
                    ['title' => 'Status Triage (Pending, Approved, Rejected)', 'desc' => 'Clear workflow badges sorting requests by pending review, approved releases, and rejected appeals.'],
                    ['title' => 'Automated Quarantine Lift', 'desc' => 'Approving an appeal immediately unblocks the associated IP address in both WAF and kernel tables.'],
                    ['title' => 'Audit History & Admin Response', 'desc' => 'Stores administrative notes explaining why an appeal was granted or dismissed for future reference.'],
                ],
                'id' => [
                    ['title' => 'Pengajuan Banding dari Halaman 403', 'desc' => 'Pengguna yang terblokir dapat mengisi nama, email, dan alasan langsung dari halaman blokir bawaan.'],
                    ['title' => 'Manajemen Status Tiket', 'desc' => 'Kategorisasi tiket yang jelas antara Menunggu (Pending), Disetujui (Approved), dan Ditolak (Rejected).'],
                    ['title' => 'Pencabutan Blokir Otomatis', 'desc' => 'Menyetujui tiket banding akan langsung menghapus hukuman blokir IP terkait dari database dan WAF.'],
                    ['title' => 'Catatan Keputusan Administrator', 'desc' => 'Menyimpan alasan penolakan atau persetujuan dari admin sebagai rekaman audit resmi.'],
                ],
            ],
            'actions' => [
                'en' => [
                    ['title' => 'Approve & Release', 'desc' => 'Click "Approve" to automatically validate the request and restore visitor access in one click.'],
                    ['title' => 'Reject Appeal', 'desc' => 'Dismiss illegitimate or confirmed attacker appeals with an optional explanation note.'],
                    ['title' => 'Inspect Ticket Detail', 'desc' => 'Open the full ticket detail view to cross-examine triggering attack logs with the user\'s appeal story.'],
                ],
                'id' => [
                    ['title' => 'Setujui & Buka Blokir', 'desc' => 'Klik "Setujui" untuk memverifikasi permohonan dan membuka pemblokiran IP secara otomatis.'],
                    ['title' => 'Tolak Permohonan Banding', 'desc' => 'Tolak permohonan dari penyerang yang tidak valid disertai catatan evaluasi jika diperlukan.'],
                    ['title' => 'Buka Detail Tiket', 'desc' => 'Tinjau rincian tiket untuk mencocokkan alasan pemohon dengan catatan log serangan yang memicu blokir.'],
                ],
            ],
            'tips' => [
                'en' => 'Always check the triggering logs on the Ticket Detail page before approving an unblock to confirm the original traffic was truly a false positive.',
                'id' => 'Periksa riwayat log yang memicu blokir pada halaman Detail Tiket sebelum menyetujui, guna memastikan bahwa insiden awal memang merupakan false-positive.',
            ],
        ],
        'settings' => [
            'icon' => '⚙️',
            'title' => [
                'en' => 'WAF & Sensor Security Settings',
                'id' => 'Pengaturan WAF & Sensor Keamanan',
            ],
            'badge' => [
                'en' => 'Enforcement Policies, Thresholds & Channels',
                'id' => 'Kebijakan Penegakan, Ambang Batas & Notifikasi',
            ],
            'purpose' => [
                'en' => 'The nerve center for configuring Pasak\'s security posture. Adjust automated quarantine thresholds, select enforcement modes (IP vs Device), tune rate limits, and set up notification webhooks.',
                'id' => 'Pusat kendali konfigurasi postur pertahanan Pasak. Di sini Anda dapat mengatur ambang batas penjatuhan sanksi blokir otomatis, mode penegakan (IP vs Perangkat), rate limiting, dan webhook notifikasi.',
            ],
            'features' => [
                'en' => [
                    ['title' => 'Enforcement Modes (Passive vs Active)', 'desc' => 'Switch between passive monitoring-only mode or active WAF quarantine enforcement (HTTP 403 block).'],
                    ['title' => 'Granular Threat Level Thresholds', 'desc' => 'Fine-tune hit tolerance before blocking across Critical (1 hit), High (3 hits), Medium (10 hits), and Low (25 hits).'],
                    ['title' => 'WiFi-Safe Quarantine Scope', 'desc' => 'Choose between locking only the attacker\'s device fingerprint or dropping the entire public gateway IP.'],
                    ['title' => 'Main Dashboard Navigation URL', 'desc' => 'Configure the destination link for the "Main Dashboard" header button to seamlessly integrate into your host app.'],
                ],
                'id' => [
                    ['title' => 'Mode Penegakan (Pasif vs Aktif)', 'desc' => 'Beralih antara mode pemantauan pasif (hanya catat log) atau mode pertahanan aktif (blokir otomatis HTTP 403).'],
                    ['title' => 'Ambang Batas Toleransi per Level', 'desc' => 'Atur batas toleransi hit sebelum pemblokiran otomatis untuk Kritis (1 hit), Tinggi, Sedang, dan Rendah.'],
                    ['title' => 'Lingkup Isolasi WiFi-Safe', 'desc' => 'Pilih apakah pemblokiran otomatis mengunci seluruh IP router atau hanya perangkat penyerang.'],
                    ['title' => 'URL Tautan Dashboard Utama', 'desc' => 'Atur tujuan tombol "Dashboard Utama" pada header untuk menghubungkan kembali ke aplikasi induk Anda.'],
                ],
            ],
            'actions' => [
                'en' => [
                    ['title' => 'Save Configuration', 'desc' => 'Commit all policy changes securely to database and apply them instantly to all web requests.'],
                    ['title' => 'Test Back URL Link', 'desc' => 'Verify that your configured main app URL opens correctly in a new tab.'],
                    ['title' => 'Toggle Dark Mode / Theme', 'desc' => 'Switch between Dark, Light, or System-following visual themes.'],
                ],
                'id' => [
                    ['title' => 'Simpan Pengaturan', 'desc' => 'Terapkan seluruh perubahan kebijakan ke database secara permanen seketika tanpa perlu restart server.'],
                    ['title' => 'Uji Tautan Dashboard Utama', 'desc' => 'Pastikan tautan kembali ke aplikasi utama yang Anda atur berfungsi dengan membuka tab baru.'],
                    ['title' => 'Ubah Tema Gelap / Terang', 'desc' => 'Sesuaikan tema antarmuka visual antara mode Gelap (Dark), Terang (Light), atau Otomatis (Sistem).'],
                ],
            ],
            'tips' => [
                'en' => 'Keep Critical threats set to threshold 1 so webshell injection and SQLi exploits are dropped on the very first probe.',
                'id' => 'Pertahankan ambang batas ancaman Kritis pada angka 1 agar serangan webshell, SQLi, dan RCE langsung dijatuhkan pada percobaan pertama.',
            ],
        ],
        'log-detail' => [
            'icon' => '🔍',
            'title' => [
                'en' => 'Forensic Threat Log Details',
                'id' => 'Detail Forensik Log Ancaman',
            ],
            'badge' => [
                'en' => 'Evidence Dumps & Attack Chronology',
                'id' => 'Dump Bukti Muatan & Kronologi Penyerangan',
            ],
            'purpose' => [
                'en' => 'In-depth forensic examination of a specific intercepted threat event. Review raw HTTP headers, query string payloads, device fingerprint data, and related incident telemetry.',
                'id' => 'Pemeriksaan forensik mendalam terhadap satu insiden ancaman yang terintersepsi. Analisis mencakup raw header HTTP, muatan query string / payload, data sidik jari perangkat, dan insiden terkait.',
            ],
            'features' => [
                'en' => [
                    ['title' => 'Evidence Inspector Box', 'desc' => 'Displays exact malicious patterns that triggered the WAF rule with syntax highlighting.'],
                    ['title' => 'Client Fingerprint & IP Telemetry', 'desc' => 'Full IP address, resolved geographic coordinates, client user-agent, and local IP.'],
                    ['title' => 'Attack Chronology from Same IP', 'desc' => 'Chronological table of other requests originating from this client leading up to this incident.'],
                ],
                'id' => [
                    ['title' => 'Inspeksi Kotak Bukti Muatan', 'desc' => 'Menampilkan potongan pola muatan berbahaya yang memicu aturan WAF secara terisolasi.'],
                    ['title' => 'Telemetri Klien & Geolokasi', 'desc' => 'Alamat IP lengkap, estimasi koordinat geolokasi, user-agent browser klien, dan identitas perangkat.'],
                    ['title' => 'Kronologi Serangan dari IP yang Sama', 'desc' => 'Tabel jejak serangan lain yang dikirimkan oleh klien ini secara berurutan dalam rentang waktu yang sama.'],
                ],
            ],
            'actions' => [
                'en' => [
                    ['title' => 'Quarantine Attacker', 'desc' => 'Instantly block this IP address or device directly from the forensic view.'],
                    ['title' => 'Back to Security Logs', 'desc' => 'Return to the master audit log stream with active filters preserved.'],
                ],
                'id' => [
                    ['title' => 'Karantina Penyerang', 'desc' => 'Blokir alamat IP atau perangkat ini seketika langsung dari halaman analisis forensik.'],
                    ['title' => 'Kembali ke Daftar Log', 'desc' => 'Kembali ke halaman utama daftar log keamanan dengan mempertahankan filter sebelumnya.'],
                ],
            ],
            'tips' => [
                'en' => 'Cross-check the User-Agent and query parameters. Automated vulnerability scanners often send distinctive fingerprint probes.',
                'id' => 'Perhatikan header User-Agent dan parameter URL. Scanner otomatis umumnya meninggalkan jejak signature yang khas.',
            ],
        ],
        'blocked-ip-detail' => [
            'icon' => '🔎',
            'title' => [
                'en' => 'Quarantined IP Intelligence & History',
                'id' => 'Intelijen & Riwayat IP Terblokir',
            ],
            'badge' => [
                'en' => 'Pre-Block vs Post-Block Attack Metrics',
                'id' => 'Metrik Serangan Sebelum & Sesudah Pemblokiran',
            ],
            'purpose' => [
                'en' => 'Detailed intelligence profile for a quarantined client. Compares attack logs leading up to the block against subsequent requests dropped with HTTP 403, and reviews associated unblock appeal tickets.',
                'id' => 'Profil intelijen lengkap bagi klien yang sedang dikarantina. Membandingkan seluruh log serangan sebelum pemblokiran dengan percobaan request setelah diblokir yang ditolak HTTP 403.',
            ],
            'features' => [
                'en' => [
                    ['title' => 'Quarantine Status Card', 'desc' => 'Examines block scope, source (manual vs auto-sensor), blocked timestamp, expiration, and total dropped hits.'],
                    ['title' => 'Pre-Block Incident Sequence', 'desc' => 'Chronological evidence log of malicious requests that caused the system to enforce the block.'],
                    ['title' => 'Post-Block Dropped Probes (HTTP 403)', 'desc' => 'Tracks persistent requests sent by the attacker after being blocked, proving the WAF is actively protecting the app.'],
                    ['title' => 'Appeal Tickets Desk', 'desc' => 'Reviews any unblock appeal tickets submitted by this IP address.'],
                ],
                'id' => [
                    ['title' => 'Kartu Status Karantina', 'desc' => 'Menampilkan lingkup blokir, sumber (manual vs sensor), waktu diblokir, waktu kedaluwarsa, dan total hit yang ditolak.'],
                    ['title' => 'Urutan Insiden Pemicu Blokir', 'desc' => 'Jejak rekaman serangan yang dilakukan oleh IP ini secara berurutan hingga kuota toleransi terlampaui.'],
                    ['title' => 'Aktivitas Pasca-Blokir (Ditolak 403)', 'desc' => 'Mencatat request yang tetap dikirimkan oleh penyerang setelah diblokir dan langsung digagalkan WAF.'],
                    ['title' => 'Riwayat Tiket Banding', 'desc' => 'Menampilkan permohonan banding pembukaan blokir yang pernah diajukan oleh pengguna dari IP ini.'],
                ],
            ],
            'actions' => [
                'en' => [
                    ['title' => 'Toggle Active Status', 'desc' => 'Temporarily lift or re-enable the quarantine without deleting the intelligence record.'],
                    ['title' => 'Delete Quarantine Record', 'desc' => 'Permanently delete the block to restore full access for this client.'],
                ],
                'id' => [
                    ['title' => 'Ubah Status Blokir', 'desc' => 'Buka sementara atau aktifkan kembali pemblokiran tanpa menghilangkan data jejak serangan.'],
                    ['title' => 'Hapus Catatan Karantina', 'desc' => 'Hapus hukuman blokir secara permanen untuk memulihkan akses pengguna ini sepenuhnya.'],
                ],
            ],
            'tips' => [
                'en' => 'A high post-block hit count indicates an aggressive automated bot continuing to hammer your server, confirming the block was warranted.',
                'id' => 'Jumlah hit pasca-blokir yang tinggi menandakan bot otomatis yang terus membombardir server, membuktikan bahwa sanksi blokir tepat sasaran.',
            ],
        ],
        'ticket-detail' => [
            'icon' => '📝',
            'title' => [
                'en' => 'Unblock Appeal Ticket Investigation',
                'id' => 'Investigasi Tiket Banding Pembukaan Blokir',
            ],
            'badge' => [
                'en' => 'Claim Verification & Admin Decision',
                'id' => 'Verifikasi Klaim & Keputusan Administrator',
            ],
            'purpose' => [
                'en' => 'Investigate and resolve an unblock appeal ticket submitted by a quarantined visitor. Cross-check their explanation against their recorded attack telemetry before deciding to approve or reject.',
                'id' => 'Meneliti dan menyelesaikan permohonan pembukaan blokir yang diajukan pengunjung. Bandingkan alasan pemohon dengan log bukti serangan yang tercatat sebelum memutuskan menerima atau menolak.',
            ],
            'features' => [
                'en' => [
                    ['title' => 'Requester Claim & Metadata', 'desc' => 'Displays requester name, email, IP address, submission timestamp, and their stated justification.'],
                    ['title' => 'Associated Block Record Context', 'desc' => 'Links directly to the corresponding quarantine record and active enforcement scope.'],
                    ['title' => 'Triggering Forensic Attack Log', 'desc' => 'Examines the exact request that originally triggered the block, enabling easy false-positive verification.'],
                ],
                'id' => [
                    ['title' => 'Klaim Pemohon & Data Pengenal', 'desc' => 'Menyajikan nama pemohon, alamat email, IP, waktu pengajuan, dan alasan pembukaan yang ditulis pemohon.'],
                    ['title' => 'Konteks Rekaman Blokir Terkait', 'desc' => 'Terhubung langsung dengan data blokir aktif dan lingkup penegakan yang sedang berjalan.'],
                    ['title' => 'Log Serangan Asal yang Memicu Blokir', 'desc' => 'Menampilkan request asli yang menyebabkan sistem memblokir IP ini agar Anda dapat memastikan false-positive.'],
                ],
            ],
            'actions' => [
                'en' => [
                    ['title' => 'Approve Appeal & Lift Block', 'desc' => 'Automatically lifts the IP/device block and marks the ticket as approved.'],
                    ['title' => 'Reject Appeal', 'desc' => 'Rejects the appeal while maintaining the active quarantine enforcement.'],
                ],
                'id' => [
                    ['title' => 'Setujui Banding & Buka Blokir', 'desc' => 'Membuka blokir IP/perangkat secara otomatis dan menandai tiket sebagai disetujui (Approved).'],
                    ['title' => 'Tolak Permohonan Banding', 'desc' => 'Menolak banding dan membiarkan status pemblokiran tetap aktif menolak akses penyerang.'],
                ],
            ],
            'tips' => [
                'en' => 'If the triggering log shows harmless behavior caused by a legitimate browser form submission, approve the appeal and consider tuning rule sensitivity in Settings.',
                'id' => 'Jika log pemicu menunjukkan aktivitas wajar dari form web biasa, setujui banding dan sesuaikan sensitivitas aturan di menu Pengaturan.',
            ],
        ],
    ];

    $guide = $pageGuides[$pageKey] ?? $pageGuides['overview'];
    $currentLang = in_array(app()->getLocale(), ['en', 'id'], true) ? app()->getLocale() : 'en';
@endphp

<!-- Page Support & Guide Modal Component -->
<div class="sec-modal-backdrop" id="pasakPageSupportModal" hidden role="dialog" aria-modal="true" aria-labelledby="pasakSupportModalTitle">
    <div class="sec-support-modal-box">
        <!-- Modal Header with Page Title & In-Modal Language Switcher -->
        <div class="sec-modal-header" style="display: flex; align-items: center; justify-content: space-between; gap: 14px; padding: 18px 24px; border-bottom: 1px solid var(--sec-border); background: var(--sec-card);">
            <div style="display: flex; align-items: center; gap: 12px; min-width: 0;">
                <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(37, 99, 235, 0.1); border: 1px solid rgba(37, 99, 235, 0.2); display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">
                    {{ $guide['icon'] }}
                </div>
                <div style="min-width: 0;">
                    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                        <h3 class="sec-card-title" id="pasakSupportModalTitle" style="font-size: 16px; margin: 0; font-weight: 700;">
                            <span data-guide-lang="en" style="{{ $currentLang === 'en' ? '' : 'display:none;' }}">{{ $guide['title']['en'] }}</span>
                            <span data-guide-lang="id" style="{{ $currentLang === 'id' ? '' : 'display:none;' }}">{{ $guide['title']['id'] }}</span>
                        </h3>
                        <span class="sec-badge sec-badge-low" style="font-size: 11px; font-weight: 600;">
                            <span data-guide-lang="en" style="{{ $currentLang === 'en' ? '' : 'display:none;' }}">{{ $guide['badge']['en'] }}</span>
                            <span data-guide-lang="id" style="{{ $currentLang === 'id' ? '' : 'display:none;' }}">{{ $guide['badge']['id'] }}</span>
                        </span>
                    </div>
                    <div style="font-size: 12px; color: var(--sec-text-muted); margin-top: 2px;">
                        <span data-guide-lang="en" style="{{ $currentLang === 'en' ? '' : 'display:none;' }}">Official Page Documentation & Usage Guide</span>
                        <span data-guide-lang="id" style="{{ $currentLang === 'id' ? '' : 'display:none;' }}">Dokumentasi & Panduan Resmi Penggunaan Halaman</span>
                    </div>
                </div>
            </div>

            <!-- Language Switcher & Close Button -->
            <div style="display: flex; align-items: center; gap: 8px; flex-shrink: 0;">
                <div class="sec-lang-switcher" style="height: 30px; padding: 2px 4px;" title="Switch Guide Language / Ganti Bahasa Panduan">
                    <button type="button" class="sec-lang-btn {{ $currentLang === 'en' ? 'is-active' : '' }}" onclick="window.pasakSwitchGuideLang('en');" id="pasakGuideLangEn" style="padding: 2px 6px; font-size: 11px;">EN</button>
                    <span class="sec-lang-sep" style="font-size: 10px;">/</span>
                    <button type="button" class="sec-lang-btn {{ $currentLang === 'id' ? 'is-active' : '' }}" onclick="window.pasakSwitchGuideLang('id');" id="pasakGuideLangId" style="padding: 2px 6px; font-size: 11px;">ID</button>
                </div>
                <button type="button" class="sec-btn sec-btn-ghost sec-btn-sm" onclick="window.pasakClosePageSupport();" title="{{ __('Tutup') }}" style="width: 30px; height: 30px; padding: 0; display: flex; align-items: center; justify-content: center; font-size: 18px; line-height: 1; border-radius: 6px;">
                    &times;
                </button>
            </div>
        </div>

        <!-- Modal Body Content -->
        <div class="sec-support-modal-body">
            <!-- 1. Purpose / Overview Section -->
            <div class="sec-support-section" style="border-left: 3px solid var(--sec-primary);">
                <div class="sec-support-section-title">
                    <span>🎯</span>
                    <span data-guide-lang="en" style="{{ $currentLang === 'en' ? '' : 'display:none;' }}">Page Purpose & Value</span>
                    <span data-guide-lang="id" style="{{ $currentLang === 'id' ? '' : 'display:none;' }}">Tujuan & Nilai Guna Halaman</span>
                </div>
                <p style="margin: 0; font-size: 13.5px; line-height: 1.6; color: var(--sec-text);">
                    <span data-guide-lang="en" style="{{ $currentLang === 'en' ? '' : 'display:none;' }}">{{ $guide['purpose']['en'] }}</span>
                    <span data-guide-lang="id" style="{{ $currentLang === 'id' ? '' : 'display:none;' }}">{{ $guide['purpose']['id'] }}</span>
                </p>
            </div>

            <!-- 2. Core Features Breakdown -->
            <div>
                <div class="sec-support-section-title">
                    <span>⚡</span>
                    <span data-guide-lang="en" style="{{ $currentLang === 'en' ? '' : 'display:none;' }}">Core Capabilities & Telemetry Widgets</span>
                    <span data-guide-lang="id" style="{{ $currentLang === 'id' ? '' : 'display:none;' }}">Kemampuan Utama & Widget Telemetri</span>
                </div>
                <div class="sec-support-grid">
                    @foreach ($guide['features']['en'] as $idx => $featEn)
                        @php $featId = $guide['features']['id'][$idx] ?? $featEn; @endphp
                        <div class="sec-support-item">
                            <div class="sec-support-item-title">
                                <span style="color: var(--sec-primary);">•</span>
                                <span data-guide-lang="en" style="{{ $currentLang === 'en' ? '' : 'display:none;' }}">{{ $featEn['title'] }}</span>
                                <span data-guide-lang="id" style="{{ $currentLang === 'id' ? '' : 'display:none;' }}">{{ $featId['title'] }}</span>
                            </div>
                            <div class="sec-support-item-desc">
                                <span data-guide-lang="en" style="{{ $currentLang === 'en' ? '' : 'display:none;' }}">{{ $featEn['desc'] }}</span>
                                <span data-guide-lang="id" style="{{ $currentLang === 'id' ? '' : 'display:none;' }}">{{ $featId['desc'] }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- 3. Action Guide -->
            <div>
                <div class="sec-support-section-title">
                    <span>🛠️</span>
                    <span data-guide-lang="en" style="{{ $currentLang === 'en' ? '' : 'display:none;' }}">Available Actions & Operator Guide</span>
                    <span data-guide-lang="id" style="{{ $currentLang === 'id' ? '' : 'display:none;' }}">Panduan Tindakan & Aksi Operator</span>
                </div>
                <div class="sec-support-grid">
                    @foreach ($guide['actions']['en'] as $idx => $actEn)
                        @php $actId = $guide['actions']['id'][$idx] ?? $actEn; @endphp
                        <div class="sec-support-item">
                            <div class="sec-support-item-title">
                                <span>▶️</span>
                                <span data-guide-lang="en" style="{{ $currentLang === 'en' ? '' : 'display:none;' }}">{{ $actEn['title'] }}</span>
                                <span data-guide-lang="id" style="{{ $currentLang === 'id' ? '' : 'display:none;' }}">{{ $actId['title'] }}</span>
                            </div>
                            <div class="sec-support-item-desc">
                                <span data-guide-lang="en" style="{{ $currentLang === 'en' ? '' : 'display:none;' }}">{{ $actEn['desc'] }}</span>
                                <span data-guide-lang="id" style="{{ $currentLang === 'id' ? '' : 'display:none;' }}">{{ $actId['desc'] }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- 4. Security Best Practice Tip -->
            <div class="sec-support-section" style="background: rgba(37, 99, 235, 0.05); border: 1px solid rgba(37, 99, 235, 0.2);">
                <div class="sec-support-section-title" style="color: var(--sec-primary);">
                    <span>💡</span>
                    <span data-guide-lang="en" style="{{ $currentLang === 'en' ? '' : 'display:none;' }}">Security Recommendation & Pro-Tip</span>
                    <span data-guide-lang="id" style="{{ $currentLang === 'id' ? '' : 'display:none;' }}">Rekomendasi Keamanan & Tips Ahli</span>
                </div>
                <p style="margin: 0; font-size: 13px; line-height: 1.55; color: var(--sec-text);">
                    <span data-guide-lang="en" style="{{ $currentLang === 'en' ? '' : 'display:none;' }}">{{ $guide['tips']['en'] }}</span>
                    <span data-guide-lang="id" style="{{ $currentLang === 'id' ? '' : 'display:none;' }}">{{ $guide['tips']['id'] }}</span>
                </p>
            </div>

            @yield('page_support_custom')
        </div>

        <!-- Modal Footer -->
        <div class="sec-modal-footer" style="display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 14px 24px; border-top: 1px solid var(--sec-border); background: var(--sec-card-hover);">
            <div style="font-size: 12px; color: var(--sec-text-muted);">
                <span>Pasak Autonomous Linux VPS & Web Security</span>
                <span> &bull; </span>
                <a href="https://github.com/robyajo/pasak" target="_blank" rel="noopener" style="color: var(--sec-primary); text-decoration: none; font-weight: 500;">GitHub Docs &rarr;</a>
            </div>
            <button type="button" class="sec-btn sec-btn-primary" onclick="window.pasakClosePageSupport();">
                <span data-guide-lang="en" style="{{ $currentLang === 'en' ? '' : 'display:none;' }}">Got it, continue</span>
                <span data-guide-lang="id" style="{{ $currentLang === 'id' ? '' : 'display:none;' }}">Mengerti, tutup</span>
            </button>
        </div>
    </div>
</div>

<script>
    (function() {
        // Modal Open / Close Handlers
        window.pasakOpenPageSupport = function() {
            var modal = document.getElementById('pasakPageSupportModal');
            if (modal) {
                modal.removeAttribute('hidden');
                document.body.style.overflow = 'hidden';
            }
        };

        window.pasakClosePageSupport = function() {
            var modal = document.getElementById('pasakPageSupportModal');
            if (modal) {
                modal.setAttribute('hidden', '');
                document.body.style.overflow = '';
            }
        };

        // In-modal language switcher without page reload
        window.pasakSwitchGuideLang = function(lang) {
            var enElements = document.querySelectorAll('[data-guide-lang="en"]');
            var idElements = document.querySelectorAll('[data-guide-lang="id"]');
            var btnEn = document.getElementById('pasakGuideLangEn');
            var btnId = document.getElementById('pasakGuideLangId');

            if (lang === 'id') {
                enElements.forEach(function(el) { el.style.display = 'none'; });
                idElements.forEach(function(el) { el.style.display = ''; });
                if (btnEn) btnEn.classList.remove('is-active');
                if (btnId) btnId.classList.add('is-active');
            } else {
                idElements.forEach(function(el) { el.style.display = 'none'; });
                enElements.forEach(function(el) { el.style.display = ''; });
                if (btnId) btnId.classList.remove('is-active');
                if (btnEn) btnEn.classList.add('is-active');
            }
        };

        // Close on ESC key or backdrop click
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' || e.key === 'Esc') {
                var modal = document.getElementById('pasakPageSupportModal');
                if (modal && !modal.hasAttribute('hidden')) {
                    window.pasakClosePageSupport();
                }
            }
        });

        var modalEl = document.getElementById('pasakPageSupportModal');
        if (modalEl) {
            modalEl.addEventListener('click', function(e) {
                if (e.target === modalEl) {
                    window.pasakClosePageSupport();
                }
            });
        }
    })();
</script>
