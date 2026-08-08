<?php

namespace App\Validation\Exercise;

use App\Enums\ExerciseTypeEnum;
use App\Validation\Exercise\Strategies\DefaultStrategy;
use App\Validation\Exercise\Strategies\MultipleChoiceStrategy;
use App\Validation\Exercise\Strategies\FillInBlankStrategy;
use App\Validation\Exercise\Strategies\TrueFalseStrategy;
use App\Validation\Exercise\Strategies\ColumnMatchStrategy;
use App\Validation\Exercise\Strategies\DefinitionMatchStrategy;
use App\Validation\Exercise\Strategies\DialogueStrategy;
use App\Validation\Exercise\Strategies\ListenChooseStrategy;
use App\Validation\Exercise\Strategies\ListenRespondStrategy;
use App\Validation\Exercise\Strategies\TranslateSentenceStrategy;
use App\Validation\Exercise\Strategies\ListenWriteStrategy;

class ExerciseValidationFactory
{
    public static function make(?ExerciseTypeEnum $type): ExerciseValidationStrategy
    {
        if (!$type) {
            return new DefaultStrategy();
        }

        return match ($type) {
            ExerciseTypeEnum::MULTIPLE_CHOICE => new MultipleChoiceStrategy(),
            ExerciseTypeEnum::FILL_IN_BLANK => new FillInBlankStrategy(),
            ExerciseTypeEnum::TRUE_FALSE => new TrueFalseStrategy(),
            ExerciseTypeEnum::COLUMN_MATCH, ExerciseTypeEnum::ORDERING => new ColumnMatchStrategy(),
            ExerciseTypeEnum::DEFINITION_MATCH => new DefinitionMatchStrategy(),
            ExerciseTypeEnum::DIALOGUE => new DialogueStrategy(),
            ExerciseTypeEnum::LISTEN_AND_CHOOSE => new ListenChooseStrategy(),
            ExerciseTypeEnum::LISTEN_AND_RESPOND => new ListenRespondStrategy(),
            ExerciseTypeEnum::TRANSLATE_SENTENCE, ExerciseTypeEnum::SAY_THE_PHRASE => new TranslateSentenceStrategy(),
            ExerciseTypeEnum::LISTEN_AND_WRITE => new ListenWriteStrategy(),
            default => new DefaultStrategy(),
        };
    }
}
