<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        {{-- Force light mode background --}}
        <style>
            html {
                background-color: oklch(1 0 0);
            }
        </style>

        @inertiaHead
        <!-- Favicon links -->
        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" type="image/svg+xml" href="/favicon.svg">

        <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">

        <link rel="apple-touch-icon" href="/logo-touch-icon.png">
        <link rel="manifest" href="/site.webmanifest">
        
        <!-- iOS specific meta tags -->
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="default">
        <meta name="apple-mobile-web-app-title" content="Bali Villa Spot">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
        
        @routes
        @vite(['resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
        <script type="application/ld+json">
            {
                "@context": "https://schema.org",
                "@type": "Organization",
                "@id": "https://balivillaspot.com/#organization",
                "name": "Bali Villa Spot",
                "url": "https://balivillaspot.com/",
                "logo": "https://balivillaspot.com/images/logo/Logo.png",
                "sameAs": [
                    "https://www.instagram.com/balivillaspot",
                    "https://www.facebook.com/balivillaspot"
                ]
            }
        </script>

        <script type="application/ld+json">
            {
                "@context": "https://schema.org",
                "@type": "WebSite",
                "@id": "https://balivillaspot.com/#website",
                "url": "https://balivillaspot.com/",
                "name": "Bali Villa Spot",
                "publisher": {
                    "@id": "https://balivillaspot.com/#organization"
                }
            }
        </script>
        <!-- Google tag (gtag.js) -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=G-XS9NW8Z5P8"></script>
        <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'G-XS9NW8Z5P8');
        </script>
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
