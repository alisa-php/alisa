<?php

namespace Alisa\Support;

use Alisa\Stores\Assets;

class Render
{
    public static function process(array $value): array
    {
        return self::apply($value, [
            'quotes',      // Первым делом заменяем кавычки, так как они могут содержать другие теги
            'accent',      // Удаляем акценты до обработки других тегов
            'textTts',     // Обрабатываем комбинированные теги {text},{tts} до индивидуальных text и tts
            'text',        // Затем обрабатываем отдельные текстовые вставки
            'tts',         // И отдельные TTS-вставки
            'rand',        // Обрабатываем случайные варианты
            'plural',      // Множественные формы чисел (может содержать другие теги)
            'pause',       // Паузы в TTS
            'effect',      // Эффекты для TTS
            'audio',       // Аудио вставки
            'space',       // Пробелы (после обработки текста)
            'br',          // Переносы строк
            'trim',        // Завершаем очисткой пробелов и переносов
        ]);
    }

    public static function pause(array $value): array
    {
        return [
            'text' => self::removePauseTags($value['text']),
            'tts' => self::replacePauseTagsWithSilence($value['tts']),
        ];
    }

    public static function text(array $value): array
    {
        return [
            'text' => self::replaceTextTags($value['text']),
            'tts' => self::removeTextTags($value['tts']),
        ];
    }

    public static function tts(array $value): array
    {
        return [
            'text' => self::removeTtsTags($value['text']),
            'tts' => self::replaceTtsTags($value['tts']),
        ];
    }

    public static function space(array $value): array
    {
        return [
            'text' => self::removeSpaceTags($value['text']),
            'tts' => self::replaceSpaceTags($value['tts']),
        ];
    }

    public static function br(array $value): array
    {
        return [
            'text' => self::processBrTags($value['text']),
            'tts' => self::removeBrTags($value['tts']),
        ];
    }

    public static function effect(array $value): array
    {
        return [
            'text' => self::removeEffectTags($value['text']),
            'tts' => self::processEffectTags($value['tts']),
        ];
    }

    public static function audio(array $value): array
    {
        return [
            'text' => self::removeAudioTags($value['text']),
            'tts' => self::processAudioTags($value['tts']),
        ];
    }

    public static function plural(array $value): array
    {
        return [
            'text' => self::processPluralTags($value['text']),
            'tts' => self::processPluralTags($value['tts']),
        ];
    }

    public static function rand(array $value): array
    {
        return [
            'text' => self::processRandTags($value['text']),
            'tts' => self::processRandTags($value['tts']),
        ];
    }

    public static function textTts(array $value): array
    {
        return [
            'text' => self::processTextTtsTextTags($value['text']),
            'tts' => self::processTextTtsTtsTags($value['tts']),
        ];
    }

    public static function accent(array $value): array
    {
        return [
            'text' => self::removeAccentMarks($value['text']),
            'tts' => $value['tts'],
        ];
    }

    public static function quotes(array $value): array
    {
        return [
            'text' => self::processQuotes($value['text']),
            'tts' => self::processQuotes($value['tts']),
        ];
    }

    public static function trim(array $value): array
    {
        return [
            'text' => self::whitespace($value['text']),
            'tts' => self::whitespace($value['tts']),
        ];
    }

    public static function variant(array|string $variants): string
    {
        if (is_string($variants)) {
            $variants = explode('|', $variants);
        }

        $variants = array_filter(array_map('trim', $variants));

        return $variants[array_rand($variants)];
    }

    public static function apply(array $value, array $methods): array
    {
        $callbacks = array_map(
            fn(string $method) => fn(array $value) => self::$method($value),
            $methods
        );

        return Pipeline::make($callbacks)->process($value);
    }

    private static function removePauseTags(string $text): string
    {
        return preg_replace('/{\s?pause:(.+?)}/iu', '', $text);
    }

    private static function replacePauseTagsWithSilence(string $tts): string
    {
        return preg_replace_callback('/{\s?pause:(.+?)}/iu', fn($match) =>
            'sil <[' . self::variant($match[1]) . ']>',
            $tts
        );
    }

    private static function replaceTextTags(string $text): string
    {
        return preg_replace_callback('/{\s?text:(.+?)}/iu',
            fn($match) => self::variant($match[1]),
            $text
        );
    }

    private static function removeTextTags(string $tts): string
    {
        return preg_replace('/{\s?text:(.+?)}/iu', '', $tts);
    }

    private static function removeTtsTags(string $text): string
    {
        return preg_replace('/{\s?tts:(.+?)}/iu', '', $text);
    }

    private static function replaceTtsTags(string $tts): string
    {
        return preg_replace_callback('/{\s?tts:(.+?)}/iu',
            fn($match) => self::variant($match[1]),
            $tts
        );
    }

    private static function removeSpaceTags(string $text): string
    {
        return preg_replace('/{\s?space\s?}/iu', '', $text);
    }

    private static function replaceSpaceTags(string $tts): string
    {
        return preg_replace('/{\s?space\s?}/iu', ' ', $tts);
    }

    private static function processBrTags(string $text): string
    {
        $text = preg_replace('/{\s?br\s?}/iu', "\n", $text);
        return preg_replace_callback('/{\s?br:(.+?)}/iu',
            fn($match) => str_repeat("\n", self::variant($match[1])),
            $text
        );
    }

    private static function removeBrTags(string $tts): string
    {
        $tts = preg_replace('/{\s?br\s?}/iu', '', $tts);
        return preg_replace('/{\s?br:(.+?)}/iu', '', $tts);
    }

    private static function removeEffectTags(string $text): string
    {
        $text = preg_replace('/{\s?effect:(.+?)}/iu', '', $text);
        return preg_replace('/{\s?\/\s?effect\s?}/iu', '', $text);
    }

    private static function processEffectTags(string $tts): string
    {
        $tts = preg_replace_callback('/{\s?effect:(.+?)}/iu',
            fn($match) => '<speaker effect="' . self::variant($match[1]) . '">',
            $tts
        );
        return preg_replace('/{\s?\/\s?effect\s?}/iu', '<speaker effect="-">', $tts);
    }

    private static function removeAudioTags(string $text): string
    {
        $text = preg_replace('/{\s?audio:(.+?)}/iu', '', $text);
        return preg_replace('/{\s?\/\s?audio\s?}/iu', '', $text);
    }

    private static function processAudioTags(string $tts): string
    {
        return preg_replace_callback('/{\s?audio:(.+?)}/iu', function ($match) {
            $variant = self::variant($match[1]);
            $variant = Assets::get($variant) ?? $variant;

            if (!str_ends_with($variant, '.opus')) {
                $variant .= '.opus';
            }

            return '<speaker audio="' . $variant . '">';
        }, $tts);
    }

    private static function processPluralTags(string $content): string
    {
        return preg_replace_callback('/{\s?(\d+):(.+?)}/iu', function ($match) {
            return plural(
                $match[1],
                array_map([self::class, 'variant'], array_filter(array_map('trim', explode(',', $match[2]))))
            );
        }, $content);
    }

    private static function processRandTags(string $content): string
    {
        return preg_replace_callback('/{\s?rand:(.+?)}/iu',
            fn($match) => self::variant($match[1]),
            $content
        );
    }

    private static function processTextTtsTextTags(string $text): string
    {
        return preg_replace_callback('/{\s?{(.+?)}\s?,\s?{(.+?)}\s?}/iu',
            fn($match) => $match[1],
            $text
        );
    }

    private static function processTextTtsTtsTags(string $tts): string
    {
        return preg_replace_callback('/{\s?{(.+?)}\s?,\s?{(.+?)}\s?}/iu',
            fn($match) => $match[2],
            $tts
        );
    }

    private static function removeAccentMarks(string $text): string
    {
        return preg_replace('/\+(?=[a-zA-Zа-яА-Яё])/iu', '', $text);
    }

    private static function processQuotes(string $content): string
    {
        $content = str_replace('<<<', '«', $content);
        return str_replace('>>>', '»', $content);
    }

    public static function whitespace(string $str): string
    {
        return trim(implode("\n", array_map('trim', explode("\n", preg_replace('/ {2,}/', ' ', $str)))));
    }
}