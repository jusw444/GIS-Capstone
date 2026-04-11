<?php

namespace Database\Seeders;

use App\Models\DefaultLocation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class LagunaBoundariesSeeder extends Seeder
{
    public function run(): void
{
    $this->command->info('🏗️ Seeding Laguna Boundaries (including w-laguna.zip)...');

    // === SAFE CLEARING (instead of truncate) ===
    $existingCount = DefaultLocation::count();

    if ($existingCount > 0) {
        $this->command->warn("⚠️  Clearing {$existingCount} existing boundary records...");

        // Disable foreign key checks temporarily
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Clear dependent table first
        DB::table('feature_models')->whereNotNull('default_location_id')->update(['default_location_id' => null]);

        // Now safely delete from default_locations
        DefaultLocation::query()->delete();

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info("   ✅ Cleared all boundary records safely.");
    }

    $totalImported = 0;

    // 1. Regular GeoJSON files (if you have them)
    $files = [
        'laguna_province.geojson',
        'laguna_districts.geojson',
        'laguna_municipalities.geojson',
        'laguna_barangays.geojson',
    ];

    foreach ($files as $file) {
        $filePath = database_path("seeders/geojsons/{$file}");
        if (File::exists($filePath)) {
            $this->command->info("📄 Processing: {$file}");
            $imported = $this->importGeoJSON($filePath, $this->detectBoundaryType($file));
            $totalImported += $imported;
        }
    }

    // 2. Handle ZIP files (especially w-laguna.zip)
    $zipFiles = glob(database_path('seeders/geojsons/*.zip'));
    foreach ($zipFiles as $zipFile) {
        $this->command->info("📦 Processing ZIP: " . basename($zipFile));
        $imported = $this->importFromZip($zipFile);
        $totalImported += $imported;
    }

    $this->command->info("✅ Successfully seeded {$totalImported} boundary features.");
    $this->displaySummary();
}

    private function detectBoundaryType(string $filename): string
    {
        if (str_contains($filename, 'province')) return 'province';
        if (str_contains($filename, 'district')) return 'district';
        if (str_contains($filename, 'municipal') || str_contains($filename, 'city')) return 'municipality';
        if (str_contains($filename, 'barangay')) return 'barangay';
        return 'unknown';
    }

    private function importGeoJSON(string $filePath, string $boundaryType = 'unknown'): int
    {
        $contents = File::get($filePath);
        $geoArray = json_decode($contents, true);

        if (!$geoArray) return 0;

        $count = 0;

        DB::transaction(function () use ($geoArray, &$count, $boundaryType) {
            $features = $geoArray['features'] ?? [$geoArray];

            foreach ($features as $feature) {
                if ($this->createBoundaryRecord($feature, $boundaryType)) {
                    $count++;
                }
            }
        });

        return $count;
    }

    private function importFromZip(string $zipPath): int
    {
        $zip = new \ZipArchive;
        if ($zip->open($zipPath) !== true) {
            $this->command->error("Cannot open ZIP: " . basename($zipPath));
            return 0;
        }

        $extractPath = storage_path('app/temp/extract_' . uniqid());
        mkdir($extractPath, 0777, true);
        $zip->extractTo($extractPath);
        $zip->close();

        $count = 0;
        $geojsonFiles = glob($extractPath . '/*.{geojson,json}', GLOB_BRACE);

        foreach ($geojsonFiles as $file) {
            $this->command->info("   → Processing: " . basename($file));
            $boundaryType = str_contains(basename($file), 'province') || 
                           basename($zipPath) === 'w-laguna.zip' 
                           ? 'province' : 'unknown';

            $count += $this->importGeoJSON($file, $boundaryType);
        }

        $this->deleteDirectory($extractPath);
        return $count;
    }

    /**
     * Improved createBoundaryRecord - handles both lowercase and uppercase properties
     */
    private function createBoundaryRecord(array $feature, string $forcedBoundaryType = 'unknown'): bool
    {
        if (!isset($feature['geometry'])) {
            return false;
        }

        $props = array_change_key_case($feature['properties'] ?? [], CASE_LOWER);

        $district   = $props['name_1'] ?? $props['district'] ?? $props['name'] ?? $props['province'] ?? null;
        $municity   = $props['location'] ?? $props['municipality'] ?? $props['city'] ?? $props['municity'] ?? null;
        $brgy       = $props['brgy'] ?? $props['barangay'] ?? null;

        $boundaryType = $forcedBoundaryType;
        if ($forcedBoundaryType === 'unknown') {
            if ($brgy) $boundaryType = 'barangay';
            elseif ($municity) $boundaryType = 'municipality';
            elseif ($district) $boundaryType = 'district';
            else $boundaryType = 'province';
        }

        try {
            DefaultLocation::create([
                'geometry'      => DB::raw("ST_GeomFromGeoJSON('" . 
                    addslashes(json_encode($feature['geometry'])) . "')"),
                'district'      => $district,
                'municity'      => $municity,
                'brgy'          => $brgy,
                'boundary_type' => $boundaryType,
            ]);

            return true;
        } catch (\Exception $e) {
            $this->command->error("   ❌ " . $e->getMessage());
            return false;
        }
    }

    // Keep your existing displaySummary() and deleteDirectory() methods...
    private function displaySummary(): void { /* ... your existing code ... */ }
    private function deleteDirectory(string $dir): void { /* ... your existing code ... */ }
}