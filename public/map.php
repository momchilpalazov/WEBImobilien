<?php
require_once "../config/database.php";
use App\Database;
require_once "../includes/header.php";

$db = Database::getInstance()->getConnection();

// Вземаме всички имоти с координати
$sql = "
    SELECT p.*, pi.image_path 
    FROM properties p
    LEFT JOIN property_images pi ON p.id = pi.property_id AND pi.is_main = 1
    WHERE p.latitude IS NOT NULL 
    AND p.longitude IS NOT NULL
    AND p.active = 1
";

$properties = $db->query($sql)->fetchAll();
?>

<div class="container-fluid mt-4">
    <div class="row">
        <!-- Филтри -->
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-3"><?php echo $translations['map']['filters']; ?></h5>
                    <form id="mapFilters">
                        <div class="mb-3">
                            <label class="form-label"><?php echo $translations['property']['type']['label']; ?></label>
                            <?php foreach ($translations['property']['type'] as $key => $value): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="types[]" 
                                           value="<?php echo $key; ?>" id="type_<?php echo $key; ?>" checked>
                                    <label class="form-check-label" for="type_<?php echo $key; ?>">
                                        <?php echo $value; ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><?php echo $translations['property']['status']['label']; ?></label>
                            <?php foreach ($translations['property']['status'] as $key => $value): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="statuses[]" 
                                           value="<?php echo $key; ?>" id="status_<?php echo $key; ?>" checked>
                                    <label class="form-check-label" for="status_<?php echo $key; ?>">
                                        <?php echo $value; ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Списък с имоти -->
            <div class="card mt-3">
                <div class="card-body">
                    <h5 class="card-title mb-3"><?php echo $translations['map']['properties_list']; ?></h5>
                    <div class="properties-list" style="max-height: 500px; overflow-y: auto;">
                        <?php foreach ($properties as $property): ?>
                            <div class="property-item mb-3" data-id="<?php echo $property['id']; ?>">
                                <div class="d-flex">
                                    <img src="<?php echo $property['image_path'] ? 'uploads/properties/' . $property['image_path'] : 'images/no-image.jpg'; ?>" 
                                         class="property-thumbnail" alt="<?php echo $property["title_{$current_language}"]; ?>">
                                    <div class="ms-3">
                                        <h6 class="mb-1"><?php echo $property["title_{$current_language}"]; ?></h6>
                                        <p class="mb-1">€<?php echo number_format($property['price']); ?></p>
                                        <small><?php echo $property['area']; ?> m²</small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Карта -->
        <div class="col-md-9">
            <div id="map" style="height: 800px;"></div>
        </div>
    </div>
</div>

<!-- Google Maps -->
<script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY"></script>
<script>
let map;
let markers = [];
const properties = <?php echo json_encode($properties); ?>;

function initMap() {
    // Център на България
    const center = { lat: 42.7339, lng: 25.4858 };
    
    map = new google.maps.Map(document.getElementById('map'), {
        zoom: 7,
        center: center
    });
    
    // Добавяме маркери за всички имоти
    properties.forEach(property => {
        addMarker(property);
    });
    
    // Клъстериране на маркерите
    const markerCluster = new MarkerClusterer(map, markers, {
        imagePath: 'https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m'
    });
}

function addMarker(property) {
    const marker = new google.maps.Marker({
        position: { 
            lat: parseFloat(property.latitude), 
            lng: parseFloat(property.longitude) 
        },
        map: map,
        title: property[`title_${currentLanguage}`]
    });
    
    // Информационен прозорец
    const infowindow = new google.maps.InfoWindow({
        content: `
            <div class="map-popup">
                <img src="${property.image_path ? 'uploads/properties/' + property.image_path : 'images/no-image.jpg'}" 
                     alt="${property[`title_${currentLanguage}`]}">
                <h5>${property[`title_${currentLanguage}`]}</h5>
                <p>€${Number(property.price).toLocaleString()}</p>
                <p>${property.area} m²</p>
                <a href="/property.php?id=${property.id}" class="btn btn-primary btn-sm">
                    ${translations.property.details}
                </a>
            </div>
        `
    });
    
    marker.addListener('click', () => {
        infowindow.open(map, marker);
    });
    
    markers.push(marker);
}

// Филтриране на маркери
document.getElementById('mapFilters').addEventListener('change', function() {
    const selectedTypes = [...document.querySelectorAll('input[name="types[]"]:checked')]
        .map(cb => cb.value);
    const selectedStatuses = [...document.querySelectorAll('input[name="statuses[]"]:checked')]
        .map(cb => cb.value);
    
    markers.forEach((marker, i) => {
        const property = properties[i];
        const isVisible = selectedTypes.includes(property.type) && 
                         selectedStatuses.includes(property.status);
        marker.setVisible(isVisible);
    });
});

// Инициализация на картата
google.maps.event.addDomListener(window, 'load', initMap);
</script>

<?php require_once "../includes/footer.php"; ?> 