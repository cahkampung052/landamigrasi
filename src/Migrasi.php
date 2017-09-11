<?php
namespace Cahkampung;

/**
 * Migrasi Database Mysql
 * author : Wahyu Agung Tribawono
 * email : wahyuagun26@gmail.com
 * versi : 1.0
 */

class Migrasi
{
    /** @var $path */
    private $path;

    /** @var $db */
    private $db;

    /**
     * Construct landa migrasi
     * @param $setting
     */
    public function __construct($setting)
    {

        $this->db = mysqli_connect($setting['host'], $setting['username'], $setting['password'], $setting['database']);

        if (mysqli_connect_errno()) {
            echo "Failed to connect to MySQL: " . mysqli_connect_error();
        }
    }

    /**
     * ambil riwayat migrasi dari database
     */
    public function get_migrasi()
    {
        $file = [];

        if (!mysqli_query($this->db, "DESCRIBE migrasi")) {
            mysqli_query($this->db, "
                CREATE TABLE `migrasi` (
                  `id` int(11) NOT NULL,
                  `nama` varchar(200) NOT NULL,
                  `created` datetime NOT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=latin1;
            ");

            mysqli_query($this->db, "
                ALTER TABLE `migrasi` ADD PRIMARY KEY (`id`);
            ");

            mysqli_query($this->db, "
                ALTER TABLE `migrasi` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
            ");

            mysqli_query($this->db, "
                ALTER TABLE `migrasi` CHANGE `created` `created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
            ");
        } else {
            $list = mysqli_query($this->db, "SELECT * FROM migrasi");

            while ($row = mysqli_fetch_array($list, MYSQLI_ASSOC)) {
                $file = $row['nama'];
            }
        }
    }

    public function migrasi()
    {
        
        $filename = 'src/migrasi.sql';
        $templine = '';

        $lines = file($filename);
        foreach ($lines as $line) {
            // Skip it if it's a comment
            if (substr($line, 0, 2) == '--' || $line == '') {
                continue;
            }

            // Add this line to the current segment
            $templine .= $line;
            // If it has a semicolon at the end, it's the end of the query
            if (substr(trim($line), -1, 1) == ';') {
                // Perform the query
                if (!mysqli_query($this->db, $templine)) {
                    print('Error performing query \'<strong>' . $templine . '\': ' . mysql_error() . '<br /><br />');
                }
                // Reset temp variable to empty
                $templine = '';
            }

        }
    }
}
