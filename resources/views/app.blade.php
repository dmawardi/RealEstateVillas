<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"  @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        {{-- Inline script to detect system dark mode preference and apply it immediately --}}
        <script>
            (function() {
                const appearance = '{{ $appearance ?? "system" }}';

                if (appearance === 'system') {
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                    if (prefersDark) {
                        document.documentElement.classList.add('dark');
                    }
                }
            })();
        </script>

        {{-- Inline style to set the HTML background color based on our theme in app.css --}}
        <style>
            html {
                background-color: oklch(1 0 0);
            }

            html.dark {
                background-color: oklch(0.145 0 0);
            }
        </style>

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" type="image/png" href="/logo-touch-icon.png" sizes="180x180">
        <link rel="apple-touch-icon" href="/logo-touch-icon.png">

        

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        @routes
        @vite(['resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
        @inertiaHead
        <script type="application/ld+json">
            {
                "@context": "https://schema.org",
                "@type": "Organization",
                "@id": "https://balivillaspot.com/#organization",
                "name": "Bali Villa Spot",
                "url": "https://balivillaspot.com/",
                "logo": "https://balivillaspot.com/logo.png",
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
