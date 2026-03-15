<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;

class ConfigController extends Controller
{
    private array $publicFields = [
        'mode',
    ];

    public function index()
    {
        return response()->json(ConfigController::getConfig());
    }

    public function public()
    {
        $config = ConfigController::getConfig();
        $publicConfig = array_intersect_key(
            $config,
            array_flip($this->publicFields)
        );

        return response()->json($publicConfig);
    }

    public static function getConfig(): array
    {
        $path = base_path('config/config.json');
        
        if (!File::exists($path)) {
            return [];
        }

        $content = File::get($path);
        $config = json_decode($content, true);

        return is_array($config) ? $config : [];
    }

    private function setConfig(array $data): void
    {
        File::put(base_path('config/config.json'), json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    public function changeMode(Request $request)
    {
        $mode = $request->input('mode');

        if (!$mode || !in_array($mode, ['marketplace', 'shop'])) {
            return response()->json([
                'error' => 'Invalid mode. Allowed: marketplace, shop'
            ], 422);
        }

        $config = ConfigController::getConfig();
        $config['mode'] = $mode;
        $this->setConfig($config);

        return response()->json(['mode' => $mode]);
    }
}