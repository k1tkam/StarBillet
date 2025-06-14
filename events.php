<?php
session_start();
require_once 'database.php'; // Asegúrate de que getDBConnection() esté definido aquí.

$is_logged_in = isset($_SESSION['user_id']);
$user_email = $is_logged_in ? htmlspecialchars($_SESSION['user_email']) : '';
$user_first_name = $is_logged_in && isset($_SESSION['user_name']) ? htmlspecialchars(explode(' ', $_SESSION['user_name'])[0]) : '';

// --- Lógica de Búsqueda y Filtros ---
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
// Establecer el valor por defecto de selected_city a 'Bogota' si no está presente o está vacío
// Es crucial que 'Bogota' aquí y en la base de datos sea idéntico (case-sensitive si la DB lo es)
$selected_city = isset($_GET['city']) && !empty($_GET['city']) ? trim($_GET['city']) : 'Bogota';
$selected_date = isset($_GET['date']) ? trim($_GET['date']) : '';
$max_price = isset($_GET['price']) ? (int) $_GET['price'] : null;

$where_clauses = ["status = 'approved'"];
$params = [];

// Filtro por búsqueda general (nombre del evento, sala, ciudad)
if (!empty($search_query)) {
    $where_clauses[] = "(name LIKE :search_query OR venue LIKE :search_query OR city LIKE :search_query)";
    $params[':search_query'] = '%' . $search_query . '%';
}

// --- Filtro por Ciudad Mejorado ---
// Aplicar el filtro de ciudad a menos que 'Todas las Ciudades' esté seleccionado
if (strtolower($selected_city) !== 'todas las ciudades') {
    $where_clauses[] = "city = :city";
    $params[':city'] = $selected_city;
}
// Fin del Filtro por Ciudad Mejorado


// Filtro por fecha (se asume formato DD-MM-YYYY para la base de datos)
if (!empty($selected_date)) {
    $date_obj = DateTime::createFromFormat('d-m-Y', $selected_date);
    if ($date_obj) {
        $db_date = $date_obj->format('Y-m-d');
        // Usamos DATE() para comparar solo la parte de la fecha, ignorando la hora
        $where_clauses[] = "DATE(date) = :date_exact";
        $params[':date_exact'] = $db_date;
    }
}

// Filtro por precio máximo
if ($max_price !== null && $max_price >= 0) {
    $where_clauses[] = "price <= :max_price";
    $params[':max_price'] = $max_price;
}

$sql = "SELECT * FROM events";
if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(' AND ', $where_clauses);
}
$sql .= " ORDER BY date ASC";

try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    /*
    // --- Bloque de depuración: Descomenta para ver la consulta y los parámetros ---
    echo '<pre>';
    echo 'SQL: ' . $sql . "\n";
    echo 'Parámetros: ' . print_r($params, true) . "\n";
    echo '</pre>';
    // --- Fin Bloque de depuración ---
    */

} catch (PDOException $e) {
    error_log("Error al obtener los eventos: " . $e->getMessage());
    die("Lo sentimos, no pudimos cargar los eventos en este momento. Inténtalo de nuevo más tarde.");
}

// --- Fin Lógica de Búsqueda y Filtros ---
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>StarBillet</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Poppins:wght@700&display=swap"
        rel="stylesheet" />
    <link rel="stylesheet" href="style.css" />
    <link rel="icon" type="image/png" href="img/logoblanco.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://kit.fontawesome.com/YOUR_FONT_AWESOME_KIT_ID.js" crossorigin="anonymous"></script>

</head>

<body>
    <header>
        <nav role="navigation" aria-label="Main navigation">
            <div class="logo-section">
                <a href="index.php">
                    <div class="logo-wrapper">
                        <img src="img/logo.png" alt="Logo de StarBillet" class="logo-img" />
                        <div class="gif-wrapper">
                            <img id="gif-logo" src="img/giflogos.gif" alt="Animación del logo" class="gif-logo" />
                            <img id="static-logo" src="img/Logotipo3.png" alt="Logotipo final"
                                class="gif-logo static-logo" style="display: none;" />
                        </div>
                    </div>
                </a>
            </div>
            <div class="nav-links" role="menu" style="display: flex; align-items: center; gap: 1rem;">
                <a href="events.php" role="menuitem" tabindex="0">Eventos</a>
                <a href="contacto.php" role="menuitem" tabindex="0">Contáctanos</a>
                <?php if ($is_logged_in): ?>
                    <span style="color: var(--color-text-muted); font-size: 0.9rem;">
                        Hola, <?php echo $user_first_name; ?>
                    </span>
                    <a href="logout.php" role="menuitem" tabindex="0">Cerrar sesión</a>
                <?php else: ?>
                    <a href="login.php" role="menuitem" tabindex="0">Iniciar sesión</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <script>
        const gif = document.getElementById("gif-logo");
        const staticLogo = document.getElementById("static-logo");

        const gifDurationSeconds = 2;
        const timesToPlay = 2;

        setTimeout(() => {
            gif.style.display = "none";
            staticLogo.style.display = "block";
        }, gifDurationSeconds * timesToPlay * 1000);
    </script>

    <section class="busqueda-eventos">
        <div class="search-container">
            <div class="search-bar" onclick="openSearch()">
                <input type="text" placeholder="Busca por evento, sala o ciudad" readonly
                    value="<?= htmlspecialchars($search_query) ?>" />
                <i class="fas fa-search"></i>
            </div>
            <div class="filters">
                <button onclick="openCityModal()"><i class="fas fa-location-dot"></i> <span
                        id="currentCityFilter"><?= htmlspecialchars($selected_city) ?></span></button>
                <button onclick="openDateModal()"><i class="fas fa-calendar"></i> <span
                        id="currentDateFilter"><?= !empty($selected_date) ? htmlspecialchars($selected_date) : 'FECHA' ?></span></button>
                <button onclick="openPriceModal()"><i class="fas fa-dollar-sign"></i> <span
                        id="currentPriceFilter"><?= $max_price !== null ? 'HASTA $' . number_format($max_price, 0, ',', '.') : 'PRECIO' ?></span></button>
            </div>
        </div>
    </section>

    <div id="modal-root"></div>

    <script>
        let selectedCity = "<?= htmlspecialchars($selected_city) ?>";
        let selectedDate = "<?= htmlspecialchars($selected_date) ?>";
        let maxPrice = <?= $max_price !== null ? (int) $max_price : 'null' ?>;
        let generalSearchQuery = "<?= htmlspecialchars($search_query) ?>";

        function closeModal() {
            document.getElementById("modal-root").innerHTML = "";
        }

        function createModal(content) {
            document.getElementById("modal-root").innerHTML = `
                <div class="modal" onclick="closeModal()">
                    <div class="modal-content" onclick="event.stopPropagation()">
                        <button class="close-btn" onclick="closeModal()">✖</button>
                        ${content}
                    </div>
                </div>
            `;
        }

        function buildQueryString() {
            const params = new URLSearchParams();
            if (generalSearchQuery) {
                params.append('search', generalSearchQuery);
            }
            // Solo añadir el parámetro 'city' si no es 'Bogota' (valor por defecto) o 'Todas las Ciudades'
            if (selectedCity && selectedCity.toLowerCase() !== 'bogota' && selectedCity.toLowerCase() !== 'todas las ciudades') {
                params.append('city', selectedCity);
            }
            if (selectedDate) {
                params.append('date', selectedDate);
            }
            if (maxPrice !== null) {
                params.append('price', maxPrice);
            }
            return params.toString();
        }

        function applyFilters() {
            const queryString = buildQueryString();
            window.location.href = `index.php?${queryString}`;
        }

        // --- Ciudad Modal ---
        function openCityModal() {
            const cities = {
                "América del Norte": ["New York", "Los Angeles", "Chicago", "Houston", "Miami", "San Francisco", "Washington D.C.", "Atlanta", "Boston", "Seattle", "Toronto", "Vancouver", "Montreal", "Calgary", "Ottawa", "Edmonton", "Quebec City", "Winnipeg", "Hamilton", "Halifax", "Ciudad de Mexico", "Guadalajara", "Monterrey", "Puebla", "Tijuana", "Merida", "Cancun", "Queretaro", "Toluca", "Leon"],
                "América del Sur": ["Bogota", "Medellín", "Cali", "Barranquilla", "Cartagena", "Bucaramanga", "Pereira", "Manizales", "Cucuta", "Santa Marta", "São Paulo", "Rio de Janeiro", "Brasilia", "Salvador", "Belo Horizonte", "Curitiba", "Fortaleza", "Porto Alegre", "Manaus", "Recife", "Buenos Aires", "Cordoba", "Rosario", "Mendoza", "La Plata", "Mar del Plata", "San Miguel de Tucuman", "Salta", "Santa Fe", "Neuquen", "Santiago", "Valparaiso", "Concepcion", "La Serena", "Antofagasta", "Temuco", "Iquique", "Rancagua", "Talca", "Puerto Montt", "Lima", "Arequipa", "Cusco", "Trujillo", "Chiclayo", "Piura", "Iquitos", "Huancayo", "Tacna", "Puno", "Quito", "Guayaquil", "Cuenca", "Ambato", "Manta", "Portoviejo", "Loja", "Machala", "Santo Domingo", "Esmeraldas"],
                "América Central y Caribe": ["Ciudad de Panama", "Colon", "David", "Santiago", "Chitre", "La Chorrera", "Penonome", "Las Tablas", "Aguadulce", "Changuinola", "San Jose", "Managua", "Tegucigalpa", "San Salvador", "Ciudad de Guatemala", "Santo Domingo", "La Habana", "San Juan"]
            };

            let cityOptions = '<li onclick="selectCityFilter(\'Todas las Ciudades\')" style="font-weight: bold; padding-left: 10px; cursor: pointer; color: var(--color-primary);">Todas las Ciudades</li>';
            for (const region in cities) {
                cityOptions += `<li style="font-weight: bold; margin-top: 10px; color: var(--color-primary-dark);">${region}</li>`;
                cities[region].forEach(city => {
                    cityOptions += `<li onclick="selectCityFilter('${city}')" style="padding-left: 20px; cursor: pointer;">${city}</li>`;
                });
            }

            createModal(`
                <h3 style="margin-bottom: 10px;">Buscar ciudad</h3>
                <input type="text" id="citySearchInput" placeholder="Ej. Medellín, Cali..." style="padding: 10px; border-radius: 8px; border: none; width: 100%; margin-bottom: 10px;" />
                <ul id="cityList" style="list-style: none; padding:0; max-height: 250px; overflow-y: auto;">
                    ${cityOptions}
                </ul>
            `);

            setTimeout(() => {
                const input = document.getElementById("citySearchInput");
                const listItems = document.querySelectorAll("#cityList li");
                input.addEventListener("input", function () {
                    const filter = this.value.toLowerCase();
                    listItems.forEach(item => {
                        const isHeader = item.style.fontWeight === "bold";
                        const txt = item.textContent.toLowerCase();
                        if (!isHeader) {
                            item.style.display = txt.includes(filter) ? "" : "none";
                        } else {
                            // Headers are always visible, or you can implement more complex logic to hide them
                            // if no child elements are visible after filtering.
                            item.style.display = "";
                        }
                    });
                });
            }, 0);
        }

        function selectCityFilter(cityName) {
            selectedCity = cityName;
            document.getElementById('currentCityFilter').innerText = cityName;
            document.getElementById('selectedCityDisplay').innerText = `Eventos en ${cityName === 'Todas las Ciudades' ? 'Todos los Eventos' : cityName}`;
            closeModal();
            applyFilters();
        }

        // --- Fecha Modal ---
        function openDateModal() {
            createModal(`
                <h3>Seleccionar fecha</h3>
                <input id="calendarPicker" placeholder="Selecciona una fecha" style="padding: 10px; border-radius: 8px; border: none; width: 100%; margin-top: 10px;" />
            `);

            flatpickr("#calendarPicker", {
                locale: "es",
                dateFormat: "d-m-Y",
                minDate: "today",
                defaultDate: selectedDate,
                onChange: function (selectedDates, dateStr) {
                    selectedDate = dateStr;
                    document.getElementById('currentDateFilter').innerText = dateStr;
                    closeModal();
                    applyFilters();
                }
            });
        }

        // --- Precio Modal ---
        function openPriceModal() {
            createModal(`
                <h3>Filtrar por precio</h3>
                <label for="priceRange">Hasta: $<span id="priceValue">${maxPrice !== null ? parseInt(maxPrice).toLocaleString('es-CO') : '50000'}</span></label>
                <input type="range" id="priceRange" min="0" max="1000000" value="${maxPrice !== null ? maxPrice : '50000'}" step="1000" />
                <button onclick="applyPriceFilter()" style="margin-top: 20px; padding: 10px 15px; background-color: var(--color-primary); color: white; border: none; border-radius: 5px; cursor: pointer;">Aplicar</button>
            `);

            setTimeout(() => {
                const rangeInput = document.getElementById("priceRange");
                const priceValue = document.getElementById("priceValue");

                if (rangeInput && priceValue) {
                    rangeInput.addEventListener("input", function () {
                        priceValue.textContent = parseInt(this.value).toLocaleString('es-CO');
                    });
                }
            }, 0);
        }

        function applyPriceFilter() {
            maxPrice = document.getElementById("priceRange").value;
            document.getElementById('currentPriceFilter').innerText = `HASTA $${parseInt(maxPrice).toLocaleString('es-CO')}`;
            closeModal();
            applyFilters();
        }

        // --- Búsqueda General Modal ---
        function openSearch() {
            document.getElementById("searchOverlay").classList.add("active");
            setTimeout(() => {
                const searchInput = document.getElementById('generalSearchInput');
                searchInput.value = generalSearchQuery;
                searchInput.focus();
            }, 0);
        }

        function closeSearch() {
            document.getElementById("searchOverlay").classList.remove("active");
            generalSearchQuery = document.getElementById('generalSearchInput').value.trim();
            applyFilters();
        }

        document.addEventListener('DOMContentLoaded', () => {
            const generalSearchInput = document.getElementById('generalSearchInput');
            if (generalSearchInput) {
                generalSearchInput.addEventListener('keypress', function (event) {
                    if (event.key === 'Enter') {
                        closeSearch();
                    }
                });
            }

            // Inicializar el texto de los botones de filtro al cargar la página
            document.getElementById('currentCityFilter').innerText = selectedCity;
            if (selectedDate) {
                document.getElementById('currentDateFilter').innerText = selectedDate;
            } else {
                document.getElementById('currentDateFilter').innerText = 'FECHA';
            }
            if (maxPrice !== null) {
                document.getElementById('currentPriceFilter').innerText = `HASTA $${parseInt(maxPrice).toLocaleString('es-CO')}`;
            } else {
                document.getElementById('currentPriceFilter').innerText = 'PRECIO';
            }
            document.getElementById('selectedCityDisplay').innerText = `Eventos en ${selectedCity === 'Todas las Ciudades' ? 'Todos los Eventos' : selectedCity}`;
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/es.js"></script>


    <section class="ciudad-section">
        <div style="text-align: center; padding: 2rem 0;">
            <h2 id="selectedCityDisplay">Eventos en
                <?= htmlspecialchars($selected_city === 'Todas las Ciudades' ? 'Todos los Eventos' : $selected_city) ?>
            </h2>
        </div>
    </section>

    <section id="events" class="container-events">
        <div class="eventus">
            <?php if (count($events) > 0): ?>
                <?php foreach ($events as $event): ?>
                    <article class="card">
                        <img src="<?= htmlspecialchars($event['image_url']) ?>" alt="<?= htmlspecialchars($event['name']) ?>" />
                        <div class="card-content">
                            <h3><?= htmlspecialchars($event['name']) ?></h3>
                            <div class="date-location">
                                <?= date('D, d M', strtotime($event['date'])) ?> -
                                <?= htmlspecialchars($event['venue']) ?>
                            </div>
                            <div class="price">
                                <?= $event['price'] == 0 ? 'Desde Gratis' : 'Precio: $' . number_format($event['price'], 2, ',', '.') ?>
                            </div>
                            <?php if ($is_logged_in): ?>
                                <button class="btn-secondary" style="padding: 0.6rem 1rem; font-size: 0.6rem; font-weight: 500;
                                        background-color: #000; color: #fff; border: none; border-radius: 5px;
                                        display: block; margin: 0 auto; margin-top: auto; margin-bottom: 0.75rem;
                                        transition: background-color 0.3s, color 0.3s;"
                                    onmouseover="this.style.backgroundColor='#fff'; this.style.color='#000';"
                                    onmouseout="this.style.backgroundColor='#000'; this.style.color='#fff';">
                                    Comprar ahora
                                </button>
                            <?php else: ?>
                                <button class="btn-secondary" onclick="window.location.href='login.php'" style="padding: 0.6rem 1rem; font-size: 0.6rem; font-weight: 500;
                                        background-color: #000; color: #fff; border: none; border-radius: 5px;
                                        display: block; margin: 0 auto; margin-top: auto; margin-bottom: 0.75rem;
                                        transition: background-color 0.3s, color 0.3s;"
                                    onmouseover="this.style.backgroundColor='#fff'; this.style.color='#000';"
                                    onmouseout="this.style.backgroundColor='#000'; this.style.color='#fff';">
                                    Inicia sesión para comprar
                                </button>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="text-align: center; width: 100%;">No se encontraron eventos disponibles con los filtros aplicados.
                </p>
            <?php endif; ?>
        </div>
    </section>

    <section id="contact" class="container">
        <h2>Contáctanos</h2>
        <address style="font-style: normal; color: var(--color-text-muted);">
            <span class="label">Email:</span> <a href="mailto:contacto@starbillet.com">contacto@starbillet.com</a><br />
            <span class="label">Teléfono:</span> <a href="tel:+521234567890">+57 123 456 7890</a>
        </address>

    </section>

    <footer>
        &copy; 2025 StarBillet. Todos los derechos reservados.
    </footer>

    <div class="search-overlay" id="searchOverlay">
        <div class="search-modal">
            <input type="text" id="generalSearchInput" placeholder="Busca por evento, sala o ciudad" />
            <i class="fas fa-times" onclick="closeSearch()"></i>
        </div>
    </div>
</body>

</html>