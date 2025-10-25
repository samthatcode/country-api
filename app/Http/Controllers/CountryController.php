<?php

namespace App\Http\Controllers;

use App\Country;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class CountryController extends Controller
{
    public function refresh()
    {
        $countries_response = Http::get('https://restcountries.com/v2/all?fields=name,capital,region,population,flag,currencies');

        if (!$countries_response->successful()) {
            return response()->json([
                'error' => 'External data source unavailable',
                'details' => 'Could not fetch data from Countries API'
            ], 503);
        }

        $countries = $countries_response->json();

        $exchange_response = Http::get('https://open.er-api.com/v6/latest/USD');

        if (!$exchange_response->successful()) {
            return response()->json([
                'error' => 'External data source unavailable',
                'details' => 'Could not fetch data from Exchange API'
            ], 503);
        }

        $rates = $exchange_response['rates'] ?? [];

        try {
            DB::transaction(function () use ($countries, $rates) {
                $now = Carbon::now('UTC')->format('Y-m-d\TH:i:s\Z');

                foreach ($countries as $country) {
                    $name = $country['name'] ?? null;
                    if (!$name || !isset($country['population'])) {
                        continue; // Skip if missing required fields
                    }

                    $capital = $country['capital'] ?? null;
                    $region = $country['region'] ?? null;
                    $population = $country['population'];
                    $flag_url = $country['flag'] ?? null;
                    $currencies = $country['currencies'] ?? [];

                    $currency_code = null;
                    if (!empty($currencies)) {
                        $currency_code = $currencies[0]['code'] ?? null;
                    }

                    $exchange_rate = null;
                    if ($currency_code && isset($rates[$currency_code])) {
                        $exchange_rate = $rates[$currency_code];
                    }

                    $estimated_gdp = null;
                    if ($exchange_rate !== null) {
                        $random_multiplier = mt_rand(1000, 2000);
                        $estimated_gdp = $population * $random_multiplier / $exchange_rate;
                    } elseif (empty($currencies)) {
                        $estimated_gdp = 0;
                    }
                    // Else null if currency_code exists but no rate

                    $existing = Country::whereRaw('LOWER(name) = ?', [strtolower($name)])->first();

                    if ($existing) {
                        $existing->update([
                            'capital' => $capital,
                            'region' => $region,
                            'population' => $population,
                            'currency_code' => $currency_code,
                            'exchange_rate' => $exchange_rate,
                            'estimated_gdp' => $estimated_gdp,
                            'flag_url' => $flag_url,
                            'last_refreshed_at' => $now,
                        ]);
                    } else {
                        Country::create([
                            'name' => $name,
                            'capital' => $capital,
                            'region' => $region,
                            'population' => $population,
                            'currency_code' => $currency_code,
                            'exchange_rate' => $exchange_rate,
                            'estimated_gdp' => $estimated_gdp,
                            'flag_url' => $flag_url,
                            'last_refreshed_at' => $now,
                        ]);
                    }
                }

                // Generate and save image
                $total = Country::count();
                $top5 = Country::orderBy('estimated_gdp', 'desc')->take(5)->get(['name', 'estimated_gdp']);

                try {
                    $manager = new ImageManager(new Driver());
                    $img = $manager->create(800, 600,)->fill('#ffffff');

                    $img->text('Summary', 400, 50, function ($font) {
                        $font->file(public_path('fonts/arial.ttf'));
                        $font->size(40);
                        $font->color('#000000');
                        $font->align('center');
                    });

                    $img->text("Total Countries: {$total}", 400, 150, function ($font) {
                        $font->file(public_path('fonts/arial.ttf'));
                        $font->size(24);
                        $font->color('#000000');
                        $font->align('center');
                    });

                    $img->text('Top 5 by GDP:', 400, 200, function ($font) {
                        $font->file(public_path('fonts/arial.ttf'));
                        $font->size(24);
                        $font->color('#000000');
                        $font->align('center');
                    });

                    $y = 250;
                    foreach ($top5 as $c) {
                        $gdp = number_format($c->estimated_gdp, 1);
                        $img->text("{$c->name}: {$gdp}", 400, $y, function ($font) {
                            $font->file(public_path('fonts/arial.ttf'));
                            $font->size(20);
                            $font->color('#000000');
                            $font->align('center');
                        });
                        $y += 50;
                    }

                    $img->text("Last Refresh: {$now}", 400, $y, function ($font) {
                        $font->file(public_path('fonts/arial.ttf'));
                        $font->size(20);
                        $font->color('#000000');
                        $font->align('center');
                    });
                    Storage::disk('public')->put('cache/summary.png', $img->encodeByExtension('png'));
                } catch (\Throwable $th) {
                    throw $th;
                }
            });

            return response()->json(['message' => 'Refresh successful']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    public function index(Request $request)
    {
        $query = Country::query();

        if ($request->filled('region')) {
            $query->where('region', $request->region);
        }

        if ($request->filled('currency')) {
            $query->where('currency_code', $request->currency);
        }

        if ($request->filled('sort') && $request->sort === 'gdp_desc') {
            $query->orderBy('estimated_gdp', 'desc');
        }
        // You can extend for other sorts like 'gdp_asc' if needed

        return response()->json($query->get());
    }

    public function show($name)
    {
        $country = Country::whereRaw('LOWER(name) = ?', [strtolower($name)])->first();

        if (!$country) {
            return response()->json(['error' => 'Country not found'], 404);
        }

        return response()->json($country);
    }

    public function destroy($name)
    {
        $country = Country::whereRaw('LOWER(name) = ?', [strtolower($name)])->first();

        if (!$country) {
            return response()->json(['error' => 'Country not found'], 404);
        }

        $country->delete();

        return response()->json(null, 204);
    }

    public function status()
    {
        $total = Country::count();
        $last_refreshed_at = Country::max('last_refreshed_at') ?? null;

        return response()->json([
            'total_countries' => $total,
            'last_refreshed_at' => $last_refreshed_at,
        ]);
    }

    public function image()
    {
        $path = 'cache/summary.png';

        if (!Storage::disk('public')->exists($path)) {
            return response()->json(['error' => 'Summary image not found'], 404);
        }

        return response()->file(storage_path('app/public/' . $path), ['Content-Type' => 'image/png']);
    }
}
