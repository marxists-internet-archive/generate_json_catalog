<?

//--------------------------------------------------------------
// Функция рекурсивного сканирования каталога
//--------------------------------------------------------------
// Параметры:
//   $directory - начальный каталог
//   $callback - функция для обработки найденных файлов
//--------------------------------------------------------------

function scan_recursive($directory, $callback = null, $file) {

    // Привести каталог в канонизированный абсолютный путь
    $directory=realpath($directory);
 
    if ($d=opendir($directory)) {
        while($fname = readdir($d)) {
            if ($fname == '.' || $fname == '..') {
                continue;
            }
            
            if (is_dir($directory.DIRECTORY_SEPARATOR.$fname)) {
                scan_recursive($directory.DIRECTORY_SEPARATOR.$fname, $callback, $file);
            } else {
                // Передать путь файла в callback-функцию
                if ($callback != null && is_callable($callback)) {
                    $callback($directory.DIRECTORY_SEPARATOR.$fname, $file);
                }
            }
        }
        closedir($d);
    }
}


function scan_callback($fname, $file) {
    $PATH = "../";
    static $count = 0;
    
    $path_parts = pathinfo($fname);
    if (($path_parts['extension'] === "html") or($path_parts['extension'] === "htm")){
      $content = file_get_contents($fname);
      $pos_start = strpos($content, "<!-- metaJSON") + 13 > 13 ?  strpos($content, "<!-- metaJSON") + 13 : 0;
      if ($pos_start > 0){
        $pos_end = strpos($content, "metaJSON -->", $pos_start);
          $json = trim(substr($content, $pos_start, $pos_end - $pos_start));
          
          $link = str_replace(dirname(__FILE__)."/", "", $fname);
          
          $temp_arr = json_decode($json, true);
          $temp_arr['link'] = $PATH.$link;
          $json = json_encode($temp_arr, JSON_UNESCAPED_UNICODE);
          
          echo "$PATH$link<br>";
          if($count != 0){
            $json = ",".$json;
          }
          fwrite($file, $json);
          $count++;
          echo 'Обработано! '.$fname.'<br/>';
      } else {
          echo 'Не обработано! '.$fname.'<br/>';
      }
    }
}


if (!$handle = $handle = fopen("catalog.json", "w")) {
    echo "Не могу открыть файл (catalog.json)";
    exit;
}

fwrite($handle, "[");

scan_recursive('.', 'scan_callback', $handle);

fwrite($handle, "]");

fclose($handle);




?>