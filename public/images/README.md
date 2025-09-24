# Images Directory

This directory contains static images for your Alchy Smart Inventory application.

## Directory Structure

- `logos/` - Company logos, client logos, brand assets
- `backgrounds/` - Background images, patterns, textures
- `icons/` - Custom icons, favicons, UI elements

## Usage

### In Blade Templates
```blade
<img src="{{ asset('images/logos/your-logo.png') }}" alt="Logo">
```

### In CSS/JavaScript
```css
background-image: url('/images/backgrounds/hero-bg.jpg');
```

### File Uploads (User Content)
For user-uploaded files, use the `storage/app/public/` directory instead.

## Best Practices

- Use descriptive filenames (e.g., `alchy-logo-blue.png`)
- Optimize images for web (compress, appropriate formats)
- Use appropriate directories for organization
- Consider responsive images for different screen sizes

## Examples

- Place your company logo in `logos/`
- Background images for login page in `backgrounds/`
- Custom icons in `icons/`

## Access URLs

- Local: `http://alchy_v2.test/images/logos/your-image.jpg`
- Direct: `http://127.0.0.1/images/logos/your-image.jpg`