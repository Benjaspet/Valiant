<?php

declare(strict_types=1);

namespace Valiant\Generator;

use Valiant\Core;

class Generator {

    private $plugin;

    public function __construct(Core $plugin) {
        $this->plugin = $plugin;
    }

    public function generateMap(string $map, string $id, string $dst, string $src = null): void {
        if ($src === null) $src = $this->plugin->getServer()->getDataPath() . "/plugin_data/Valiant/maps/" . $map;
        if (!is_dir($src)) return;

        $dir = opendir($src);
        @mkdir($dst);

        foreach (scandir($src) as $file) {
            if (( $file != '.' ) && ( $file != '..' )) {
                if (is_dir($src . '/' . $file) ) {
                    $this->generateMap($map, $id,$dst . '/' . $file, $src . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }

    public function deleteMap(string $level): void {
        if (!is_dir($this->plugin->getServer()->getDataPath() . "/worlds/" . $level)) return;
        if ($this->plugin->getServer()->isLevelLoaded($level)) {
            $this->plugin->getServer()->unloadLevel($this->plugin->getServer()->getLevelByName($level));
        }
        $this->removeDir($this->plugin->getServer()->getDataPath() . "/worlds/" . $level);
    }

    public function removeDir(string $path): void {
        if(basename($path) == "." || basename($path) == "..") {
            return;
        }

        foreach (scandir($path) as $item) {
            if($item != "." || $item != "..") {
                if(is_dir($path . "/" . $item)) {
                    $this->removeDir($path . "/" . $item);
                }
                if(is_file($path . "/" . $item)) {
                    unlink($path . "/" . $item);
                }
            }
        }
        rmdir($path);
    }
}
