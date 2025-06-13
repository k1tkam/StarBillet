<?php
require_once 'database.php';
session_start();

// Verifica si el usuario ha iniciado sesión
$is_logged_in = isset($_SESSION['user_id']);
$user_email = $is_logged_in ? $_SESSION['user_email'] : '';

// Obtener todos los eventos de la base de datos
try {
    $pdo = getDBConnection();
    $stmt = $pdo->query("SELECT * FROM events ORDER BY date ASC");
    $events = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error al obtener los eventos: " . $e->getMessage());
}
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://kit.fontawesome.com/your-kit-id.js" crossorigin="anonymous"></script>

    <style>
    .busqueda-eventos {
        background-color: var(--color-white);
            padding: 2rem 1rem;
            display: flex;
            justify-content: center;
    }

    .search-container {
        display: flex;
        flex-direction: column;
        gap: 20px;
        align-items: center;
    }

    .search-bar {
    padding: 15px 20px;
    font-size: 1.1rem;
    border-radius: 15px;
    background-color: black;
    color: white;
    display: flex;
    align-items: center;
    gap: 15px;
    width: 100%;
    max-width: 600px;
    }

    .search-bar i {
    font-size: 1.3rem;
    }

    .search-bar input {
        background: transparent;
        border: none;
        outline: none;
        color: white;
        width: 100%;
    }

    .filters {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        justify-content: center;
    }

    .filters button {
    padding: 15px 20px;
    font-size: 1.1rem;
    border-radius: 12px;
    background-color: black;
    color: white;
    border: 2px solid white;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: background 0.3s, transform 0.2s;
    }

    .filters button:hover {
    background-color: #222;
    transform: scale(1.05);
    }

    .modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.7);
        display: flex;
        align-items: center;
        justify-content: center;
        animation: fadeIn 0.3s ease-in-out;
        z-index: 1000;
    }

    .modal-content {
    background: #111;
    padding: 30px;
    border-radius: 20px;
    margin-top: 20px; 
    animation: zoomIn 0.3s ease-in-out;
    color: white;
    width: 100%;
    max-width: 400px;
    }   

    .close-btn {
        background: none;
        border: none;
        color: white;
        font-size: 18px;
        float: right;
        cursor: pointer;
        margin-top: -20px;
        margin-right: -10px;
    }

    input[type=range]::-webkit-slider-thumb {
        background: white;
    }

    input[type=range] {
        accent-color: black;
        width: 100%;
    }

    @keyframes fadeIn {
        from { opacity: 0 }
        to { opacity: 1 }
    }

    @keyframes zoomIn {
        from {
            transform: scale(0.6);
            opacity: 0;
        }
        to {
            transform: scale(1);
            opacity: 1;
        }
    }

    #cityList li {
    padding: 10px;
    border-bottom: 1px solid #444;
    cursor: pointer;
    transition: background 0.2s;
    color: white;
    }

    #cityList li:hover {
        background-color: #222;
    }

    /* Botón de lupa inicial */
    .search-trigger {
    position: fixed;
    top: 20px;
    left: 20px;
    z-index: 1000;
    font-size: 20px;
    cursor: pointer;
    background: white;
    padding: 10px;
    border-radius: 50%;
    color: black;
    }

    /* Fondo oscuro */
    .search-overlay {
    display: flex;
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.8);
    z-index: 999;
    align-items: flex-start; 
    justify-content: center;
    padding-top: 80px;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.3s ease;
    }

    /* Caja del input */
    .search-modal {
    background: white;
    display: flex;
    align-items: center;
    padding: 15px 20px;
    border-radius: 30px;
    width: 90%;
    max-width: 600px;
    transform: scale(0.5) translateY(200px); 
    opacity: 0;
    transition: transform 0.4s ease, opacity 0.4s ease;
    }

    /* Input de texto */
    .search-modal input {
    flex: 1;
    font-size: 18px;
    border: none;
    outline: none;
    padding: 10px;
    }

    /* Ícono "X" */
    .search-modal i {
    font-size: 20px;
    cursor: pointer;
    color: #555;
    margin-left: 10px;
    }

    /* Clase activa (cuando se abre) */
    .search-overlay.active {
    opacity: 1;
    pointer-events: all;
    }

    .search-overlay.active .search-modal {
    transform: scale(1) translateY(0);
    opacity: 1;
    }

    .card {
    min-height: 420px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    }   


    </style>

</head>

<body>
    <header>
        <nav role="navigation" aria-label="Main navigation">
            <div class="logo-section">
                <a href="index.php"><div class="logo-section">
                <div class="logo-wrapper">
                    <img src="img/logo.png" alt="Logo de StarBillet" class="logo-img" />
                    <div class="gif-wrapper">
                        <img id="gif-logo" src="img/giflogos.gif" alt="Animación del logo" class="gif-logo" />
                        <img id="static-logo" src="img/Logotipo3.png" alt="Logotipo final" class="gif-logo static-logo"
                            style="display: none;" />
                    </div>
                </div>
            </div></a>
            </div>
            <div class="nav-links" role="menu" style="display: flex; align-items: center; gap: 1rem;">
                <a href="#events" role="menuitem" tabindex="0">Eventos</a>
                <a href="contacto.php" role="menuitem" tabindex="0">Contactanos</a>
                <?php if ($is_logged_in): ?>
                    <span style="color: var(--color-text-muted); font-size: 0.9rem;">
                        Hola, <?php echo htmlspecialchars(explode(' ', $_SESSION['user_name'])[0]); ?>
                    </span>
                    <a href="logout.php" role="menuitem" tabindex="0">Cerrar sesion</a>
                <?php else: ?>
                    <a href="login.php" role="menuitem" tabindex="0">Iniciar sesion</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <section>
    <section class="busqueda-eventos">
    <div class="search-container">
        <div class="search-bar" onclick="openSearch()">
            <input type="text" placeholder="Busca por evento, sala o ciudad" />
            <i class="fas fa-search"></i>
        </div>
        <div class="filters">
            <button onclick="openCityModal()"><i class="fas fa-location-dot"></i> BOGOTÁ</button>
            <button onclick="openDateModal()"><i class="fas fa-calendar"></i> FECHA</button>
            <button onclick="openPriceModal()"><i class="fas fa-dollar-sign"></i> PRECIO</button>
        </div>
    </div>
    </section>

    <div id="modal-root"></div>

    <script>
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

    function openCityModal() {
        createModal(`
            <h3>Buscar ciudad</h3>
            <input type="text" placeholder="Ej. Medellín, Cali..." style="padding: 10px; border-radius: 8px; border: none; width: 100%; margin-top: 10px;" />
        `);
    }

    function openDateModal() {
        createModal(`
            <h3>Seleccionar fecha</h3>
            <input id="calendarPicker" placeholder="Selecciona una fecha" style="padding: 10px; border-radius: 8px; border: none; width: 100%; margin-top: 10px;" />
        `);

        flatpickr("#calendarPicker", {
        locale: "es",
        dateFormat: "d-m-Y",
        minDate: "today",
        onChange: function(selectedDates, dateStr) {
            selectedDate = dateStr;
            document.querySelector('.filters button:nth-child(2)').innerHTML = `<i class="fas fa-calendar"></i> ${dateStr}`;
            closeModal();
            }
        });
    }

    function openPriceModal() {
    createModal(`
        <h3>Filtrar por precio</h3>
        <label for="priceRange">Hasta: $<span id="priceValue">500</span></label>
        <input type="range" id="priceRange" min="0" max="1000" value="500" step="50" />
    `);

    // Aquí sí funciona porque está fuera del innerHTML
    setTimeout(() => {
        const rangeInput = document.getElementById("priceRange");
        const priceValue = document.getElementById("priceValue");

        if (rangeInput && priceValue) {
            rangeInput.addEventListener("input", function() {
                priceValue.textContent = this.value;
            });
        }
    }, 0);
    }

    function openCityModal() {
    const cities = {
        "Estados Unidos": ["New York", "Los Angeles", "Chicago", "Houston", "Miami", "San Francisco", "Washington D.C.", "Atlanta", "Boston", "Seattle"],
        "Canadá": ["Toronto", "Vancouver", "Montreal", "Calgary", "Ottawa", "Edmonton", "Quebec City", "Winnipeg", "Hamilton", "Halifax"],
        "México": ["Ciudad de Mexico", "Guadalajara", "Monterrey", "Puebla", "Tijuana", "Merida", "Cancun", "Queretaro", "Toluca", "Leon"],
        "Brasil": ["São Paulo", "Rio de Janeiro", "Brasilia", "Salvador", "Belo Horizonte", "Curitiba", "Fortaleza", "Porto Alegre", "Manaus", "Recife"],
        "Argentina": ["Buenos Aires", "Cordoba", "Rosario", "Mendoza", "La Plata", "Mar del Plata", "San Miguel de Tucuman", "Salta", "Santa Fe", "Neuquen"],
        "Colombia": ["Bogota", "Medellín", "Cali", "Barranquilla", "Cartagena", "Bucaramanga", "Pereira", "Manizales", "Cucuta", "Santa Marta"],
        "Chile": ["Santiago", "Valparaiso", "Concepcion", "La Serena", "Antofagasta", "Temuco", "Iquique", "Rancagua", "Talca", "Puerto Montt"],
        "Perú": ["Lima", "Arequipa", "Cusco", "Trujillo", "Chiclayo", "Piura", "Iquitos", "Huancayo", "Tacna", "Puno"],
        "Ecuador": ["Quito", "Guayaquil", "Cuenca", "Ambato", "Manta", "Portoviejo", "Loja", "Machala", "Santo Domingo", "Esmeraldas"],
        "Panamá": ["Ciudad de Panama", "Colon", "David", "Santiago", "Chitre", "La Chorrera", "Penonome", "Las Tablas", "Aguadulce", "Changuinola"]
    };

    let cityOptions = '';

    for (const country in cities) {
        cityOptions += `<li style="font-weight: bold; margin-top: 10px;">${country}</li>`;
        cities[country].forEach(city => {
            cityOptions += `<li onclick="selectCity('${city}')" style="padding-left: 10px;">${city}</li>`;
        });
    }

    createModal(`
        <h3 style="margin-bottom: 10px;">Buscar ciudad</h3>
        <input type="text" id="citySearchInput" placeholder="Ej. Medellín, Cali..." style="padding: 10px; border-radius: 8px; border: none; width: 100%; margin-bottom: 10px;" />
        <ul id="cityList" style="list-style: none; padding:0; max-height: 250px; overflow-y: auto;">
            ${cityOptions}
        </ul>
    `);

    // Filtro en tiempo real
    setTimeout(() => {
        const input = document.getElementById("citySearchInput");
        const listItems = document.querySelectorAll("#cityList li");

        input.addEventListener("input", function () {
            const filter = this.value.toLowerCase();
            listItems.forEach(item => {
                const isHeader = item.style.fontWeight === "bold";
                const txt = item.textContent.toLowerCase();
                item.style.display = isHeader || txt.includes(filter) ? "" : "none";
            });
        });
    }, 0);
    }

    // Ciudad seleccionada
    let selectedCity = "BOGOTÁ"; // Valor por defecto

    function selectCity(cityName) {
    selectedCity = cityName;
    document.querySelector('.filters button:nth-child(1)').innerHTML = `<i class="fas fa-location-dot"></i> ${cityName}`;
    document.getElementById('selectedCityDisplay').innerText = `Eventos en ${cityName}`;
    closeModal();
    }
    </script>
    </section>

    <section>
    <div class="search-overlay" id="searchOverlay">
    <div class="search-modal">
        <input type="text" placeholder="Busca por evento o ciudad" />
        <i class="fas fa-times" onclick="closeSearch()"></i>
    </div>
    </div>

    <script>
    function openSearch() {
        document.getElementById("searchOverlay").classList.add("active");
    }

    function closeSearch() {
        document.getElementById("searchOverlay").classList.remove("active");
    }
    </script>
    </section>

   

    <section>
    <div style="text-align: center; padding: 2rem 0;">
        <h2 id="selectedCityDisplay">Eventos en BOGOTA</h2>
    </div>
    </section>

    <section id="events" class="container">
    <div class="events">
        <?php if (count($events) > 0): ?>
            <?php foreach ($events as $event): ?>
                <article class="card" style="min-height: 420px; display: flex; flex-direction: column; justify-content: space-between;">
                    <img src="<?= htmlspecialchars($event['image_url']) ?>" alt="<?= htmlspecialchars($event['name']) ?>" style="width: 100%; border-radius: 6px;" />
                    <div class="card-content" style="margin-top: 0.75rem;">
                        <h3 style="font-size: 1.4rem; font-weight: bold; margin-bottom: 0.5rem;">
                            <?= htmlspecialchars($event['name']) ?>
                        </h3>
                        
                        <div class="date-location" style="font-size: 1.1rem; font-weight: 500; margin-bottom: 0.3rem;">
                            <?= date('D, d M', strtotime($event['date'])) ?> - <?= htmlspecialchars($event['venue']) ?>
                        </div>

                        <div class="price" style="font-size: 1.1rem; font-weight: bold; margin-bottom: 0.75rem; text-align: center;">
                            <?= $event['price'] == 0 ? 'Desde Gratis' : 'Precio: $' . number_format($event['price'], 2) ?>
                        </div>

                        <?php if ($is_logged_in): ?>
                            <button class="btn-secondary" style="padding: 0.6rem 1rem; font-size: 1rem; font-weight: 500; background-color: #222; color: #fff; border: none; border-radius: 5px;">
                                Comprar ahora
                            </button>
                        <?php else: ?>
                            <button class="btn-secondary" onclick="window.location.href='login.php'" style="padding: 0.6rem 1rem; font-size: 0.8rem; font-weight: 300; display: block; margin: 0 auto;">Inicia sesion para comprar</button>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No se encontraron eventos con los filtros aplicados.</p>
        <?php endif; ?>
    </div>
    </section>


    <section id="contact" class="container">
            <h2>Contactanos</h2>
            <address style="font-style: normal; color: var(--color-text-muted);">
                <span class="label">Email:</span> <a
                    href="mailto:contacto@starbillet.com">contacto@starbillet.com</a><br />
                <span class="label">Telefono:</span> <a href="tel:+521234567890">+57 123 456 7890</a>
            </address>

        </section>

        <footer>
        &copy; 2025 StarBillet. Todos los derechos reservados.
    </footer>
    </body>
</html>
