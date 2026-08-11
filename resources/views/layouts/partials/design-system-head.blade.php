<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ $title ?? config('app.name', 'DentalOS') }}</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&amp;display=swap" rel="stylesheet"/>
<script id="tailwind-config">
    tailwind.config = {
        darkMode: "class",
        theme: {
            extend: {
                colors: {
                    "on-surface": "#0b1c30",
                    "on-secondary-fixed-variant": "#00504c",
                    "surface-container": "#e5eeff",
                    "surface-container-low": "#eff4ff",
                    "primary-fixed-dim": "#b3c5ff",
                    "tertiary-container": "#cc4204",
                    "on-secondary-container": "#00716b",
                    "surface-container-highest": "#d3e4fe",
                    "on-background": "#0b1c30",
                    "surface": "#f8f9ff",
                    "surface-bright": "#f8f9ff",
                    "error-container": "#ffdad6",
                    "secondary-container": "#6ff7ee",
                    "tertiary": "#a33200",
                    "surface-tint": "#0054d6",
                    "inverse-primary": "#b3c5ff",
                    "secondary-fixed-dim": "#4edbd2",
                    "surface-container-lowest": "#ffffff",
                    "surface-container-high": "#dce9ff",
                    "on-primary-fixed-variant": "#003fa4",
                    "on-tertiary-fixed-variant": "#832600",
                    "error": "#ba1a1a",
                    "inverse-surface": "#213145",
                    "tertiary-fixed-dim": "#ffb59d",
                    "primary-container": "#0066ff",
                    "background": "#f8f9ff",
                    "on-tertiary": "#ffffff",
                    "on-error-container": "#93000a",
                    "on-error": "#ffffff",
                    "on-tertiary-fixed": "#390c00",
                    "outline-variant": "#c2c6d8",
                    "secondary": "#006a65",
                    "on-primary": "#ffffff",
                    "primary-fixed": "#dae1ff",
                    "on-primary-container": "#f8f7ff",
                    "on-primary-fixed": "#001849",
                    "on-tertiary-container": "#fff6f4",
                    "outline": "#727687",
                    "on-secondary-fixed": "#00201e",
                    "on-surface-variant": "#424656",
                    "surface-dim": "#cbdbf5",
                    "surface-variant": "#d3e4fe",
                    "on-secondary": "#ffffff",
                    "tertiary-fixed": "#ffdbd0",
                    "inverse-on-surface": "#eaf1ff",
                    "primary": "#0050cb",
                    "secondary-fixed": "#6ff7ee"
                },
                borderRadius: {
                    DEFAULT: "0.125rem",
                    lg: "0.25rem",
                    xl: "0.5rem",
                    full: "0.75rem"
                },
                spacing: {
                    sm: "8px",
                    unit: "4px",
                    md: "16px",
                    xs: "4px",
                    xl: "32px",
                    "container-max": "1440px",
                    gutter: "20px",
                    "sidebar-width": "260px",
                    lg: "24px"
                },
                fontFamily: {
                    "body-sm": ["Inter"],
                    h1: ["Inter"],
                    "body-lg": ["Inter"],
                    h3: ["Inter"],
                    h2: ["Inter"],
                    "body-md": ["Inter"],
                    "label-sm": ["Inter"],
                    "label-md": ["Inter"]
                },
                fontSize: {
                    "body-sm": ["13px", { lineHeight: "18px", letterSpacing: "0", fontWeight: "400" }],
                    h1: ["32px", { lineHeight: "40px", letterSpacing: "-0.02em", fontWeight: "700" }],
                    "body-lg": ["16px", { lineHeight: "24px", letterSpacing: "0", fontWeight: "400" }],
                    h3: ["20px", { lineHeight: "28px", letterSpacing: "-0.01em", fontWeight: "600" }],
                    h2: ["24px", { lineHeight: "32px", letterSpacing: "-0.01em", fontWeight: "600" }],
                    "body-md": ["14px", { lineHeight: "20px", letterSpacing: "0", fontWeight: "400" }],
                    "label-sm": ["11px", { lineHeight: "14px", letterSpacing: "0.05em", fontWeight: "700" }],
                    "label-md": ["12px", { lineHeight: "16px", letterSpacing: "0.02em", fontWeight: "600" }]
                }
            },
        },
    }
</script>
<style>
    [x-cloak] {
        display: none !important;
    }

    .material-symbols-outlined {
        font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
    }
    body { font-family: 'Inter', sans-serif; }
</style>
