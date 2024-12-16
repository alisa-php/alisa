<?php

namespace Alisa\Support;

use Alisa\Stores\Assets;

use function Alisa\Support\Helpers\plural;

class Render
{
    public static function pause(array $value): array
    {
        return [
            'text' => preg_replace('/{\s?pause:(.+?)}/iu', '', $value['text']),
            'tts' => preg_replace_callback('/{\s?pause:(.+?)}/iu', function ($match) {
                return 'sil <[' . self::variant($match[1]) . ']>';
            }, $value['tts']),
        ];
    }

    public static function text(array $value): array
    {
        return [
            'text' => preg_replace_callback('/{\s?text:(.+?)}/iu', function ($match) {
                return self::variant($match[1]);
            }, $value['text']),
            'tts' => preg_replace('/{\s?text:(.+?)}/iu', '', $value['tts']),
        ];
    }

    public static function tts(array $value): array
    {
        return [
            'text' => preg_replace('/{\s?tts:(.+?)}/iu', '', $value['text']),
            'tts' => preg_replace_callback('/{\s?tts:(.+?)}/iu', function ($match) {
                return self::variant($match[1]);
            }, $value['tts']),
        ];
    }

    public static function space(array $value): array
    {
        return [
            'text' => preg_replace('/{\s?space\s?}/iu', '', $value['text']),
            'tts' => preg_replace('/{\s?space\s?}/iu', ' ', $value['tts']),
        ];
    }

    public static function br(array $value): array
    {
        return [
            'text' => preg_replace_callback('/{\s?br:(.+?)}/iu', function ($match) {
                return str_repeat("\n", self::variant($match[1]));
            }, preg_replace('/{\s?br\s?}/iu', "\n", $value['text'])),
            'tts' => preg_replace('/{\s?br:(.+?)}/iu', '', preg_replace('/{\s?br\s?}/iu', '', $value['tts'])),
        ];
    }

    public static function effect(array $value): array
    {
        return [
            'text' => preg_replace('/{\s?effect:(.+?)}/iu', '', preg_replace('/{\s?\/\s?effect\s?}/iu', '', $value['text'])),
            'tts' => preg_replace_callback('/{\s?effect:(.+?)}/iu', function ($match) {
                return '<speaker effect="' . self::variant($match[1]) . '">';
            }, preg_replace('/{\s?\/\s?effect\s?}/iu', '<speaker effect="-">', $value['tts'])),
        ];
    }

    public static function audio(array $value): array
    {
        return [
            'text' => preg_replace('/{\s?audio:(.+?)}/iu', '', preg_replace('/{\s?\/\s?audio\s?}/iu', '', $value['text'])),
            'tts' => preg_replace_callback('/{\s?audio:(.+?)}/iu', function ($match) {
                $variant = self::variant($match[1]);
                $variant = Assets::get($variant) ?? $variant;

                if (!str_ends_with($variant, '.opus')) {
                    $variant .= '.opus';
                }

                return '<speaker audio="' . $variant . '">';
            }, $value['tts']),
        ];
    }

    public static function plural(array $value): array
    {
        $pluralize = function (string $text) {
            return preg_replace_callback('/{\s?(\d+):(.+?)}/iu', function ($match) {
                return plural(
                    $match[1],
                    array_map([self::class, 'variant'], array_filter(array_map('trim', explode(',', $match[2]))))
                );
            }, $text);
        };

        return [
            'text' => $pluralize($value['text']),
            'tts' => $pluralize($value['tts']),
        ];
    }

    public static function rand(array $value): array
    {
        $randomize = function (string $text) {
            return preg_replace_callback('/{\s?rand:(.+?)}/iu', function ($match) {
                return self::variant($match[1]);
            }, $text);
        };

        return [
            'text' => $randomize($value['text']),
            'tts' => $randomize($value['tts']),
        ];
    }

    public static function textTts(array $value): array
    {
        return [
            'text' => preg_replace_callback('/{\s?{(.+?)}\s?,\s?{(.+?)}\s?}/iu', function ($match) {
                return $match[1];
            }, $value['text']),
            'tts' => preg_replace_callback('/{\s?{(.+?)}\s?,\s?{(.+?)}\s?}/iu', function ($match) {
                return $match[2];
            }, $value['tts']),
        ];
    }

    public static function accent(array $value): array
    {
        return [
            'text' => preg_replace('/\+(?=[a-zA-Zа-яА-Яё])/iu', '', $value['text']),
            'tts' => $value['tts'],
        ];
    }

    public static function trim(array $value): array
    {
        return [
            'text' => self::trimWhitespace($value['text']),
            'tts' => self::trimWhitespace($value['tts']),
        ];
    }

    public static function quotes(array $value): array
    {
        $value['text'] = str_replace('<<<', '«', $value['text']);
        $value['text'] = str_replace('>>>', '»', $value['text']);

        $value['tts'] = str_replace('<<<', '«', $value['tts']);
        $value['tts'] = str_replace('>>>', '»', $value['tts']);

        return [
            'text' => $value['text'],
            'tts' => $value['tts'],
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

    public static function markup(array $value, array $methods): array
    {
        foreach ($methods as $method) {
            $value = self::$method($value);
        }

        return $value;
    }

    public static function process(array $value): array
    {
        $methods = [
            'pause', 'text', 'tts', 'space', 'br', 'audio', 'effect',
            'plural', 'rand', 'textTts', 'accent', 'quotes', 'trim',
        ];

        return self::markup($value, $methods);
    }

    public static function trimWhitespace(string $str): string
    {
        return trim(implode("\n", array_map('trim', explode("\n", preg_replace('/ {2,}/', ' ', $str)))));
    }
}