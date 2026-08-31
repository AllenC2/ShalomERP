<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeocodingService
{
    protected string $userAgent;
    protected string $baseUrl = 'https://nominatim.openstreetmap.org/search';
    protected int $rateLimitMs = 1500;

    public function __construct()
    {
        $this->userAgent = config('app.name', 'ShalomERP') . '/1.0';
    }

    public function geocode(string $direccion): ?array
    {
        if (empty(trim($direccion))) {
            return null;
        }

        $queries = $this->buildQueries($direccion);

        foreach ($queries as $query) {
            $result = $this->search($query);
            if ($result && $this->esResultadoUtil($result, $direccion)) {
                $result['approximate'] = ($query !== $this->limpiarDireccion($direccion));
                $result['query_used'] = $query;
                return $result;
            }
        }

        Log::warning('Geocoding: sin resultados útiles', ['direccion' => $direccion]);
        return null;
    }

    protected function buildQueries(string $direccion): array
    {
        $limpia = $this->limpiarDireccion($direccion);
        $partes = array_map('trim', explode(',', $limpia));

        // Identificar municipio y estado
        $municipio = '';
        $estado = '';
        $municipioIdx = -1;
        foreach ($partes as $idx => $parte) {
            $p = strtolower(trim($parte));
            // Ciudades principales
            if (in_array($p, ['guadalajara', 'zapopan', 'tlajomulco', 'tonala', 'tlaquepaque', 'san pedro tlaquepaque', 'monterrey', 'puebla', 'leon', 'león', 'mexicali', 'tijuana', 'chihuahua', 'merida', 'mérida', 'cancun', 'cancún', 'veracruz', 'villahermosa', 'querétaro', 'queretaro'])) {
                $municipio = trim($parte);
                $municipioIdx = $idx;
            }
            // Delegaciones de CDMX
            if (in_array($p, ['cuauhtémoc', 'cuauhtemoc', 'coyoacán', 'coyoacan', 'miguel hidalgo', 'benito juárez', 'benito juarez', 'azcapotzalco', 'venustiano carranza', 'iztacalco', 'iztapalapa', 'tlalpan', 'xochimilco', 'magdalena contreras', 'tlahuac', 'gustavo a. madero', 'alvaro obregón', 'álvaro obregón'])) {
                $municipio = trim($parte);
                $municipioIdx = $idx;
            }
            // Estados
            if (in_array($p, ['jalisco', 'nuevo leon', 'nuevo león', 'puebla', 'querétaro', 'queretaro', 'guanajuato', 'estado de méxico', 'chihuahua', 'yucatán', 'yucatan', 'tabasco', 'veracruz'])) {
                $estado = trim($parte);
            }
            if (in_array($p, ['cdmx', 'ciudad de méxico'])) {
                $estado = 'CDMX';
            }
        }

        // Si no detectamos, intentar por posición
        if (!$municipio && count($partes) >= 3) {
            $municipio = $partes[2];
            $municipioIdx = 2;
        }
        if (!$estado && count($partes) >= 4) {
            $estado = $partes[3];
        }

        $queries = [];

        // Función para quitar solo el numero de casa (ultimo numero en la primera parte)
        $quitarNumCasa = function (string $parte0): string {
            // Qitar el numero al final: "5 de Mayo 85" → "5 de Mayo", "Hacienda Lencero 2132" → "Hacienda Lencero"
            // Pero NO quitar "5" de "5 de Mayo" (es parte del nombre)
            $result = preg_replace('/\s+\d{1,6}\s*$/', '', $parte0);
            return trim($result);
        };

        // 1. Dirección limpia completa (con número)
        $queries[] = $limpia;

        // 2. Sin número de casa
        $sinNumero = $quitarNumCasa($limpia);
        $sinNumero = preg_replace('/\s+/', ' ', trim($sinNumero));
        if ($sinNumero !== $limpia && strlen($sinNumero) > 5) {
            $queries[] = $sinNumero;
        }

        // 3. Sin el último fragmento (sin estado)
        if (count($partes) >= 2) {
            $q = implode(', ', array_slice($partes, 0, -1));
            if ($this->queryTieneCiudad($q, $municipio)) {
                $queries[] = $q;
            }
            // También sin número de casa
            $qSinNum = $quitarNumCasa($q);
            $qSinNum = preg_replace('/\s+/', ' ', trim($qSinNum));
            if ($qSinNum !== $q && $this->queryTieneCiudad($qSinNum, $municipio)) {
                $queries[] = $qSinNum;
            }
        }

        // 4. calle (sin número) + colonia + municipio + estado
        if ($municipio && count($partes) >= 2) {
            $calleSinNum = $quitarNumCasa($partes[0]);
            if (count($partes) >= 3) {
                $queries[] = $calleSinNum . ', ' . $partes[1] . ', ' . $municipio . ($estado ? ', ' . $estado : '') . ', Mexico';
            }
        }

        // 5. calle (sin número) + municipio + estado
        if ($municipio && count($partes) >= 1) {
            $calleSinNum = $quitarNumCasa($partes[0]);
            $q = $calleSinNum . ', ' . $municipio;
            if ($estado) $q .= ', ' . $estado;
            $queries[] = $q . ', Mexico';
        }

        // 6. colonia + municipio + estado
        if ($municipio && count($partes) >= 2) {
            $colonia = $partes[1];
            $q = $colonia . ', ' . $municipio;
            if ($estado) $q .= ', ' . $estado;
            $queries[] = $q . ', Mexico';
        }

        // 7. calle + municipio (sin estado, sin número)
        if ($municipio && count($partes) >= 1) {
            $calleSinNum = $quitarNumCasa($partes[0]);
            $queries[] = $calleSinNum . ', ' . $municipio;
        }

        // 8. solo municipio + estado (centro de la ciudad)
        if ($municipio) {
            $q = $municipio;
            if ($estado) $q .= ', ' . $estado;
            $queries[] = $q . ', Mexico';
        }

        // Eliminar duplicados y muy cortos
        $queries = array_unique(array_filter($queries, fn($q) => strlen($q) > 5));

        return array_values($queries);
    }

    protected function queryTieneCiudad(string $query, string $municipio): bool
    {
        if (!$municipio) return true; // Si no detectamos ciudad, aceptar todo
        return stripos($query, $municipio) !== false;
    }

    protected function limpiarDireccion(string $direccion): string
    {
        $dir = preg_replace('/Entre:\s*[^,]*/i', '', $direccion);
        $dir = preg_replace('/CP:\s*\d+/', '', $dir);
        $dir = str_replace(['Av.', 'av.', 'AV.'], 'Av', $dir);
        $dir = str_replace(['No.', 'no.'], '', $dir);
        $dir = str_replace('#', '', $dir);
        $dir = str_ireplace('Ciudad de México', 'CDMX', $dir);
        $dir = str_ireplace(['Gdl', 'GDL'], 'Guadalajara', $dir);

        // Expandir abreviaciones de tipos de calle
        $dir = str_ireplace(['Hda.', 'Hda'], 'Hacienda', $dir);
        $dir = str_ireplace(['Fracc.', 'Fracc', 'Frac.', 'Frac'], 'Fraccionamiento', $dir);
        $dir = str_ireplace(['Col.', 'Col', 'Col.'], 'Colonia', $dir);
        $dir = str_ireplace(['Priv.', 'Priv', 'Priv.'], 'Privada', $dir);
        $dir = str_ireplace(['Calz.', 'Calz', 'Calz.'], 'Calzada', $dir);
        $dir = str_ireplace(['Blvd.', 'Blvd', 'Blvr.', 'Blvr'], 'Boulevard', $dir);
        $dir = str_ireplace(['Pro.', 'Pro', 'Pro.'], 'Providencia', $dir);
        $dir = str_ireplace(['Prol.', 'Prol', 'Prol.'], 'Prolongacion', $dir);
        $dir = str_ireplace(['Cto.', 'Cto', 'Cto.'], 'Circuito', $dir);
        $dir = str_ireplace(['Jard.', 'Jard', 'Jard.'], 'Jardines', $dir);
        $dir = str_ireplace(['Resid.', 'Resid', 'Resid.'], 'Residencial', $dir);

        $dir = preg_replace('/\s*,\s*,/', ',', $dir);
        $dir = preg_replace('/,+/', ',', $dir);
        $dir = preg_replace('/\s+/', ' ', $dir);
        $dir = trim($dir, ', ');
        return $dir;
    }

    protected function esResultadoUtil(array $result, string $direccionOriginal): bool
    {
        $nombre = strtolower($result['display_name'] ?? '');

        // Rechazar aeropuertos y terminales
        $rechazar = ['aeropuerto', 'airport', 'terminal'];
        foreach ($rechazar as $palabra) {
            if (str_contains($nombre, $palabra)) {
                return false;
            }
        }

        // Rechazar si el display_name tiene menos de 2 comas (muy genérico)
        if (substr_count($nombre, ',') < 2) {
            return false;
        }

        // Verificar que la ciudad del resultado coincida con la dirección original
        $dirLower = strtolower($direccionOriginal);
        $ciudadesConocidas = [
            'guadalajara' => 'jalisco',
            'zapopan' => 'jalisco',
            'cdmx' => 'ciudad de méxico',
            'monterrey' => 'nuevo león',
            'puebla' => 'puebla',
            'querétaro' => 'querétaro',
            'leon' => 'guanajuato',
        ];

        foreach ($ciudadesConocidas as $ciudad => $estado) {
            if (str_contains($dirLower, $ciudad)) {
                // La dirección original menciona esta ciudad
                // Verificar que el resultado esté en el mismo estado
                if (!str_contains($nombre, strtolower($estado)) && !str_contains($nombre, $ciudad)) {
                    return false;
                }
            }
        }

        return true;
    }

    protected function search(string $query): ?array
    {
        $maxRetries = 3;

        for ($attempt = 0; $attempt < $maxRetries; $attempt++) {
            try {
                $waitMs = $attempt === 0 ? $this->rateLimitMs : $this->rateLimitMs * (2 + $attempt);
                usleep($waitMs * 1000);

                $response = Http::withHeaders([
                    'User-Agent' => $this->userAgent,
                    'Accept-Language' => 'es',
                ])->timeout(15)->get($this->baseUrl, [
                    'q' => $query,
                    'format' => 'json',
                    'limit' => 1,
                    'countrycodes' => 'mx',
                ]);

                // Si Nominatim devuelve 429 (Too Many Requests), reintentar con espera mas larga
                if ($response->status() === 429) {
                    Log::warning('Geocoding: rate limited (429), reintentando', ['attempt' => $attempt + 1]);
                    continue;
                }

                if ($response->successful()) {
                    $data = $response->json();

                    if (!empty($data) && isset($data[0]['lat'], $data[0]['lon'])) {
                        return [
                            'lat' => (float) $data[0]['lat'],
                            'lng' => (float) $data[0]['lon'],
                            'display_name' => $data[0]['display_name'] ?? null,
                        ];
                    }
                }

                return null;

            } catch (\Exception $e) {
                Log::error('Geocoding: error', ['query' => $query, 'error' => $e->getMessage()]);
                return null;
            }
        }

        Log::error('Geocoding: max retries exceeded (429)', ['query' => $query]);
        return null;
    }

    public function reverseGeocode(float $lat, float $lng): ?string
    {
        try {
            usleep($this->rateLimitMs * 1000);

            $response = Http::withHeaders([
                'User-Agent' => $this->userAgent,
                'Accept-Language' => 'es',
            ])->timeout(10)->get('https://nominatim.openstreetmap.org/reverse', [
                'lat' => $lat,
                'lon' => $lng,
                'format' => 'json',
                'zoom' => 18,
                'addressdetails' => 1,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['display_name'] ?? null;
            }

            return null;

        } catch (\Exception $e) {
            Log::error('Reverse geocoding: error', ['error' => $e->getMessage()]);
            return null;
        }
    }
}
