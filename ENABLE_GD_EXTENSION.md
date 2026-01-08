# Enable GD Extension for Better OCR

The GD extension is not currently enabled in your PHP installation. Enabling it will significantly improve OCR accuracy for low-quality images by allowing image preprocessing.

## Why Enable GD?

- **Image Preprocessing**: Automatically enhances images before OCR
- **Better Results**: Improves text extraction from low-quality images
- **Automatic Enhancement**: Resizes, sharpens, and enhances contrast

## How to Enable GD Extension

### Windows (XAMPP/WAMP)

1. Open `php.ini` file (usually in `C:\xampp\php\php.ini` or `C:\wamp\bin\php\phpX.X.X\php.ini`)
2. Find the line: `;extension=gd`
3. Remove the semicolon: `extension=gd`
4. Save the file
5. Restart Apache/PHP server

### Windows (Standalone PHP)

1. Find your `php.ini` file:
   ```powershell
   php --ini
   ```
2. Edit `php.ini` and uncomment: `extension=gd`
3. Restart your web server or PHP-FPM

### Verify Installation

Run this command to check if GD is enabled:
```powershell
php -m | Select-String "gd"
```

You should see `gd` in the output.

## Alternative: Use Imagick

If GD is not available, you can use Imagick instead:

1. Install ImageMagick: https://imagemagick.org/script/download.php
2. Install PHP Imagick extension
3. The system will automatically use Imagick if available

## Note

Even without GD/Imagick, OCR will still work, but image preprocessing won't be available, which may result in lower accuracy for low-quality images.

