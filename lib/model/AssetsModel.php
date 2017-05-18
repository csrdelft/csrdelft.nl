<?php
/**
 * The AssetsModel file.
 */

namespace CsrDelft\model;

use CsrDelft\view\CompressedLayout;
use JShrink\Minifier as JsMin;
use tubalmartin\CssMin\Minifier as CssMin;
use Psr\Cache\CacheItemInterface;
use Stash\Driver\FileSystem;
use Stash\Pool;

/**
 * Class AssetsModel.
 *
 * @author Gerben Oolbekkink <gerben@bunq.com>
 * @since 20170514 Initial creation.
 */
class AssetsModel
{
    /**
     * url(<relatieve url>)
     *
     * <relatieve url> begint niet met 'data', 'http', of '/'
     */
    const CSS_REGEX_URL = '/url\([\'"]{1}(?!\/)(?!data)(?!http)(.*?)[\'"]{1}\)/';

    private $minify;
    private $gzip;

    public function __construct(
        $minify,
        $gzip
    ) {
        $this->minify = $minify;
        $this->gzip = $gzip;
    }

    public function createJavascript(CacheItemInterface $item)
    {
        $key = explode('/', $item->getKey());

        $extension = $key[0];
        $layout = $key[1];
        $module = $key[2];

        $modules = CompressedLayout::getUserModules($module, $extension);

        $files = $this->parseConfig($layout, $extension);

        ob_start();

        // load files
        foreach ($modules as $mod) {
            if (!key_exists($mod, $files)) continue;
            foreach ($files[$mod] as $file) {
                if (!file_exists(ASSETS_PATH . $file)) continue;
                $filename = str_replace(ASSETS_PATH, '', $file);

                echo "/* Begin van " . $filename . " */\n";
                if (!DEBUG) echo "try {\n";
                echo file_get_contents(ASSETS_PATH . $file) . PHP_EOL;
                if (!DEBUG) echo "} catch (e) {\n   logError(e, '" . $file . "');\n}\n";
                echo "/* Eind van " . $filename . " */\n\n";
            }
        }

        $js = ob_get_clean();

        if ($this->minify) {
            // Tijdelijke fix voor #57 in JShrink
            $js = preg_replace('/\*\/\/\*/', "*/\n/*", $js);
            $js = JsMin::minify($js);
        }

        return $js;
    }

    public function createCss(CacheItemInterface $item)
    {
        $driver = new FileSystem(['path' => DATA_PATH . 'less']);
        $pool = new Pool($driver);

        $key = explode('/', $item->getKey());

        $extension = $key[0];
        $layout = $key[1];
        $module = $key[2];

        $modules = CompressedLayout::getUserModules($module, $extension);


        $files = $this->parseConfig($layout, $extension);

        // start output buffering

        ob_start();

        // build the stylesheet
        foreach ($modules as $mod) {
            if (!key_exists($mod, $files)) continue;
            // load files
            foreach ($files[$mod] as $file) {
                echo "/* file:$file */\n";
                $item = $pool->getItem($file);

                if ($item->isHit()) {
                    $css = $item->get();
                    $prefix = strtok($css, PHP_EOL);

                    $prefix = substr($prefix, 2, strlen($prefix) - 1);

                    $includes = explode("|", $prefix);
                    $timestamp = array_pop($includes);

                    array_unshift($includes, ASSETS_PATH . $file);

                    if ($this->invalidateCache($includes, $timestamp)) {
                        echo "/* invalidate cache */" .PHP_EOL;
                        $pool->deleteItem($file);
                        $item = $pool->getItem($file);
                    }

                    if ($item->isHit()) {
                        //echo substr($css, strpos($css, PHP_EOL) + 1);
                        echo $css;
                        continue;
                    }
                }

                // Prefix timestamp
                if (strstr($file, '.css') === '.css') {
                    $cssContents = sprintf("/*%s*/" . PHP_EOL, filemtime(ASSETS_PATH . $file));
                    $cssContents .= file_get_contents(ASSETS_PATH . $file);
                    $dir = dirname('/assets/' . $file);
                    $cssContents = preg_replace(self::CSS_REGEX_URL, "url($dir/$1)", $cssContents);
                } else {
                    $less = new \Less_Parser();
                    $less->parse(
                        file_get_contents(ASSETS_PATH . $file),
                        ASSETS_PATH . $file
                    );
                    $parsedLess = $less->getCss();
                    $references = $less->allParsedFiles();
                    $cssContents = "/*";
                    $lastModified = filemtime(ASSETS_PATH . $file);
                    foreach ($references as $reference) {
                        $cssContents .= sprintf("%s|", $reference);
                        $modified = filemtime($reference);
                        if ($modified > $lastModified) {
                            $lastModified = $modified;
                        }
                    }
                    $cssContents .= sprintf("%s*/" . PHP_EOL, $lastModified);
                    $cssContents .= $parsedLess;
                }

                $pool->save($item->set($cssContents));
                echo $cssContents;

            }
        }

        $css = ob_get_clean();

        if ($this->minify) {
            $css = (new CssMin())->run($css);
        }

        return $css;
    }

    public static function parseConfig(
        $layout,
        $extension
    ) {
        if ($extension == 'js') {
            $ininame = 'script';
            $sectionname = 'scripts';
        } else {
            $ininame = 'style';
            $sectionname = 'stylesheets';
        }

        $includes = array(); // mode, file => base
        // load style.ini/script.ini
        $incbase = ASSETS_PATH;
        $ini = $incbase . $layout . '/' . $ininame . '.ini';
        if (file_exists($ini)) {
            $data = parse_ini_file($ini, true);

            // stylesheets
            if (is_array($data[$sectionname]))
                foreach ($data[$sectionname] as $module => $files) {
                    foreach ($files as $file) {
                        $includes[$module][] = $file;
                    }
                }
        }

        return $includes;
    }

    /**
     * @param $includes
     * @param $timestamp
     *
     * @return mixed
     */
    private function invalidateCache(
        $includes,
        $timestamp
    ) {
        foreach ($includes as $includedLessFile) {
            echo "/* $includedLessFile */";
            if ($timestamp < filemtime($includedLessFile)) {
                return true;
            }
        }
        return false;
    }
}
