<?php

class Helper_GeoipUpdater {
    const GEOIP_URL = 'http://geolite.maxmind.com/download/geoip/database/GeoLiteCity_CSV/GeoLiteCity-latest.zip';


    public function run() {
        $tempFileName = tempnam(sys_get_temp_dir(), 'php-geolite-city-');

        $this->downloadUrlToFile(self::GEOIP_URL, $tempFileName);
        $unpackedFiles = $this->unpackGeoliteCity($tempFileName);
        unlink($tempFileName);

        $this->importFromFiles($unpackedFiles);
        foreach ($unpackedFiles as $fileName) {
            unlink($fileName);
        }
    }
    //--------------------------------------------------------------------------


    public function downloadUrlToFile($url, $fileName) {
        $fpTmp = fopen($fileName, 'wb');
        if (!$fpTmp) {
            throw new EExplainableError("Unable to open temporary file $fileName");
        }

        $fpRemote = fopen($url, 'rb');
        if (!$fpRemote) {
            throw new EExplainableError("Unable to open remote file $url");
        }

        while (!feof($fpRemote)) {
            $bytes = fread($fpRemote, 40960);
            fwrite($fpTmp, $bytes);
        }
        fclose($fpTmp);
    }
    //--------------------------------------------------------------------------


    public function importFromFiles(array $unpackedFiles) {
        $locker = new MySQLLocker(Config::$lockPrefix . __CLASS__  . '/import-geoip.lock', 0);

        $model = SingletonRegistry::getModelGeoipLocation();
        if (isset($unpackedFiles['GeoLiteCity-Location.csv']) && is_string($unpackedFiles['GeoLiteCity-Location.csv']) && is_readable($unpackedFiles['GeoLiteCity-Location.csv'])) {
            $model->readLocations($unpackedFiles['GeoLiteCity-Location.csv']);
        }

        if (isset($unpackedFiles['GeoLiteCity-Blocks.csv']) && is_string($unpackedFiles['GeoLiteCity-Blocks.csv']) && is_readable($unpackedFiles['GeoLiteCity-Blocks.csv'])) {
            $model->readBlocks($unpackedFiles['GeoLiteCity-Blocks.csv']);
        }
        $locker->release();
    }
    //--------------------------------------------------------------------------


    public function unpackGeoliteCity($zipFile) {
        $zip = new ZipArchive();
        if (!$zip->open($zipFile)) {
            return false;
        }

        $folder = sys_get_temp_dir();
        $files  = array();

        for($i = 0; $i < $zip->numFiles; $i++) {
            $entry = $zip->getNameIndex($i);
            if (substr($entry, -1) == '/') {
                continue;
            }
            $zipStream = $zip->getStream($entry);
            $targetFilename = tempnam(sys_get_temp_dir(), 'php-geolite-city-');
            $fp = fopen($targetFilename, 'wb');
            if ($fp) {
                while (!feof($zipStream)) {
                    $bytes = fread($zipStream, 40960);
                    if ($bytes === false) {
                        break;
                    }
                    fwrite($fp, $bytes);
                }
                fclose($fp);
                $files[basename($entry)] = $targetFilename;
            }
            fclose($zipStream);
        }
        return $files;
    }
    //--------------------------------------------------------------------------
}

