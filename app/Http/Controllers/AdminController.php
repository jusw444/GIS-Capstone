<?php

namespace App\Http\Controllers;

use App\Models\Shapefile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function index()
    {
        $shapefiles = Shapefile::withTrashed()->with('metadata', 'user')->paginate(10);
        return view('admin.dashboard', compact('shapefiles'));
    }

    public function mapview(Request $request)
    {
        $category = $request->category;

        $query = Shapefile::with('metadata')
            ->select('id', 'category', 'user_id')
            ->selectRaw('ST_AsGeoJSON(geometry, 6) AS geometry');

        if ($category) {
            $query->where('category', $category);
        }

        $shapefiles = $query->get();

        $geojson = $shapefiles->map(function ($item) {
            return [
                'id' => $item->id,
                'category' => $item->category,
                'metadata' => $item->metadata->map(fn($m) => [
                    'meta_key' => $m->meta_key,
                    'meta_value' => $m->meta_value
                ]),
                'geometry' => $item->geometry,
            ];
        });

        return view('admin.map', [
            'geojson' => $geojson,
            'selectedCategory' => $category,
        ]);
    }

    public function create()
    {
        $categories = ['disaster', 'health', 'land_use'];
        return view('admin.create', compact('categories'));
    }

    public function store(Request $request)
{
    $request->validate([
        'geometry' => 'required|json',
        'category' => 'required|in:disaster,health,land_use',
        'metadata.*.key' => 'required|string',
        'metadata.*.value' => 'nullable|string',
    ]);

    DB::transaction(function() use ($request) {
        $shapefile = Shapefile::create([
            'category' => $request->category,
            'user_id' => auth()->id(),
        ]);

        // Save geometry
        $shapefile->setGeometryRaw($request->geometry);

        // Save metadata
        if ($request->filled('metadata')) {
            foreach ($request->metadata as $meta) {
                $shapefile->metadata()->create([
                    'meta_key' => $meta['key'],
                    'meta_value' => $meta['value'] ?? null,
                ]);
            }
        }
    });

    return redirect()->route('admin.dashboard')->with('success', 'Shapefile created successfully.');
}

    public function edit($id)
    {
        $shapefile = Shapefile::with('metadata')->findOrFail($id);
        $categories = ['disaster', 'health', 'land_use'];

        // Load fresh geometry
        $shapefile->refresh();

        $geoJson = $shapefile->geometry;

        return view('admin.edit', compact('shapefile', 'categories', 'geoJson'));
    }

    public function update(Request $request, $id)
{
    $request->validate([
        'geometry' => 'required|json',
        'category' => 'required|in:disaster,health,land_use',
        'metadata.*.key' => 'required|string',
        'metadata.*.value' => 'nullable|string',
    ]);

    DB::transaction(function() use ($request, $id) {
        $shapefile = Shapefile::findOrFail($id);

        $geoArray = json_decode($request->geometry, true);
        if (!isset($geoArray['type']) || $geoArray['type'] !== 'Polygon') {
            throw new \Exception("Invalid geometry. Polygon is required.");
        }

        // Update category
        $shapefile->update(['category' => $request->category]);

        // Update geometry
        $shapefile->setGeometryRaw($request->geometry);

        // Update metadata
        $shapefile->metadata()->delete();
        if ($request->filled('metadata')) {
            foreach ($request->metadata as $meta) {
                $shapefile->metadata()->create([
                    'meta_key' => $meta['key'],
                    'meta_value' => $meta['value'] ?? null,
                ]);
            }
        }
    });

    return redirect()->route('admin.dashboard')->with('success', 'Shapefile updated successfully.');
}

    public function destroy($id)
    {
        $shapefile = Shapefile::findOrFail($id);
        $shapefile->delete();
        return back()->with('success', 'Shapefile deleted successfully.');
    }

    public function restore($id)
    {
        $shapefile = Shapefile::withTrashed()->findOrFail($id);
        $shapefile->restore();
        return back()->with('success', 'Shapefile restored successfully.');
    }
}
