<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Config;

class OcrService
{
    /**
     * Check if Tesseract is available
     */
    public function isTesseractAvailable(): bool
    {
        return $this->findTesseractPath() !== null;
    }

    /**
     * Extract timetable data from image using regex pattern matching
     * This is a zero-interference implementation that doesn't require Tesseract
     */
    public function extractTimetableData(UploadedFile $file): array
    {
        // Store the uploaded file temporarily
        $path = $file->store('temp', 'local');
        $fullPath = Storage::path($path);

        // For now, we'll return a structure that can be manually edited
        // In production, you would integrate Tesseract OCR here
        // Example: $text = shell_exec("tesseract {$fullPath} stdout");
        
        // Simulated extraction - in real implementation, this would come from OCR
        // For now, return empty array so user can manually input
        $extractedData = [];

        // Clean up temp file
        Storage::delete($path);

        return $extractedData;
    }

    /**
     * Parse text content to extract timetable entries
     * Uses regex to find time patterns (HH:MM) and subject names
     */
    public function parseTimetableText(string $text): array
    {
        $entries = [];
        
        // Pattern to match time (HH:MM format)
        $timePattern = '/(\d{1,2}):(\d{2})\s*-\s*(\d{1,2}):(\d{2})/i';
        
        // Pattern to match day names
        $dayPattern = '/(Monday|Tuesday|Wednesday|Thursday|Friday|Saturday|Sunday)/i';
        
        // Split text into lines
        $lines = preg_split('/\r\n|\r|\n/', $text);
        
        $currentDay = null;
        $currentSubject = null;
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            // Check if line contains a day
            if (preg_match($dayPattern, $line, $dayMatches)) {
                $currentDay = ucfirst(strtolower($dayMatches[1]));
                continue;
            }
            
            // Check if line contains time pattern
            if (preg_match($timePattern, $line, $timeMatches)) {
                $startTime = sprintf('%02d:%s', $timeMatches[1], $timeMatches[2]);
                $endTime = sprintf('%02d:%s', $timeMatches[3], $timeMatches[4]);
                
                // Try to extract subject name (text before or after time)
                $subjectText = preg_replace($timePattern, '', $line);
                $subjectText = trim($subjectText);
                
                if (!empty($subjectText) && $currentDay) {
                    $entries[] = [
                        'day_of_week' => $currentDay,
                        'start_time' => $startTime,
                        'end_time' => $endTime,
                        'subject_name' => $subjectText,
                    ];
                }
            } elseif (!empty($line) && $currentDay) {
                // If line doesn't have time but has text, might be subject name
                // This is a fallback for different timetable formats
                if (strlen($line) > 3 && !preg_match('/^\d/', $line)) {
                    $currentSubject = $line;
                }
            }
        }
        
        return $entries;
    }

    /**
     * Process uploaded image and return parsed data
     */
    public function processImage(UploadedFile $file): array
    {
        // First, try to extract text (if Tesseract is available)
        $text = $this->extractTextFromImage($file);
        
        if (empty($text)) {
            // Return empty structure for manual input
            return [];
        }
        
        // Parse the extracted text
        return $this->parseTimetableText($text);
    }

    /**
     * Parse division-based timetable from OCR text
     * Extracts lecture number, time, subject, instructor, location for each division
     */
    public function parseDivisionTimetable(string $text, string $division): array
    {
        $entries = [];
        $lines = preg_split('/\r\n|\r|\n/', $text);
        
        // Pattern to match day of week from header (e.g., "8-Jan-2026, Thursday")
        $dayPattern = '/(Monday|Tuesday|Wednesday|Thursday|Friday|Saturday|Sunday|Mon|Tue|Wed|Thu|Fri|Sat|Sun)/i';
        $dayMap = ['Mon' => 'Monday', 'Tue' => 'Tuesday', 'Wed' => 'Wednesday', 
                  'Thu' => 'Thursday', 'Fri' => 'Friday', 'Sat' => 'Saturday', 'Sun' => 'Sunday'];
        
        // Pattern to match lecture number (Lec. No. 1, Lec No. 2, etc.)
        $lecturePattern = '/Lec\.?\s*No\.?\s*(\d+)/i';
        
        // Pattern to match time ranges (8:05 to 8:55, 9:00 to 9:50, etc.)
        $timePattern = '/(\d{1,2}):(\d{2})\s*(?:to|-)\s*(\d{1,2}):(\d{2})/i';
        
        // Pattern to match division columns (Div-A, Div-B, etc.)
        $divisionPattern = '/Div-([A-E])/i';
        
        // Pattern to match subject, instructor, location
        // Handles: "DS-TH, MK, Lab # 509" or "Java, YG, Lab # 409" or "SE, NNS, Class # 502"
        $classPattern = '/([A-Za-z\-\s]{2,}?),\s*([A-Z]{2,4}),\s*(Class|Lab)\s*#\s*(\d+[A-Z\-]?)/i';
        $classPatternSimple = '/([A-Za-z\-\s]{2,}?),\s*([A-Z]{2,4})/i';
        
        // Extract day of week from header (usually in first few lines)
        $extractedDay = null;
        foreach (array_slice($lines, 0, 10) as $line) {
            if (preg_match($dayPattern, $line, $dayMatches)) {
                $dayName = ucfirst(strtolower($dayMatches[1]));
                $extractedDay = $dayMap[$dayName] ?? $dayName;
                break;
            }
        }
        
        // Structure to hold lecture data
        $lectureData = [];
        $currentLecture = null;
        $currentTime = null;
        
        // Build a structure of all lectures with their times
        foreach ($lines as $lineIndex => $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            // Check for lecture number
            if (preg_match($lecturePattern, $line, $lectureMatches)) {
                $currentLecture = (int)$lectureMatches[1];
                $lectureData[$currentLecture] = [
                    'number' => $currentLecture,
                    'time' => null,
                    'classes' => []
                ];
                continue;
            }
            
            // Check for time range
            if (preg_match($timePattern, $line, $timeMatches)) {
                $startTime = sprintf('%02d:%s', $timeMatches[1], $timeMatches[2]);
                $endTime = sprintf('%02d:%s', $timeMatches[3], $timeMatches[4]);
                if ($currentLecture) {
                    $lectureData[$currentLecture]['time'] = [
                        'start' => $startTime,
                        'end' => $endTime,
                    ];
                    $currentTime = $lectureData[$currentLecture]['time'];
                }
                continue;
            }
            
            // Try to extract class information
            if ($currentLecture && $currentTime) {
                // Try full pattern with location
                if (preg_match($classPattern, $line, $classMatches)) {
                    $subject = trim($classMatches[1]);
                    $instructor = trim($classMatches[2]);
                    $type = trim($classMatches[3]);
                    $location = trim($classMatches[4]);
                    
                    $lectureData[$currentLecture]['classes'][] = [
                        'subject' => $subject,
                        'instructor' => $instructor,
                        'location' => $type . ' #' . $location,
                    ];
                } elseif (preg_match($classPatternSimple, $line, $classMatches)) {
                    // Simpler pattern
                    $subject = trim($classMatches[1]);
                    $instructor = trim($classMatches[2]);
                    
                    $lectureData[$currentLecture]['classes'][] = [
                        'subject' => $subject,
                        'instructor' => $instructor,
                        'location' => '',
                    ];
                }
            }
        }
        
        // Extract entries - for now, extract all classes found
        // User will need to confirm which belong to their division
        // In future, we can improve this with better column detection
        foreach ($lectureData as $lectureNum => $data) {
            if (!$data['time'] || empty($data['classes'])) continue;
            
            foreach ($data['classes'] as $class) {
                $entries[] = [
                    'lecture_number' => $lectureNum,
                    'day_of_week' => $extractedDay ?: 'Monday', // Use extracted day or default
                    'start_time' => $data['time']['start'],
                    'end_time' => $data['time']['end'],
                    'subject_name' => $class['subject'],
                    'instructor' => $class['instructor'],
                    'location' => $class['location'],
                    'division' => $division,
                ];
            }
        }
        
        // If still no entries, try flexible parsing
        if (empty($entries)) {
            $entries = $this->parseTimetableFlexible($text, $division);
            // Add extracted day to flexible parsing results
            if ($extractedDay) {
                foreach ($entries as &$entry) {
                    if (empty($entry['day_of_week'])) {
                        $entry['day_of_week'] = $extractedDay;
                    }
                }
            }
        }
        
        return $entries;
    }

    /**
     * Extract timetable data for a specific division
     */
    public function extractDivisionTimetable(UploadedFile $file, string $division): array
    {
        $text = $this->extractTextFromImage($file);
        
        // Log extracted text for debugging
        \Log::info('OCR Extracted Text:', ['text' => $text, 'length' => strlen($text)]);
        
        if (empty($text)) {
            // Try alternative extraction method
            $text = $this->extractTextFromImageAlternative($file);
            \Log::info('Alternative OCR Extracted Text:', ['text' => $text, 'length' => strlen($text)]);
        }
        
        if (empty($text)) {
            return [];
        }
        
        // Store raw text for debugging
        $this->rawOcrText = $text;
        
        // Parse for the specific division
        $entries = $this->parseDivisionTimetable($text, $division);
        
        // If no entries found, try simpler parsing
        if (empty($entries)) {
            $entries = $this->parseTimetableTextSimple($text, $division);
        }
        
        // If still no entries, try flexible parsing
        if (empty($entries)) {
            $entries = $this->parseTimetableFlexible($text, $division);
        }
        
        // Filter entries for the requested division
        // Note: OCR parsing might need manual verification
        return array_map(function($entry) use ($division) {
            $entry['division'] = $division;
            return $entry;
        }, $entries);
    }

    /**
     * Store raw OCR text for debugging
     */
    private $rawOcrText = '';

    /**
     * Get raw OCR text (for debugging)
     */
    public function getRawOcrText(): string
    {
        return $this->rawOcrText;
    }

    /**
     * Flexible parsing that handles partial/incomplete OCR output
     */
    private function parseTimetableFlexible(string $text, string $division): array
    {
        $entries = [];
        $lines = preg_split('/\r\n|\r|\n/', $text);
        
        // Patterns
        $lecturePattern = '/Lec\.?\s*No\.?\s*(\d+)/i';
        $timePattern = '/(\d{1,2}):(\d{2})/';
        $timeRangePattern = '/(\d{1,2}):(\d{2})\s*(?:to|-)\s*(\d{1,2}):(\d{2})/i';
        $subjectPattern = '/([A-Z]{2,}(?:\s+[A-Z]+)?(?:\-[A-Z]+)?)/'; // Matches subjects like "SE", "DS-Lab", "Java", etc.
        $instructorPattern = '/,\s*([A-Z]{2,4})\s*,/'; // Matches instructor codes like "NNS", "JCB", "MK"
        $locationPattern = '/(Class|Lab)\s*#\s*(\d+[A-Z\-]?)/i';
        $dayPattern = '/(Monday|Tuesday|Wednesday|Thursday|Friday|Saturday|Sunday|Mon|Tue|Wed|Thu|Fri|Sat|Sun)/i';
        
        $currentDay = null;
        $currentLecture = null;
        $currentTime = null;
        $lastTime = null;
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || strlen($line) < 3) continue;
            
            // Check for day
            if (preg_match($dayPattern, $line, $dayMatches)) {
                $dayName = ucfirst(strtolower($dayMatches[1]));
                // Convert abbreviations
                $dayMap = ['Mon' => 'Monday', 'Tue' => 'Tuesday', 'Wed' => 'Wednesday', 
                          'Thu' => 'Thursday', 'Fri' => 'Friday', 'Sat' => 'Saturday', 'Sun' => 'Sunday'];
                $currentDay = $dayMap[$dayName] ?? $dayName;
                continue;
            }
            
            // Check for lecture number
            if (preg_match($lecturePattern, $line, $lectureMatches)) {
                $currentLecture = (int)$lectureMatches[1];
                continue;
            }
            
            // Extract time range (preferred)
            if (preg_match($timeRangePattern, $line, $timeMatches)) {
                $startTime = sprintf('%02d:%s', $timeMatches[1], $timeMatches[2]);
                $endTime = sprintf('%02d:%s', $timeMatches[3], $timeMatches[4]);
                $currentTime = [
                    'start' => $startTime,
                    'end' => $endTime,
                ];
                $lastTime = $endTime;
                continue;
            }
            
            // Extract individual times
            $times = [];
            if (preg_match_all($timePattern, $line, $timeMatches, PREG_SET_ORDER)) {
                foreach ($timeMatches as $match) {
                    $times[] = sprintf('%02d:%s', $match[1], $match[2]);
                }
                if (count($times) >= 2) {
                    $currentTime = [
                        'start' => $times[0],
                        'end' => $times[1],
                    ];
                    $lastTime = $times[1];
                } elseif (count($times) == 1) {
                    $currentTime = [
                        'start' => $times[0],
                        'end' => $lastTime ?: '09:00',
                    ];
                }
            }
            
            // Extract subject, instructor, location
            $subject = null;
            $instructor = null;
            $location = null;
            
            if (preg_match($subjectPattern, $line, $subjectMatches)) {
                $subject = trim($subjectMatches[1]);
            }
            
            if (preg_match($instructorPattern, $line, $instructorMatches)) {
                $instructor = trim($instructorMatches[1]);
            }
            
            if (preg_match($locationPattern, $line, $locationMatches)) {
                $location = $locationMatches[1] . ' #' . $locationMatches[2];
            }
            
            // If we have subject and time, create entry
            if ($subject && $currentTime && $currentDay) {
                $entries[] = [
                    'lecture_number' => $currentLecture ?: null,
                    'day_of_week' => $currentDay,
                    'start_time' => $currentTime['start'],
                    'end_time' => $currentTime['end'],
                    'subject_name' => $subject,
                    'instructor' => $instructor ?: '',
                    'location' => $location ?: '',
                    'division' => $division,
                ];
            }
        }
        
        return $entries;
    }

    /**
     * Alternative text extraction method with aggressive preprocessing
     */
    private function extractTextFromImageAlternative(UploadedFile $file): string
    {
        $tesseractPath = $this->findTesseractPath();
        
        if (!$tesseractPath) {
            return '';
        }
        
        try {
            $path = $file->store('temp', 'local');
            $fullPath = Storage::path($path);
            
            // Apply aggressive preprocessing for low quality images
            $processedPath = $this->preprocessImageAggressive($fullPath);
            $imageToUse = $processedPath ?: $fullPath;
            
            $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
            $errorRedirect = $isWindows ? '2>nul' : '2>/dev/null';
            
            // Try multiple PSM modes
            $psmModes = [11, 6, 4]; // Sparse text, uniform block, single column
            
            foreach ($psmModes as $psm) {
                $command = escapeshellarg($tesseractPath) . ' ' . 
                          escapeshellarg($imageToUse) . ' stdout -psm ' . $psm . ' -l eng ' . $errorRedirect;
                $text = shell_exec($command);
                
                if ($text && strlen(trim($text)) > 10) {
                    // Clean up
                    Storage::delete($path);
                    if ($processedPath && $processedPath !== $fullPath && file_exists($processedPath)) {
                        @unlink($processedPath);
                    }
                    return trim($text);
                }
            }
            
            // Clean up
            Storage::delete($path);
            if ($processedPath && $processedPath !== $fullPath && file_exists($processedPath)) {
                @unlink($processedPath);
            }
            
            return '';
        } catch (\Exception $e) {
            \Log::error('OCR Alternative Extraction Error:', ['error' => $e->getMessage()]);
            return '';
        }
    }

    /**
     * Aggressive image preprocessing for very low quality images
     */
    private function preprocessImageAggressive(string $imagePath): ?string
    {
        if (!extension_loaded('gd') && !extension_loaded('imagick')) {
            return null;
        }
        
        try {
            if (extension_loaded('imagick')) {
                return $this->preprocessImageImagickAggressive($imagePath);
            } elseif (extension_loaded('gd')) {
                return $this->preprocessImageGDAggressive($imagePath);
            }
        } catch (\Exception $e) {
            \Log::error('Aggressive preprocessing error:', ['error' => $e->getMessage()]);
        }
        
        return null;
    }

    /**
     * Aggressive preprocessing with GD
     */
    private function preprocessImageGDAggressive(string $imagePath): ?string
    {
        $imageInfo = @getimagesize($imagePath);
        if (!$imageInfo) {
            return null;
        }
        
        $mimeType = $imageInfo['mime'];
        $width = $imageInfo[0];
        $height = $imageInfo[1];
        
        switch ($mimeType) {
            case 'image/jpeg':
                $source = @imagecreatefromjpeg($imagePath);
                break;
            case 'image/png':
                $source = @imagecreatefrompng($imagePath);
                break;
            case 'image/gif':
                $source = @imagecreatefromgif($imagePath);
                break;
            default:
                return null;
        }
        
        if (!$source) {
            return null;
        }
        
        // Scale up significantly for better OCR
        $scale = max(2.0, 1500 / $width);
        $newWidth = (int)($width * $scale);
        $newHeight = (int)($height * $scale);
        
        $resized = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($resized, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        
        // Convert to grayscale
        imagefilter($resized, IMG_FILTER_GRAYSCALE);
        
        // Strong contrast enhancement
        imagefilter($resized, IMG_FILTER_CONTRAST, -30);
        imagefilter($resized, IMG_FILTER_BRIGHTNESS, 10);
        
        // Multiple sharpening passes
        for ($i = 0; $i < 2; $i++) {
            $sharpenMatrix = [
                [0, -1, 0],
                [-1, 5, -1],
                [0, -1, 0]
            ];
            imageconvolution($resized, $sharpenMatrix, 1, 0);
        }
        
        $processedPath = $imagePath . '_processed_aggressive.jpg';
        imagejpeg($resized, $processedPath, 100);
        
        imagedestroy($source);
        imagedestroy($resized);
        
        return $processedPath;
    }

    /**
     * Aggressive preprocessing with Imagick
     */
    private function preprocessImageImagickAggressive(string $imagePath): ?string
    {
        try {
            $image = new \Imagick($imagePath);
            
            // Scale up significantly
            $width = $image->getImageWidth();
            $scale = max(2.0, 1500 / $width);
            $newWidth = (int)($width * $scale);
            $newHeight = (int)($image->getImageHeight() * $scale);
            $image->resizeImage($newWidth, $newHeight, \Imagick::FILTER_LANCZOS, 1);
            
            // Convert to grayscale
            $image->transformImageColorspace(\Imagick::COLORSPACE_GRAY);
            
            // Aggressive contrast and brightness
            $image->normalizeImage();
            $image->contrastImage(1);
            $image->brightnessContrastImage(10, 30);
            
            // Multiple sharpening passes
            for ($i = 0; $i < 3; $i++) {
                $image->sharpenImage(2, 1);
            }
            
            // Noise reduction and enhancement
            $image->despeckleImage();
            $image->enhanceImage();
            $image->equalizeImage();
            
            $processedPath = $imagePath . '_processed_aggressive.jpg';
            $image->setImageFormat('jpeg');
            $image->setImageCompressionQuality(100);
            $image->writeImage($processedPath);
            
            $image->clear();
            $image->destroy();
            
            return $processedPath;
        } catch (\Exception $e) {
            \Log::error('Imagick aggressive preprocessing error:', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Simpler parsing method for when complex parsing fails
     */
    private function parseTimetableTextSimple(string $text, string $division): array
    {
        $entries = [];
        $lines = preg_split('/\r\n|\r|\n/', $text);
        
        // Look for common patterns in timetable text
        $dayPattern = '/(Monday|Tuesday|Wednesday|Thursday|Friday|Saturday|Sunday)/i';
        $timePattern = '/(\d{1,2}):(\d{2})/';
        
        $currentDay = null;
        $lectureNum = 1;
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            // Check for day
            if (preg_match($dayPattern, $line, $matches)) {
                $currentDay = ucfirst(strtolower($matches[1]));
                $lectureNum = 1;
                continue;
            }
            
            // Look for time patterns
            if (preg_match_all($timePattern, $line, $timeMatches, PREG_SET_ORDER)) {
                if (count($timeMatches) >= 2) {
                    $startTime = sprintf('%02d:%s', $timeMatches[0][1], $timeMatches[0][2]);
                    $endTime = sprintf('%02d:%s', $timeMatches[1][1], $timeMatches[1][2]);
                    
                    // Try to extract subject name (text before or after times)
                    $subjectText = preg_replace($timePattern, '', $line);
                    $subjectText = preg_replace('/[^A-Za-z\s]/', '', $subjectText);
                    $subjectText = trim($subjectText);
                    
                    if (!empty($subjectText) && strlen($subjectText) > 2 && $currentDay) {
                        $entries[] = [
                            'lecture_number' => $lectureNum++,
                            'day_of_week' => $currentDay,
                            'start_time' => $startTime,
                            'end_time' => $endTime,
                            'subject_name' => $subjectText,
                            'instructor' => '',
                            'location' => '',
                            'division' => $division,
                        ];
                    }
                }
            }
        }
        
        return $entries;
    }

    /**
     * Extract text from image using Tesseract (if available)
     * Includes image preprocessing for better OCR results on low quality images
     */
    private function extractTextFromImage(UploadedFile $file): string
    {
        // Check if Tesseract is available
        $tesseractPath = $this->findTesseractPath();
        
        if (!$tesseractPath) {
            \Log::warning('Tesseract not found. Please install Tesseract OCR or set TESSERACT_PATH in .env');
            return '';
        }
        
        try {
            $path = $file->store('temp', 'local');
            $fullPath = Storage::path($path);
            
            // Preprocess image for better OCR results
            $processedPath = $this->preprocessImage($fullPath);
            $imageToUse = $processedPath ?: $fullPath;
            
            $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
            $errorRedirect = $isWindows ? '2>nul' : '2>/dev/null';
            
            // Try multiple PSM modes for better results
            // PSM modes: 3=auto, 4=single column, 6=uniform block, 11=sparse text, 12=sparse text with OSD
            $psmModes = [3, 6, 11, 4, 12]; // Try auto first, then table-specific modes
            
            $bestText = '';
            $bestLength = 0;
            
            foreach ($psmModes as $psm) {
                // Try with different configurations
                $configs = [
                    '-psm ' . $psm . ' -l eng',
                    '-psm ' . $psm . ' -l eng --oem 3', // LSTM OCR Engine
                    '-psm ' . $psm . ' -l eng --oem 1', // Legacy OCR Engine
                ];
                
                foreach ($configs as $config) {
                    $command = escapeshellarg($tesseractPath) . ' ' . 
                              escapeshellarg($imageToUse) . ' stdout ' . $config . ' ' . $errorRedirect;
                    $text = shell_exec($command);
                    
                    if ($text) {
                        $text = trim($text);
                        // Prefer longer extracted text (more likely to be complete)
                        if (strlen($text) > $bestLength) {
                            $bestText = $text;
                            $bestLength = strlen($text);
                            \Log::info('Better OCR result found', ['psm' => $psm, 'length' => $bestLength]);
                        }
                    }
                }
            }
            
            // If still no text, try without PSM mode
            if (empty($bestText)) {
                $command = escapeshellarg($tesseractPath) . ' ' . 
                          escapeshellarg($imageToUse) . ' stdout -l eng ' . $errorRedirect;
                $text = shell_exec($command);
                $bestText = $text ? trim($text) : '';
            }
            
            // Clean up
            Storage::delete($path);
            if ($processedPath && $processedPath !== $fullPath && file_exists($processedPath)) {
                @unlink($processedPath);
            }
            
            if (empty($bestText)) {
                \Log::warning('Tesseract returned empty text. Check image quality and Tesseract installation.');
            } else {
                \Log::info('OCR extracted text successfully', ['length' => strlen($bestText)]);
            }
            
            return $bestText;
        } catch (\Exception $e) {
            \Log::error('OCR Extraction Error:', ['error' => $e->getMessage(), 'file' => $file->getClientOriginalName()]);
            return '';
        }
    }

    /**
     * Preprocess image to improve OCR accuracy for low quality images
     * - Resize if too small
     * - Enhance contrast
     * - Convert to grayscale
     * - Apply sharpening
     */
    private function preprocessImage(string $imagePath): ?string
    {
        if (!extension_loaded('gd') && !extension_loaded('imagick')) {
            \Log::warning('GD or Imagick extension not available. Skipping image preprocessing.');
            return null;
        }
        
        try {
            // Use GD if available, otherwise Imagick
            if (extension_loaded('gd')) {
                return $this->preprocessImageGD($imagePath);
            } elseif (extension_loaded('imagick')) {
                return $this->preprocessImageImagick($imagePath);
            }
        } catch (\Exception $e) {
            \Log::error('Image preprocessing error:', ['error' => $e->getMessage()]);
            return null;
        }
        
        return null;
    }

    /**
     * Preprocess image using GD library
     */
    private function preprocessImageGD(string $imagePath): ?string
    {
        $imageInfo = @getimagesize($imagePath);
        if (!$imageInfo) {
            return null;
        }
        
        $mimeType = $imageInfo['mime'];
        $width = $imageInfo[0];
        $height = $imageInfo[1];
        
        // Create image resource based on type
        switch ($mimeType) {
            case 'image/jpeg':
                $source = @imagecreatefromjpeg($imagePath);
                break;
            case 'image/png':
                $source = @imagecreatefrompng($imagePath);
                break;
            case 'image/gif':
                $source = @imagecreatefromgif($imagePath);
                break;
            default:
                return null;
        }
        
        if (!$source) {
            return null;
        }
        
        // Minimum size for good OCR (at least 1000px width)
        $minWidth = 1000;
        $scale = $width < $minWidth ? $minWidth / $width : 1;
        $newWidth = (int)($width * $scale);
        $newHeight = (int)($height * $scale);
        
        // Create resized image
        $resized = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($resized, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        
        // Convert to grayscale
        imagefilter($resized, IMG_FILTER_GRAYSCALE);
        
        // Enhance contrast
        imagefilter($resized, IMG_FILTER_CONTRAST, -20);
        
        // Sharpen
        $sharpenMatrix = [
            [0, -1, 0],
            [-1, 5, -1],
            [0, -1, 0]
        ];
        imageconvolution($resized, $sharpenMatrix, 1, 0);
        
        // Save processed image
        $processedPath = $imagePath . '_processed.jpg';
        imagejpeg($resized, $processedPath, 95);
        
        imagedestroy($source);
        imagedestroy($resized);
        
        return $processedPath;
    }

    /**
     * Preprocess image using Imagick library (better quality)
     */
    private function preprocessImageImagick(string $imagePath): ?string
    {
        try {
            $image = new \Imagick($imagePath);
            
            // Resize if too small (minimum 1000px width)
            $width = $image->getImageWidth();
            if ($width < 1000) {
                $scale = 1000 / $width;
                $newWidth = 1000;
                $newHeight = (int)($image->getImageHeight() * $scale);
                $image->resizeImage($newWidth, $newHeight, \Imagick::FILTER_LANCZOS, 1);
            }
            
            // Convert to grayscale
            $image->transformImageColorspace(\Imagick::COLORSPACE_GRAY);
            
            // Enhance contrast
            $image->normalizeImage();
            $image->contrastImage(1);
            
            // Sharpen
            $image->sharpenImage(2, 1);
            
            // Enhance for OCR
            $image->despeckleImage();
            $image->enhanceImage();
            
            // Save processed image
            $processedPath = $imagePath . '_processed.jpg';
            $image->setImageFormat('jpeg');
            $image->setImageCompressionQuality(95);
            $image->writeImage($processedPath);
            
            $image->clear();
            $image->destroy();
            
            return $processedPath;
        } catch (\Exception $e) {
            \Log::error('Imagick preprocessing error:', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Find Tesseract executable path
     */
    private function findTesseractPath(): ?string
    {
        // First, check if TESSERACT_PATH is set in environment or config
        $envPath = Config::get('ocr.tesseract_path') ?: env('TESSERACT_PATH');
        if ($envPath) {
            // Remove quotes if present
            $envPath = trim($envPath, '"\'');
            if ($this->isExecutable($envPath)) {
                \Log::info('Using Tesseract from TESSERACT_PATH:', ['path' => $envPath]);
                return $envPath;
            } else {
                \Log::warning('TESSERACT_PATH set but not executable:', ['path' => $envPath]);
            }
        }
        
        // Fallback to common paths
        $possiblePaths = [
            'tesseract',
            '/usr/bin/tesseract',
            '/usr/local/bin/tesseract',
            'C:\\Program Files\\Tesseract-OCR\\tesseract.exe',
            'C:\\Program Files (x86)\\Tesseract-OCR\\tesseract.exe',
        ];
        
        foreach ($possiblePaths as $path) {
            if ($this->isExecutable($path)) {
                \Log::info('Found Tesseract at:', ['path' => $path]);
                return $path;
            }
        }
        
        \Log::warning('Tesseract not found in any common paths');
        return null;
    }

    /**
     * Check if a command is executable
     */
    private function isExecutable(string $command): bool
    {
        // Check if file exists first (for Windows paths)
        if (strpos($command, '.exe') !== false || strpos($command, '\\') !== false) {
            if (!file_exists($command)) {
                return false;
            }
        }
        
        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        
        if ($isWindows) {
            // On Windows, test by running version command
            $testCommand = escapeshellarg($command) . ' --version 2>nul';
        } else {
            // On Unix, use which command
            $testCommand = 'which ' . escapeshellarg($command) . ' 2>&1';
        }
        
        $result = @shell_exec($testCommand);
        return !empty($result) && strpos($result, 'error') === false;
    }
}

