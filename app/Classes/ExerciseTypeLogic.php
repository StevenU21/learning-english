<?php

namespace App\Classes;

class ExerciseTypeLogic
{
    public static function validateAndProcess(string $typeName, array $data): array
    {
        $errors = [];
        switch ($typeName) {
            case 'Opción múltiple':
                if (!isset($data['options']) || count($data['options']) < 2) {
                    $errors['options'] = 'Debes agregar al menos dos opciones.';
                }
                // Permitir que la solución sea array (para opción múltiple)
                $solution = $data['solution'] ?? [];
                if (!is_array($solution)) {
                    $solution = [$solution];
                }
                $invalid = array_filter($solution, function ($sol) use ($data) {
                    return !in_array($sol, $data['options'] ?? []);
                });
                if (count($invalid) > 0) {
                    $errors['solution'] = 'La solución debe estar entre las opciones.';
                }
                break;
            case 'Completar espacios':
                if (empty($data['solution'])) {
                    $errors['solution'] = 'Debes ingresar la solución.';
                }
                break;
            case 'Verdadero o falso':
                $data['options'] = ['True', 'False'];
                $solution = $data['solution'] ?? [];
                if (!is_array($solution)) {
                    $solution = [$solution];
                }
                // Solo debe haber un valor y debe ser True o False
                if (count($solution) !== 1 || !in_array($solution[0], $data['options'])) {
                    $errors['solution'] = 'La solución debe ser True o False.';
                }
                break;
            case 'Relacionar columnas':
                if (!is_array($data['options']) || count($data['options']) < 2) {
                    $errors['options'] = 'Debes agregar al menos dos pares para relacionar.';
                }
                break;
            case 'Ordenar elementos':
                if (!is_array($data['options']) || count($data['options']) < 2) {
                    $errors['options'] = 'Debes agregar al menos dos elementos para ordenar.';
                }
                if (!is_array($data['solution'])) {
                    $errors['solution'] = 'La solución debe ser un array con el orden correcto.';
                }
                break;
            case 'Emparejar definiciones':
                // options: [{concepto, definicion}], solution: [{concepto, definicion}]
                if (!is_array($data['options']) || count($data['options']) < 2) {
                    $errors['options'] = 'Debes agregar al menos dos pares de concepto y definición.';
                }
                if (!is_array($data['solution']) || count($data['solution']) < 2) {
                    $errors['solution'] = 'Debes ingresar la solución como pares de concepto y definición.';
                }
                break;
            case 'Completar diálogo':
                if (!is_array($data['options']) || count($data['options']) < 1) {
                    $errors['options'] = 'Debes agregar al menos una frase para el diálogo.';
                }
                if (!is_array($data['solution']) || count($data['solution']) < 1) {
                    $errors['solution'] = 'Debes ingresar la(s) frase(s) correcta(s) para completar el diálogo.';
                }
                break;
        }
        return [
            'errors' => $errors,
            'data' => $data
        ];
    }
}
