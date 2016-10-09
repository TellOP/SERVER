<?php
/* Copyright © 2016 University of Murcia
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
namespace TellOP;

use/** @noinspection PhpUndefinedClassInspection */
    /** @noinspection PhpUndefinedNamespaceInspection */
    Composer\Script\Event;
use/** @noinspection PhpUndefinedClassInspection */
    /** @noinspection PhpUndefinedNamespaceInspection */
    Composer\Installer\PackageEvent;

/**
 * Class containing Composer hooks.
 * @package TellOP
 */
class Installer {
    /**
     * Create a directory if it does not exist.
     * @param string $dirname Directory name.
     * @return bool TRUE if the directory already exists and is writable, or if
     * did not exist and was created; FALSE otherwise.
     */
    private static function createDir($dirname) {
        if (file_exists($dirname) === FALSE) {
            return mkdir($dirname, 0755);
        } else {
            return is_writable($dirname);
        }
    }

    /**
     * Copies an asset from the vendor directory to its destination directory.
     * @param string $orig Path to the original asset.
     * @param string $destdir Destination directory.
     * @param string $destname Destination filename.
     * @return bool TRUE if the asset was copied successfully, FALSE otherwise.
     */
    private static function copyAsset($orig, $destdir, $destname) {
        if (self::createDir($destdir) === FALSE) {
            fwrite(STDERR, "Unable to create destination directory: $destdir");
            return FALSE;
        }
        if (copy($orig, $destdir . '/' . $destname) === FALSE) {
            fwrite(STDERR, "Unable to copy the asset: $destname");
            return FALSE;
        }
        return TRUE;
    }

    /**
     * Hook run after an update command - copies the Bootstrap and JQuery
     * assets to the assets/ directory.
     * @param Event $event Composer event.
     * @noinspection PhpUnusedParameterInspection
     * @noinspection PhpUndefinedClassInspection
     */
    public static function postUpdate(Event $event) {
        self::copyAsset('vendor/components/jquery/jquery.min.js', 'js',
            'jquery.min.js');
        self::copyAsset('vendor/twbs/bootstrap/dist/css/bootstrap-theme.min.css',
            'css', 'bootstrap-theme.min.css');
        self::copyAsset('vendor/twbs/bootstrap/dist/css/bootstrap.min.css', 'css',
            'bootstrap.min.css');
        self::copyAsset('vendor/twbs/bootstrap/dist/fonts/glyphicons-halflings-regular.ttf',
            'fonts', 'glyphicons-halflings-regular.ttf');
        self::copyAsset('vendor/twbs/bootstrap/dist/fonts/glyphicons-halflings-regular.woff',
            'fonts', 'glyphicons-halflings-regular.woff');
        self::copyAsset('vendor/twbs/bootstrap/dist/fonts/glyphicons-halflings-regular.svg',
            'fonts', 'glyphicons-halflings-regular.svg');
        self::copyAsset('vendor/twbs/bootstrap/dist/fonts/glyphicons-halflings-regular.eot',
            'fonts', 'glyphicons-halflings-regular.eot');
        self::copyAsset('vendor/twbs/bootstrap/dist/fonts/glyphicons-halflings-regular.woff2',
            'fonts', 'glyphicons-halflings-regular.woff2');
        self::copyAsset('vendor/twbs/bootstrap/dist/js/bootstrap.min.js', 'js',
            'bootstrap.min.js');
        self::copyAsset('vendor/1000hz/bootstrap-validator/dist/validator.min.js',
            'js', 'validator.min.js');
        self::copyAsset('vendor/ryanseddon/H5F/h5f.min.js', 'js', 'h5f.min.js');
        self::copyAsset('vendor/eligrey/l10n.js/l10n.min.js', 'js', 'l10n.min.js');
    }
}
