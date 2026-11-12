<?php

declare(strict_types=1);

namespace App\Services;

final class VehicleDataNormalizer
{
    /**
     * @return array{source_make_id: int, name: string}|null
     */
    public function make(mixed $row): ?array
    {
        if (!is_array($row)) {
            return null;
        }

        $id = $this->positiveId($row['Make_ID'] ?? $row['MakeId'] ?? null);
        $name = $this->name($row['Make_Name'] ?? $row['MakeName'] ?? null);
        if ($id === null || $name === null) {
            return null;
        }

        return ['source_make_id' => $id, 'name' => $name];
    }

    /**
     * @return array{source_make_id: int, source_model_id: int, name: string}|null
     */
    public function model(mixed $row, int $expectedMakeId): ?array
    {
        if (!is_array($row)) {
            return null;
        }

        $makeId = $this->positiveId($row['Make_ID'] ?? null);
        $modelId = $this->positiveId($row['Model_ID'] ?? null);
        $name = $this->name($row['Model_Name'] ?? null);
        if ($makeId !== $expectedMakeId || $modelId === null || $name === null) {
            return null;
        }

        return [
            'source_make_id' => $makeId,
            'source_model_id' => $modelId,
            'name' => $name,
        ];
    }

    private function positiveId(mixed $value): ?int
    {
        $id = filter_var($value, FILTER_VALIDATE_INT);

        return $id !== false && $id > 0 ? $id : null;
    }

    private function name(mixed $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $name = preg_replace('/\s+/u', ' ', trim($value));
        if (!is_string($name) || $name === '' || mb_strlen($name, 'UTF-8') > 100) {
            return null;
        }

        return $name;
    }
}
