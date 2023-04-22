<?php

namespace Sakadda\Cli\Concerns;

trait RenderAscii
{
    /**
     * Sakadda name in Ascii Art
     */
    public function renderPowergridAscii(): void
    {
        $string = <<<'EOT'

            .d8888.  .d8b.  db   dD  .d8b.  d8888b. d8888b.  .d8b.
            88'  YP d8' `8b 88 ,8P' d8' `8b 88  `8D 88  `8D d8' `8b
            `8bo.   88ooo88 88,8P   88ooo88 88   88 88   88 88ooo88
              `Y8b. 88~~~88 88`8b   88~~~88 88   88 88   88 88~~~88
            db   8D 88   88 88 `88. 88   88 88  .8D 88  .8D 88   88
            `8888Y' YP   YP YP   YD YP   YP Y8888D' Y8888D' YP   YP


        EOT;

        $array = preg_split("/\r\n|\n|\r/", $string);

        // $this->line($string);

        $consoleColor = new ConsoleColor();

        echo "\n";

        if ($consoleColor->are256ColorsSupported()) {
            $j = 255;
            for ($i = 0; $i <= count($array) - 1; $i++) {
                echo $consoleColor->apply("color_$j", str_pad($array[$i], 6, ' ', STR_PAD_BOTH));
                echo "\n";

                $j = $j - 3;
            }
        }
    }
}

class ConsoleColor
{
    public const FOREGROUND = 38;

    public const BACKGROUND = 48;

    public const COLOR256_REGEXP = '~^(bg_)?color_([0-9]{1,3})$~';

    public const RESET_STYLE = 0;

    /** @var bool */
    private $forceStyle = false;

    /** @var array */
    private $styles = [
        'none' => null,
        'bold' => '1',
        'dark' => '2',
        'italic' => '3',
        'underline' => '4',
        'blink' => '5',
        'reverse' => '7',
        'concealed' => '8',

        'default' => '39',
        'black' => '30',
        'red' => '31',
        'green' => '32',
        'yellow' => '33',
        'blue' => '34',
        'magenta' => '35',
        'cyan' => '36',
        'light_gray' => '37',

        'dark_gray' => '90',
        'light_red' => '91',
        'light_green' => '92',
        'light_yellow' => '93',
        'light_blue' => '94',
        'light_magenta' => '95',
        'light_cyan' => '96',
        'white' => '97',

        'bg_default' => '49',
        'bg_black' => '40',
        'bg_red' => '41',
        'bg_green' => '42',
        'bg_yellow' => '43',
        'bg_blue' => '44',
        'bg_magenta' => '45',
        'bg_cyan' => '46',
        'bg_light_gray' => '47',

        'bg_dark_gray' => '100',
        'bg_light_red' => '101',
        'bg_light_green' => '102',
        'bg_light_yellow' => '103',
        'bg_light_blue' => '104',
        'bg_light_magenta' => '105',
        'bg_light_cyan' => '106',
        'bg_white' => '107',
    ];

    /** @var array */
    private $themes = [];

    /**
     * @param  string|array  $style
     * @param  string  $text
     * @return string
     *
     * @throws InvalidStyleException
     * @throws \InvalidArgumentException
     */
    public function apply($style, $text)
    {
        if (! $this->isStyleForced() && ! $this->isSupported()) {
            return $text;
        }

        if (is_string($style)) {
            $style = [$style];
        }
        if (! is_array($style)) {
            throw new \InvalidArgumentException('Style must be string or array.');
        }

        $sequences = [];

        foreach ($style as $s) {
            if (isset($this->themes[$s])) {
                $sequences = array_merge($sequences, $this->themeSequence($s));
            } elseif ($this->isValidStyle($s)) {
                $sequences[] = $this->styleSequence($s);
            } else {
                // throw new InvalidStyleException($s);
            }
        }

        $sequences = array_filter($sequences, function ($val) {
            return $val !== null;
        });

        if (empty($sequences)) {
            return $text;
        }

        return $this->escSequence(implode(';', $sequences)).$text.$this->escSequence(self::RESET_STYLE);
    }

    /**
     * @param  bool  $forceStyle
     */
    public function setForceStyle($forceStyle)
    {
        $this->forceStyle = (bool) $forceStyle;
    }

    /**
     * @return bool
     */
    public function isStyleForced()
    {
        return $this->forceStyle;
    }

    /**
     * @throws InvalidStyleException
     * @throws \InvalidArgumentException
     */
    public function setThemes(array $themes)
    {
        $this->themes = [];
        foreach ($themes as $name => $styles) {
            $this->addTheme($name, $styles);
        }
    }

    /**
     * @param  string  $name
     * @param  array|string  $styles
     *
     * @throws InvalidStyleException
     * @throws \InvalidArgumentException
     */
    public function addTheme($name, $styles)
    {
        if (is_string($styles)) {
            $styles = [$styles];
        }
        if (! is_array($styles)) {
            throw new \InvalidArgumentException('Style must be string or array.');
        }

        foreach ($styles as $style) {
            if (! $this->isValidStyle($style)) {
                // throw new InvalidStyleException($style);
            }
        }

        $this->themes[$name] = $styles;
    }

    /**
     * @return array
     */
    public function getThemes()
    {
        return $this->themes;
    }

    /**
     * @param  string  $name
     * @return bool
     */
    public function hasTheme($name)
    {
        return isset($this->themes[$name]);
    }

    /**
     * @param  string  $name
     */
    public function removeTheme($name)
    {
        unset($this->themes[$name]);
    }

    /**
     * @return bool
     */
    public function isSupported()
    {
        if (DIRECTORY_SEPARATOR === '\\') {
            if (function_exists('sapi_windows_vt100_support') && @sapi_windows_vt100_support(STDOUT)) {
                return true;
            } elseif (getenv('ANSICON') !== false || getenv('ConEmuANSI') === 'ON') {
                return true;
            }

            return false;
        } else {
            return function_exists('posix_isatty') && @posix_isatty(STDOUT);
        }
    }

    /**
     * @return bool
     */
    public function are256ColorsSupported()
    {
        if (DIRECTORY_SEPARATOR === '\\') {
            return function_exists('sapi_windows_vt100_support') && @sapi_windows_vt100_support(STDOUT);
        } else {
            return strpos(getenv('TERM'), '256color') !== false;
        }
    }

    /**
     * @return array
     */
    public function getPossibleStyles()
    {
        return array_keys($this->styles);
    }

    /**
     * @param  string  $name
     * @return string[]
     */
    private function themeSequence($name)
    {
        $sequences = [];
        foreach ($this->themes[$name] as $style) {
            $sequences[] = $this->styleSequence($style);
        }

        return $sequences;
    }

    /**
     * @param  string  $style
     * @return string
     */
    private function styleSequence($style)
    {
        if (array_key_exists($style, $this->styles)) {
            return $this->styles[$style];
        }

        if (! $this->are256ColorsSupported()) {
            return '';
        }

        preg_match(self::COLOR256_REGEXP, $style, $matches);

        $type = $matches[1] === 'bg_' ? self::BACKGROUND : self::FOREGROUND;
        $value = $matches[2];

        return "$type;5;$value";
    }

    /**
     * @param  string  $style
     * @return bool
     */
    private function isValidStyle($style)
    {
        return array_key_exists($style, $this->styles) || preg_match(self::COLOR256_REGEXP, $style);
    }

    /**
     * @param  string|int  $value
     * @return string
     */
    private function escSequence($value)
    {
        return "\033[{$value}m";
    }
}
