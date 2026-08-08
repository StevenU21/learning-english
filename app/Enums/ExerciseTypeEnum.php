<?php

namespace App\Enums;

enum ExerciseTypeEnum: string
{
    case MULTIPLE_CHOICE = 'Opción múltiple';
    case FILL_IN_BLANK = 'Completar espacios';
    case TRUE_FALSE = 'Verdadero o falso';
    case COLUMN_MATCH = 'Relacionar columnas';
    case ORDERING = 'Ordenar elementos';
    case DEFINITION_MATCH = 'Emparejar definiciones';
    case DIALOGUE = 'Completar diálogo';
    case LISTEN_AND_CHOOSE = 'Elige lo que escuchas';
    case LISTEN_AND_RESPOND = 'Escucha y responde';
    case LISTEN_AND_WRITE = 'Escucha y escribe';
    case TRANSLATE_SENTENCE = 'Traduce la oración';
    case SAY_THE_PHRASE = 'Di la frase';
}
