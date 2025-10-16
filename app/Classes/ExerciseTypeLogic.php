<?php

namespace App\Classes;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class ExerciseTypeLogic
{
    protected const VALIDATION_METHODS = [
        'Opción múltiple' => 'validateMultipleChoice',
        'Completar espacios' => 'validateFillInTheBlank',
        'Verdadero o falso' => 'validateTrueFalse',
        'Relacionar columnas' => 'validateColumnMatch',
        'Ordenar elementos' => 'validateOrdering',
        'Emparejar definiciones' => 'validateDefinitionMatch',
        'Completar diálogo' => 'validateDialogue',
        'Elige lo que escuchas' => 'validateListenAndChoose',
        'Escucha y responde' => 'validateListenAndRespond',
    ];

    public static function validateAndProcess(string $typeName, array $data): array
    {
        $payload = collect($data);
        $errors = collect();

        $method = Arr::get(self::VALIDATION_METHODS, $typeName);

        if ($method && method_exists(self::class, $method)) {
            self::{$method}($payload, $errors);
        }

        return [
            'errors' => $errors->toArray(),
            'data' => $payload->toArray(),
        ];
    }

    protected static function validateMultipleChoice(Collection $data, Collection $errors): void
    {
        $options = collect($data->get('options', []));

        if ($options->count() < 2) {
            $errors->put('options', 'Debes agregar al menos dos opciones.');
        }

        $solution = collect(Arr::wrap($data->get('solution')));
        $data->put('solution', $solution->toArray());

        $invalid = $solution->reject(static fn ($value) => $options->contains($value));

        if ($invalid->isNotEmpty()) {
            $errors->put('solution', 'La solución debe estar entre las opciones.');
        }
    }

    protected static function validateFillInTheBlank(Collection $data, Collection $errors): void
    {
        if (blank($data->get('solution'))) {
            $errors->put('solution', 'Debes ingresar la solución.');
        }
    }

    protected static function validateTrueFalse(Collection $data, Collection $errors): void
    {
        $data->put('options', ['True', 'False']);
        self::ensureSingleSolution($data, $errors, 'La solución debe ser True o False.', $data->get('options'));
    }

    protected static function validateColumnMatch(Collection $data, Collection $errors): void
    {
        self::ensureMinimumOptions($data, $errors, 2, 'Debes agregar al menos dos pares para relacionar.');
    }

    protected static function validateOrdering(Collection $data, Collection $errors): void
    {
        self::ensureMinimumOptions($data, $errors, 2, 'Debes agregar al menos dos elementos para ordenar.');

        if (!is_array($data->get('solution'))) {
            $errors->put('solution', 'La solución debe ser un array con el orden correcto.');
        }
    }

    protected static function validateDefinitionMatch(Collection $data, Collection $errors): void
    {
        self::ensureMinimumOptions($data, $errors, 2, 'Debes agregar al menos dos pares de concepto y definición.');

        $solution = $data->get('solution');
        if (!is_array($solution) || count($solution) < 2) {
            $errors->put('solution', 'Debes ingresar la solución como pares de concepto y definición.');
        }
    }

    protected static function validateDialogue(Collection $data, Collection $errors): void
    {
        self::ensureMinimumOptions($data, $errors, 1, 'Debes agregar al menos una frase para el diálogo.');

        $solution = $data->get('solution');
        if (!is_array($solution) || count($solution) < 1) {
            $errors->put('solution', 'Debes ingresar la(s) frase(s) correcta(s) para completar el diálogo.');
        }
    }

    protected static function validateListenAndChoose(Collection $data, Collection $errors): void
    {
        if (blank($data->get('file'))) {
            $errors->put('file', 'Debes subir un archivo de audio.');
        }

        $options = collect($data->get('options', []));
        if ($options->count() < 2 || $options->count() > 4) {
            $errors->put('options', 'Debes agregar entre 2 y 4 opciones.');
        }

        self::ensureSingleSolution($data, $errors, 'Debes elegir solo una opción como solución.', $options->toArray());
    }

    protected static function validateListenAndRespond(Collection $data, Collection $errors): void
    {
        if (blank($data->get('file'))) {
            $errors->put('file', 'Debes subir el primer audio.');
        }

        if (blank($data->get('file_b'))) {
            $errors->put('file_b', 'Debes subir el segundo audio.');
        }

        $data->put('options', ['Igual', 'Distinto']);
        self::ensureSingleSolution($data, $errors, 'La solución debe ser "Igual" o "Distinto".', $data->get('options'));
    }

    /**
     * Centraliza la validación de opciones con un mínimo requerido.
     */
    protected static function ensureMinimumOptions(Collection $data, Collection $errors, int $minimum, string $message): void
    {
        $options = $data->get('options');
        if (!is_array($options) || count($options) < $minimum) {
            $errors->put('options', $message);
        }
    }

    /**
     * Fuerza la solución a ser un único valor y valida que pertenezca a las opciones permitidas.
     */
    protected static function ensureSingleSolution(Collection $data, Collection $errors, string $message, ?array $allowedOptions = null): void
    {
        $solution = collect(Arr::wrap($data->get('solution')));
        $data->put('solution', $solution->toArray());

        if ($solution->count() !== 1) {
            $errors->put('solution', $message);
            return;
        }

        if ($allowedOptions !== null && !collect($allowedOptions)->contains($solution->first())) {
            $errors->put('solution', $message);
        }
    }
}
