<?php

namespace App\Helpers;

class UnitKerjaHelper
{
    private static $refMap = null; // full name -> code
    private static $nameMap = null; // code -> full name
    private static $relationMap = null; // eselon1_code -> array of eselon2_codes

    private static function init()
    {
        if (self::$refMap !== null) {
            return;
        }

        self::$refMap = [];
        self::$nameMap = [];
        self::$relationMap = [];

        // Load unit_kerja_ref.csv
        $refPath = base_path('../unit_kerja_ref.csv');
        if (!file_exists($refPath)) {
            $refPath = 'E:\\Anti Gravity\\ATLAS Prototype\\unit_kerja_ref.csv';
        }

        if (file_exists($refPath)) {
            if (($fh = fopen($refPath, 'r')) !== false) {
                $header = fgetcsv($fh);
                while (($line = fgetcsv($fh)) !== false) {
                    if (count($line) >= 2) {
                        $code = trim($line[0]);
                        $name = trim($line[1]);
                        self::$nameMap[$code] = $name;
                        self::$refMap[strtolower($name)] = $code;
                    }
                }
                fclose($fh);
            }
        }

        // Load unit_kerja_relasi.csv
        $relPath = base_path('../unit_kerja_relasi.csv');
        if (!file_exists($relPath)) {
            $relPath = 'E:\\Anti Gravity\\ATLAS Prototype\\unit_kerja_relasi.csv';
        }

        if (file_exists($relPath)) {
            if (($fh = fopen($relPath, 'r')) !== false) {
                $header = fgetcsv($fh);
                while (($line = fgetcsv($fh)) !== false) {
                    if (count($line) >= 2) {
                        $eselon2 = trim($line[0]);
                        $eselon1 = trim($line[1]);
                        self::$relationMap[$eselon1][] = $eselon2;
                    }
                }
                fclose($fh);
            }
        }
    }

    /**
     * Get all Eselon 2 unit names under the given scope name (if the scope is Eselon 1).
     * Otherwise returns the scope name itself.
     */
    public static function getSubUnits($scopeName)
    {
        self::init();

        $scopeLower = strtolower(trim($scopeName));
        if (!isset(self::$refMap[$scopeLower])) {
            return [$scopeName];
        }

        $code = self::$refMap[$scopeLower];
        
        // If the code is an eselon 1 unit, find all eselon 2 units under it
        if (isset(self::$relationMap[$code])) {
            $subCodes = self::$relationMap[$code];
            $subNames = [];
            foreach ($subCodes as $sc) {
                if (isset(self::$nameMap[$sc])) {
                    $subNames[] = self::$nameMap[$sc];
                }
            }
            return $subNames;
        }

        return [$scopeName];
    }
}
