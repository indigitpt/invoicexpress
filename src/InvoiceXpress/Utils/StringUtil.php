<?php


namespace InvoiceXpress\Utils;


use Illuminate\Support\Str;

class StringUtil
{
    /**
     * Generate a list for PHP class with
     *
     * @param $data
     * @param string $prefix
     * @return string
     */
    public static function lines_to_constants($data, $prefix = ''): string
    {
        $array = self::lines_to_array($data);

        # Build the constants
        $file = '<?php';
        $file .= PHP_EOL;
        $file .= PHP_EOL;

        # --> Build the static constants first
        $filtered = [];
        foreach ($array as $key => $value) {

            $valueFixed = str_replace('\t', '', $value); # Remove tabs
            $valueFixed = preg_replace("/[^A-Za-z0-9 ]/", '', $valueFixed); # Remove non-characters
            $valueFixed = preg_replace('/[ ]{2,}|[\t]/', ' ', trim($valueFixed)); # Remove multiple spaces
            $valueFixed = preg_replace('/\s+/', '', $valueFixed); # Remove Once again spaces
            $valueFixed = str_replace(' ', '_', $valueFixed); # Convert spaces into underscores
            $valueFixed = Str::ascii($valueFixed); # UTF8 to ASCII Convertor
            # Skip here so strings like "UK" are snake cased.
            if (strlen($valueFixed) > 2) {
                $valueFixed = Str::snake($valueFixed); # Snake Case
            }
            $valueFixed = strtoupper($valueFixed);
            $valueFixed = $prefix . $valueFixed;

            $file .= "public const " . $valueFixed . " = '" . $value . "';";
            $file .= PHP_EOL;
            $filtered[$valueFixed] = $value;
        }

        # -->  Build VALUES pointing to KEYS
        $file .= PHP_EOL;
        $file .= PHP_EOL;
        $file .= PHP_EOL;
        $file .= 'public const ALL_KEYS = [';
        $file .= PHP_EOL;
        foreach ($filtered as $key => $value) {
            $file .= "self::" . strtoupper($key) . " => '" . $value . "',";
            $file .= PHP_EOL;
        }
        $file .= PHP_EOL;
        $file .= '];';
        return $file;
    }

    /** Convert Form Lines to a Clean Array
     *
     * @param $data
     * @param bool $unique
     * @return array
     */
    public static function lines_to_array($data, $unique = false)
    {
        if (is_array($data)) {
            if ($unique) {
                return array_unique(array_filter($data));
            }
            return array_filter($data);
        }

        $attempt = preg_split('/$\R?^/m', $data);

        $filtered = [];
        foreach ($attempt as $line) {
            $filtered[] = str_replace(["\r", "\n"], '', $line);
        }

        if ($unique) {
            return array_unique(array_filter($filtered));
        }
        return array_filter($filtered);
    }
}