<?php

namespace CsrDelft\model;

use DateInterval;
use JShrink\Minifier as JsMin;
use Psr\Cache\CacheItemInterface;
use Stash\Driver\FileSystem as FileSystemDriver;
use Stash\Interfaces\ItemInterface;
use Stash\Pool as CachePool;
use Symfony\Component\Config\Definition\Exception\Exception;
use tubalmartin\CssMin\Minifier as CssMin;
use function CsrDelft\endsWith;

/**
 * Class AssetsModel.
 *
 * @author Gerben Oolbekkink <gerben@bunq.com>
 */
class AssetsModel {
    /**
     * url(<relatieve url>)
     *
     * <relatieve url> begint niet met 'data', 'http', of '/'
     */
    const CSS_REGEX_URL = '/url\([\'"]{1}(?!\/)(?!data)(?!http)(.*?)[\'"]{1}\)/';

    private $minify;
    private $cachePool;

    public function __construct($minify) {
        $this->minify = $minify;

        $driver = new FileSystemDriver(['path' => DATA_PATH . 'assets/']);
        $this->cachePool = new CachePool($driver);
    }

    /**
     * Haal item op uit cache.
     *
     * Cache ziet er als volgt uit:
     *
     * /$extension/$layout/$module, bijv. /js/layout-owee/general
     *
     * De cache is op verschillende niveaus te clearen, alle js van een layout clearen doe je als volgt:
     *
     *   $this->cachePool->deleteItem('/js/layout-owee');
     *
     * De cache verwijdert dan ook items die specifieker zijn.
     *
     * @param $layout
     * @param $module
     * @param $extension
     *
     * @return ItemInterface
     */
    public function getItem($layout, $module, $extension) {
        return $this->cachePool->getItem(sprintf('/%s/%s/%s', $extension, $layout, $module));
    }

    /**
     * @param CacheItemInterface $item
     *
     * @return bool
     */
    public function save(CacheItemInterface $item) {
        $item->expiresAfter(DateInterval::createFromDateString('1 year'));
        return $this->cachePool->save($item);
    }

    /**
     * @param $layout
     * @param $module
     * @param $extension
     *
     * @return string
     */
    public function checkCache($layout, $module, $extension) {
        $item = $this->getItem($layout, $module, $extension);
        return dechex($item->getCreation()->format('u'));
    }

    public function createJavascript(CacheItemInterface $item) {
        list($extension, $layout, $module) = explode('/', $item->getKey());

        $modules = $this->getUserModules($module, $extension);

        $files = $this->parseConfig($layout, $extension);

        ob_start();

        // load files
        foreach ($modules as $mod) {
            if (!key_exists($mod, $files)) {
                // Geen js voor deze mod
                continue;
            }
            foreach ($files[$mod] as $file) {
                if (!file_exists(ASSETS_PATH . $file)) {
                    throw new Exception('Bestand niet gevonden: ' . $file);
                }

                $filename = str_replace(ASSETS_PATH, '', $file);

                echo "/* Begin van " . $filename . " */\n";
                if (DEBUG) {
                    echo 'try {' . PHP_EOL;
                }

                echo file_get_contents(ASSETS_PATH . $file) . PHP_EOL;

                if (DEBUG) {
                    echo '} catch (e) {' . PHP_EOL . 'logError(e, "' . $file . '");' . PHP_EOL . '}' . PHP_EOL;
                }

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

    public function createCss(CacheItemInterface $item) {
        $driver = new FileSystemDriver(['path' => DATA_PATH . 'less/']);
        $pool = new CachePool($driver);

        list($extension, $layout, $module) = explode('/', $item->getKey());

        $modules = $this->getUserModules($module, $extension);

        $files = $this->parseConfig($layout, $extension);

        // start output buffering
        ob_start();

        // build the stylesheet
        foreach ($modules as $mod) {
            if (!key_exists($mod, $files)) {
                // Geen css voor deze mod
                continue;
            }
            // load files
            foreach ($files[$mod] as $file) {
                $item = $pool->getItem($file);

                if ($item->isHit()) {
                    $item = $this->invalidateLessCache($item, $pool);
                }

                if ($item->isHit()) {
                    echo $item->get();
                    continue;
                } else {
                    if (endsWith($file, '.css')) {
                        $cssContents = $this->parseCss($file);
                    } else {
                        $cssContents = $this->parseLess($file);
                    }

                    $pool->save($item->set($cssContents));
                    echo $cssContents;
                }
            }
        }

        $css = ob_get_clean();

        if ($this->minify) {
            $css = (new CssMin())->run($css);
        }

        return $css;
    }

    public static function parseConfig($layout, $extension) {
        if ($extension == 'js') {
            $ininame = 'script';
            $sectionname = 'scripts';
        } else {
            $ininame = 'style';
            $sectionname = 'stylesheets';
        }

        $includes = array(); // mode, file => base
        // load style.ini/script.ini
        $ini = ASSETS_PATH . $layout . DIRECTORY_SEPARATOR . $ininame . '.ini';
        if (file_exists($ini)) {
            $data = parse_ini_file($ini, true);
            if (is_array($data[$sectionname])) {
                foreach ($data[$sectionname] as $module => $files) {
                    foreach ($files as $file) {
                        $includes[$module][] = $file;
                    }
                }
            }
        }

        return $includes;
    }

    /**
     * Is het tijd de cache te refreshen?
     *
     * @param $includes
     * @param $timestamp
     *
     * @return mixed
     */
    private function invalidateCache($includes, $timestamp) {
        foreach ($includes as $includedLessFile) {
            if ($timestamp < filemtime($includedLessFile)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Geeft een array met gevraagde modules, afhankelijk van lidinstellingen
     * [elke module bestaat uit een set css- of js-bestanden]
     *
     * @param $module
     * @param $extension
     *
     * @return array
     */
    public function getUserModules($module, $extension) {
        $modules = array();

        if ($module == 'front-page') {
            $modules[] = 'general';
        } elseif ($module == 'general') {
            // de algemene module gevraagd, ook worden modules gekoppeld aan instellingen opgezocht
            $modules[] = 'general';
            $modules[] = 'formulier';
            $modules[] = 'datatable';
            $modules[] = 'grafiek';

            if ($extension == 'css') {
                //voeg modules toe afhankelijk van instelling
                $modules[] = LidInstellingenModel::get('layout', 'opmaak');
                if (LidInstellingenModel::get('layout', 'toegankelijk') == 'bredere letters') {
                    $modules[] = 'bredeletters';
                }
                if (LidInstellingenModel::get('layout', 'fx') == 'sneeuw') {
                    $modules[] = 'fxsnow';
                } elseif (LidInstellingenModel::get('layout', 'fx') == 'space') {
                    $modules[] = 'fxspace';
                }
            } elseif ($extension == 'js') {
                if (LidInstellingenModel::get('layout', 'fx') == 'wolken') {
                    $modules[] = 'fxclouds';
                }
            }

            if (LidInstellingenModel::get('layout', 'minion') == 'ja') {
                $modules[] = 'minion';
            }
            if (LidInstellingenModel::get('layout', 'fx') == 'onontdekt') {
                $modules[] = 'fxonontdekt';
            } elseif (LidInstellingenModel::get('layout', 'fx') == 'civisaldo') {
                $modules[] = 'fxcivisaldo';
            }
        } else {
            // een niet-algemene module gevraagd
            $modules[] = $module;
        }

        return $modules;
    }

    /**
     * @param $file
     *
     * @return mixed|string
     */
    protected function parseCss($file) {
        $cssContents = sprintf("/*%s*/" . PHP_EOL, filemtime(ASSETS_PATH . $file));
        $cssContents .= file_get_contents(ASSETS_PATH . $file);
        $dir = dirname('/assets/' . $file);
        $cssContents = preg_replace(self::CSS_REGEX_URL, "url($dir/$1)", $cssContents);
        return $cssContents;
    }

    /**
     * @param $file
     *
     * @return string
     */
    protected function parseLess($file) {
        $less = new \Less_Parser();
        $less->parseFile(
            ASSETS_PATH . $file,
            dirname(ASSETS_DIR . DIRECTORY_SEPARATOR . $file)
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
        return $cssContents;
    }

    /**
     * @param CacheItemInterface $item
     * @param CachePool $pool
     *
     * @return CacheItemInterface
     */
    protected function invalidateLessCache(CacheItemInterface $item, CachePool $pool) {
        $css = $item->get();
        $prefix = strtok($css, PHP_EOL);

        $prefix = substr($prefix, 2, strlen($prefix) - 1);

        $includes = explode("|", $prefix);
        $timestamp = array_pop($includes);

        array_unshift($includes, ASSETS_PATH . $item->getKey());

        if ($this->invalidateCache($includes, $timestamp)) {
            $pool->deleteItem($item->getKey());
            $item = $pool->getItem($item->getKey());
        }
        return $item;
    }
}
