<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;

class InventoryImportAnalyzer
{
    private const MAX_ROWS = 120;

    public function __construct(private GeminiClient $gemini)
    {
    }

    public function analyze(UploadedFile $file): array
    {
        $extension = strtolower((string) $file->getClientOriginalExtension());
        $mimeType = strtolower((string) $file->getMimeType());

        if (in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            return $this->analyzeImage($file);
        }

        if (in_array($extension, ['csv', 'tsv'], true)) {
            return $this->analyzeDelimitedFile($file, $extension === 'tsv' ? "\t" : ',');
        }

        if ($extension === 'json') {
            return $this->analyzeJsonFile($file);
        }

        if ($extension === 'txt' || str_starts_with($mimeType, 'text/')) {
            return $this->analyzeTextFile($file);
        }

        return [
            'ok' => false,
            'message' => 'Unsupported inventory file. Upload a clear image, CSV, TSV, TXT, or JSON file.',
        ];
    }

    private function analyzeImage(UploadedFile $file): array
    {
        $path = (string) $file->getRealPath();
        $imageInfo = @getimagesize($path);

        if ($imageInfo === false) {
            return [
                'ok' => false,
                'message' => 'The uploaded image appears corrupted or unreadable.',
            ];
        }

        [$width, $height] = $imageInfo;
        if ($width < 900 || $height < 600) {
            return [
                'ok' => false,
                'message' => 'The inventory image is too small to analyze reliably. Upload a clearer, higher-resolution photo.',
            ];
        }

        $blurScore = $this->estimateImageSharpness($file);
        if ($blurScore !== null && $blurScore < 22) {
            return [
                'ok' => false,
                'message' => 'The inventory image is too blurry to import safely. Retake it with better focus and lighting.',
                'quality' => [
                    'status' => 'blurry',
                    'score' => (int) round($blurScore),
                    'width' => $width,
                    'height' => $height,
                ],
            ];
        }

        $prompt = <<<'PROMPT'
Read this clinic inventory image.

Return only JSON with this shape:
{
  "visible_title": "document title",
  "quality": {"status": "clear", "confidence": 0-100, "issues": []},
  "rows": [
    {
      "date": "DATE column text",
      "stock_number": "Stock Number column text",
      "name": "MEDICINES & MATERIALS item name",
      "category": "Medicine|Supplies|Equipment",
      "unit": "UNIT column text",
      "quantity": "QUANTITY column value",
      "consumed": "CONSUMED column value",
      "balance": "BALANCE column value",
      "expiration_date": "EXPIRATION DATE column text",
      "confidence": 0-100,
      "notes": ""
    }
  ]
}

Extract all visible table rows that have an item name. Do not invent rows.
If the document title or section says SUPPLIES, use category "Supplies".
Use the BALANCE column as the current inventory quantity.
Return no rows only if the image is truly unreadable, corrupted, or not an inventory list.
PROMPT;

        if ($this->shouldUseGemini()) {
            return $this->analyzeImageWithGemini($file, $prompt, $blurScore, $width, $height);
        }

        return $this->analyzeImageWithOpenAi($file, $prompt, $blurScore, $width, $height);
    }

    private function analyzeImageWithGemini(UploadedFile $file, string $prompt, ?float $blurScore, int $width, int $height): array
    {
        if (!$this->gemini->configured()) {
            return [
                'ok' => false,
                'message' => 'Image inventory import requires GEMINI_API_KEY to be configured.',
            ];
        }

        try {
            $text = $this->gemini->generateImageText($prompt, $file, 8192, 0.0);
            if ($text === null) {
                return [
                    'ok' => false,
                    'message' => 'The Gemini analyzer could not process this image right now. ' . ($this->gemini->lastError() ?: 'Check the API key and model.'),
                ];
            }

            return $this->buildImageAnalysisResult($text, 'gemini-image', $file->getClientOriginalName(), $blurScore, $width, $height);
        } catch (\Throwable $exception) {
            return [
                'ok' => false,
                'message' => 'The Gemini analyzer failed. Please try a clearer image or upload CSV.',
            ];
        }
    }

    private function analyzeImageWithOpenAi(UploadedFile $file, string $prompt, ?float $blurScore, int $width, int $height): array
    {
        $apiKey = trim((string) config('services.openai.api_key'));
        if ($apiKey === '') {
            return [
                'ok' => false,
                'message' => 'Image inventory import requires an AI API key. Configure GEMINI_API_KEY or OPENAI_API_KEY.',
            ];
        }

        $model = trim((string) config('services.openai.model', ''));
        if ($model === '') {
            $model = 'gpt-4.1-mini';
        }

        $path = (string) $file->getRealPath();
        $base64 = base64_encode((string) file_get_contents($path));
        $mimeType = $file->getMimeType() ?: 'image/jpeg';
        $imageData = 'data:' . $mimeType . ';base64,' . $base64;

        try {
            $response = Http::withToken($apiKey)
                ->acceptJson()
                ->timeout(45)
                ->post('https://api.openai.com/v1/responses', [
                    'model' => $model,
                    'input' => [[
                        'role' => 'user',
                        'content' => [
                            ['type' => 'input_text', 'text' => $prompt],
                            ['type' => 'input_image', 'image_url' => $imageData, 'detail' => 'high'],
                        ],
                    ]],
                ]);

            if (!$response->successful()) {
                return [
                    'ok' => false,
                    'message' => 'The AI analyzer could not process this image right now.',
                ];
            }

            return $this->buildImageAnalysisResult(
                $this->extractOpenAiOutputText($response->json() ?: []),
                'openai-image',
                $file->getClientOriginalName(),
                $blurScore,
                $width,
                $height
            );
        } catch (\Throwable $exception) {
            return [
                'ok' => false,
                'message' => 'The image analyzer failed. Please try a clearer image or upload CSV.',
            ];
        }
    }

    private function buildImageAnalysisResult(string $jsonText, string $sourceType, string $sourceName, ?float $blurScore, int $width, int $height): array
    {
        $decoded = $this->decodeJsonText($jsonText);
        if (!is_array($decoded)) {
            return [
                'ok' => false,
                'message' => 'The AI analyzer returned an unreadable response. Upload a clearer inventory image or use CSV.',
            ];
        }

        $rowSource = $decoded['rows']
            ?? $decoded['items']
            ?? $decoded['inventory']
            ?? ($this->isListArray($decoded) ? $decoded : []);
        $rows = $this->normalizeRows((array) $rowSource);

        $quality = is_array($decoded['quality'] ?? null) ? $decoded['quality'] : [];
        $status = strtolower(trim((string) ($quality['status'] ?? '')));
        if ($status === '' && $rows !== []) {
            $status = 'clear';
        }

        $confidence = array_key_exists('confidence', $quality)
            ? (int) ($quality['confidence'] ?? 0)
            : ($rows !== [] ? 90 : 0);

        if (!in_array($status, ['clear', 'ok'], true) || $confidence < 85) {
            return [
                'ok' => false,
                'message' => 'The uploaded image was not clear enough for safe import. Please retake it or upload a CSV file.',
                'quality' => [
                    'status' => $status ?: 'uncertain',
                    'confidence' => $confidence,
                    'issues' => array_values((array) ($quality['issues'] ?? [])),
                    'blur_score' => $blurScore,
                    'width' => $width,
                    'height' => $height,
                ],
            ];
        }

        if ($rows === []) {
            return [
                'ok' => false,
                'message' => 'No inventory rows were detected in the uploaded image.',
            ];
        }

        return [
            'ok' => true,
            'source_type' => $sourceType,
            'source_name' => $sourceName,
            'quality' => [
                'status' => 'clear',
                'confidence' => $confidence,
                'issues' => array_values((array) ($quality['issues'] ?? [])),
                'blur_score' => $blurScore,
                'width' => $width,
                'height' => $height,
            ],
            'rows' => $rows,
        ];
    }

    private function analyzeDelimitedFile(UploadedFile $file, string $delimiter): array
    {
        $path = (string) $file->getRealPath();
        $handle = @fopen($path, 'r');
        if (!$handle) {
            return [
                'ok' => false,
                'message' => 'The uploaded inventory file appears corrupted or unreadable.',
            ];
        }

        $rows = [];
        $headers = null;
        $currentCategory = null;
        $lineNumber = 0;

        while (($line = fgetcsv($handle, 0, $delimiter)) !== false) {
            $lineNumber++;
            $line = array_map(static fn ($value) => trim((string) $value), $line);

            if ($this->isEmptyRow($line)) {
                continue;
            }

            if ($headers === null) {
                $headers = $this->normalizeHeaders($line);
                if (!$this->hasRecognizedHeaders($headers)) {
                    fclose($handle);
                    return [
                        'ok' => false,
                        'message' => 'The file needs a header row with item/name and quantity/balance columns.',
                    ];
                }
                continue;
            }

            $record = [];
            foreach ($headers as $index => $field) {
                if ($field !== '') {
                    $record[$field] = $line[$index] ?? '';
                }
            }

            $nonEmptyValues = array_values(array_filter(array_map(static fn ($value) => trim((string) $value), $record), static fn ($value) => $value !== ''));
            if (count($nonEmptyValues) === 1) {
                $sectionCategory = $this->categorySectionMarker($nonEmptyValues[0]);
                if ($sectionCategory !== null) {
                    $currentCategory = $sectionCategory;
                    continue;
                }
            }

            if ($currentCategory !== null && trim((string) ($record['category'] ?? '')) === '') {
                $record['category'] = $currentCategory;
            }

            $rows[] = $record;
            if (count($rows) >= self::MAX_ROWS) {
                break;
            }
        }

        fclose($handle);

        $normalizedRows = $this->normalizeRows($rows);
        if ($normalizedRows === []) {
            return [
                'ok' => false,
                'message' => 'No valid inventory rows were found in the uploaded file.',
            ];
        }

        return [
            'ok' => true,
            'source_type' => $delimiter === "\t" ? 'tsv' : 'csv',
            'source_name' => $file->getClientOriginalName(),
            'quality' => [
                'status' => 'parsed',
                'confidence' => 100,
                'issues' => [],
            ],
            'rows' => $normalizedRows,
        ];
    }

    private function analyzeJsonFile(UploadedFile $file): array
    {
        $decoded = json_decode((string) file_get_contents((string) $file->getRealPath()), true);
        if (!is_array($decoded)) {
            return [
                'ok' => false,
                'message' => 'The uploaded JSON file is corrupted or invalid.',
            ];
        }

        $rows = isset($decoded['rows']) && is_array($decoded['rows'])
            ? $decoded['rows']
            : $decoded;

        $normalizedRows = $this->normalizeRows((array) $rows);
        if ($normalizedRows === []) {
            return [
                'ok' => false,
                'message' => 'No valid inventory rows were found in the JSON file.',
            ];
        }

        return [
            'ok' => true,
            'source_type' => 'json',
            'source_name' => $file->getClientOriginalName(),
            'quality' => [
                'status' => 'parsed',
                'confidence' => 100,
                'issues' => [],
            ],
            'rows' => $normalizedRows,
        ];
    }

    private function analyzeTextFile(UploadedFile $file): array
    {
        $text = trim((string) file_get_contents((string) $file->getRealPath()));
        if ($text === '') {
            return [
                'ok' => false,
                'message' => 'The uploaded text file is empty or unreadable.',
            ];
        }

        if (str_contains($text, "\t")) {
            return $this->analyzeDelimitedFile($file, "\t");
        }

        $prompt = "Extract clinic inventory rows from the text below. Return strict JSON with a rows array using fields: name, category, stock_number, unit, quantity, consumed, starting_stock, minimum_stock, date_added, expiration_date, medicine_type, confidence, notes. Do not invent rows.\n\n" . $this->limitText($text, 12000);

        if ($this->shouldUseGemini()) {
            if (!$this->gemini->configured()) {
                return [
                    'ok' => false,
                    'message' => 'Plain text inventory import requires GEMINI_API_KEY. Use CSV/TSV for local parsing.',
                ];
            }

            try {
                $output = $this->gemini->generateText($prompt, 8192, 0.0);
                if ($output === null) {
                    return [
                        'ok' => false,
                        'message' => 'The Gemini text analyzer could not process this file right now. ' . ($this->gemini->lastError() ?: 'Check the API key and model.'),
                    ];
                }

                return $this->buildTextAnalysisResult($output, 'gemini-text', $file->getClientOriginalName());
            } catch (\Throwable $exception) {
                return [
                    'ok' => false,
                    'message' => 'The Gemini text analyzer failed. Please upload CSV/TSV or try again.',
                ];
            }
        }

        $apiKey = trim((string) config('services.openai.api_key'));
        if ($apiKey === '') {
            return [
                'ok' => false,
                'message' => 'Plain text inventory import requires an AI API key. Configure GEMINI_API_KEY or OPENAI_API_KEY, or use CSV/TSV.',
            ];
        }

        $model = trim((string) config('services.openai.model', '')) ?: 'gpt-4.1-mini';

        try {
            $response = Http::withToken($apiKey)
                ->acceptJson()
                ->timeout(35)
                ->post('https://api.openai.com/v1/responses', [
                    'model' => $model,
                    'input' => [[
                        'role' => 'user',
                        'content' => [
                            ['type' => 'input_text', 'text' => $prompt],
                        ],
                    ]],
                ]);

            if (!$response->successful()) {
                return [
                    'ok' => false,
                    'message' => 'The text analyzer could not process this file right now.',
                ];
            }

            return $this->buildTextAnalysisResult($this->extractOpenAiOutputText($response->json() ?: []), 'openai-text', $file->getClientOriginalName());
        } catch (\Throwable $exception) {
            return [
                'ok' => false,
                'message' => 'The text analyzer failed. Please upload CSV/TSV or try again.',
            ];
        }
    }

    private function buildTextAnalysisResult(string $jsonText, string $sourceType, string $sourceName): array
    {
        $decoded = $this->decodeJsonText($jsonText);
        $rows = $this->normalizeRows((array) ($decoded['rows'] ?? []));

        if ($rows === []) {
            return [
                'ok' => false,
                'message' => 'No valid inventory rows were detected in the text file.',
            ];
        }

        return [
            'ok' => true,
            'source_type' => $sourceType,
            'source_name' => $sourceName,
            'quality' => [
                'status' => 'parsed',
                'confidence' => 90,
                'issues' => [],
            ],
            'rows' => $rows,
        ];
    }

    private function normalizeRows(array $rows): array
    {
        $normalized = [];
        $normalizer = new InventoryDataNormalizer();

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $name = $this->firstString($row, [
                'name',
                'item',
                'item_name',
                'item_description',
                'description',
                'description_of_items',
                'particulars',
                'product',
                'article',
                'medicine',
                'medicines',
                'medicine_name',
                'medicine_material',
                'medicine_materials',
                'medicine_and_materials',
                'medicines_materials',
                'medicines_and_materials',
                'medical_supplies',
                'supplies',
                'supply',
                'supplies_materials',
                'materials',
            ]);

            $quantityValue = $this->firstValue($row, [
                'quantity',
                'qty',
                'balance',
                'ending_balance',
                'current_balance',
                'remaining_balance',
                'balance_on_hand',
                'current_stock',
                'stock',
                'on_hand',
                'available',
                'remaining',
            ]);
            $hasQuantityValue = $quantityValue !== null && trim((string) $quantityValue) !== '';
            $quantity = $this->parseNumber($quantityValue);
            $consumed = $this->parseNumber($this->firstValue($row, ['consumed', 'consumed_qty', 'used', 'used_qty', 'issued', 'issued_qty', 'out', 'dispensed']));
            $startingStock = $this->parseNumber($this->firstValue($row, [
                'starting_stock',
                'starting',
                'beginning_balance',
                'beginning',
                'initial_stock',
                'start_stock',
                'stock_on_hand_beginning',
            ]));
            if (!$hasQuantityValue && $startingStock > 0) {
                $quantity = max(0, $startingStock - $consumed);
            }

            $hasMeaningfulInventoryValues = $quantity > 0
                || $consumed > 0
                || $startingStock > 0
                || $this->firstString($row, ['stock_number', 'stock_no', 'stock_num', 'stock_code', 'inventory_number', 'property_number']) !== ''
                || $this->firstString($row, ['unit', 'units', 'uom', 'u_m', 'um', 'unit_of_measure', 'unit_measure', 'measure', 'stock_unit']) !== '';
            $hasMeaningfulInventoryData = $name !== '' || $hasMeaningfulInventoryValues;

            if ($name !== '' && $this->categorySectionMarker($name) !== null && !$hasMeaningfulInventoryValues) {
                continue;
            }

            if (!$hasMeaningfulInventoryData) {
                continue;
            }

            $category = $this->normalizeCategory($this->firstString($row, ['category', 'type', 'classification']));

            if ($startingStock <= 0 && $consumed > 0) {
                $startingStock = $quantity + $consumed;
            } elseif ($startingStock <= 0) {
                $startingStock = $quantity;
            }

            $normalized[] = [
                'name' => $this->limitText($name, 255),
                'category' => $category,
                'stock_number' => $this->limitText($this->firstString($row, ['stock_number', 'stock_no', 'stock_num', 'stock_code', 'inventory_number', 'property_number']), 50),
                'unit' => $this->limitText($normalizer->normalizeUnit($this->firstString($row, ['unit', 'units', 'uom', 'u_m', 'um', 'unit_of_measure', 'unit_measure', 'measure', 'stock_unit']) ?: 'pcs'), 50),
                'quantity' => max(0, $quantity),
                'consumed' => max(0, $consumed),
                'starting_stock' => max(0, $startingStock),
                'minimum_stock' => max(0, $this->parseNumber($this->firstValue($row, ['minimum_stock', 'minimum', 'minimum_qty', 'reorder_level', 'reorder_point']), 10)),
                'date_added' => $normalizer->normalizeDate($this->firstString($row, ['date_added', 'date', 'received_date', 'restock_date', 'inventory_date'])),
                'expiration_date' => $normalizer->normalizeExpirationDate($this->firstString($row, ['expiration_date', 'expiry', 'expiry_date', 'expiration', 'expiry_expiration_date'])),
                'medicine_type' => $this->limitText($this->firstString($row, ['medicine_type', 'medicine_class', 'drug_class', 'medicine_category']), 255),
                'confidence' => min(100, max(0, (int) $this->parseNumber($this->firstValue($row, ['confidence']), 100))),
                'notes' => $this->limitText($this->firstString($row, ['notes', 'note', 'remarks']) ?: ($name === '' ? 'Item name was not detected. Type the item name before importing this row.' : ''), 500),
            ];

            if (count($normalized) >= self::MAX_ROWS) {
                break;
            }
        }

        return $normalized;
    }

    private function shouldUseGemini(): bool
    {
        $provider = strtolower(trim((string) config('services.ai.provider', 'auto')));

        if ($provider === 'gemini') {
            return true;
        }

        if ($provider === 'openai') {
            return false;
        }

        return $this->gemini->configured();
    }

    private function isListArray(array $value): bool
    {
        if ($value === []) {
            return true;
        }

        return array_keys($value) === range(0, count($value) - 1);
    }

    private function normalizeHeaders(array $headers): array
    {
        return array_map(function ($header) {
            $value = strtolower(trim((string) $header));
            $value = preg_replace('/^\xEF\xBB\xBF/', '', $value) ?? $value;
            $value = preg_replace('/[^a-z0-9]+/', '_', $value) ?? $value;
            $value = trim($value, '_');

            return match ($value) {
                'item', 'item_name', 'medicine', 'medicines', 'medicine_name', 'material', 'materials',
                'description', 'item_description', 'description_of_items', 'particulars', 'product', 'article',
                'supplies', 'supply', 'medical_supplies', 'supplies_materials', 'medicine_material',
                'medicine_materials', 'medicine_and_materials', 'medicines_materials', 'medicines_and_materials',
                'medicines_materials_supplies', 'medicines_and_materials_supplies', 'medicines_materials' => 'name',
                'stock', 'stock_no', 'stock_num', 'stock_number', 'stk', 'stk_no', 'sku', 'stock_code', 'inventory_number', 'property_number' => 'stock_number',
                'qty', 'quantity', 'quantity_on_hand', 'current_stock', 'balance', 'ending_balance', 'current_balance',
                'remaining_balance', 'balance_on_hand', 'on_hand', 'available', 'remaining' => 'quantity',
                'consumed_qty', 'used_qty', 'issued_qty', 'used', 'issued', 'dispensed', 'consumed' => 'consumed',
                'starting', 'beginning', 'beginning_balance', 'initial_stock', 'start_stock', 'stock_on_hand_beginning', 'starting_stock' => 'starting_stock',
                'minimum', 'minimum_qty', 'minimum_stock', 'reorder_level', 'reorder_point' => 'minimum_stock',
                'unit', 'units', 'uom', 'u_m', 'um', 'unit_of_measure', 'unit_measure', 'measure', 'stock_unit' => 'unit',
                'date', 'date_received', 'received_date', 'inventory_date', 'date_added' => 'date_added',
                'expiry', 'expiry_date', 'expiration', 'expiration_date', 'expiry_expiration_date' => 'expiration_date',
                'medicine_class', 'drug_class', 'medicine_category' => 'medicine_type',
                default => $value,
            };
        }, $headers);
    }

    private function hasRecognizedHeaders(array $headers): bool
    {
        $hasItemLikeColumn = count(array_intersect($headers, ['name', 'stock_number'])) > 0;
        $hasInventoryQuantityColumn = count(array_intersect($headers, ['quantity', 'starting_stock', 'consumed'])) > 0;

        return $hasItemLikeColumn && $hasInventoryQuantityColumn;
    }

    private function firstString(array $row, array $keys): string
    {
        $value = $this->firstValue($row, $keys);
        return trim((string) $value);
    }

    private function firstValue(array $row, array $keys): mixed
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $row) && $row[$key] !== null && trim((string) $row[$key]) !== '') {
                return $row[$key];
            }
        }

        return null;
    }

    private function parseNumber(mixed $value, float $default = 0): float
    {
        if ($value === null || trim((string) $value) === '') {
            return $default;
        }

        $clean = preg_replace('/[^0-9.\-]/', '', (string) $value) ?? '';
        if ($clean === '' || !is_numeric($clean)) {
            return $default;
        }

        return (float) $clean;
    }

    private function normalizeCategory(string $value): string
    {
        $value = strtolower(trim($value));

        if (str_contains($value, 'equip')) {
            return 'Equipment';
        }

        if (str_contains($value, 'suppl') || str_contains($value, 'material')) {
            return 'Supplies';
        }

        return 'Medicine';
    }

    private function categorySectionMarker(string $value): ?string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z]+/', ' ', $value) ?? $value;
        $value = trim(preg_replace('/\s+/', ' ', $value) ?? $value);

        return match ($value) {
            'medicine', 'medicines', 'drug', 'drugs', 'medication', 'medications' => 'Medicine',
            'supply', 'supplies', 'medical supply', 'medical supplies', 'clinic supply', 'clinic supplies' => 'Supplies',
            'equipment', 'equipments', 'clinic equipment', 'medical equipment' => 'Equipment',
            default => null,
        };
    }


    private function limitText(string $value, int $maxLength): string
    {
        $value = trim($value);

        if (function_exists('mb_substr')) {
            return mb_substr($value, 0, $maxLength);
        }

        return substr($value, 0, $maxLength);
    }

    private function isEmptyRow(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    private function extractOpenAiOutputText(array $payload): string
    {
        $outputText = trim((string) data_get($payload, 'output_text', ''));
        if ($outputText !== '') {
            return $outputText;
        }

        $parts = [];
        foreach ((array) data_get($payload, 'output', []) as $output) {
            foreach ((array) data_get($output, 'content', []) as $content) {
                $text = trim((string) data_get($content, 'text', ''));
                if ($text !== '') {
                    $parts[] = $text;
                }
            }
        }

        return trim(implode("\n", $parts));
    }

    private function decodeJsonText(string $text): ?array
    {
        $text = trim($text);
        $text = preg_replace('/^```json\s*/i', '', $text) ?? $text;
        $text = preg_replace('/^```\s*/', '', $text) ?? $text;
        $text = preg_replace('/\s*```$/', '', $text) ?? $text;

        $decoded = json_decode($text, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        $objectStart = strpos($text, '{');
        $objectEnd = strrpos($text, '}');
        if ($objectStart !== false && $objectEnd !== false && $objectEnd > $objectStart) {
            $decoded = json_decode(substr($text, $objectStart, $objectEnd - $objectStart + 1), true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        $arrayStart = strpos($text, '[');
        $arrayEnd = strrpos($text, ']');
        if ($arrayStart !== false && $arrayEnd !== false && $arrayEnd > $arrayStart) {
            $decoded = json_decode(substr($text, $arrayStart, $arrayEnd - $arrayStart + 1), true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return null;
    }

    private function estimateImageSharpness(UploadedFile $file): ?float
    {
        if (!extension_loaded('gd')) {
            return null;
        }

        $path = (string) $file->getRealPath();
        $extension = strtolower((string) $file->getClientOriginalExtension());

        $image = match ($extension) {
            'jpg', 'jpeg' => @imagecreatefromjpeg($path),
            'png' => @imagecreatefrompng($path),
            'webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false,
            default => false,
        };

        if (!$image) {
            return null;
        }

        $sourceWidth = imagesx($image);
        $sourceHeight = imagesy($image);
        if ($sourceWidth < 2 || $sourceHeight < 2) {
            imagedestroy($image);
            return 0;
        }

        $width = min(180, $sourceWidth);
        $height = max(2, (int) round($sourceHeight * ($width / $sourceWidth)));
        $sample = imagecreatetruecolor($width, $height);
        imagecopyresampled($sample, $image, 0, 0, 0, 0, $width, $height, $sourceWidth, $sourceHeight);
        imagefilter($sample, IMG_FILTER_GRAYSCALE);

        $values = [];
        for ($y = 1; $y < $height - 1; $y++) {
            for ($x = 1; $x < $width - 1; $x++) {
                $center = imagecolorat($sample, $x, $y) & 0xFF;
                $laplacian = (4 * $center)
                    - (imagecolorat($sample, $x - 1, $y) & 0xFF)
                    - (imagecolorat($sample, $x + 1, $y) & 0xFF)
                    - (imagecolorat($sample, $x, $y - 1) & 0xFF)
                    - (imagecolorat($sample, $x, $y + 1) & 0xFF);
                $values[] = $laplacian;
            }
        }

        imagedestroy($sample);
        imagedestroy($image);

        if ($values === []) {
            return 0;
        }

        $mean = array_sum($values) / count($values);
        $variance = 0;
        foreach ($values as $value) {
            $variance += ($value - $mean) ** 2;
        }

        return $variance / count($values);
    }
}
