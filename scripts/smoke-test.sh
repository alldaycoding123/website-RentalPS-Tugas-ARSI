#!/usr/bin/env bash
# Smoke test PSRental — jalankan aplikasi dulu: php artisan serve --port=8000
# Pemakaian: bash scripts/smoke-test.sh [BASE_URL]
set -u
BASE="${1:-http://127.0.0.1:8000}"
PASS=0
FAIL=0

ok()   { PASS=$((PASS+1)); echo "  ✅ $1"; }
fail() { FAIL=$((FAIL+1)); echo "  ❌ $1"; }
assert_code() { # $1=kode aktual $2=kode harapan $3=pesan
  if [ "$1" = "$2" ]; then ok "$3 ($1)"; else fail "$3 — dapat $1, harap $2"; fi
}
csrf() { grep -o 'name="_token" value="[^"]*"' "$1" | head -1 | sed 's/.*value="//;s/"//'; }

echo "=== 1. HALAMAN PUBLIK ==="
assert_code "$(curl -s -o /dev/null -w '%{http_code}' "$BASE/")" 200 "beranda"
assert_code "$(curl -s -o /dev/null -w '%{http_code}' "$BASE/pelanggan/login")" 200 "login pelanggan"
assert_code "$(curl -s -o /dev/null -w '%{http_code}' "$BASE/staff/login")" 200 "login staff"

echo "=== 2. AUTH TANPA LOGIN (harus redirect) ==="
assert_code "$(curl -s -o /dev/null -w '%{http_code}' "$BASE/pelanggan/unit")" 302 "unit tanpa login"
assert_code "$(curl -s -o /dev/null -w '%{http_code}' "$BASE/admin/dashboard")" 302 "admin tanpa login"
assert_code "$(curl -s -o /dev/null -w '%{http_code}' "$BASE/kasir/dashboard")" 302 "kasir tanpa login"

echo "=== 3. ALUR PELANGGAN ==="
CJ=/tmp/sm-cust.jar; rm -f "$CJ"
curl -s -c "$CJ" "$BASE/pelanggan/login" -o /tmp/sm-login.html
T=$(csrf /tmp/sm-login.html)
assert_code "$(curl -s -b "$CJ" -c "$CJ" -o /dev/null -w '%{http_code}' -X POST "$BASE/pelanggan/login" \
  --data-urlencode "_token=$T" --data-urlencode "no_hp=081234567890")" 302 "login no. HP"
assert_code "$(curl -s -b "$CJ" -o /dev/null -w '%{http_code}' "$BASE/pelanggan/unit")" 200 "halaman unit"
assert_code "$(curl -s -b "$CJ" -o /dev/null -w '%{http_code}' "$BASE/api/units/status")" 200 "API status unit"

TGL=$(date -d tomorrow +%F 2>/dev/null || date -v+1d +%F)
curl -s -b "$CJ" -c "$CJ" "$BASE/pelanggan/unit/1/jadwal?tanggal=$TGL" -o /tmp/sm-jadwal.html
assert_code "$(head -c 50 /tmp/sm-jadwal.html | grep -q html && echo 200 || echo 500)" 200 "halaman jadwal"
SLOTS_JSON=$(curl -s -b "$CJ" "$BASE/api/units/1/slots?tanggal=$TGL")
echo "  ℹ️  contoh slot: $(echo "$SLOTS_JSON" | head -c 120)"

# Pilih 2 slot pertama yang benar-benar tersedia (urut), dari server
read -r S1 S2 <<< "$(php -r '
$j = json_decode(file_get_contents("php://stdin"), true);
$t = array_values(array_filter($j["slots"], fn($s) => $s["status"] === "tersedia"));
echo ($t[0]["jam"] ?? "")." ".($t[1]["jam"] ?? "");
' <<< "$SLOTS_JSON")"

if [ -z "${S1:-}" ]; then
  fail "tidak ada slot tersedia untuk besok"
else
  T2=$(csrf /tmp/sm-jadwal.html)
  assert_code "$(curl -s -b "$CJ" -c "$CJ" -o /dev/null -w '%{http_code}' -X POST "$BASE/pelanggan/unit/1/jadwal" \
    --data-urlencode "_token=$T2" --data-urlencode "tgl_main=$TGL" \
    --data-urlencode "slot_mulai[]=$S1" --data-urlencode "slot_mulai[]=$S2" \
    --data-urlencode "nama=Tester Lokal" --data-urlencode "no_hp=081234567890")" 302 "simpan pilihan slot"

  curl -s -b "$CJ" -c "$CJ" "$BASE/pelanggan/pembayaran" -o /tmp/sm-bayar.html
  assert_code "$(grep -q "Rincian Booking\|Konfirmasi" /tmp/sm-bayar.html && echo 200 || echo 500)" 200 "halaman pembayaran"
  grep -q "$S1 - $(printf '%02d' $((10#${S1:0:2}+2))):00" /tmp/sm-bayar.html \
    && ok "jam tampil benar ($S1–$((10#${S1:0:2}+2)):00)" || fail "jam tidak benar di halaman pembayaran"

  T3=$(csrf /tmp/sm-bayar.html)
  REDIR=$(curl -s -b "$CJ" -c "$CJ" -o /dev/null -w '%{redirect_url}' -X POST "$BASE/pelanggan/pembayaran" \
    --data-urlencode "_token=$T3" --data-urlencode "nama=Tester Lokal" \
    --data-urlencode "no_hp=081234567890" --data-urlencode "metode=TRANSFER_BANK")
  case "$REDIR" in
    */pelanggan/booking/*/sukses) ok "booking dibuat -> $REDIR" ;;
    *) fail "redirect sukses tidak sesuai: $REDIR" ;;
  esac
  KODE=$(echo "$REDIR" | sed 's|.*/booking/||;s|/sukses||')
  SUC=$(curl -s -b "$CJ" "$BASE/pelanggan/booking/$KODE/sukses")
  echo "$SUC" | grep -q "Booking Berhasil" && ok "halaman sukses tampil" || fail "halaman sukses tidak tampil"
  echo "$SUC" | grep -qE "$S1 - $(printf '%02d' $((10#${S1:0:2}+2))):00" && ok "jam di sukses benar" || fail "jam di sukses salah"
  echo "$SUC" | grep -q "$KODE" && ok "kode booking $KODE tampil" || fail "kode booking tidak tampil"

  # Booking ganda pada jam sama harus ditolak
  T2=$(csrf /tmp/sm-jadwal.html)
  OUT=$(curl -s -b "$CJ" -c "$CJ" -o /dev/null -w '%{http_code} %{redirect_url}' -X POST "$BASE/pelanggan/unit/1/jadwal" \
    --data-urlencode "_token=$T2" --data-urlencode "tgl_main=$TGL" \
    --data-urlencode "slot_mulai[]=$S1" --data-urlencode "slot_mulai[]=$S2" \
    --data-urlencode "nama=Tester" --data-urlencode "no_hp=081234567890")
  case "$OUT" in
    "302 "*jadwal*) ok "slot terpakai ditolak (tidak tersedia)" ;;
    *) fail "slot terpakai seharusnya ditolak: $OUT" ;;
  esac
fi

assert_code "$(curl -s -b "$CJ" -o /dev/null -w '%{http_code}' "$BASE/pelanggan/riwayat")" 200 "riwayat booking"
# Booking milik pelanggan lain harus 404
BK_LAIN=$(php -r 'require "vendor/autoload.php"; $app=require "bootstrap/app.php"; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
  $b = App\Models\Booking::where("id_pelanggan", "!=", 1)->first();
  echo $b ? $b->id_booking : "";')
if [ -n "$BK_LAIN" ]; then
  assert_code "$(curl -s -b "$CJ" -o /dev/null -w '%{http_code}' "$BASE/pelanggan/riwayat/$BK_LAIN")" 404 "riwayat booking milik orang lain (404)"
else
  fail "tidak ada booking milik pelanggan lain"
fi

echo "=== 4. ALUR KASIR ==="
KJ=/tmp/sm-kasir.jar; rm -f "$KJ"
curl -s -c "$KJ" "$BASE/staff/login" -o /tmp/sm-sl.html
T=$(csrf /tmp/sm-sl.html)
assert_code "$(curl -s -b "$KJ" -c "$KJ" -o /dev/null -w '%{http_code}' -X POST "$BASE/staff/login" \
  --data-urlencode "_token=$T" --data-urlencode "email=kasir@psrental.test" --data-urlencode "password=password123")" 302 "login kasir"
assert_code "$(curl -s -b "$KJ" -o /dev/null -w '%{http_code}' "$BASE/kasir/dashboard")" 200 "dashboard kasir"
assert_code "$(curl -s -b "$KJ" -o /dev/null -w '%{http_code}' "$BASE/admin/dashboard")" 302 "kasir tidak boleh akses admin"

# Booking hari ini berstatus CONFIRMED/PENDING untuk check-in
BK=$(php -r '
require "vendor/autoload.php"; $app = require "bootstrap/app.php"; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$b = App\Models\Booking::where("tgl_main", date("Y-m-d"))->whereIn("status", ["PENDING","CONFIRMED"])->first();
echo $b ? $b->id_booking : "";')
if [ -z "$BK" ]; then
  fail "tidak ada booking hari ini untuk check-in"
else
  T=$(curl -s -b "$KJ" "$BASE/kasir/checkin" -o /tmp/sm-ci.html; csrf /tmp/sm-ci.html)
  assert_code "$(curl -s -b "$KJ" -c "$KJ" -o /dev/null -w '%{http_code}' -X POST "$BASE/kasir/checkin/$BK" --data-urlencode "_token=$T")" 302 "proses check-in"
  ST=$(php -r 'require "vendor/autoload.php"; $app=require "bootstrap/app.php"; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    echo App\Models\Booking::find('"$BK"')->status;')
  [ "$ST" = "CHECKIN" ] && ok "status booking CHECKIN" || fail "status booking = $ST"

  T=$(curl -s -b "$KJ" "$BASE/kasir/checkout" -o /tmp/sm-co.html; csrf /tmp/sm-co.html)
  assert_code "$(curl -s -b "$KJ" -c "$KJ" -o /dev/null -w '%{http_code}' -X POST "$BASE/kasir/checkout/$BK" --data-urlencode "_token=$T")" 302 "proses check-out"
  ST=$(php -r 'require "vendor/autoload.php"; $app=require "bootstrap/app.php"; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    echo App\Models\Booking::find('"$BK"')->status;')
  [ "$ST" = "SELESAI" ] && ok "status booking SELESAI" || fail "status booking = $ST"
fi

assert_code "$(curl -s -b "$KJ" -o /dev/null -w '%{http_code}' "$BASE/kasir/pembayaran")" 200 "halaman pembayaran kasir"
PAY=$(php -r 'require "vendor/autoload.php"; $app=require "bootstrap/app.php"; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
  $p = App\Models\Booking::whereHas("pembayaran", fn($q) => $q->where("status","BELUM_BAYAR"))
      ->where("status","PENDING")->first();
  $p = $p ?? App\Models\Booking::whereHas("pembayaran", fn($q) => $q->where("status","BELUM_BAYAR"))->first();
  echo $p ? $p->id_booking : "";')
if [ -n "$PAY" ]; then
  T=$(curl -s -b "$KJ" "$BASE/kasir/pembayaran" -o /tmp/sm-pb.html; csrf /tmp/sm-pb.html)
  assert_code "$(curl -s -b "$KJ" -c "$KJ" -o /dev/null -w '%{http_code}' -X POST "$BASE/kasir/pembayaran/$PAY" \
    --data-urlencode "_token=$T" --data-urlencode "metode=E_WALLET" --data-urlencode "nominal=50000")" 302 "proses pembayaran"
  ST=$(php -r 'require "vendor/autoload.php"; $app=require "bootstrap/app.php"; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    $b = App\Models\Booking::find((int) $argv[1]); echo $b->pembayaran->status."|".$b->status;' "$PAY")
  [ "$(echo "$ST" | cut -d'|' -f1)" = "LUNAS" ] && ok "pembayaran LUNAS" || fail "pembayaran = $ST"
  [ "$(echo "$ST" | cut -d'|' -f2)" != "PENDING" ] && ok "booking PENDING -> $(echo "$ST" | cut -d'|' -f2)" || fail "status booking tetap PENDING"
else
  fail "tidak ada pembayaran BELUM_BAYAR"
fi

echo "=== 5. ALUR ADMIN ==="
AJ=/tmp/sm-admin.jar; rm -f "$AJ"
curl -s -c "$AJ" "$BASE/staff/login" -o /tmp/sm-sl.html
T=$(csrf /tmp/sm-sl.html)
assert_code "$(curl -s -b "$AJ" -c "$AJ" -o /dev/null -w '%{http_code}' -X POST "$BASE/staff/login" \
  --data-urlencode "_token=$T" --data-urlencode "email=admin@psrental.test" --data-urlencode "password=password123")" 302 "login admin"

for u in /admin/dashboard /admin/units /admin/jadwal /admin/booking /admin/pelanggans /admin/pembayaran /admin/laporan; do
  assert_code "$(curl -s -b "$AJ" -o /dev/null -w '%{http_code}' "$BASE$u")" 200 "GET $u"
done

# Tambah unit
T=$(curl -s -b "$AJ" "$BASE/admin/units/create" -o /tmp/sm-uc.html; csrf /tmp/sm-uc.html)
assert_code "$(curl -s -b "$AJ" -c "$AJ" -o /dev/null -w '%{http_code}' -X POST "$BASE/admin/units" \
  --data-urlencode "_token=$T" --data-urlencode "kode_unit=PS4-TEST" --data-urlencode "nama=PS4 Slim Test" \
  --data-urlencode "tipe=PS4" --data-urlencode "ruang=Ruang D" --data-urlencode 'tv=TV 32" Full HD' \
  --data-urlencode "harga_jam=7000" --data-urlencode "status=TERSEDIA")" 302 "tambah unit"
php -r 'require "vendor/autoload.php"; $app=require "bootstrap/app.php"; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
  $u = App\Models\UnitPs::where("kode_unit","PS4-TEST")->first();
  echo $u ? "" : "GAGAL";' | grep -q GAGAL && fail "unit baru tidak tersimpan" || ok "unit baru tersimpan"

# Unit dengan booking tidak boleh dihapus
T=$(curl -s -b "$AJ" "$BASE/admin/units" -o /tmp/sm-ul.html; csrf /tmp/sm-ul.html)
assert_code "$(curl -s -b "$AJ" -c "$AJ" -o /dev/null -w '%{http_code}' -X POST "$BASE/admin/units/1" \
  --data-urlencode "_token=$T" --data-urlencode "_method=DELETE")" 302 "hapus unit (dicek server)"
ERR=$(curl -s -b "$AJ" "$BASE/admin/units" | grep -o "masih memiliki data booking" | head -1)
[ -n "$ERR" ] && ok "hapus unit ber-booking DITOLAK" || fail "hapus unit ber-booking seharusnya ditolak"

# Hapus unit tanpa booking
UNIT_BARU=$(php -r 'require "vendor/autoload.php"; $app=require "bootstrap/app.php"; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
  echo App\Models\UnitPs::where("kode_unit","PS4-TEST")->first()->id_unit;')
assert_code "$(curl -s -b "$AJ" -c "$AJ" -o /dev/null -w '%{http_code}' -X POST "$BASE/admin/units/$UNIT_BARU" \
  --data-urlencode "_token=$T" --data-urlencode "_method=DELETE")" 302 "hapus unit tanpa booking"

# Booking walk-in
T=$(curl -s -b "$AJ" "$BASE/admin/booking/create" -o /tmp/sm-wk.html; csrf /tmp/sm-wk.html)
TGL2=$(date -d "+3 days" +%F 2>/dev/null || date -v+3d +%F)
REDIR=$(curl -s -b "$AJ" -c "$AJ" -o /dev/null -w '%{redirect_url}' -X POST "$BASE/admin/booking" \
  --data-urlencode "_token=$T" --data-urlencode "id_unit=3" --data-urlencode "id_pelanggan=1" \
  --data-urlencode "tgl_main=$TGL2" --data-urlencode "mulai=10:00" --data-urlencode "durasi_jam=2" \
  --data-urlencode "metode=BAYAR_DI_TEMPAT")
case "$REDIR" in
  */admin/booking*) ok "booking walk-in dibuat" ;;
  *) fail "booking walk-in gagal: $REDIR" ;;
esac
# Walk-in ganda harus ditolak
T=$(curl -s -b "$AJ" "$BASE/admin/booking/create" -o /tmp/sm-wk.html; csrf /tmp/sm-wk.html)
OUT=$(curl -s -b "$AJ" -c "$AJ" -o /tmp/sm-wk2.html -w '%{redirect_url}' -X POST "$BASE/admin/booking" \
  --data-urlencode "_token=$T" --data-urlencode "id_unit=3" --data-urlencode "id_pelanggan=1" \
  --data-urlencode "tgl_main=$TGL2" --data-urlencode "mulai=11:00" --data-urlencode "durasi_jam=2" \
  --data-urlencode "metode=BAYAR_DI_TEMPAT")
case "$OUT" in
  */create|"" ) ok "walk-in bentrok ditolak" ;;
  *) fail "walk-in bentrok seharusnya ditolak: $OUT" ;;
esac

# Pelanggan CRUD & laporan + CSV
T=$(curl -s -b "$AJ" "$BASE/admin/pelanggans/create" -o /tmp/sm-pc.html; csrf /tmp/sm-pc.html)
assert_code "$(curl -s -b "$AJ" -c "$AJ" -o /dev/null -w '%{http_code}' -X POST "$BASE/admin/pelanggans" \
  --data-urlencode "_token=$T" --data-urlencode "nama=Uji Coba" --data-urlencode "no_hp=089999999999")" 302 "tambah pelanggan"
assert_code "$(curl -s -b "$AJ" -o /dev/null -w '%{http_code}' "$BASE/admin/laporan")" 200 "GET laporan"
assert_code "$(curl -s -b "$AJ" -o /dev/null -w '%{http_code}' "$BASE/admin/laporan/csv?dari=2026-01-01&sampai=2026-12-31")" 200 "ekspor CSV"

echo "=== 6. ALUR MANAJER ==="
MJ=/tmp/sm-mgr.jar; rm -f "$MJ"
curl -s -c "$MJ" "$BASE/staff/login" -o /tmp/sm-sl.html
T=$(csrf /tmp/sm-sl.html)
assert_code "$(curl -s -b "$MJ" -c "$MJ" -o /dev/null -w '%{http_code}' -X POST "$BASE/staff/login" \
  --data-urlencode "_token=$T" --data-urlencode "email=manajer@psrental.test" --data-urlencode "password=password123")" 302 "login manajer"
for u in /manajer/dashboard /manajer/laporan/pendapatan /manajer/laporan/performa-unit /manajer/laporan/tren /manajer/api/pendapatan /api/chart/pendapatan; do
  assert_code "$(curl -s -b "$MJ" -o /dev/null -w '%{http_code}' "$BASE$u")" 200 "GET $u"
done
assert_code "$(curl -s -b "$MJ" -o /dev/null -w '%{http_code}' "$BASE/admin/dashboard")" 302 "manajer tidak boleh akses admin"

echo
echo "=== RINGKASAN: PASS=$PASS FAIL=$FAIL ==="
[ "$FAIL" -eq 0 ]
