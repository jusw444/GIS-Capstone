<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('page_title') | Laguna GIS</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Assets -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])   
</head>

<body>
    <div id="app">
        <div class="layout-wrapper">
            @include('layouts.sidebar')

            <main class="main-content">
                @yield('content')
            </main>
        </div>
    </div>

    <div id="loading-overlay">
    <div class="loader-content">
        <div class="spinner"></div>

        <h5>Laguna GIS</h5>

        <p>Loading GIS Data</p>
    </div>
</div>

    @include('admin.profile')

    <!-- Stack scripts here -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @stack('styles')
    @stack('scripts')

<script>

// =========================
// Loader Functions
// =========================

window.showLoader = function (
    message = 'Loading GIS Data...'
) {

    const overlay =
        document.getElementById(
            'loading-overlay'
        );

    const text =
        overlay.querySelector('p');

    text.innerText = message;

    overlay.style.display = 'flex';
};

window.hideLoader = function () {

    const overlay =
        document.getElementById(
            'loading-overlay'
        );

    overlay.style.display = 'none';
};

// =========================
// Hide Loader After Page Load
// =========================

window.addEventListener(
    'load',
    function () {

        hideLoader();

    }
);

// =========================
// Auto Detect Form Submit
// =========================

document.addEventListener(
    'submit',
    function () {

        showLoader(
            'Processing Request...'
        );

    },
    true
);

// =========================
// Auto Detect Page Navigation
// =========================

window.addEventListener(
    'beforeunload',
    function () {

        showLoader(
            'Loading Page...'
        );

    }
);

// =========================
// Auto Detect Fetch Requests
// =========================

const originalFetch =
    window.fetch;

window.fetch = async (
    ...args
) => {

    showLoader(
        'Loading GIS Data...'
    );

    try {

        return await originalFetch(
            ...args
        );

    } catch (error) {

        throw error;

    } finally {

        hideLoader();

    }

};

</script>
</body>

</html>
