<?php
$page_title = 'Hartă Interactivă';
require_once __DIR__ . '/includes/header.php';

$mysqli = getMySQLiConnection();
$privelisti_data = [];

if ($mysqli) {
    // Fetch approved perspectives with coordinates
    $result = $mysqli->query("SELECT * FROM privelisti WHERE status='aprobat' AND lat IS NOT NULL AND lng IS NOT NULL");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            // Default image if none provided
            if (empty($row['imagine'])) {
                $row['imagine'] = 'images/' . $row['tip'] . '.jpg';
            }
            $privelisti_data[] = $row;
        }
    }
}
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css">

<h2>Hartă interactivă priveliști</h2>

<div id="harta-controls">
    <label for="filtru-tip">Filtrează după tip:</label>
    <select id="filtru-tip">
        <option value="">Toate tipurile</option>
        <option value="munte">Munte</option>
        <option value="lac">Lac</option>
        <option value="mare">Mare</option>
        <option value="oras">Oraș</option>
    </select>
    <div id="harta-legenda" style="margin-top: 10px;">
        <span class="legenda-item legenda-munte" style="color: #27ae60; margin-right: 15px;">● Munte</span>
        <span class="legenda-item legenda-lac" style="color: #2980b9; margin-right: 15px;">● Lac</span>
        <span class="legenda-item legenda-mare" style="color: #1abc9c; margin-right: 15px;">● Mare</span>
        <span class="legenda-item legenda-oras" style="color: #e67e22;">● Oraș</span>
    </div>
</div>

<div id="harta-map" style="height: 500px; margin-top: 20px; border-radius: 8px; border: 2px solid #ccc;"></div>

<!-- Inject PHP data into JS -->
<script>
    var privelisti = <?php echo json_encode($privelisti_data); ?>;
    var judete = {
        'AB': 'Alba', 'AR': 'Arad', 'AG': 'Argeș', 'BC': 'Bacău', 'BH': 'Bihor', 
        'BN': 'Bistrița-Năsăud', 'BT': 'Botoșani', 'BV': 'Brașov', 'BR': 'Brăila', 
        'BZ': 'Buzău', 'CS': 'Caraș-Severin', 'CL': 'Călărași', 'CJ': 'Cluj', 
        'CT': 'Constanța', 'CV': 'Covasna', 'DB': 'Dâmbovița', 'DJ': 'Dolj', 
        'GL': 'Galați', 'GR': 'Giurgiu', 'GJ': 'Gorj', 'HR': 'Harghita', 
        'HD': 'Hunedoara', 'IL': 'Ialomița', 'IS': 'Iași', 'IF': 'Ilfov', 
        'MM': 'Maramureș', 'MH': 'Mehedinți', 'MS': 'Mureș', 'NT': 'Neamț', 
        'OT': 'Olt', 'PH': 'Prahova', 'SM': 'Satu Mare', 'SJ': 'Sălaj', 
        'SB': 'Sibiu', 'SV': 'Suceava', 'TR': 'Teleorman', 'TM': 'Timiș', 
        'TL': 'Tulcea', 'VS': 'Vaslui', 'VL': 'Vâlcea', 'VN': 'Vrancea', 'B': 'București'
    };
</script>

<script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="js/harta.js"></script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>