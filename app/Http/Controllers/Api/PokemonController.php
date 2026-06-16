<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pokemon;
use Illuminate\Http\Request;

class PokemonController extends Controller
{
    public function index(Request $request)
    {
        $query = Pokemon::query()
            ->with(['primaryType', 'secondaryType']);

        // Name search
        if ($request->filled('search')) {
            $search = $request->string('search');

            $query->where('name', 'like', "%{$search}%");
        }

        // 🧬 Type filtering (AND logic if both present)
        if ($request->filled('primary_type')) {
            $query->where('primary_type_id', $request->primary_type);
        }

        if ($request->filled('secondary_type')) {
            $query->where('secondary_type_id', $request->secondary_type);
        }

        // fallback combined "type" filter (OR across both fields)
        if ($request->filled('type')) {
            $type = $request->type;

            $query->where(function ($q) use ($type) {
                $q->where('primary_type_id', $type)
                  ->orWhere('secondary_type_id', $type);
            });
        }

        $perPage = min($request->integer('per_page', 20), 100);

        return $query
            ->orderBy('pokedex_number')
            ->paginate($perPage);
    }

    public function search(Request $request)
    {
        return Pokemon::query()
            ->where('name', 'like', "%{$request->q}%")
            ->limit(10)
            ->get();
    }

    public function show(Pokemon $pokemon)
    {
        return $pokemon->load(['primaryType', 'secondaryType', 'cards']);
    }
}